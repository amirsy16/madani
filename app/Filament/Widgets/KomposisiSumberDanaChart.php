<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Services\StatsCache;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class KomposisiSumberDanaChart extends ApexChartWidget
{
    protected static ?string $chartId = 'komposisiSumberDanaChart';

    protected static ?string $heading = 'Komposisi Sumber Dana';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 1;

    protected static ?int $contentHeight = 220;

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'tahun_ini';

    // Warna pertama = brand maroon, kedua = gold
    private const PALET = [
        '#800020', '#FFD700', '#10B981', '#3B82F6', '#F59E0B',
        '#EF4444', '#8B5CF6', '#14B8A6', '#64748B',
    ];

    public function getSubheading(): string | Htmlable | null
    {
        return 'Total donasi terverifikasi per sumber dana — ' . $this->labelPeriode();
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
            'komposisi_sumber_dana_' . ($this->filter ?? 'tahun_ini'),
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
            'labels' => $rows->pluck('sumber')->all(),
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

        $query = Donasi::where('donasis.status_konfirmasi', 'verified');

        if ($start && $end) {
            $query->whereBetween('donasis.tanggal_donasi', [$start->toDateString(), $end->toDateString()]);
        }

        return $query
            ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->leftJoin('sumber_dana_penyalurans', 'jenis_donasis.sumber_dana_penyaluran_id', '=', 'sumber_dana_penyalurans.id')
            ->select(
                DB::raw('COALESCE(sumber_dana_penyalurans.nama_sumber_dana, "Tanpa Sumber Dana") as sumber'),
                DB::raw('SUM(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as total')
            )
            ->groupBy('sumber')
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
