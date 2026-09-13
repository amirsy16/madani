<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Models\Donatur;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Services\StatsCache;

class ZakatStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    // Menggunakan 4 kolom untuk menampilkan 4 statistik (1 baris x 4 kolom)
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        return StatsCache::remember('zakat_overview', function () {
            return $this->computeStats();
        });
    }

    protected function computeStats(): array
    {
        // --- Current period data ---
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // --- 1. Total Donasi Keseluruhan ---
        // Exclude "Penyaluran Langsung" karena tidak masuk kas organisasi
        $totalDonasiKeseluruhan = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
            ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));

        // --- 2. Total Donasi Bulan Ini ---
        // Exclude "Penyaluran Langsung" untuk konsistensi
        $totalDonasiBulanIni = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
            ->whereMonth('tanggal_donasi', $currentMonth)
            ->whereYear('tanggal_donasi', $currentYear)
            ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));

        // --- 3. Total Donatur ---
        $totalDonatur = Donatur::count();

        // --- 4. Total Transaksi ---
        // Exclude "Penyaluran Langsung" untuk konsistensi
        $totalTransaksi = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
            ->count();

        return [
            // 1. Total Donasi Keseluruhan
            Stat::make('Total Donasi', 'Rp ' . number_format($totalDonasiKeseluruhan, 0, ',', '.'))
                ->description('Semua donasi terverifikasi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // 2. Total Donasi Bulan Ini
            Stat::make('Bulan Ini', 'Rp ' . number_format($totalDonasiBulanIni, 0, ',', '.'))
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            // 3. Total Donatur
            Stat::make('Donatur', number_format($totalDonatur, 0, ',', '.'))
                ->description('Total donatur terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            // 4. Total Transaksi
            Stat::make('Transaksi', number_format($totalTransaksi, 0, ',', '.'))
                ->description('Total transaksi terverifikasi')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('danger'),
        ];
    }

}


