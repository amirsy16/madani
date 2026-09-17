<?php

namespace Tests\Unit;

use App\Models\Donasi;
use App\Models\JenisDonasi;
use App\Models\JenisPenggunaanHakAmil;
use App\Models\PenggunaanHakAmil;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use App\Models\User;
use App\Services\DanaService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaldoGuardTest extends TestCase
{
    protected function seedSaldo(float $donasi = 1_000_000, float $persen = 10): SumberDanaPenyaluran
    {
        $sumber = SumberDanaPenyaluran::create([
            'nama_sumber_dana' => 'Dana Uji Guard',
            'aktif' => true,
            'persentase_hak_amil' => $persen,
        ]);
        $jenis = JenisDonasi::create([
            'nama' => 'Zakat Uji Guard',
            'aktif' => true,
            'sumber_dana_penyaluran_id' => $sumber->id,
        ]);
        Donasi::create([
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => $donasi,
            'status_konfirmasi' => 'verified',
            'tanggal_donasi' => '2026-01-15',
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);

        return $sumber;
    }

    public function test_assert_cukup_saldo_menolak_kelebihan(): void
    {
        $sumber = $this->seedSaldo(); // raw = 900rb
        $svc = new DanaService;

        // Batas pas: lolos.
        $svc->assertCukupSaldo($sumber->id, 900_000);
        $this->assertTrue(true);

        // Lebih 1 rupiah: ditolak.
        try {
            $svc->assertCukupSaldo($sumber->id, 900_001);
            $this->fail('Harus melempar ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('jumlah_dana', $e->errors());
        }
    }

    public function test_assert_cukup_saldo_memperhitungkan_nilai_lama_saat_edit(): void
    {
        $sumber = $this->seedSaldo();
        $pencatat = User::create(['name' => 'X', 'email' => uniqid().'@t.local', 'password' => 'password']);
        $existing = ProgramPenyaluran::create([
            'nama_program' => 'Uji',
            'tanggal_penyaluran' => '2026-01-20',
            'jumlah_dana' => 400_000,
            'sumber_dana_penyaluran_id' => $sumber->id,
            'dicatat_oleh_id' => $pencatat->id,
        ]);
        // raw kini 500rb; edit 400rb -> 500rb harus lolos (500rb <= 500rb+400rb).
        (new DanaService)->assertCukupSaldo($sumber->id, 500_000, (float) $existing->jumlah_dana);
        $this->assertTrue(true);

        try {
            (new DanaService)->assertCukupSaldo($sumber->id, 900_001, (float) $existing->jumlah_dana);
            $this->fail('Harus melempar ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('jumlah_dana', $e->errors());
        }
    }

    public function test_penggunaan_amil_tidak_boleh_melebihi_sisa(): void
    {
        $this->seedSaldo(); // teoritis amil = 100rb
        $svc = new DanaService;
        $this->assertEqualsWithDelta(100_000, $svc->getSisaHakAmil(), 0.01);

        $svc->assertCukupHakAmil(100_000);
        $this->assertTrue(true);

        try {
            $svc->assertCukupHakAmil(100_001);
            $this->fail('Harus melempar ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('jumlah', $e->errors());
        }

        // Setelah pakai 60rb, sisa 40rb.
        $jenis = JenisPenggunaanHakAmil::create(['nama' => 'Uji Amil']);
        $user = User::create(['name' => 'Y', 'email' => uniqid().'@t.local', 'password' => 'password']);
        $pakai = PenggunaanHakAmil::create([
            'tanggal' => '2026-01-20',
            'jenis_penggunaan_hak_amil_id' => $jenis->id,
            'jumlah' => 60_000,
            'user_id' => $user->id,
        ]);
        $this->assertEqualsWithDelta(40_000, $svc->getSisaHakAmil(), 0.01);
        // Edit record itu sendiri ke 90rb harus lolos (90rb <= 40rb+60rb).
        $svc->assertCukupHakAmil(90_000, $pakai->id);
        $this->assertTrue(true);
    }
}
