<?php

namespace App\Filament\Widgets;

use App\Models\Donatur;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopDonaturChart extends ApexChartWidget
{
    protected static ?string $chartId = 'topDonaturChart';

    protected static ?string $heading = 'Top 5 Donatur';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'bulan_ini';

    public function getSubheading(): string | Htmlable | null
    {
        return 'Total donasi terverifikasi per donatur — ' . $this->labelPeriode();
    }

    protected function getFilters(): ?array
    {
        return [
            'bulan_ini' => 'Bulan Ini',
            'tahun_ini' => 'Tahun Ini',
            'tahun_lalu' => 'Tahun Lalu',
            'semua' => 'Semua Waktu',
        ];
    }

    private function labelPeriode(): string
    {
        return match ($this->filter ?? 'bulan_ini') {
            'tahun_ini' => Carbon::now()->translatedFormat('Y'),
            'tahun_lalu' => Carbon::now()->subYear()->translatedFormat('Y'),
            'semua' => 'Semua Waktu',
            default => Carbon::now()->translatedFormat('F Y'),
        };
    }

    private function rentangPeriode(): array
    {
        return match ($this->filter ?? 'bulan_ini') {
            'tahun_ini' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'tahun_lalu' => [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()],
            'semua' => [null, null],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    protected function getOptions(): array
    {
        $rows = StatsCache::remember(
            'top_donatur_' . ($this->filter ?? 'bulan_ini'),
            fn () => $this->computeRows()
        );

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
                    'name' => 'Total Donasi',
                    'data' => $rows->pluck('total_donasi')->map(fn ($v) => (float) $v)->all(),
                ],
            ],
            'xaxis' => [
                'categories' => $rows->pluck('nama')->all(),
                'labels' => ['style' => ['fontWeight' => 600]],
            ],
            'colors' => ['#FFD700', '#EAB308', '#B91C1C', '#9F1239', '#800020'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '55%',
                    'horizontal' => true,
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'legend' => ['show' => false],
        ];
    }

    private function computeRows(): mixed
    {
        [$start, $end] = $this->rentangPeriode();

        $query = Donatur::query()
            ->select([
                'donaturs.id',
                'donaturs.nama',
                DB::raw('SUM(donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)) as total_donasi'),
            ])
            ->join('donasis', 'donaturs.id', '=', 'donasis.donatur_id')
            ->where('donasis.status_konfirmasi', 'verified');

        if ($start && $end) {
            $query->whereBetween('donasis.tanggal_donasi', [$start->toDateString(), $end->toDateString()]);
        }

        return $query
            ->groupBy('donaturs.id', 'donaturs.nama')
            ->orderByDesc('total_donasi')
            ->limit(5)
            ->get();
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            xaxis: {
                labels: {
                    formatter: (val) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val),
                },
            },
            tooltip: {
                x: {
                    formatter: (val, opts) => opts.w.globals.labels[opts.dataPointIndex],
                },
                y: {
                    formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                },
            },
        }
        JS);
    }
}
