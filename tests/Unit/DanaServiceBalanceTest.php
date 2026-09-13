<?php

namespace Tests\Unit;

use App\Models\Donasi;
use App\Models\JenisDonasi;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use App\Models\User;
use App\Services\DanaService;
use Tests\TestCase;

/**
 * Invariant keseimbangan DanaService diuji di SQLite :memory:.
 *
 * Angka mentah yang di-hardcode hanya penerimaan (1.000.000) dan
 * persentase hak amil (10%) — sisa asersi memeriksa INVARIANT:
 * surplus_defisit = penerimaan - bagian_amil - penyaluran
 * saldo_akhir     = saldo_awal + surplus_defisit
 */
class DanaServiceBalanceTest extends TestCase
{
    protected function seedSumberDana(float $persentase = 10): SumberDanaPenyaluran
    {
        return SumberDanaPenyaluran::create([
            'nama_sumber_dana' => 'Dana Uji Test', // bukan 'Penyaluran Langsung'
            'aktif' => true,
            'persentase_hak_amil' => $persentase,
        ]);
    }

    protected function seedDonasiVerified(SumberDanaPenyaluran $sumberDana, float $jumlah): Donasi
    {
        $jenis = JenisDonasi::create([
            'nama' => 'Zakat Uji',
            'aktif' => true,
            'sumber_dana_penyaluran_id' => $sumberDana->id,
        ]);

        return Donasi::create([
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => $jumlah,
            'status_konfirmasi' => 'verified',
            'tanggal_donasi' => '2026-01-15',
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);
    }

    protected function seedPenyaluran(SumberDanaPenyaluran $sumberDana, float $jumlah): ProgramPenyaluran
    {
        $pencatat = User::create([
            'name' => 'Pencatat',
            'email' => uniqid().'@test.local',
            'password' => 'password',
        ]);

        return ProgramPenyaluran::create([
            'nama_program' => 'Penyaluran Uji',
            'tanggal_penyaluran' => '2026-01-20',
            'jumlah_dana' => $jumlah,
            'sumber_dana_penyaluran_id' => $sumberDana->id,
            'dicatat_oleh_id' => $pencatat->id,
        ]);
    }

    public function test_laporan_perubahan_dana_seimbang(): void
    {
        $sumberDana = $this->seedSumberDana(10);
        $this->seedDonasiVerified($sumberDana, 1_000_000);

        $laporan = (new DanaService)->getLaporanPerubahanDana('2026-01-01', '2026-01-31');

        $this->assertArrayHasKey($sumberDana->id, $laporan);
        $detail = $laporan[$sumberDana->id];

        // Penerimaan periode berisi donasi verified di atas.
        $this->assertEqualsWithDelta(1_000_000, $detail['penerimaan'], 0.01);

        // Hak amil 10% dari 1.000.000, dibulatkan ke rupiah.
        $this->assertEqualsWithDelta(100_000, $detail['bagian_amil'], 0.01);

        // INVARIANT: surplus/defisit = penerimaan - bagian_amil - penyaluran.
        $this->assertEqualsWithDelta(
            $detail['penerimaan'] - $detail['bagian_amil'] - $detail['penyaluran'],
            $detail['surplus_defisit'],
            0.01
        );

        // INVARIANT: saldo_akhir = saldo_awal + surplus_defisit.
        $this->assertEqualsWithDelta(
            $detail['saldo_awal'] + $detail['surplus_defisit'],
            $detail['saldo_akhir'],
            0.01
        );

        // Tanpa penyaluran dan tanpa saldo awal, saldo akhir harus kongkrit positif.
        $this->assertEqualsWithDelta(0, $detail['saldo_awal'], 0.01);
        $this->assertEqualsWithDelta(0, $detail['penyaluran'], 0.01);
        $this->assertEqualsWithDelta(900_000, $detail['saldo_akhir'], 0.01);

        // Summary global harus konsisten dengan detail satu-satunya.
        $summary = $laporan['summary'];
        $this->assertEqualsWithDelta($detail['penerimaan'], $summary['total_penerimaan'], 0.01);
        $this->assertEqualsWithDelta($detail['bagian_amil'], $summary['total_bagian_amil'], 0.01);
        $this->assertEqualsWithDelta($detail['saldo_akhir'], $summary['total_saldo_akhir'], 0.01);
    }

    public function test_laporan_perubahan_dana_invariant_tetap_dengan_penyaluran(): void
    {
        $sumberDana = $this->seedSumberDana(10);
        $this->seedDonasiVerified($sumberDana, 1_000_000);
        $this->seedPenyaluran($sumberDana, 400_000);

        $laporan = (new DanaService)->getLaporanPerubahanDana('2026-01-01', '2026-01-31');
        $detail = $laporan[$sumberDana->id];

        $this->assertEqualsWithDelta(1_000_000, $detail['penerimaan'], 0.01);
        $this->assertEqualsWithDelta(400_000, $detail['penyaluran'], 0.01);

        $this->assertEqualsWithDelta(
            $detail['penerimaan'] - $detail['bagian_amil'] - $detail['penyaluran'],
            $detail['surplus_defisit'],
            0.01
        );
        $this->assertEqualsWithDelta(
            $detail['saldo_awal'] + $detail['surplus_defisit'],
            $detail['saldo_akhir'],
            0.01
        );
    }

    public function test_saldo_tersedia_raw_boleh_negatif(): void
    {
        $sumberDana = $this->seedSumberDana(10);
        $this->seedDonasiVerified($sumberDana, 1_000_000);
        // Penyaluran melebihi penerimaan: over-distribution.
        $this->seedPenyaluran($sumberDana, 2_000_000);

        $service = new DanaService;

        $raw = $service->getSaldoTersediaRaw($sumberDana->id);

        // 1.000.000 - 100.000 (amil 10%) - 2.000.000 = -1.100.000
        $this->assertLessThan(0, $raw);
        $this->assertEqualsWithDelta(-1_100_000, $raw, 0.01);
    }

    public function test_saldo_tersedia_diolir_ke_nol(): void
    {
        $sumberDana = $this->seedSumberDana(10);
        $this->seedDonasiVerified($sumberDana, 1_000_000);
        $this->seedPenyaluran($sumberDana, 2_000_000);

        $service = new DanaService;

        $this->assertEqualsWithDelta(0, $service->getSaldoTersedia($sumberDana->id), 0.01);
        $this->assertGreaterThanOrEqual(0, $service->getSaldoTersedia($sumberDana->id));
    }
}
