<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DonasiHarianChart extends ApexChartWidget
{
    protected static ?string $chartId = 'donasiHarianChart';

    protected static ?string $heading = 'Donasi Harian';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'bulan_ini';

    public function getSubheading(): string|Htmlable|null
    {
        $bulan = ($this->filter ?? 'bulan_ini') === 'bulan_lalu'
            ? Carbon::now()->subMonth()
            : Carbon::now();

        return 'Total donasi terverifikasi per tanggal — '.$bulan->translatedFormat('F Y');
    }

    protected function getFilters(): ?array
    {
        return [
            'bulan_ini' => 'Bulan Ini',
            'bulan_lalu' => 'Bulan Lalu',
        ];
    }

    protected function getOptions(): array
    {
        $bulan = ($this->filter ?? 'bulan_ini') === 'bulan_lalu'
            ? Carbon::now()->subMonth()
            : Carbon::now();

        $labels = [];
        $data = [];

        $totals = StatsCache::remember(
            'donasi_harian_'.($this->filter ?? 'bulan_ini'),
            function () use ($bulan) {
                $start = $bulan->copy()->startOfMonth();
                $end = $bulan->copy()->endOfMonth();

                return Donasi::where('status_konfirmasi', 'verified')
                    // Paritas dengan kartu statistik: Penyaluran Langsung
                    // tidak masuk kas organisasi.
                    ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
                    ->whereBetween('tanggal_donasi', [$start->toDateString(), $end->toDateString()])
                    ->select(
                        DB::raw('DATE_FORMAT(tanggal_donasi, "%Y-%m-%d") as tanggal'),
                        DB::raw('SUM(COALESCE(jumlah, 0) + COALESCE(perkiraan_nilai_barang, 0)) as total')
                    )
                    ->groupBy('tanggal')
                    ->pluck('total', 'tanggal')
                    ->all();
            }
        );

        for ($hari = $bulan->copy()->startOfMonth(); $hari <= $bulan->copy()->endOfMonth(); $hari->addDay()) {
            $labels[] = $hari->format('d');
            $data[] = (float) ($totals[$hari->format('Y-m-d')] ?? 0);
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
                    'name' => 'Total Donasi',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['fontWeight' => 600]],
                'tickAmount' => 10,
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
            xaxis: {
                labels: {
                    formatter: (val) => 'Tgl ' + val,
                },
            },
            yaxis: {
                labels: {
                    formatter: (val) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val),
                },
            },
            tooltip: {
                x: {
                    formatter: (val) => 'Tanggal ' + val,
                },
                y: {
                    formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                },
            },
        }
        JS);
    }
}
