<?php

namespace Tests\Feature;

use App\Filament\Widgets\FundraiserPerformanceWidget;
use App\Filament\Widgets\TopDonaturWidget;
use App\Models\Donasi;
use App\Models\Donatur;
use App\Models\Fundraiser;
use App\Models\JenisDonasi;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresi bug filter Top N di halaman Statistik (laporan pengguna:
 * ganti Top 10 ke Top 5 tak berubah, ganti ke Top 15 baru menjadi Top 5).
 *
 * Akarnya: query tabel dibangun di fase booted dengan limit properti lama,
 * sementara closure filter baru menulis properti saat fase render. Kini
 * nilai filter dibaca langsung dari state filter ter-hydrate sehingga
 * berubah seketika dalam interaksi yang sama.
 */
class FilterTopDonaturTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_filter_top_n_diterapkan_seketika_tanpa_lag(): void
    {
        $jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);

        // 20 donatur, masing-masing satu donasi terverifikasi bernilai unik
        // agar peringkat deterministik.
        Donatur::factory()->count(20)->create()->each(function (Donatur $donatur, $i) use ($jenis) {
            Donasi::factory()->create([
                'donatur_id' => $donatur->id,
                'jenis_donasi_id' => $jenis->id,
                'jumlah' => 100000 * ($i + 1),
                'status_konfirmasi' => 'verified',
            ]);
        });

        $widget = Livewire::test(TopDonaturWidget::class);

        // Bawaan: Top 10.
        $this->assertSame(10, $widget->instance()->getFilteredTableQuery()->get()->count());

        // Ganti ke Top 5 — harus langsung 5 dalam interaksi yang sama.
        $widget->filterTable('top_count', 5);
        $this->assertSame(5, $widget->instance()->getFilteredTableQuery()->get()->count());
        // Judul widget mengikuti nilai terbaru (dievaluasi dari method,
        // karena view tabel bawaan tidak merender getHeading).
        $this->assertSame('Top 5 Donatur - Analisis Lengkap', $widget->instance()->getHeading());

        // Ganti lagi ke Top 15 — langsung 15, bukan nilai interaksi sebelumnya.
        $widget->filterTable('top_count', 15);
        $this->assertSame(15, $widget->instance()->getFilteredTableQuery()->get()->count());
        $this->assertSame('Top 15 Donatur - Analisis Lengkap', $widget->instance()->getHeading());
    }

    public function test_filter_periode_dan_limit_widget_fundraiser_seketika(): void
    {
        $jenis = JenisDonasi::factory()->create(['apakah_barang' => false]);

        // 8 fundraiser, masing-masing satu donasi terverifikasi tahun ini.
        foreach (range(1, 8) as $i) {
            $fundraiser = Fundraiser::create([
                'nama_fundraiser' => 'Fundraiser Uji '.$i,
                'aktif' => true,
            ]);
            Donasi::factory()->create([
                'fundraiser_id' => $fundraiser->id,
                'jenis_donasi_id' => $jenis->id,
                'jumlah' => 100000 * $i,
                'tanggal_donasi' => now()->toDateString(),
                'status_konfirmasi' => 'verified',
            ]);
        }

        $widget = Livewire::test(FundraiserPerformanceWidget::class);
        $this->assertSame(8, $widget->instance()->getFilteredTableQuery()->get()->count());

        $widget->filterTable('limit', 5);
        $this->assertSame(5, $widget->instance()->getFilteredTableQuery()->get()->count());

        // Periode ikut seketika: donasi uji bertanggal hari ini sehingga
        // filter Bulan Ini tetap menampilkan 5 baris teratas.
        $widget->filterTable('time_period', 'current_month');
        $this->assertSame(5, $widget->instance()->getFilteredTableQuery()->get()->count());
        $this->assertStringContainsString('Top 5', $widget->instance()->getHeading());
    }
}
