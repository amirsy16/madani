<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DonaturAktifChart extends ApexChartWidget
{
    protected static ?string $chartId = 'donaturAktifChart';

    protected static ?string $heading = 'Donatur Aktif';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    protected static ?string $pollingInterval = null;

    public ?string $filter = '6_bulan';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Jumlah donatur unik yang berdonasi terverifikasi per bulan — '.(($this->filter ?? '6_bulan') === '12_bulan' ? '12 bulan terakhir' : '6 bulan terakhir');
    }

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
            'donatur_aktif_'.($this->filter ?? '6_bulan'),
            function () use ($jumlahBulan) {
                $start = Carbon::now()->subMonths($jumlahBulan - 1)->startOfMonth();
                $end = Carbon::now()->endOfMonth();

                return Donasi::where('status_konfirmasi', 'verified')
                    ->whereNotNull('donatur_id')
                    // Paritas dengan kartu statistik: Penyaluran Langsung
                    // tidak masuk kas organisasi.
                    ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
                    ->whereBetween('tanggal_donasi', [$start->toDateString(), $end->toDateString()])
                    ->select(
                        DB::raw('DATE_FORMAT(tanggal_donasi, "%Y-%m") as bulan'),
                        DB::raw('COUNT(DISTINCT donatur_id) as total')
                    )
                    ->groupBy('bulan')
                    ->pluck('total', 'bulan')
                    ->all();
            }
        );

        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $data[] = (int) ($totals[$month->format('Y-m')] ?? 0);
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 220,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
            ],
            'series' => [
                [
                    'name' => 'Donatur Aktif',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['fontWeight' => 600]],
            ],
            'colors' => ['#800020'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '55%',
                ],
            ],
            'dataLabels' => ['enabled' => false],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: (val) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(val),
                },
            },
        }
        JS);
    }
}
