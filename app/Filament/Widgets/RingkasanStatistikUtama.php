<?php

namespace App\Filament\Widgets;

use App\Models\Donasi;
use App\Models\ProgramPenyaluran;
use App\Models\SumberDanaPenyaluran;
use App\Services\StatsCache;
use Carbon\Carbon;
use Closure;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class RingkasanStatistikUtama extends Widget
{
    protected $listeners = ['analisisFiltersUpdated' => 'onAnalisisFiltersUpdated'];

    protected static string $view = 'filament.widgets.ringkasan-statistik-utama';

    protected ?string $heading = '📊 Dashboard Keuangan Madani - Ringkasan Lengkap';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    // Properties untuk blade view
    public $currentPeriod = 'keseluruhan';

    public $timePeriodOptions = [
        'keseluruhan' => 'Keseluruhan',
        'tahun_ini' => 'Tahun Ini',
        'bulan_ini' => 'Bulan Ini',
        'custom' => 'Pilih Tanggal',
    ];

    // Property untuk custom date range
    public $startDate = null;

    public $endDate = null;

    public $showDatePicker = false;

    // Modal breakdown "Lihat Selengkapnya": 'infaq_terikat' | 'zakat' | 'barang' | null
    public $showBreakdownModal = null;

    // Properties untuk modal detail donasi
    public $selectedKategori = null;

    public $selectedJenisZakat = null;

    public $selectedJenisBarang = null;

    public $showDetailDonasiModal = false;

    public $detailDonasiType = null; // 'infaq', 'zakat', atau 'barang'

    public $detailPage = 1;

    const DETAIL_PER_PAGE = 50;

    public function mount()
    {
        $this->currentPeriod = request()->get('period', 'keseluruhan');
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
        $this->currentPeriod = $map[$periode] ?? 'keseluruhan';
    }

    public function setTimePeriod($period)
    {
        $this->currentPeriod = $period;

        // Tampilkan date picker jika memilih custom
        if ($period === 'custom') {
            $this->showDatePicker = true;
            // Set default dates jika belum ada
            if (! $this->startDate) {
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            }
            if (! $this->endDate) {
                $this->endDate = Carbon::now()->format('Y-m-d');
            }
        } else {
            $this->showDatePicker = false;
            $this->startDate = null;
            $this->endDate = null;
        }
    }

    public function applyDateFilter()
    {
        // Validasi tanggal
        if ($this->startDate && $this->endDate) {
            if (strtotime($this->startDate) > strtotime($this->endDate)) {
                // Tukar jika start date lebih besar dari end date
                $temp = $this->startDate;
                $this->startDate = $this->endDate;
                $this->endDate = $temp;
            }
        }
        // Refresh stats dengan memanggil getStats
        $this->dispatch('refreshStats');
    }

    public function resetDateFilter()
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->currentPeriod = 'keseluruhan';
        $this->showDatePicker = false;
    }

    public function openInfaqTerikatModal()
    {
        $this->showBreakdownModal = 'infaq_terikat';
    }

    public function openZakatModal()
    {
        $this->showBreakdownModal = 'zakat';
    }

    public function openBarangModal()
    {
        $this->showBreakdownModal = 'barang';
    }

    public function closeBreakdownModal()
    {
        $this->showBreakdownModal = null;
        $this->closeDetailDonasiModal();
    }

    public function showBarangDonasi()
    {
        $this->detailPage = 1;
        $this->detailDonasiType = 'barang';
        $this->showDetailDonasiModal = true;
    }

    public function showInfaqTerikatDonasi($kategori)
    {
        $this->detailPage = 1;
        $this->selectedKategori = $kategori;
        $this->detailDonasiType = 'infaq';
        $this->showDetailDonasiModal = true;
    }

    public function showZakatDonasi($jenis)
    {
        $this->detailPage = 1;
        $this->selectedJenisZakat = $jenis;
        $this->detailDonasiType = 'zakat';
        $this->showDetailDonasiModal = true;
    }

    public function showBarangDonasiByJenis($jenis)
    {
        $this->detailPage = 1;
        $this->selectedJenisBarang = $jenis;
        $this->detailDonasiType = 'barang_by_jenis';
        $this->showDetailDonasiModal = true;
    }

    public function goToDetailPage($page)
    {
        $this->detailPage = max(1, (int) $page);
    }

    public function closeDetailDonasiModal()
    {
        $this->showDetailDonasiModal = false;
        $this->selectedKategori = null;
        $this->selectedJenisZakat = null;
        $this->selectedJenisBarang = null;
        $this->detailDonasiType = null;
        $this->detailPage = 1;
    }

    public function getTimePeriodLabelProperty()
    {
        return $this->timePeriodOptions[$this->currentPeriod] ?? 'Keseluruhan';
    }

    public function getStatsProperty()
    {
        return $this->getStats();
    }

    public function getInfaqTerikatDetailProperty()
    {
        $filter = $this->currentPeriod ?? 'keseluruhan';
        $dateConstraint = $this->getDateConstraint($filter);

        return $this->getInfaqTerikatByKategori($dateConstraint);
    }

    public function getZakatDetailProperty()
    {
        $filter = $this->currentPeriod ?? 'keseluruhan';
        $dateConstraint = $this->getDateConstraint($filter);

        return $this->getZakatByJenis($dateConstraint);
    }

    public function getBarangDetailProperty()
    {
        $filter = $this->currentPeriod ?? 'keseluruhan';
        $dateConstraint = $this->getDateConstraint($filter);

        return $this->getBarangByJenis($dateConstraint);
    }

    public function getDetailDonasiDataProperty(): array
    {
        $empty = [
            'rows' => [], 'total' => 0, 'page' => 1, 'lastPage' => 1,
            'summary' => ['transaksi' => 0, 'donatur' => 0, 'nominal' => 0],
        ];

        if (! $this->showDetailDonasiModal) {
            return $empty;
        }

        $dateConstraint = $this->getDateConstraint($this->currentPeriod ?? 'keseluruhan');

        [$query, $amountSql, $mapRow] = match (true) {
            $this->detailDonasiType === 'infaq' && (bool) $this->selectedKategori => [
                $this->donasiInfaqKategoriQuery($this->selectedKategori, $dateConstraint),
                'SUM(jumlah + IFNULL(perkiraan_nilai_barang, 0))',
                fn (Donasi $d) => $this->mapDetailDonasiRow($d, barang: false),
            ],
            $this->detailDonasiType === 'zakat' && (bool) $this->selectedJenisZakat => [
                $this->donasiZakatQuery($this->selectedJenisZakat, $dateConstraint),
                'SUM(jumlah + IFNULL(perkiraan_nilai_barang, 0))',
                fn (Donasi $d) => $this->mapDetailDonasiRow($d, barang: false),
            ],
            $this->detailDonasiType === 'barang' => [
                $this->donasiBarangQuery($dateConstraint),
                'SUM(CASE WHEN perkiraan_nilai_barang > 0 THEN perkiraan_nilai_barang ELSE jumlah END)',
                fn (Donasi $d) => $this->mapDetailDonasiRow($d, barang: true),
            ],
            $this->detailDonasiType === 'barang_by_jenis' && (bool) $this->selectedJenisBarang => [
                $this->donasiBarangByJenisQuery($this->selectedJenisBarang, $dateConstraint),
                'SUM(CASE WHEN perkiraan_nilai_barang > 0 THEN perkiraan_nilai_barang ELSE jumlah END)',
                fn (Donasi $d) => $this->mapDetailDonasiRow($d, barang: true),
            ],
            default => [null, null, null],
        };

        if (! $query) {
            return $empty;
        }

        return $this->paginateDetailRows($query, $amountSql, $mapRow);
    }

    /**
     * Potong query jadi satu halaman + hitung summary level-SQL agar
     * angka ringkasan mencakup seluruh data, bukan hanya baris halaman ini.
     */
    private function paginateDetailRows($query, string $amountSql, Closure $mapRow): array
    {
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / self::DETAIL_PER_PAGE));
        $page = min(max(1, (int) $this->detailPage), $lastPage);

        $summary = (clone $query)
            ->selectRaw("COUNT(*) as transaksi, COUNT(DISTINCT donatur_id) as donatur, {$amountSql} as nominal")
            ->first();

        $rows = $query
            ->orderByDesc('tanggal_donasi')
            ->forPage($page, self::DETAIL_PER_PAGE)
            ->get()
            ->map($mapRow)
            ->values()
            ->all();

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'lastPage' => $lastPage,
            'summary' => [
                'transaksi' => (int) ($summary->transaksi ?? 0),
                'donatur' => (int) ($summary->donatur ?? 0),
                'nominal' => (float) ($summary->nominal ?? 0),
            ],
        ];
    }

    private function mapDetailDonasiRow(Donasi $d, bool $barang): array
    {
        // Anonimisasi berlaku untuk SEMUA jenis donasi bertanda hamba Allah
        // (paritas dengan tampilan tabel Donasi) — bukan hanya jenis barang.
        $anonim = (bool) $d->atas_nama_hamba_allah;

        $row = [
            'id' => $d->id,
            'tanggal' => $d->tanggal_donasi,
            'kode' => $d->nomor_transaksi_unik ?? 'N/A',
            'donatur_nama' => $anonim ? 'Hamba Allah' : ($d->donatur->nama ?? 'Anonim'),
            'donatur_hp' => $anonim ? null : ($d->donatur->nomor_hp ?? null),
            'jenis_donasi' => $d->jenisDonasi->nama ?? 'N/A',
            'metode_pembayaran' => $d->metodePembayaran->nama ?? 'N/A',
        ];

        if ($barang) {
            $row['nomor_transaksi'] = $d->nomor_transaksi_unik ?? 'N/A';
            $row['deskripsi_barang'] = $d->deskripsi_barang ?? '-';
            $row['nilai_barang'] = ($d->perkiraan_nilai_barang && $d->perkiraan_nilai_barang > 0)
                ? $d->perkiraan_nilai_barang
                : $d->jumlah;
            $row['fundraiser'] = $d->fundraiser->nama_fundraiser ?? '-';
        } else {
            $row['nominal'] = ($d->jumlah ?? 0) + ($d->perkiraan_nilai_barang ?? 0);
        }

        return $row;
    }

    private function donasiInfaqKategoriQuery(string $kategori, ?array $dateConstraint)
    {
        $query = Donasi::with(['donatur', 'jenisDonasi', 'fundraiser', 'metodePembayaran'])
            ->where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', 'Infaq Terikat'))
            ->where('keterangan_infak_khusus', $kategori);

        if ($dateConstraint) {
            $query->whereBetween('tanggal_donasi', $dateConstraint);
        }

        return $query;
    }

    private function donasiZakatQuery(string $jenis, ?array $dateConstraint)
    {
        $query = Donasi::with(['donatur', 'jenisDonasi', 'fundraiser', 'metodePembayaran'])
            ->where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', $jenis));

        if ($dateConstraint) {
            $query->whereBetween('tanggal_donasi', $dateConstraint);
        }

        return $query;
    }

    private function donasiBarangQuery(?array $dateConstraint)
    {
        $query = Donasi::with(['donatur', 'jenisDonasi', 'fundraiser', 'metodePembayaran'])
            ->where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('apakah_barang', true)
                ->where('nama', '!=', 'Penyaluran Langsung'));

        if ($dateConstraint) {
            $query->whereBetween('tanggal_donasi', $dateConstraint);
        }

        return $query;
    }

    private function donasiBarangByJenisQuery(string $jenis, ?array $dateConstraint)
    {
        $query = Donasi::with(['donatur', 'jenisDonasi', 'fundraiser', 'metodePembayaran'])
            ->where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', fn ($q) => $q->where('nama', $jenis)
                ->where('apakah_barang', true));

        if ($dateConstraint) {
            $query->whereBetween('tanggal_donasi', $dateConstraint);
        }

        return $query;
    }

    protected function getStats(): array
    {
        // Sertakan bulan berjalan pada key: untuk filter "bulan ini" key
        // lama identik antar-bulan sehingga angka basi tampil setelah
        // pergantian bulan (dalam TTL) — paritas dengan ZakatStatsOverview.
        $cacheKey = 'ringkasan:'.now()->format('Y-m')
            .':'.($this->currentPeriod ?? 'keseluruhan')
            .':'.($this->startDate ?? '-')
            .':'.($this->endDate ?? '-');

        return StatsCache::remember($cacheKey, function () {
            return $this->computeStats();
        });
    }

    protected function computeStats(): array
    {
        // Tentukan periode berdasarkan filter
        $filter = $this->currentPeriod ?? 'keseluruhan';

        // Siapkan constraint tanggal berdasarkan filter
        $dateConstraint = $this->getDateConstraint($filter);

        // TOTAL DONASI BERDASARKAN FILTER (EXCLUDE PENYALURAN LANGSUNG)
        $totalDonasiQuery = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', function ($query) {
                $query->where('nama', '!=', 'Penyaluran Langsung');
            });

        if ($dateConstraint) {
            $totalDonasiQuery->whereBetween('tanggal_donasi', $dateConstraint);
        }

        $totalDonasi = $totalDonasiQuery->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));

        // BREAKDOWN PER KATEGORI
        $totalZakat = $this->getTotalByCategory('zakat', $dateConstraint);

        // Detail Infaq
        $totalInfaqTerikat = $this->getTotalByJenisDonasi('Infaq Terikat', $dateConstraint);
        $totalInfaqTidakTerikat = $this->getTotalByJenisDonasi('Infaq Tidak Terikat', $dateConstraint);
        $totalInfaq = $totalInfaqTerikat + $totalInfaqTidakTerikat;

        // Detail Sedekah
        $totalSedekah = $this->getTotalByJenisDonasi('Sedekah', $dateConstraint);

        $totalCSR = $this->getTotalByCategory('csr', $dateConstraint);
        $totalDSKL = $this->getTotalByCategory('dskl', $dateConstraint);

        // STATISTIK TAMBAHAN
        $totalTransaksiQuery = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', function ($query) {
                $query->where('nama', '!=', 'Penyaluran Langsung');
            });

        if ($dateConstraint) {
            $totalTransaksiQuery->whereBetween('tanggal_donasi', $dateConstraint);
        }

        $totalTransaksi = $totalTransaksiQuery->count();
        $totalDonatur = $totalTransaksiQuery->distinct('donatur_id')->count('donatur_id');

        // HITUNG DONASI BARANG
        $donasiBarangQuery = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', function ($query) {
                $query->where('nama', '!=', 'Penyaluran Langsung')
                    ->where('apakah_barang', true);
            });

        if ($dateConstraint) {
            $donasiBarangQuery->whereBetween('tanggal_donasi', $dateConstraint);
        }

        $totalNilaiBarang = $donasiBarangQuery->sum(DB::raw('CASE WHEN perkiraan_nilai_barang IS NOT NULL AND perkiraan_nilai_barang > 0 THEN perkiraan_nilai_barang ELSE jumlah END'));
        $totalTransaksiBarang = $donasiBarangQuery->count();

        // HITUNG HAK AMIL (Bagian Amil dari setiap sumber dana)
        $totalHakAmil = 0;
        $detailHakAmil = [];

        // Hitung hak amil per sumber dana berdasarkan persentase_hak_amil
        $sumberDanaList = SumberDanaPenyaluran::where('aktif', true)
            ->where('nama_sumber_dana', '!=', 'Hak Amil')
            ->get();

        foreach ($sumberDanaList as $sumberDana) {
            if ($sumberDana->persentase_hak_amil > 0) {
                // Ambil jenis donasi yang terkait dengan sumber dana ini
                $jenisDonasi = \App\Models\JenisDonasi::where('sumber_dana_penyaluran_id', $sumberDana->id)
                    ->where('nama', '!=', 'Penyaluran Langsung')
                    ->pluck('id');

                // Hitung total penerimaan untuk sumber dana ini
                $penerimaanQuery = Donasi::whereIn('jenis_donasi_id', $jenisDonasi)
                    ->where('status_konfirmasi', 'verified');

                if ($dateConstraint) {
                    $penerimaanQuery->whereBetween('tanggal_donasi', $dateConstraint);
                }

                $penerimaan = $penerimaanQuery->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));

                // Hitung bagian amil
                $hakAmilSumberDana = ($penerimaan * $sumberDana->persentase_hak_amil) / 100;
                $totalHakAmil += $hakAmilSumberDana;

                if ($hakAmilSumberDana > 0) {
                    $detailHakAmil[$sumberDana->nama_sumber_dana] = [
                        'penerimaan' => $penerimaan,
                        'persentase' => $sumberDana->persentase_hak_amil,
                        'hak_amil' => $hakAmilSumberDana,
                    ];
                }
            }
        }

        // TOTAL PENYALURAN PROGRAM (dari sumber dana yang dikelola, TIDAK termasuk penyaluran langsung)
        $totalPenyaluranProgramQuery = ProgramPenyaluran::whereHas('sumberDanaPenyaluran', function ($query) {
            $query->where('nama_sumber_dana', 'NOT LIKE', '%Penyaluran Langsung%');
        });
        if ($dateConstraint) {
            $totalPenyaluranProgramQuery->whereBetween('tanggal_penyaluran', $dateConstraint);
        }
        $totalPenyaluranProgram = $totalPenyaluranProgramQuery->sum('jumlah_dana');
        $totalTransaksiPenyaluranProgram = $totalPenyaluranProgramQuery->count();

        // STATISTIK PENYALURAN LANGSUNG
        // Dana yang langsung disalurkan tanpa melalui penghimpunan (tidak masuk ke kas organisasi)
        $totalPenyaluranLangsungQuery = ProgramPenyaluran::whereHas('sumberDanaPenyaluran', function ($query) {
            $query->where('nama_sumber_dana', 'LIKE', '%Penyaluran Langsung%');
        });
        if ($dateConstraint) {
            $totalPenyaluranLangsungQuery->whereBetween('tanggal_penyaluran', $dateConstraint);
        }
        $totalPenyaluranLangsung = $totalPenyaluranLangsungQuery->sum('jumlah_dana');
        $totalTransaksiPenyaluranLangsung = $totalPenyaluranLangsungQuery->count();

        // PENGGUNAAN HAK AMIL
        $penggunaanHakAmilQuery = \App\Models\PenggunaanHakAmil::query();
        if ($dateConstraint) {
            $penggunaanHakAmilQuery->whereBetween('tanggal', $dateConstraint);
        }
        $totalPenggunaanHakAmil = $penggunaanHakAmilQuery->sum('jumlah');
        $totalTransaksiPenggunaanHakAmil = $penggunaanHakAmilQuery->count();

        // SISA DANA TERSEDIA (Saldo Akhir Dana Program)
        // Formula: Total Dana Terhimpun - Hak Amil - Penyaluran Program = Sisa Dana
        // NOTE: Penyaluran Langsung tidak dikurangi karena dana tersebut tidak pernah masuk ke kas organisasi
        $sisaDana = $totalDonasi - $totalHakAmil - $totalPenyaluranProgram;

        // SISA HAK AMIL
        // Formula: Total Hak Amil - Penggunaan Hak Amil = Sisa Hak Amil
        $sisaHakAmil = $totalHakAmil - $totalPenggunaanHakAmil;

        $baseStats = [
            [
                'label' => 'TOTAL DANA TERHIMPUN',
                'value' => 'Rp '.number_format($totalDonasi, 0, ',', '.'),
                'description' => $totalTransaksi.' transaksi dari '.$totalDonatur.' donatur',
                'icon' => '💰',
                'color' => 'success',
            ],
            [
                'label' => 'DANA ZAKAT',
                'value' => 'Rp '.number_format($totalZakat, 0, ',', '.'),
                'description' => number_format(($totalZakat / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '🕌',
                'color' => 'success',
                'clickable' => true,
                'action' => 'openZakatModal',
            ],
            [
                'label' => 'DANA INFAQ',
                'value' => 'Rp '.number_format($totalInfaq, 0, ',', '.'),
                'description' => number_format(($totalInfaq / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '💚',
                'color' => 'info',
            ],
            [
                'label' => '↳ INFAQ TERIKAT',
                'value' => 'Rp '.number_format($totalInfaqTerikat, 0, ',', '.'),
                'description' => number_format(($totalInfaqTerikat / max($totalInfaq, 1)) * 100, 1).'% dari total infaq',
                'icon' => '🎯',
                'color' => 'info',
            ],
            [
                'label' => '↳ INFAQ TIDAK TERIKAT',
                'value' => 'Rp '.number_format($totalInfaqTidakTerikat, 0, ',', '.'),
                'description' => number_format(($totalInfaqTidakTerikat / max($totalInfaq, 1)) * 100, 1).'% dari total infaq',
                'icon' => '🌟',
                'color' => 'info',
            ],
            [
                'label' => 'DANA SEDEKAH',
                'value' => 'Rp '.number_format($totalSedekah, 0, ',', '.'),
                'description' => number_format(($totalSedekah / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '💝',
                'color' => 'info',
            ],
            [
                'label' => 'DONASI BARANG',
                'value' => 'Rp '.number_format($totalNilaiBarang, 0, ',', '.'),
                'description' => $totalTransaksiBarang.' transaksi • '.number_format(($totalNilaiBarang / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '📦',
                'color' => 'warning',
                'clickable' => true,
                'action' => 'openBarangModal',
            ],
            [
                'label' => 'DANA CSR',
                'value' => 'Rp '.number_format($totalCSR, 0, ',', '.'),
                'description' => number_format(($totalCSR / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '🏢',
                'color' => 'warning',
            ],
            [
                'label' => 'DANA SOSIAL KEAGAMAAN',
                'value' => 'Rp '.number_format($totalDSKL, 0, ',', '.'),
                'description' => number_format(($totalDSKL / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '🏛️',
                'color' => 'gray',
            ],
            [
                'label' => 'HAK AMIL',
                'value' => 'Rp '.number_format($totalHakAmil, 0, ',', '.'),
                'description' => 'Bagian amil • '.number_format(($totalHakAmil / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '👥',
                'color' => 'primary',
            ],
            [
                'label' => 'PENGGUNAAN HAK AMIL',
                'value' => 'Rp '.number_format($totalPenggunaanHakAmil, 0, ',', '.'),
                'description' => $totalTransaksiPenggunaanHakAmil.' transaksi • '.number_format(($totalPenggunaanHakAmil / max($totalHakAmil, 1)) * 100, 1).'% terpakai',
                'icon' => '💼',
                'color' => 'info',
            ],
            [
                'label' => 'SISA HAK AMIL',
                'value' => 'Rp '.number_format($sisaHakAmil, 0, ',', '.'),
                'description' => 'Belum digunakan • '.number_format(($sisaHakAmil / max($totalHakAmil, 1)) * 100, 1).'% tersisa',
                'icon' => '💰',
                'color' => $sisaHakAmil > 0 ? 'success' : 'warning',
            ],
            [
                'label' => 'PENYALURAN PROGRAM',
                'value' => 'Rp '.number_format($totalPenyaluranProgram, 0, ',', '.'),
                'description' => $totalTransaksiPenyaluranProgram.' program • '.number_format(($totalPenyaluranProgram / max($totalDonasi, 1)) * 100, 1).'% dari donasi',
                'icon' => '🎁',
                'color' => 'info',
            ],
            [
                'label' => 'PENYALURAN LANGSUNG',
                'value' => 'Rp '.number_format($totalPenyaluranLangsung, 0, ',', '.'),
                'description' => $totalTransaksiPenyaluranLangsung.' program • Dana tidak melalui kas',
                'icon' => '🔄',
                'color' => 'gray',
            ],
            [
                'label' => 'SISA DANA PROGRAM',
                'value' => 'Rp '.number_format($sisaDana, 0, ',', '.'),
                'description' => 'Saldo tersedia • '.number_format(($sisaDana / max($totalDonasi, 1)) * 100, 1).'% dari total',
                'icon' => '🏦',
                'color' => $sisaDana > 0 ? 'success' : 'danger',
            ],
        ];

        return $baseStats;
    }

    private function getDateConstraint(?string $filter): ?array
    {
        switch ($filter) {
            case 'custom':
                // Gunakan custom date range jika ada
                if ($this->startDate && $this->endDate) {
                    return [
                        Carbon::parse($this->startDate)->format('Y-m-d'),
                        Carbon::parse($this->endDate)->format('Y-m-d'),
                    ];
                }

                return null;
            case 'tahun_ini':
                return [
                    Carbon::now()->startOfYear()->format('Y-m-d'),
                    Carbon::now()->endOfYear()->format('Y-m-d'),
                ];
            case 'bulan_ini':
                return [
                    Carbon::now()->startOfMonth()->format('Y-m-d'),
                    Carbon::now()->endOfMonth()->format('Y-m-d'),
                ];
            case 'bulan_lalu':
                return [
                    Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'),
                    Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d'),
                ];
            case '3_bulan':
                return [
                    Carbon::now()->subMonths(3)->format('Y-m-d'),
                    Carbon::now()->format('Y-m-d'),
                ];
            case '6_bulan':
                return [
                    Carbon::now()->subMonths(6)->format('Y-m-d'),
                    Carbon::now()->format('Y-m-d'),
                ];
            case 'keseluruhan':
            default:
                return null;
        }
    }

    private function getTotalByCategory(string $category, ?array $dateConstraint): float
    {
        $query = Donasi::join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->join('sumber_dana_penyalurans', 'jenis_donasis.sumber_dana_penyaluran_id', '=', 'sumber_dana_penyalurans.id')
            ->where('donasis.status_konfirmasi', 'verified')
            ->where('jenis_donasis.nama', '!=', 'Penyaluran Langsung');

        if ($dateConstraint) {
            $query->whereBetween('donasis.tanggal_donasi', $dateConstraint);
        }

        switch ($category) {
            case 'zakat':
                $query->where('sumber_dana_penyalurans.nama_sumber_dana', 'LIKE', '%zakat%');
                break;
            case 'infaq':
                $query->where(function ($q) {
                    $q->where('sumber_dana_penyalurans.nama_sumber_dana', 'LIKE', '%infaq%')
                        ->orWhere('sumber_dana_penyalurans.nama_sumber_dana', 'LIKE', '%sedekah%');
                });
                break;
            case 'csr':
                $query->where('sumber_dana_penyalurans.nama_sumber_dana', 'LIKE', '%csr%');
                break;
            case 'dskl':
                $query->where(function ($q) {
                    $q->where('sumber_dana_penyalurans.nama_sumber_dana', 'LIKE', '%dskl%')
                        ->orWhere('sumber_dana_penyalurans.nama_sumber_dana', 'LIKE', '%sosial keagamaan%');
                });
                break;
            default:
                return 0;
        }

        return $query->sum(DB::raw('donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)')) ?? 0;
    }

    private function getTotalByJenisDonasi(string $namaJenisDonasi, ?array $dateConstraint): float
    {
        $query = Donasi::join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->where('donasis.status_konfirmasi', 'verified')
            ->where('jenis_donasis.nama', $namaJenisDonasi);

        if ($dateConstraint) {
            $query->whereBetween('donasis.tanggal_donasi', $dateConstraint);
        }

        return $query->sum(DB::raw('donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)')) ?? 0;
    }

    /**
     * Get detail breakdown of Infaq Terikat by kategori (keterangan_infak_khusus)
     */
    private function getInfaqTerikatByKategori(?array $dateConstraint): array
    {
        $query = Donasi::join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->where('donasis.status_konfirmasi', 'verified')
            ->where('jenis_donasis.nama', 'Infaq Terikat')
            ->whereNotNull('donasis.keterangan_infak_khusus')
            ->where('donasis.keterangan_infak_khusus', '!=', '');

        if ($dateConstraint) {
            $query->whereBetween('donasis.tanggal_donasi', $dateConstraint);
        }

        $result = $query->select(
            'donasis.keterangan_infak_khusus as kategori',
            DB::raw('SUM(donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)) as total'),
            DB::raw('COUNT(*) as jumlah_transaksi')
        )
            ->groupBy('donasis.keterangan_infak_khusus')
            ->orderBy('total', 'desc')
            ->get();

        return $result->map(function ($item) {
            return [
                'kategori' => $item->kategori,
                'total' => $item->total,
                'jumlah_transaksi' => $item->jumlah_transaksi,
            ];
        })->toArray();
    }

    /**
     * Get detail breakdown of Zakat by jenis (Zakat Maal, Fitrah, Perusahaan)
     */
    private function getZakatByJenis(?array $dateConstraint): array
    {
        $query = Donasi::join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->where('donasis.status_konfirmasi', 'verified')
            ->where('jenis_donasis.nama', 'LIKE', '%Zakat%');

        if ($dateConstraint) {
            $query->whereBetween('donasis.tanggal_donasi', $dateConstraint);
        }

        $result = $query->select(
            'jenis_donasis.nama as jenis',
            DB::raw('SUM(donasis.jumlah + IFNULL(donasis.perkiraan_nilai_barang, 0)) as total'),
            DB::raw('COUNT(*) as jumlah_transaksi')
        )
            ->groupBy('jenis_donasis.nama')
            ->orderBy('total', 'desc')
            ->get();

        return $result->map(function ($item) {
            return [
                'jenis' => $item->jenis,
                'total' => $item->total,
                'jumlah_transaksi' => $item->jumlah_transaksi,
            ];
        })->toArray();
    }

    /**
     * Get detail breakdown of Donasi Barang by jenis
     */
    private function getBarangByJenis(?array $dateConstraint): array
    {
        $query = Donasi::join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
            ->where('donasis.status_konfirmasi', 'verified')
            ->where('jenis_donasis.apakah_barang', true)
            ->where('jenis_donasis.nama', '!=', 'Penyaluran Langsung');

        if ($dateConstraint) {
            $query->whereBetween('donasis.tanggal_donasi', $dateConstraint);
        }

        $result = $query->select(
            'jenis_donasis.nama as jenis',
            DB::raw('SUM(CASE WHEN donasis.perkiraan_nilai_barang IS NOT NULL AND donasis.perkiraan_nilai_barang > 0 THEN donasis.perkiraan_nilai_barang ELSE donasis.jumlah END) as total'),
            DB::raw('COUNT(*) as jumlah_transaksi')
        )
            ->groupBy('jenis_donasis.nama')
            ->orderBy('total', 'desc')
            ->get();

        return $result->map(function ($item) {
            return [
                'jenis' => $item->jenis,
                'total' => $item->total,
                'jumlah_transaksi' => $item->jumlah_transaksi,
            ];
        })->toArray();
    }
}
