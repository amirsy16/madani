<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class MetodePembayaranChart extends Widget
{
    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.metode-pembayaran-chart';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Analisis Metode Pembayaran';
    }

    protected function getHeadingClass(): ?string
    {
        return 'text-center text-2xl font-bold';
    }

    public ?string $filter = 'bulan_ini';

    public ?string $selectedMetode = null;

    public array $metodePembayaranStats = [];

    public ?string $customStartDate = null;

    public ?string $customEndDate = null;

    public bool $showCustomDateRange = false;

    public bool $showInfoModal = false;

    protected static bool $isDiscovered = false;

    protected function getListeners(): array
    {
        return [
            'analisisFiltersUpdated' => 'onAnalisisFiltersUpdated',
        ];
    }

    public function mount(): void
    {
        $this->loadMetodePembayaranStats();
    }

    public function updatedFilter(): void
    {
        if ($this->filter === 'custom') {
            $this->showCustomDateRange = true;
        } else {
            $this->showCustomDateRange = false;
            $this->customStartDate = null;
            $this->customEndDate = null;
            $this->loadMetodePembayaranStats();
            $this->selectedMetode = null;
        }
    }

    public function updatedCustomStartDate(): void
    {
        if ($this->customStartDate && $this->customEndDate) {
            $this->loadMetodePembayaranStats();
        }
    }

    public function updatedCustomEndDate(): void
    {
        if ($this->customStartDate && $this->customEndDate) {
            $this->loadMetodePembayaranStats();
        }
    }

    public function applyCustomDate(): void
    {
        if ($this->customStartDate && $this->customEndDate) {
            $this->loadMetodePembayaranStats();
            $this->selectedMetode = null;
        }
    }

    public function onAnalisisFiltersUpdated($periode, $limit)
    {
        $map = [
            'all_time' => 'keseluruhan',
            'current_month' => 'bulan_ini',
            'last_month' => 'bulan_lalu',
            'current_year' => 'tahun_ini',
            'last_3_months' => '3_bulan',
            'last_6_months' => '6_bulan',
        ];
        $this->filter = $map[$periode] ?? 'keseluruhan';
        $this->loadMetodePembayaranStats();
        $this->selectedMetode = null;
    }

    public function selectMetode(?string $metode): void
    {
        $this->selectedMetode = $metode;
    }

    public function closeModal(): void
    {
        $this->selectedMetode = null;
    }

    protected function loadMetodePembayaranStats(): void
    {
        $activeFilter = $this->filter;
        $now = Carbon::now();
        $startDate = null;
        $endDate = null;

        if ($activeFilter === 'custom' && $this->customStartDate && $this->customEndDate) {
            $startDate = Carbon::parse($this->customStartDate)->startOfDay();
            $endDate = Carbon::parse($this->customEndDate)->endOfDay();
        } else {
            switch ($activeFilter) {
                case 'tahun_ini':
                    $startDate = $now->copy()->startOfYear();
                    $endDate = $now->copy()->endOfYear();
                    break;
                case '3_bulan':
                    $startDate = $now->copy()->subMonths(3);
                    $endDate = $now->copy()->endOfDay();
                    break;
                case '6_bulan':
                    $startDate = $now->copy()->subMonths(6);
                    $endDate = $now->copy()->endOfDay();
                    break;
                case 'bulan_lalu':
                    $startDate = $now->copy()->subMonth()->startOfMonth();
                    $endDate = $now->copy()->subMonth()->endOfMonth();
                    break;
                case 'keseluruhan':
                    break;
                case 'bulan_ini':
                default:
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                    break;
            }
        }

        $query = Donasi::query()
            ->join('metode_pembayarans', 'donasis.metode_pembayaran_id', '=', 'metode_pembayarans.id')
            ->where('donasis.status_konfirmasi', 'verified')
            // Paritas dengan kartu statistik: Penyaluran Langsung tidak masuk
            // kas organisasi.
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
            ->select(
                'metode_pembayarans.id as metode_id',
                'metode_pembayarans.nama as nama_metode',
                DB::raw('COUNT(donasis.id) as jumlah_transaksi'),
                DB::raw('SUM(donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)) as total_nominal')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('donasis.tanggal_donasi', [$startDate, $endDate]);
        }

        $this->metodePembayaranStats = $query
            ->groupBy('metode_pembayarans.id', 'metode_pembayarans.nama')
            ->orderBy('total_nominal', 'desc')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                // Ambil top donatur untuk metode ini
                $topDonatur = $this->getTopDonaturForMetode($item->metode_id, $startDate, $endDate);

                return [
                    'id' => $item->metode_id,
                    'nama' => $item->nama_metode,
                    'jumlah_transaksi' => (int) $item->jumlah_transaksi,
                    'total_nominal' => (float) $item->total_nominal,
                    'top_donatur' => $topDonatur,
                    'formatted_total' => 'Rp '.number_format($item->total_nominal, 0, ',', '.'),
                ];
            })
            ->toArray();
    }

    protected function getTopDonaturForMetode(int $metodeId, $startDate = null, $endDate = null): array
    {
        $query = Donasi::query()
            ->join('donaturs', 'donasis.donatur_id', '=', 'donaturs.id')
            ->where('donasis.metode_pembayaran_id', $metodeId)
            ->where('donasis.status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
            ->select(
                'donaturs.id',
                'donaturs.nama',
                DB::raw('SUM(donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)) as total_donasi'),
                DB::raw('COUNT(donasis.id) as jumlah_donasi')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('donasis.tanggal_donasi', [$startDate, $endDate]);
        }

        $topDonatur = $query
            ->groupBy('donaturs.id', 'donaturs.nama')
            ->orderBy('total_donasi', 'desc')
            ->first();

        if (! $topDonatur) {
            return [
                'nama' => '-',
                'total' => 0,
                'formatted_total' => '-',
                'jumlah_donasi' => 0,
            ];
        }

        return [
            'nama' => $topDonatur->nama,
            'total' => (float) $topDonatur->total_donasi,
            'formatted_total' => 'Rp '.number_format($topDonatur->total_donasi, 0, ',', '.'),
            'jumlah_donasi' => (int) $topDonatur->jumlah_donasi,
        ];
    }

    public function getDetailDonasi(?string $metode): array
    {
        if (! $metode) {
            return [];
        }

        $activeFilter = $this->filter;
        $now = Carbon::now();
        $startDate = null;
        $endDate = null;

        if ($activeFilter === 'custom' && $this->customStartDate && $this->customEndDate) {
            $startDate = Carbon::parse($this->customStartDate)->startOfDay();
            $endDate = Carbon::parse($this->customEndDate)->endOfDay();
        } else {
            switch ($activeFilter) {
                case 'tahun_ini':
                    $startDate = $now->copy()->startOfYear();
                    $endDate = $now->copy()->endOfYear();
                    break;
                case '3_bulan':
                    $startDate = $now->copy()->subMonths(3);
                    $endDate = $now->copy()->endOfDay();
                    break;
                case '6_bulan':
                    $startDate = $now->copy()->subMonths(6);
                    $endDate = $now->copy()->endOfDay();
                    break;
                case 'bulan_lalu':
                    $startDate = $now->copy()->subMonth()->startOfMonth();
                    $endDate = $now->copy()->subMonth()->endOfMonth();
                    break;
                case 'keseluruhan':
                    break;
                case 'bulan_ini':
                default:
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                    break;
            }
        }

        $query = Donasi::query()
            ->join('metode_pembayarans', 'donasis.metode_pembayaran_id', '=', 'metode_pembayarans.id')
            ->join('donaturs', 'donasis.donatur_id', '=', 'donaturs.id')
            ->where('donasis.status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', '!=', 'Penyaluran Langsung'))
            ->where('metode_pembayarans.nama', $metode)
            ->select(
                'donaturs.nama as nama_donatur',
                'donasis.tanggal_donasi',
                DB::raw('(donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)) as nominal')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('donasis.tanggal_donasi', [$startDate, $endDate]);
        }

        return $query
            ->orderBy('donasis.tanggal_donasi', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'donatur' => $item->nama_donatur,
                'tanggal' => Carbon::parse($item->tanggal_donasi)->format('d M Y'),
                'nominal' => 'Rp '.number_format($item->nominal, 0, ',', '.'),
            ])
            ->toArray();
    }

    protected function getFilters(): array
    {
        return [
            'bulan_ini' => 'Bulan Ini',
            'bulan_lalu' => 'Bulan Lalu',
            'tahun_ini' => 'Tahun Ini',
            '3_bulan' => '3 Bulan Terakhir',
            '6_bulan' => '6 Bulan Terakhir',
            'keseluruhan' => 'Semua Waktu',
            'custom' => 'Pilih Tanggal',
        ];
    }

    public function getPeriodeLabel(): string
    {
        if ($this->filter === 'custom' && $this->customStartDate && $this->customEndDate) {
            return Carbon::parse($this->customStartDate)->format('d M Y').' - '.Carbon::parse($this->customEndDate)->format('d M Y');
        }

        return $this->getFilters()[$this->filter] ?? 'Bulan Ini';
    }

    public function getTotalKeseluruhan(): float
    {
        return array_sum(array_column($this->metodePembayaranStats, 'total_nominal'));
    }

    public function getPersentase(float $nominal): float
    {
        $total = $this->getTotalKeseluruhan();

        return $total > 0 ? ($nominal / $total) * 100 : 0;
    }

    public function generateColors(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $baseColors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
            '#06B6D4', '#84CC16', '#F97316', '#6366F1', '#14B8A6', '#F43F5E',
            '#A855F7', '#22C55E', '#FBBF24', '#FB7185', '#38BDF8', '#A3A3A3',
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }
}
