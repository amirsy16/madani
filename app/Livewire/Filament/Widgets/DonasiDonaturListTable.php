<?php

namespace App\Livewire\Filament\Widgets;

use App\Models\Donasi;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Carbon\Carbon;

class DonasiDonaturListTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $donaturId;
    public $currentPeriode = 'keseluruhan';
    public $customStartDate = null;
    public $customEndDate = null;
    public $currentJenisDonasi = null;

    public function mount($donaturId, $currentPeriode = 'keseluruhan', $customStartDate = null, $customEndDate = null, $currentJenisDonasi = null): void
    {
        $this->donaturId = $donaturId;
        $this->currentPeriode = $currentPeriode;
        $this->customStartDate = $customStartDate;
        $this->customEndDate = $customEndDate;
        $this->currentJenisDonasi = $currentJenisDonasi;
    }

    public function table(Table $table): Table
    {
        $query = Donasi::query()
            ->where('donatur_id', $this->donaturId)
            ->where('status_konfirmasi', 'verified')
            ->with(['jenisDonasi', 'metodePembayaran', 'fundraiser', 'dicatatOleh']);
        
        // Apply jenis donasi filter
        if ($this->currentJenisDonasi) {
            $query->where('jenis_donasi_id', $this->currentJenisDonasi);
        }
        
        // Apply period filter
        $query = $this->applyPeriodeFilter($query);
        
        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_donasi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nomor_transaksi_unik')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor transaksi disalin!')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('jenisDonasi.nama')
                    ->label('Jenis Donasi')
                    ->badge()
                    ->color(fn ($record) => $record->jenisDonasi && $record->jenisDonasi->apakah_barang ? 'purple' : 'success')
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan_infak_khusus')
                    ->label('Kategori')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('metodePembayaran.nama')
                    ->label('Metode Pembayaran')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah Uang')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('perkiraan_nilai_barang')
                    ->label('Nilai Barang')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_nilai')
                    ->label('Total')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => ($record->jumlah ?? 0) + ($record->perkiraan_nilai_barang ?? 0))
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('status_konfirmasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('fundraiser.nama_fundraiser')
                    ->label('Fundraiser')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dicatatOleh.name')
                    ->label('Dicatat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_donasi', 'desc')
            ->striped()
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'donasi-donatur-' . date('Y-m-d-H-i-s') . '.xlsx')
                        ])
                ]),
            ])
            ->emptyStateHeading('Tidak ada donasi')
            ->emptyStateDescription('Belum ada donasi untuk donatur ini.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
    
    protected function applyPeriodeFilter($query)
    {
        switch ($this->currentPeriode) {
            case 'bulan_ini':
                return $query->whereMonth('tanggal_donasi', now()->month)
                            ->whereYear('tanggal_donasi', now()->year);
                            
            case 'bulan_lalu':
                $bulanLalu = now()->subMonth();
                return $query->whereMonth('tanggal_donasi', $bulanLalu->month)
                            ->whereYear('tanggal_donasi', $bulanLalu->year);
                            
            case 'tahun_ini':
                return $query->whereYear('tanggal_donasi', now()->year);
                
            case 'tahun_lalu':
                return $query->whereYear('tanggal_donasi', now()->year - 1);
                
            case '3_bulan':
                return $query->where('tanggal_donasi', '>=', now()->subMonths(3));
                
            case '6_bulan':
                return $query->where('tanggal_donasi', '>=', now()->subMonths(6));
                
            case '12_bulan':
                return $query->where('tanggal_donasi', '>=', now()->subMonths(12));
                
            case 'custom':
                if ($this->customStartDate) {
                    $query->whereDate('tanggal_donasi', '>=', $this->customStartDate);
                }
                if ($this->customEndDate) {
                    $query->whereDate('tanggal_donasi', '<=', $this->customEndDate);
                }
                return $query;
                
            case 'keseluruhan':
            default:
                return $query; // Tidak ada filter tambahan
        }
    }

    public function render(): View
    {
        return view('livewire.filament.widgets.donasi-donatur-list-table');
    }
}
