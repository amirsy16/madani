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

class TransaksiFundraiserTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $fundraiserId;
    public $timePeriod = 'all_time';

    public function mount($fundraiserId, $timePeriod = 'all_time'): void
    {
        $this->fundraiserId = $fundraiserId;
        $this->timePeriod = $timePeriod;
    }

    public function table(Table $table): Table
    {
        $query = Donasi::query()
            ->where('fundraiser_id', $this->fundraiserId)
            ->where('status_konfirmasi', 'verified')
            ->with(['donatur', 'jenisDonasi', 'metodePembayaran']);
        
        // Apply period filter
        $query = $this->applyTimePeriodFilter($query);
        
        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_donasi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_transaksi_unik')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor transaksi disalin!')
                    ->weight('medium')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('donatur.nama')
                    ->label('Donatur')
                    ->searchable()
                    ->description(fn ($record) => $record->atas_nama_hamba_allah ? 'Hamba Allah' : null)
                    ->formatStateUsing(fn ($state, $record) => $record->atas_nama_hamba_allah ? 'Hamba Allah' : ($state ?? '-')),

                Tables\Columns\TextColumn::make('jenisDonasi.nama')
                    ->label('Jenis Donasi')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('metodePembayaran.nama')
                    ->label('Metode')
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
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_nilai')
                    ->label('Total')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => ($record->jumlah ?? 0) + ($record->perkiraan_nilai_barang ?? 0))
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('keterangan_infak_khusus')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_donasi', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'transaksi-fundraiser-' . date('Y-m-d-H-i-s') . '.xlsx')
                        ])
                ]),
            ])
            ->emptyStateHeading('Tidak ada transaksi')
            ->emptyStateDescription('Belum ada transaksi untuk fundraiser ini pada periode yang dipilih.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
    
    protected function applyTimePeriodFilter($query)
    {
        switch ($this->timePeriod) {
            case 'current_month':
                return $query->whereMonth('tanggal_donasi', Carbon::now()->month)
                            ->whereYear('tanggal_donasi', Carbon::now()->year);
                            
            case 'last_month':
                $lastMonth = Carbon::now()->subMonth();
                return $query->whereMonth('tanggal_donasi', $lastMonth->month)
                            ->whereYear('tanggal_donasi', $lastMonth->year);
                            
            case 'current_year':
                return $query->whereYear('tanggal_donasi', Carbon::now()->year);
                
            case 'last_3_months':
                return $query->where('tanggal_donasi', '>=', Carbon::now()->subMonths(3));
                
            case 'last_6_months':
                return $query->where('tanggal_donasi', '>=', Carbon::now()->subMonths(6));
                
            case 'all_time':
            default:
                return $query;
        }
    }

    public function render(): View
    {
        return view('livewire.filament.widgets.transaksi-fundraiser-table');
    }
}
