<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TrenDonasiChart extends ApexChartWidget
{
    protected static ?string $chartId = 'trenDonasiChart';

    protected static ?string $heading = 'Tren Donasi';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    // Matikan polling bawaan (5s) agar tidak membebani dashboard
    protected static ?string $pollingInterval = null;

    public ?string $filter = '6_bulan';

    protected function getFilters(): ?array
    {
        return [
            '6_bulan' => '6 Bulan Terakhir',
            '12_bulan' => '12 Bulan Terakhir',
        ];
    }

    protected function getOptions(): array
    {
        $jumlahBulan = ($this->filter ?? '6_bulan') === '12_bulan' ? 12 : 6;

        $labels = [];
        $data = [];

        $totals = StatsCache::remember(
            'tren_donasi_'.($this->filter ?? '6_bulan'),
            function () use ($jumlahBulan) {
                $start = Carbon::now()->subMonths($jumlahBulan - 1)->startOfMonth();
                $end = Carbon::now()->endOfMonth();

                return Donasi::where('status_konfirmasi', 'verified')
                    // Paritas dengan kartu statistik: Penyaluran Langsung
                    // tidak masuk kas organisasi.
                    ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
                    ->whereBetween('tanggal_donasi', [$start->toDateString(), $end->toDateString()])
                    ->select(
                        DB::raw('DATE_FORMAT(tanggal_donasi, "%Y-%m") as bulan'),
                        DB::raw('SUM(COALESCE(jumlah, 0) + COALESCE(perkiraan_nilai_barang, 0)) as total')
                    )
                    ->groupBy('bulan')
                    ->pluck('total', 'bulan')
                    ->all();
            }
        );

        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $data[] = (float) ($totals[$month->format('Y-m')] ?? 0);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 220,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
                'fontFamily' => 'inherit',
            ],
            'series' => [
                [
                    'name' => 'Total Donasi Diterima',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['fontWeight' => 600]],
            ],
            'dataLabels' => ['enabled' => false],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'colors' => ['#800020'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.35,
                    'opacityTo' => 0.05,
                ],
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: (val) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val),
                },
            },
            tooltip: {
                y: {
                    formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                },
            },
        }
        JS);
    }
}
