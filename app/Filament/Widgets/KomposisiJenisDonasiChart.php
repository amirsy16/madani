<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class KomposisiJenisDonasiChart extends ApexChartWidget
{
    protected static ?string $chartId = 'komposisiJenisDonasiChart';

    protected static ?string $heading = 'Komposisi Jenis Donasi';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'tahun_ini';

    // Warna pertama = brand maroon, kedua = gold
    private const PALET = [
        '#800020', '#FFD700', '#10B981', '#3B82F6', '#F59E0B',
        '#EF4444', '#8B5CF6', '#14B8A6', '#64748B', '#EC4899',
        '#84CC16', '#06B6D4',
    ];

    public function getSubheading(): string | Htmlable | null
    {
        return 'Total donasi terverifikasi per jenis donasi — ' . $this->labelPeriode();
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
            'komposisi_jenis_donasi_' . ($this->filter ?? 'tahun_ini'),
            fn () => $this->computeRows()
        );

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 220,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
            ],
            'series' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all(),
            'labels' => $rows->pluck('nama')->all(),
            'colors' => array_slice(self::PALET, 0, max($rows->count(), 1)),
            'legend' => [
                'position' => 'right',
                'fontFamily' => 'inherit',
            ],
            'dataLabels' => ['enabled' => false],
            'stroke' => [
                'width' => 2,
                'colors' => ['#ffffff'],
            ],
        ];
    }

    private function computeRows(): mixed
    {
        [$start, $end] = $this->rentangPeriode();

        $query = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'));

        if ($start && $end) {
            $query->whereBetween('tanggal_donasi', [$start->toDateString(), $end->toDateString()]);
        }

        return $query
            ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->select(
                'jenis_donasis.nama',
                DB::raw('SUM(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as total')
            )
            ->groupBy('jenis_donasis.nama')
            ->orderByDesc('total')
            ->get();
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            tooltip: {
                y: {
                    formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                },
            },
        }
        JS);
    }
}
