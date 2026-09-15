<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Models\JenisPenggunaanHakAmil;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Services\StatsCache;

class HakAmilOverviewWidget extends BaseWidget
{
    // Ringkasan hak amil sudah ditampilkan sebagai kotak ke-5 di ZakatStatsOverview,
    // widget ini tidak lagi tampil otomatis di dashboard.
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        return StatsCache::remember('hak_amil_overview', function () {
            return $this->computeStats();
        });
    }

    protected function computeStats(): array
    {
        // Hitung total donasi bulan ini
        $totalDonasiBulanIni = Donasi::whereMonth('tanggal_donasi', now()->month)
            ->whereYear('tanggal_donasi', now()->year)
            ->where('status_konfirmasi', 'verified')
            ->sum(DB::raw('COALESCE(jumlah, 0) + COALESCE(perkiraan_nilai_barang, 0)'));

        // Hitung total hak amil bulan ini — satu query aggregate
        // (formula sama dengan Donasi::getHakAmilAttribute: total_nilai * persentase sumber dana / 100)
        $totalHakAmilBulanIni = (float) Donasi::whereMonth('tanggal_donasi', now()->month)
            ->whereYear('tanggal_donasi', now()->year)
            ->where('status_konfirmasi', 'verified')
            ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->leftJoin('sumber_dana_penyalurans', 'jenis_donasis.sumber_dana_penyaluran_id', '=', 'sumber_dana_penyalurans.id')
            ->sum(DB::raw('(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) * COALESCE(sumber_dana_penyalurans.persentase_hak_amil, 0) / 100'));

        // Hitung total transaksi bulan ini
        $totalTransaksiBulanIni = Donasi::whereMonth('tanggal_donasi', now()->month)
            ->whereYear('tanggal_donasi', now()->year)
            ->where('status_konfirmasi', 'verified')
            ->count();

        // Hitung persentase hak amil dari total donasi
        $persentaseHakAmil = $totalDonasiBulanIni > 0 
            ? ($totalHakAmilBulanIni / $totalDonasiBulanIni) * 100 
            : 0;

        return [
            Stat::make('Total Donasi Bulan Ini', 'Rp ' . number_format($totalDonasiBulanIni, 0, ',', '.'))
                ->description('Donasi yang telah dikonfirmasi')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Hak Amil Bulan Ini', 'Rp ' . number_format($totalHakAmilBulanIni, 0, ',', '.'))
                ->description('Total hak amil bulan ini')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),

            Stat::make('Transaksi Bulan Ini', number_format($totalTransaksiBulanIni, 0, ',', '.'))
                ->description('Total transaksi terverifikasi')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),

            Stat::make('Persentase Hak Amil', number_format($persentaseHakAmil, 2) . '%')
                ->description('Dari total donasi bulan ini')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('primary'),
        ];
    }
}
