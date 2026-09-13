<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Form;
use App\Models\Donasi;
use App\Models\JenisDonasi;
use App\Models\MetodePembayaran;
use App\Models\Fundraiser;
use App\Models\SumberDanaPenyaluran;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Notifications\Notification; // Keep if you use notifications elsewhere
use Filament\Support\Enums\IconPosition; // Keep for export actions
use Illuminate\Support\HtmlString; // Keep if used, or remove
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Import untuk ekspor data
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Actions\Action; // For the refresh button

class LaporanPemasukan extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.laporan-pemasukan'; //
    protected static ?string $navigationGroup = 'Laporan & Keuangan'; //
    protected static ?string $title = 'Laporan Pemasukan Donasi'; //
    protected static ?string $navigationLabel = 'Pemasukan Donasi'; //
    protected static ?int $navigationSort = 1; //

    // Form data state
    public ?array $data = [];

    // Properti untuk menyimpan nilai filter
    public ?string $startDate = '2025-01-01';
    public ?string $endDate = null;
    public ?int $jenisDonasiId = null;
    public ?int $metodePembayaranId = null;
    public ?int $fundraiserId = null;
    public ?string $statusKonfirmasi = 'verified';
    public bool $groupByJenisDonasi = false;
    public bool $showBarangOnly = false;
    public bool $showUangOnly = false;
    public bool $showHambaAllahOnly = false;
    public ?string $kategoriInfaqTerikat = null; // Filter kategori infaq terikat
    public ?int $sumberDanaId = null; // Filter sumber dana penyaluran

    // Untuk menyimpan total pemasukan (current period)
    public float $totalPemasukan = 0;
    public float $totalNilaiBarang = 0;
    public float $grandTotalPemasukan = 0;
    public int $totalTransaksi = 0;
    public array $summaryByJenisDonasi = [];
    
    // Breakdown detail untuk ringkasan yang lebih informatif
    public int $totalDonatur = 0;
    public float $totalZakat = 0;
    public float $totalInfaq = 0;
    public float $totalSedekah = 0;
    public float $totalCSR = 0;
    public int $transaksiVerified = 0;
    public int $transaksiPending = 0;
    public int $transaksiRejected = 0;

    // Untuk menyimpan total pemasukan (previous period)
    public float $totalPemasukanPrev = 0;
    public float $totalNilaiBarangPrev = 0;
    public float $grandTotalPemasukanPrev = 0;
    public int $totalTransaksiPrev = 0;
    public ?string $previousPeriodLabel = null;

    // Untuk menyimpan perubahan persentase
    public ?float $pemasukanChange = null;
    public ?float $nilaiBarangChange = null;
    public ?float $grandTotalChange = null;
    public ?float $transaksiChange = null;

    public function mount(): void
    {
        // Set default endDate
        $this->endDate = now()->format('Y-m-d');
        
        // Calculate metrics dengan default values
        $this->calculateAllMetrics();
    }

    // Override to prevent Infolist confusion
    protected function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }
    
    // Disable infolist
    public function hasInfolist(): bool
    {
        return false;
    }
    
    // Define form - hapus getFormStatePath yang null
    // Form akan otomatis bind ke public properties
    
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data'); // Gunakan data sebagai state path
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Filter Laporan')
                ->description('Sesuaikan filter untuk melihat data yang diinginkan')
                ->icon('heroicon-o-funnel') //
                ->collapsible()
                ->columns([
                    'sm' => 2,
                    'md' => 3,
                    'lg' => 4,
                ])
                ->schema([
                    Grid::make()
                        ->columnSpan(2)
                        ->schema([
                            DatePicker::make('startDate')
                                ->label('Dari Tanggal')
                                ->live()
                                ->maxDate(fn (?string $state, callable $get) => $get('endDate') ?: now())
                                ->default('2025-01-01'),
                            DatePicker::make('endDate')
                                ->label('Sampai Tanggal')
                                ->live()
                                ->minDate(fn (?string $state, callable $get) => $get('startDate') ?: null)
                                ->default(now()),
                        ]),
                    
                    Select::make('jenisDonasiId')
                        ->label('Jenis Donasi')
                        ->options(JenisDonasi::where('aktif', true)->pluck('nama', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $this->showBarangOnly = false;
                            // Reset kategori infaq terikat jika bukan infaq terikat atau jika jenisDonasiId null
                            if (!$state) {
                                // Jika jenis donasi di-clear, reset kategori infaq juga
                                $this->kategoriInfaqTerikat = null;
                                $set('kategoriInfaqTerikat', null);
                            } else {
                                $jenisDonasi = JenisDonasi::find($state);
                                if ($jenisDonasi && !str_contains(strtolower($jenisDonasi->nama), 'infaq terikat')) {
                                    $this->kategoriInfaqTerikat = null;
                                    $set('kategoriInfaqTerikat', null);
                                }
                            }
                        }),
                    
                    Select::make('kategoriInfaqTerikat')
                        ->label('Kategori Infaq Terikat')
                        ->options(function () {
                            // Ambil unique kategori dari database
                            return Donasi::query()
                                ->whereHas('jenisDonasi', function ($query) {
                                    $query->where('nama', 'LIKE', '%Infaq Terikat%');
                                })
                                ->whereNotNull('keterangan_infak_khusus')
                                ->where('keterangan_infak_khusus', '!=', '')
                                ->distinct()
                                ->pluck('keterangan_infak_khusus', 'keterangan_infak_khusus');
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->visible(function (callable $get) {
                            // Hanya tampil jika jenis donasi adalah Infaq Terikat atau tidak ada filter jenis
                            if (!$get('jenisDonasiId')) {
                                return true; // Tampilkan jika tidak ada filter jenis donasi
                            }
                            $jenisDonasi = JenisDonasi::find($get('jenisDonasiId'));
                            return $jenisDonasi && str_contains(strtolower($jenisDonasi->nama), 'infaq terikat');
                        })
                        ->placeholder('Semua Kategori'),
                    
                    Select::make('sumberDanaId')
                        ->label('Sumber Dana')
                        ->options(SumberDanaPenyaluran::where('aktif', true)->pluck('nama_sumber_dana', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->placeholder('Semua Sumber Dana'),
                    
                    Select::make('metodePembayaranId')
                        ->label('Metode Pembayaran')
                        ->options(MetodePembayaran::where('aktif', true)->pluck('nama', 'id')) //
                        ->searchable() //
                        ->preload() //
                        ->nullable()
                        ->live(), //
                    
                    Select::make('fundraiserId')
                        ->label('Fundraiser')
                        ->options(Fundraiser::where('aktif', true)->pluck('nama_fundraiser', 'id')) //
                        ->searchable() //
                        ->preload() //
                        ->nullable()
                        ->live(), //
                    
                    Select::make('statusKonfirmasi')
                        ->label('Status Konfirmasi')
                        ->options([
                            'all' => 'Semua Status',
                            'pending' => 'Pending',
                            'verified' => 'Terverifikasi',
                            'rejected' => 'Ditolak',
                        ]) //
                        ->default('verified') //
                        ->live(), //
                    
                    Toggle::make('groupByJenisDonasi')
                        ->label('Kelompokkan per Jenis Donasi (Summary)')
                        ->helperText('Menampilkan ringkasan per jenis donasi di bawah tabel')
                        ->live(), //
                    
                    Toggle::make('showBarangOnly')
                        ->label('Hanya Donasi Barang')
                        ->live() //
                        ->afterStateUpdated(function (bool $state) { //
                            if ($state) {
                                $this->showUangOnly = false;
                                $this->jenisDonasiId = null; 
                            }
                        }),
                    
                    Toggle::make('showUangOnly')
                        ->label('Hanya Donasi Uang')
                        ->live() //
                        ->afterStateUpdated(function (bool $state) { //
                            if ($state) {
                                $this->showBarangOnly = false;
                                $this->jenisDonasiId = null;
                            }
                        }),
                    
                    Toggle::make('showHambaAllahOnly')
                        ->label('Hanya Donasi Hamba Allah')
                        ->live(), //
                ]),
        ];
    }
    
    // Add a method for the refresh button
    public function refreshDataAction(): Action
    {
        return Action::make('refreshData')
            ->label('Refresh Data')
            ->icon('heroicon-o-arrow-path')
            ->color('secondary')
            ->action(function () {
                // Call calculateAllMetrics instead, which internally calls calculateTotals with proper parameters
                $this->calculateAllMetrics();
                
                // Notify the user that data has been refreshed.
                Notification::make()
                    ->title('Data berhasil di-refresh')
                    ->success()
                    ->send();
            });
    }

    // We'll override getHeaderActions to include our custom refresh button
    protected function getHeaderActions(): array
    {
        return []; // Empty array - no header actions
    }


    public function updated($propertyName): void
    {
        // Sinkronisasi form data ke properties
        if ($propertyName === 'data.startDate') {
            $this->startDate = $this->data['startDate'] ?? null;
        } elseif ($propertyName === 'data.endDate') {
            $this->endDate = $this->data['endDate'] ?? null;
        } elseif ($propertyName === 'data.jenisDonasiId') {
            $this->jenisDonasiId = $this->data['jenisDonasiId'] ?? null;
        } elseif ($propertyName === 'data.kategoriInfaqTerikat') {
            $this->kategoriInfaqTerikat = $this->data['kategoriInfaqTerikat'] ?? null;
        } elseif ($propertyName === 'data.sumberDanaId') {
            $this->sumberDanaId = $this->data['sumberDanaId'] ?? null;
        } elseif ($propertyName === 'data.metodePembayaranId') {
            $this->metodePembayaranId = $this->data['metodePembayaranId'] ?? null;
        } elseif ($propertyName === 'data.fundraiserId') {
            $this->fundraiserId = $this->data['fundraiserId'] ?? null;
        } elseif ($propertyName === 'data.statusKonfirmasi') {
            $this->statusKonfirmasi = $this->data['statusKonfirmasi'] ?? 'verified';
        } elseif ($propertyName === 'data.groupByJenisDonasi') {
            $this->groupByJenisDonasi = $this->data['groupByJenisDonasi'] ?? false;
        } elseif ($propertyName === 'data.showBarangOnly') {
            $this->showBarangOnly = $this->data['showBarangOnly'] ?? false;
        } elseif ($propertyName === 'data.showUangOnly') {
            $this->showUangOnly = $this->data['showUangOnly'] ?? false;
        } elseif ($propertyName === 'data.showHambaAllahOnly') {
            $this->showHambaAllahOnly = $this->data['showHambaAllahOnly'] ?? false;
        }
        
        // Reset pagination saat filter berubah
        $this->resetPage();
        
        // Recalculate metrics
        $this->calculateAllMetrics();
        
        // Reset table query untuk refresh data
        $this->resetTable();
    }
    
    protected function resetTable(): void
    {
        // Force refresh table dengan reset cached query
        $this->dispatch('$refresh');
    }
    
    // Method untuk cek apakah ada filter yang aktif (selain tanggal dan status verified)
    public function hasActiveFilters(): bool
    {
        return $this->jenisDonasiId !== null
            || $this->metodePembayaranId !== null
            || $this->fundraiserId !== null
            || $this->sumberDanaId !== null
            || $this->kategoriInfaqTerikat !== null
            || $this->statusKonfirmasi !== 'verified'
            || $this->showBarangOnly
            || $this->showUangOnly
            || $this->showHambaAllahOnly;
    }
    
    // Method untuk mendapatkan deskripsi filter yang aktif
    public function getActiveFiltersDescription(): string
    {
        $filters = [];
        
        if ($this->jenisDonasiId) {
            $jenis = JenisDonasi::find($this->jenisDonasiId);
            $filters[] = 'Jenis: ' . ($jenis?->nama ?? 'Unknown');
        }
        
        if ($this->kategoriInfaqTerikat) {
            $filters[] = 'Kategori: ' . $this->kategoriInfaqTerikat;
        }
        
        if ($this->sumberDanaId) {
            $sumber = SumberDanaPenyaluran::find($this->sumberDanaId);
            $filters[] = 'Sumber Dana: ' . ($sumber?->nama_sumber_dana ?? 'Unknown');
        }
        
        if ($this->metodePembayaranId) {
            $metode = MetodePembayaran::find($this->metodePembayaranId);
            $filters[] = 'Metode: ' . ($metode?->nama ?? 'Unknown');
        }
        
        if ($this->fundraiserId) {
            $fundraiser = Fundraiser::find($this->fundraiserId);
            $filters[] = 'Fundraiser: ' . ($fundraiser?->nama_fundraiser ?? 'Unknown');
        }
        
        if ($this->statusKonfirmasi !== 'verified') {
            $statusLabel = match($this->statusKonfirmasi) {
                'all' => 'Semua Status',
                'pending' => 'Pending',
                'rejected' => 'Ditolak',
                default => 'Terverifikasi'
            };
            $filters[] = 'Status: ' . $statusLabel;
        }
        
        if ($this->showBarangOnly) {
            $filters[] = 'Hanya Barang';
        }
        
        if ($this->showUangOnly) {
            $filters[] = 'Hanya Uang';
        }
        
        if ($this->showHambaAllahOnly) {
            $filters[] = 'Hanya Hamba Allah';
        }
        
        return !empty($filters) ? implode(' • ', $filters) : 'Semua Data (Terverifikasi)';
    }
    
    // Method untuk reset semua filter ke default
    public function resetAllFilters(): void
    {
        $this->jenisDonasiId = null;
        $this->metodePembayaranId = null;
        $this->fundraiserId = null;
        $this->sumberDanaId = null;
        $this->kategoriInfaqTerikat = null;
        $this->statusKonfirmasi = 'verified';
        $this->showBarangOnly = false;
        $this->showUangOnly = false;
        $this->showHambaAllahOnly = false;
        $this->groupByJenisDonasi = false;
        
        // Sync form
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'jenisDonasiId' => null,
            'kategoriInfaqTerikat' => null,
            'sumberDanaId' => null,
            'metodePembayaranId' => null,
            'fundraiserId' => null,
            'statusKonfirmasi' => 'verified',
            'groupByJenisDonasi' => false,
            'showBarangOnly' => false,
            'showUangOnly' => false,
            'showHambaAllahOnly' => false,
        ]);
        
        // Recalculate metrics
        $this->calculateAllMetrics();
        
        // Reset table
        $this->resetTable();
        
        // Notify user
        Notification::make()
            ->title('Filter berhasil direset')
            ->success()
            ->send();
    }

    protected function calculateAllMetrics(): void
    {
        // Calculate for current period
        $this->calculateTotals($this->startDate, $this->endDate, false);
        
        // Calculate for previous period
        if ($this->startDate && $this->endDate) {
            $currentStart = Carbon::parse($this->startDate);
            $currentEnd = Carbon::parse($this->endDate);

            // Calculate the duration of the current period in days
            $durationInDays = $currentEnd->diffInDays($currentStart) + 1; // +1 to include both start and end dates
            
            // Calculate the previous period with the same duration
            $prevEndDate = $currentStart->copy()->subDay(); // Day before current start
            $prevStartDate = $prevEndDate->copy()->subDays($durationInDays - 1); // Same duration as current period
            
            if ($prevStartDate && $prevEndDate) {
                $this->calculateTotals($prevStartDate->format('Y-m-d'), $prevEndDate->format('Y-m-d'), true);
                $this->previousPeriodLabel = $prevStartDate->translatedFormat('d M Y') . ' - ' . $prevEndDate->translatedFormat('d M Y');
            } else {
                // Fallback
                $this->resetPreviousPeriodData();
                $this->previousPeriodLabel = 'N/A';
            }

            $this->calculatePercentageChanges();
        } else {
            $this->resetPreviousPeriodData();
            $this->resetPercentageChanges();
        }
        
        if ($this->groupByJenisDonasi) {
            $this->calculateSummaryByJenisDonasi();
        } else {
            $this->summaryByJenisDonasi = [];
        }
    }

    protected function resetPreviousPeriodData(): void
    {
        $this->totalPemasukanPrev = 0;
        $this->totalNilaiBarangPrev = 0;
        $this->grandTotalPemasukanPrev = 0;
        $this->totalTransaksiPrev = 0;
        $this->previousPeriodLabel = null;
    }
    
    /**
     * Query untuk RINGKASAN - PERSIS SAMA dengan RingkasanStatistikUtama
     * HANYA filter: verified + exclude Penyaluran Langsung + tanggal
     * TIDAK ada filter user lainnya
     * @return Builder<Donasi>
     */
    protected function getSummaryQuery(?string $startDate, ?string $endDate)
    {
        $query = Donasi::where('status_konfirmasi', 'verified')
            ->whereHas('jenisDonasi', function($q) {
                $q->where('nama', '!=', 'Penyaluran Langsung');
            });

        // Filter tanggal dengan whereBetween (PERSIS seperti RingkasanStatistikUtama)
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_donasi', [$startDate, $endDate]);
        }
        
        return $query;
    }
    
    /**
     * Query untuk TABEL - dengan semua filter user
     */
    protected function getBaseQuery(?string $startDate, ?string $endDate): Builder
    {
        $query = Donasi::query();

        // EXCLUDE Penyaluran Langsung (konsisten dengan RingkasanStatistikUtama)
        $query->whereHas('jenisDonasi', function($q) {
            $q->where('nama', '!=', 'Penyaluran Langsung');
        });

        // Gunakan whereBetween seperti RingkasanStatistikUtama agar konsisten
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_donasi', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('tanggal_donasi', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('tanggal_donasi', '<=', $endDate);
        }
        
        // Status Konfirmasi - DEFAULT VERIFIED jika tidak ada filter aktif
        // Ini PENTING untuk konsistensi dengan RingkasanStatistikUtama
        if ($this->statusKonfirmasi && $this->statusKonfirmasi !== 'all') {
            $query->where('status_konfirmasi', $this->statusKonfirmasi);
        } elseif (!$this->statusKonfirmasi) {
            // Fallback ke verified jika property kosong
            $query->where('status_konfirmasi', 'verified');
        }
        
        // Apply other filters HANYA jika ada
        if ($this->jenisDonasiId) {
            $query->where('jenis_donasi_id', $this->jenisDonasiId);
        }
        
        // Filter Kategori Infaq Terikat
        if ($this->kategoriInfaqTerikat) {
            $query->where('keterangan_infak_khusus', $this->kategoriInfaqTerikat);
        }
        
        // Filter Sumber Dana
        if ($this->sumberDanaId) {
            $query->whereHas('jenisDonasi', function ($q) {
                $q->where('sumber_dana_penyaluran_id', $this->sumberDanaId);
            });
        }
        
        if ($this->metodePembayaranId) {
            $query->where('metode_pembayaran_id', $this->metodePembayaranId);
        }
        if ($this->fundraiserId) {
            $query->where('fundraiser_id', $this->fundraiserId);
        }
        // Filter status sudah dihandle di atas, tidak perlu lagi di sini
        if ($this->showBarangOnly) {
            $query->whereHas('jenisDonasi', function ($q) {
                $q->where('apakah_barang', true);
            });
        }
        if ($this->showUangOnly) {
            $query->whereHas('jenisDonasi', function ($q) {
                $q->where('apakah_barang', false);
            });
        }
        if ($this->showHambaAllahOnly) {
            $query->where('atas_nama_hamba_allah', true);
        }
        
        // Eager load relations untuk performa tabel
        $query->with(['donatur', 'jenisDonasi.sumberDanaPenyaluran', 'metodePembayaran', 'fundraiser', 'dicatatOleh']);
        
        return $query;
    }

    protected function calculateTotals(?string $startDate, ?string $endDate, bool $isPreviousPeriod): void
    {
        if (!$startDate || !$endDate) {
            if ($isPreviousPeriod) $this->resetPreviousPeriodData();
            return;
        }
        
        // GUNAKAN getSummaryQuery() untuk RINGKASAN
        // Query ini PERSIS SAMA dengan RingkasanStatistikUtama
        // TIDAK terpengaruh filter user (jenis donasi, fundraiser, dll)
        $query = $this->getSummaryQuery($startDate, $endDate);

        if ($isPreviousPeriod) {
            // Previous period calculation
            $this->totalPemasukanPrev = (clone $query)
                ->whereHas('jenisDonasi', fn ($q) => $q->where('apakah_barang', false))
                ->sum('jumlah');
            $this->totalNilaiBarangPrev = (clone $query)
                ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
                ->where('jenis_donasis.apakah_barang', true)
                ->sum(DB::raw('CASE WHEN donasis.perkiraan_nilai_barang IS NOT NULL AND donasis.perkiraan_nilai_barang > 0 THEN donasis.perkiraan_nilai_barang ELSE donasis.jumlah END'));
            $this->grandTotalPemasukanPrev = (clone $query)
                ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));
            $this->totalTransaksiPrev = (clone $query)->count();
        } else {
            // Current period - hitung semua breakdown
            $this->totalPemasukan = (clone $query)
                ->whereHas('jenisDonasi', fn ($q) => $q->where('apakah_barang', false))
                ->sum('jumlah');
            $this->totalNilaiBarang = (clone $query)
                ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id')
                ->where('jenis_donasis.apakah_barang', true)
                ->sum(DB::raw('CASE WHEN donasis.perkiraan_nilai_barang IS NOT NULL AND donasis.perkiraan_nilai_barang > 0 THEN donasis.perkiraan_nilai_barang ELSE donasis.jumlah END'));
            $this->grandTotalPemasukan = (clone $query)
                ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));
            $this->totalTransaksi = (clone $query)->count();
            
            // Hitung total donatur unik
            $this->totalDonatur = (clone $query)->distinct('donatur_id')->count('donatur_id');
            
            // Breakdown per kategori donasi (TANPA filter user)
            $this->totalZakat = (clone $query)
                ->whereHas('jenisDonasi', function ($q) {
                    $q->whereHas('sumberDanaPenyaluran', function ($sq) {
                        $sq->where('nama_sumber_dana', 'LIKE', '%zakat%');
                    });
                })
                ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));
            
            $this->totalInfaq = (clone $query)
                ->whereHas('jenisDonasi', function ($q) {
                    $q->whereHas('sumberDanaPenyaluran', function ($sq) {
                        $sq->where('nama_sumber_dana', 'LIKE', '%infaq%');
                    });
                })
                ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));
            
            $this->totalSedekah = (clone $query)
                ->whereHas('jenisDonasi', function ($q) {
                    $q->whereHas('sumberDanaPenyaluran', function ($sq) {
                        $sq->where('nama_sumber_dana', 'LIKE', '%sedekah%');
                    });
                })
                ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));
            
            $this->totalCSR = (clone $query)
                ->whereHas('jenisDonasi', function ($q) {
                    $q->whereHas('sumberDanaPenyaluran', function ($sq) {
                        $sq->where('nama_sumber_dana', 'LIKE', '%csr%');
                    });
                })
                ->sum(DB::raw('jumlah + IFNULL(perkiraan_nilai_barang, 0)'));
            
            // Breakdown per status - semua sudah verified (karena menggunakan getSummaryQuery)
            $this->transaksiVerified = $this->totalTransaksi;
            $this->transaksiPending = 0;
            $this->transaksiRejected = 0;
        }
    }

    protected function calculatePercentageChanges(): void
    {
        $this->pemasukanChange = $this->calculateChange($this->totalPemasukan, $this->totalPemasukanPrev);
        $this->nilaiBarangChange = $this->calculateChange($this->totalNilaiBarang, $this->totalNilaiBarangPrev);
        $this->grandTotalChange = $this->calculateChange($this->grandTotalPemasukan, $this->grandTotalPemasukanPrev);
        $this->transaksiChange = $this->calculateChange($this->totalTransaksi, $this->totalTransaksiPrev);
    }
    
    protected function resetPercentageChanges(): void
    {
        $this->pemasukanChange = null;
        $this->nilaiBarangChange = null;
        $this->grandTotalChange = null;
        $this->transaksiChange = null;
    }

    private function calculateChange($current, $previous): ?float
    {
        if ($previous == 0) {
            return ($current > 0) ? 100.0 : (($current == 0) ? 0.0 : null); // If prev is 0, and current is also 0, change is 0%. If current > 0, it's 100% increase from nothing.
        }
        // Avoid division by zero if previous is zero and current is also zero (already handled by above)
        // if ($current == 0 && $previous == 0){
        //     return 0.0;
        // }
        return (($current - $previous) / $previous) * 100;
    }

    // REMOVED: protected function prepareDonationTrendData(): void { ... }


    protected function getTableQuery(): Builder
    {
        // GUNAKAN getBaseQuery() yang sama dengan ringkasan
        // Agar data tabel dan ringkasan SELALU SINKRON
        return $this->getBaseQuery($this->startDate, $this->endDate);
    }
    
    // Override method untuk Filament v3
    protected function getModel(): string
    {
        return Donasi::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getTableQuery())
            ->columns([
                TextColumn::make('nomor_transaksi_unik') //
                    ->label('No. Transaksi') //
                    ->searchable() //
                    ->sortable() //
                    ->copyable(), //
                
                TextColumn::make('tanggal_donasi') //
                    ->label('Tgl. Donasi') //
                    ->date('d M Y') //
                    ->sortable(), //
                
                TextColumn::make('donatur.nama') //
                    ->label('Donatur') //
                    ->searchable() //
                    ->sortable() //
                    ->formatStateUsing(fn ($state, $record) => $record->atas_nama_hamba_allah ? 'Hamba Allah' : $state), //
                
                TextColumn::make('jenisDonasi.nama')
                    ->label('Jenis Donasi')
                    ->badge()
                    ->color(fn ($record) => $record->jenisDonasi?->apakah_barang ? 'warning' : 'primary')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('keterangan_infak_khusus')
                    ->label('Kategori Infaq Terikat')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->keterangan_infak_khusus)
                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                
                TextColumn::make('jenisDonasi.sumberDanaPenyaluran.nama_sumber_dana')
                    ->label('Sumber Dana')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->default('-'),
                
                TextColumn::make('metodePembayaran.nama') //
                    ->label('Metode Bayar') //
                    ->toggleable() //
                    ->sortable(), //
                
                TextColumn::make('fundraiser.nama_fundraiser') //
                    ->label('Fundraiser') //
                    ->toggleable(isToggledHiddenByDefault: true) //
                    ->sortable(), //
                
                TextColumn::make('jumlah') //
                    ->label('Jumlah (Uang)') //
                    ->money('IDR') //
                    ->sortable() //
                    ->formatStateUsing(fn ($state, $record) => $record->jenisDonasi?->apakah_barang ? '-' : 'Rp ' . number_format($state, 0, ',', '.')), //
                
                TextColumn::make('perkiraan_nilai_barang') //
                    ->label('Nilai Barang') //
                    ->money('IDR') //
                    ->sortable() //
                    ->toggleable(isToggledHiddenByDefault: true) // Disembunyikan, bisa ditampilkan via toggle
                    ->formatStateUsing(fn ($state, $record) => $record->jenisDonasi?->apakah_barang ? ('Rp ' . number_format($state, 0, ',', '.')) : '-'), //
                
                TextColumn::make('status_konfirmasi') //
                    ->label('Status') //
                    ->badge() //
                    ->color(fn (string $state): string => match ($state) { //
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) { //
                        'pending' => 'Pending',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    })
                    ->searchable() //
                    ->sortable(), //
                
                IconColumn::make('atas_nama_hamba_allah') //
                    ->boolean() //
                    ->label('Anonim') //
                    ->toggleable(), //
                
                TextColumn::make('dicatatOleh.name') //
                    ->label('Dicatat Oleh') //
                    ->toggleable(isToggledHiddenByDefault: true) //
                    ->sortable(), //
                
                TextColumn::make('created_at') //
                    ->label('Tgl Input') //
                    ->dateTime('d M Y H:i') //
                    ->toggleable(isToggledHiddenByDefault: true) //
                    ->sortable(), //
            ])
            ->filters([
                // TIDAK ADA TABLE FILTER - semua filter sudah ada di form di atas
                // Agar tidak ada duplikasi dan konflik filter
            ])
            ->defaultSort('tanggal_donasi', 'desc') //
            ->paginationPageOptions([25, 50, 100, 250])
            ->defaultPaginationPageOption(25)
            ->actions([
                // Aksi per baris jika diperlukan
            ])
            ->bulkActions([ //
                ExportBulkAction::make() //
                    ->label('Ekspor Data Terpilih') //
                    ->exports([
                        ExcelExport::make()
                            ->withColumns([
                                Column::make('nomor_transaksi_unik')->heading('No. Transaksi'),
                                Column::make('tanggal_donasi')->heading('Tanggal Donasi')->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y')),
                                Column::make('donatur.nama')->heading('Nama Donatur')->formatStateUsing(fn ($state, $record) => $record->atas_nama_hamba_allah ? 'Hamba Allah' : $state),
                                Column::make('jenisDonasi.nama')->heading('Jenis Donasi'),
                                Column::make('keterangan_infak_khusus')->heading('Kategori Infaq Terikat'),
                                Column::make('jenisDonasi.sumberDanaPenyaluran.nama_sumber_dana')->heading('Sumber Dana'),
                                Column::make('metodePembayaran.nama')->heading('Metode Pembayaran'),
                                Column::make('jumlah')->heading('Jumlah (Rp)')->formatStateUsing(fn ($state, $record) => $record->jenisDonasi?->apakah_barang ? 0 : $state),
                                Column::make('perkiraan_nilai_barang')->heading('Nilai Barang (Rp)')->formatStateUsing(fn ($state, $record) => $record->jenisDonasi?->apakah_barang ? $state : 0),
                                Column::make('status_konfirmasi')->heading('Status Konfirmasi'),
                                Column::make('atas_nama_hamba_allah')->heading('Anonim')->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                                Column::make('fundraiser.nama_fundraiser')->heading('Fundraiser'),
                                Column::make('dicatatOleh.name')->heading('Dicatat Oleh'),
                            ])
                    ])
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('refreshTable')
                    ->label('Refresh Tabel')
                    ->icon('heroicon-o-arrow-path')
                    ->color('secondary')
                    ->action(function () {
                        $this->calculateAllMetrics();
                        
                        Notification::make()
                            ->title('Tabel berhasil di-refresh')
                            ->success()
                            ->send();
                    }),
                ExportAction::make() // Keep the export action
            ]);
    }

    public function calculateSummaryByJenisDonasi(): void
    {
        // This uses the main table query, which now includes all filters via getBaseQuery
        $summary = $this->getTableQuery() 
            ->getQuery() 
            ->select(
                'jenis_donasi_id',
                DB::raw('MAX(jenis_donasis.nama) as jenis_donasi_name'), 
                DB::raw('SUM(donasis.jumlah) as total_jumlah_raw'), // Sum of 'jumlah' column
                DB::raw('SUM(donasis.perkiraan_nilai_barang) as total_nilai_barang_raw') // Sum of 'perkiraan_nilai_barang'
            )
            ->join('jenis_donasis', 'donasis.jenis_donasi_id', '=', 'jenis_donasis.id') 
            ->groupBy('jenis_donasi_id')
            ->get();
    
        $this->summaryByJenisDonasi = $summary->map(function ($item) {
            $jenisDonasi = JenisDonasi::find($item->jenis_donasi_id);
            $jumlahUang = 0;
            $nilaiBarang = 0;

            if ($jenisDonasi) {
                if ($jenisDonasi->apakah_barang) {
                    // If it's a goods donation, perkiraan_nilai_barang is its value. 'jumlah' should be 0 or ignored.
                    $nilaiBarang = $item->total_nilai_barang_raw;
                } else {
                    // If it's a money donation, 'jumlah' is its value. 'perkiraan_nilai_barang' should be 0 or ignored.
                    $jumlahUang = $item->total_jumlah_raw;
                }
            }
            
            return [
                'jenis_donasi_id' => $item->jenis_donasi_id, //
                'jenis_donasi_name' => $item->jenis_donasi_name, //
                'total_jumlah' => $jumlahUang, // Corrected to use processed sums
                'total_nilai_barang' => $nilaiBarang, // Corrected to use processed sums
                'total' => $jumlahUang + $nilaiBarang, //
            ];
        })->toArray();
    }
    
    // REMOVED: protected function getHeaderWidgets(): array { ... }
    // This is not needed as chart is removed.

    // Method for JS to call if needed for chart (now removed)
    // public function getDonationTrendDataForJs() { return null; } // Or remove if not used by anything else

    // Query string support - DISABLED karena conflict dengan typed properties
    // Filter akan tetap bekerja melalui form, tidak perlu URL parameters
    protected $queryString = [];
}