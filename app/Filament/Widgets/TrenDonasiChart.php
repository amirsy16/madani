<?php

namespace App\Filament\Widgets;

use App\Models\Donasi; // Pastikan model Donasi Anda sudah benar
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Services\StatsCache;

class TrenDonasiChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Donasi (6 Bulan Terakhir)';

    protected static ?string $description = 'Menampilkan total donasi (uang dan nilai barang) terverifikasi per bulan.';

    protected static ?int $sort = 2;
    
    // Setengah lebar (bersebelahan dengan widget lain)
    protected int | string | array $columnSpan = 1;

    

    // Interval refresh (opsional), misalnya '5s', '10s', '30s', '1m'
    // protected static ?string $pollingInterval = '30s';

    // Warna default untuk chart (opsional: 'primary', 'success', 'danger', 'warning', 'info', 'gray')
    // Anda juga bisa mengatur warna per dataset di getData()
    // protected ?string $chartColor = 'primary';

    protected function getData(): array
    {
        // Satu query GROUP BY bulan untuk 6 bulan terakhir, di-cache
        return StatsCache::remember('tren_donasi_6bulan', function () {
            return $this->computeData();
        });
    }

    protected function computeData(): array
    {
        $labels = [];
        $data = [];

        $start = Carbon::now()->subMonths(5)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $totals = Donasi::where('status_konfirmasi', 'verified')
            ->whereBetween('tanggal_donasi', [$start->toDateString(), $end->toDateString()])
            ->select(
                DB::raw('DATE_FORMAT(tanggal_donasi, "%Y-%m") as bulan'),
                DB::raw('SUM(COALESCE(jumlah, 0) + COALESCE(perkiraan_nilai_barang, 0)) as total')
            )
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('F Y');
            $data[] = (float) ($totals[$month->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Donasi Diterima', // Label untuk legenda dataset
                    'data' => $data, // Data numerik untuk grafik
                    'fill' => 'start', // Memberi area fill di bawah garis grafik
                    'borderColor' => 'rgb(59, 130, 246)', // Warna garis (biru Tailwind)
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // Warna area fill dengan transparansi
                    'tension' => 0.3, // Membuat garis sedikit melengkung (opsional)
                ],
                // Anda bisa menambahkan dataset lain di sini jika perlu, misalnya:
                // [
                //     'label' => 'Target Donasi',
                //     'data' => [5000000, 6000000, 5500000, 7000000, 6500000, 7500000], // Contoh data target
                //     'borderColor' => 'rgb(239, 68, 68)', // Warna garis merah
                // ],
            ],
            'labels' => $labels, // Label untuk sumbu X (bulan dan tahun)
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Tipe chart: 'line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea'
    }

    // (Opsional) Atur tinggi chart jika defaultnya kurang sesuai
    // protected function getChartHeight(): ?string
    // {
    //     return '300px'; // Contoh tinggi 300 pixel
    // }
}
