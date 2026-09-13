<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FundraiserPerformanceWidget;
use Filament\Pages\Page;
use App\Filament\Widgets\RingkasanStatistikUtama;
use App\Filament\Widgets\MetodePembayaranChart;
use App\Filament\Widgets\TopDonaturWidget;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class AnalisisData extends Page
{
    use HasPageShield;
    
    // Ikon navigasi (pilih dari Heroicons)
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    // Label navigasi di sidebar
    protected static ?string $navigationLabel = 'Statistik';

    // Judul halaman
    protected static ?string $title = 'Pusat Statistik Data';

    // Grup navigasi (opsional)
    protected static ?string $navigationGroup = 'Laporan & Keuangan';

    // Urutan menu
    protected static ?int $navigationSort = 1;

    // File view Blade yang akan digunakan
    protected static string $view = 'filament.pages.analisis-data';


    protected function getHeaderWidgets(): array
    {
        return [
            RingkasanStatistikUtama::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TopDonaturWidget::class,
            MetodePembayaranChart::class,
            FundraiserPerformanceWidget::class,
        ];
    }
}
