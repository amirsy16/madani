<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopFundraiserChart extends ApexChartWidget
{
    protected static ?string $chartId = 'topFundraiserChart';

    protected static ?string $heading = 'Top 5 Fundraiser';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'tahun_ini';

    public function getSubheading(): string | Htmlable | null
    {
        return 'Total donasi terverifikasi per fundraiser — ' . $this->labelPeriode();
    }

    protected function getFilters(): ?array
    {
        return [
            'tahun_ini' => 'Tahun Ini',
            'bulan_ini' => 'Bulan Ini',
            'tahun_lalu' => 'Tahun Lalu',
            'semua' => 'Semua Waktu',
        ];
    }

    private function labelPeriode(): string
    {
        return match ($this->filter ?? 'tahun_ini') {
            'bulan_ini' => Carbon::now()->translatedFormat('F Y'),
            'tahun_lalu' => Carbon::now()->subYear()->translatedFormat('Y'),
            'semua' => 'Semua Waktu',
            default => Carbon::now()->translatedFormat('Y'),
        };
    }

    private function rentangPeriode(): array
    {
        return match ($this->filter ?? 'tahun_ini') {
            'bulan_ini' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'tahun_lalu' => [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()],
            'semua' => [null, null],
            default => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
        };
    }

    protected function getOptions(): array
    {
        $rows = StatsCache::remember(
            'top_fundraiser_' . ($this->filter ?? 'tahun_ini'),
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
                    'data' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all(),
                ],
            ],
            'xaxis' => [
                'categories' => $rows->pluck('nama_fundraiser')->all(),
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

        $query = Donasi::where('donasis.status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'));

        if ($start && $end) {
            $query->whereBetween('donasis.tanggal_donasi', [$start->toDateString(), $end->toDateString()]);
        }

        return $query
            ->join('fundraisers', 'donasis.fundraiser_id', '=', 'fundraisers.id')
            ->select(
                'fundraisers.nama_fundraiser',
                DB::raw('SUM(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as total')
            )
            ->groupBy('fundraisers.id', 'fundraisers.nama_fundraiser')
            ->orderByDesc('total')
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
