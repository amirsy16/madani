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
    
    // Menggunakan 5 kolom untuk menampilkan 5 statistik (1 baris x 5 kolom)
    protected function getColumns(): int
    {
        return 5;
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

        // --- 5. Hak Amil Bulan Ini ---
        // Formula sama dengan Donasi::getHakAmilAttribute: total_nilai * persentase sumber dana / 100
        $hakAmilBulanIni = (float) Donasi::where('status_konfirmasi', 'verified')
            ->whereMonth('tanggal_donasi', $currentMonth)
            ->whereYear('tanggal_donasi', $currentYear)
            ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->leftJoin('sumber_dana_penyalurans', 'jenis_donasis.sumber_dana_penyaluran_id', '=', 'sumber_dana_penyalurans.id')
            ->sum(DB::raw('(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) * COALESCE(sumber_dana_penyalurans.persentase_hak_amil, 0) / 100'));

        return [
            // 1. Total Donasi Keseluruhan
            Stat::make('Total Donasi', 'Rp ' . number_format($totalDonasiKeseluruhan, 0, ',', '.'))
                ->description('Terverifikasi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // 2. Total Donasi Bulan Ini
            Stat::make('Bulan Ini', 'Rp ' . number_format($totalDonasiBulanIni, 0, ',', '.'))
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            // 3. Total Donatur
            Stat::make('Donatur', number_format($totalDonatur, 0, ',', '.'))
                ->description('Terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            // 4. Total Transaksi
            Stat::make('Transaksi', number_format($totalTransaksi, 0, ',', '.'))
                ->description('Terverifikasi')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('danger'),

            // 5. Hak Amil Bulan Ini
            Stat::make('Hak Amil', 'Rp ' . number_format($hakAmilBulanIni, 0, ',', '.'))
                ->description(Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
        ];
    }

}


