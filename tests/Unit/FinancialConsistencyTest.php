<?php

namespace Tests\Unit;

use App\Filament\Resources\DonasiResource\Widgets\DonasiOverviewStats;
use App\Filament\Widgets\HakAmilOverviewWidget;
use App\Filament\Widgets\ZakatStatsOverview;
use App\Models\Donasi;
use App\Models\JenisDonasi;
use App\Models\SumberDanaPenyaluran;
use Tests\TestCase;

class FinancialConsistencyTest extends TestCase
{
    protected function seedSumber(string $nama, float $persentase = 10): SumberDanaPenyaluran
    {
        return SumberDanaPenyaluran::create([
            'nama_sumber_dana' => $nama,
            'aktif' => true,
            'persentase_hak_amil' => $persentase,
        ]);
    }

    protected function seedDonasi(SumberDanaPenyaluran $sumber, string $jenisNama, float $jumlah, bool $barang = false): Donasi
    {
        $jenis = JenisDonasi::create([
            'nama' => $jenisNama,
            'aktif' => true,
            'apakah_barang' => $barang,
            'sumber_dana_penyaluran_id' => $sumber->id,
        ]);

        return Donasi::create([
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => $barang ? 0 : $jumlah,
            'perkiraan_nilai_barang' => $barang ? $jumlah : null,
            'status_konfirmasi' => 'verified',
            'tanggal_donasi' => now()->toDateString(),
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);
    }

    protected function computeStats(object $widget): array
    {
        $m = new \ReflectionMethod($widget, 'computeStats');
        $m->setAccessible(true);

        return $m->invoke($widget);
    }

    protected function statValue(array $stats, string $label): ?string
    {
        foreach ($stats as $stat) {
            if ((string) $stat->getLabel() === $label) {
                return (string) $stat->getValue();
            }
        }

        return null;
    }

    protected function rpToFloat(?string $rp): float
    {
        if ($rp === null) {
            return -1;
        }

        return (float) preg_replace('/[^0-9]/', '', $rp);
    }

    public function test_donasi_overview_exclude_penyaluran_langsung(): void
    {
        $normal = $this->seedSumber('Dana Uji Konsisten');
        $langsung = $this->seedSumber('Penyaluran Langsung', 0);
        $this->seedDonasi($normal, 'Zakat Uji Konsisten', 1_000_000);
        $this->seedDonasi($langsung, 'Penyaluran Langsung', 500_000);

        $stats = $this->computeStats(new DonasiOverviewStats);
        $total = $this->rpToFloat($this->statValue($stats, 'Total Donasi Keseluruhan'));

        // Harus 1jt (exclude langsung), bukan 1,5jt.
        $this->assertEqualsWithDelta(1_000_000, $total, 0.01);
    }

    public function test_hak_amil_widget_exclude_penyaluran_langsung(): void
    {
        $normal = $this->seedSumber('Dana Uji Amil', 10);
        $langsung = $this->seedSumber('Penyaluran Langsung', 0);
        $this->seedDonasi($normal, 'Zakat Uji Amil', 1_000_000);
        $this->seedDonasi($langsung, 'Penyaluran Langsung', 500_000);

        $zakat = $this->computeStats(new ZakatStatsOverview);
        $hakAmil = $this->rpToFloat($this->statValue($zakat, 'Hak Amil'));
        $this->assertEqualsWithDelta(100_000, $hakAmil, 0.01);

        $amilWidget = $this->computeStats(new HakAmilOverviewWidget);
        $hakAmil2 = $this->rpToFloat($this->statValue($amilWidget, 'Hak Amil Bulan Ini'));
        $this->assertEqualsWithDelta(100_000, $hakAmil2, 0.01);

        // Total donasi di widget amil juga harus exclude langsung.
        $totalAmil = $this->rpToFloat($this->statValue($amilWidget, 'Total Donasi Bulan Ini'));
        $this->assertEqualsWithDelta(1_000_000, $totalAmil, 0.01);
    }

    public function test_model_normalisasi_uang_vs_barang(): void
    {
        $sumber = $this->seedSumber('Dana Uji Normalisasi');

        // Donasi barang yang (salah) isi dua kolom → jumlah dipaksa 0.
        $jenisBarang = JenisDonasi::create([
            'nama' => 'Logistik Uji Norm',
            'aktif' => true,
            'apakah_barang' => true,
            'sumber_dana_penyaluran_id' => $sumber->id,
        ]);
        $d1 = Donasi::create([
            'jenis_donasi_id' => $jenisBarang->id,
            'jumlah' => 500_000,
            'perkiraan_nilai_barang' => 700_000,
            'status_konfirmasi' => 'verified',
            'tanggal_donasi' => now()->toDateString(),
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);
        $this->assertEquals(0, (float) $d1->refresh()->jumlah);

        // Donasi uang yang (salah) isi nilai barang → barang di-null-kan.
        $jenisUang = JenisDonasi::create([
            'nama' => 'Zakat Uji Norm',
            'aktif' => true,
            'apakah_barang' => false,
            'sumber_dana_penyaluran_id' => $sumber->id,
        ]);
        $d2 = Donasi::create([
            'jenis_donasi_id' => $jenisUang->id,
            'jumlah' => 300_000,
            'perkiraan_nilai_barang' => 200_000,
            'status_konfirmasi' => 'verified',
            'tanggal_donasi' => now()->toDateString(),
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);
        $this->assertNull($d2->refresh()->perkiraan_nilai_barang);
    }

    public function test_detail_amil_memakai_tabel_yang_benar(): void
    {
        $jenisAmil = \App\Models\JenisPenggunaanHakAmil::create([
            'nama' => 'Operasional Uji',
        ]);
        $user = \App\Models\User::create([
            'name' => 'Amil Tester',
            'email' => uniqid().'@test.local',
            'password' => 'password',
        ]);
        \App\Models\PenggunaanHakAmil::create([
            'tanggal' => now()->toDateString(),
            'jenis_penggunaan_hak_amil_id' => $jenisAmil->id,
            'keterangan' => 'Uji',
            'jumlah' => 250_000,
            'user_id' => $user->id,
        ]);

        $svc = new \App\Services\DanaService;
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $byJenis = $svc->getDetailPenggunaanHakAmilByJenis($start, $end);
        $this->assertNotEmpty($byJenis, 'getDetailPenggunaanHakAmilByJenis harus menemukan data.');
        $this->assertEqualsWithDelta(250_000, (float) $byJenis[0]['total_jumlah'], 0.01);

        $detail = $svc->getDetailJenisPenggunaanAmil($start, $end);
        $this->assertNotEmpty($detail, 'getDetailJenisPenggunaanAmil harus baca penggunaan_hak_amils.');
        $this->assertEqualsWithDelta(250_000, (float) $detail[0]['jumlah'], 0.01);
    }

    public function test_cache_bulan_berganti_hitung_ulang(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
        // 7 menit sebelum pergantian bulan: masih dalam TTL 10 menit cache.
        \Carbon\Carbon::setTestNow('2026-01-31 23:55:00');
        try {
            $sumber = $this->seedSumber('Dana Uji Cache');
            $this->seedDonasi($sumber, 'Zakat Uji Cache', 1_000_000);

            $getStats = function (object $w) {
                $m = new \ReflectionMethod($w, 'getStats');
                $m->setAccessible(true);

                return $m->invoke($w);
            };

            $jan = $getStats(new \App\Filament\Widgets\ZakatStatsOverview);
            $this->assertEqualsWithDelta(1_000_000, $this->rpToFloat($this->statValue($jan, 'Bulan Ini')), 0.01);

            // Lewat tengah malam (masih dalam TTL): angka bulan ini harus 0, bukan sisa cache Januari.
            \Carbon\Carbon::setTestNow('2026-02-01 00:02:00');
            $feb = $getStats(new \App\Filament\Widgets\ZakatStatsOverview);
            $this->assertEqualsWithDelta(0, $this->rpToFloat($this->statValue($feb, 'Bulan Ini')), 0.01);
        } finally {
            \Carbon\Carbon::setTestNow();
            \Illuminate\Support\Facades\Cache::flush();
        }
    }

    public function test_nilai_barang_tidak_double_count(): void
    {
        $sumber = $this->seedSumber('Dana Uji Barang');
        // Data kotor: dua kolom terisi.
        $jenis = JenisDonasi::create([
            'nama' => 'Logistik Uji',
            'aktif' => true,
            'apakah_barang' => true,
            'sumber_dana_penyaluran_id' => $sumber->id,
        ]);
        Donasi::create([
            'jenis_donasi_id' => $jenis->id,
            'jumlah' => 500_000,
            'perkiraan_nilai_barang' => 700_000,
            'status_konfirmasi' => 'verified',
            'tanggal_donasi' => now()->toDateString(),
            'nomor_transaksi_unik' => 'TRX-'.uniqid(),
        ]);

        // Opsi B: satu transaksi = 700rb, bukan 1,2jt.
        $nilai = (float) Donasi::where('status_konfirmasi', 'verified')
            ->sum(\DB::raw('CASE WHEN COALESCE(perkiraan_nilai_barang,0)>0 THEN COALESCE(perkiraan_nilai_barang,0) ELSE COALESCE(jumlah,0) END'));
        $this->assertEqualsWithDelta(700_000, $nilai, 0.01);
    }
}
