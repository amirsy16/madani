<?php

namespace App\Livewire\Filament\Widgets;

use App\Models\Donatur;
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

class DonaturFundraiserTable extends Component implements HasForms, HasTable
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
        $fundraiserId = (int) $this->fundraiserId;

        // Get donatur IDs that have donated via this fundraiser
        $donaturIdsQuery = Donasi::query()
            ->where('fundraiser_id', $fundraiserId)
            ->where('status_konfirmasi', 'verified')
            ->whereNotNull('donatur_id');

        $donaturIdsQuery = $this->applyTimePeriodFilterToQuery($donaturIdsQuery);
        $donaturIds = $donaturIdsQuery->distinct()->pluck('donatur_id');

        // Subquery agregat per donatur — pakai query builder agar parameter ter-binding
        $donasiSubQuery = function (string $aggregate) use ($fundraiserId) {
            return function ($query) use ($fundraiserId, $aggregate) {
                $query->selectRaw($aggregate)
                    ->from('donasis')
                    ->whereColumn('donasis.donatur_id', 'donaturs.id')
                    ->where('donasis.fundraiser_id', $fundraiserId)
                    ->where('donasis.status_konfirmasi', 'verified');

                $this->applyTimePeriodFilterToQuery($query);
            };
        };

        $query = Donatur::query()
            ->whereIn('id', $donaturIds)
            ->selectSub($donasiSubQuery('COUNT(*)'), 'total_donasi_count')
            ->selectSub($donasiSubQuery('SUM(COALESCE(jumlah, 0) + COALESCE(perkiraan_nilai_barang, 0))'), 'total_kontribusi')
            ->selectSub($donasiSubQuery('MAX(tanggal_donasi)'), 'donasi_terakhir');
        
        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('kode_donatur')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kode donatur disalin!')
                    ->weight('medium')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Donatur')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => match($record->gender) {
                        'male' => '♂ Laki-laki',
                        'female' => '♀ Perempuan',
                        default => '🏢 Organisasi',
                    }),

                Tables\Columns\TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->url(fn ($record) => $record->nomor_hp ? 'https://wa.me/' . $record->nomor_hp : null)
                    ->openUrlInNewTab()
                    ->color('success')
                    ->icon('heroicon-m-phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_donasi_count')
                    ->label('Jml Donasi')
                    ->badge()
                    ->color('info')
                    ->suffix('x')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_kontribusi')
                    ->label('Total Kontribusi')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('donasi_terakhir')
                    ->label('Donasi Terakhir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('total_kontribusi', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->tooltip('Hubungi via WhatsApp')
                    ->url(fn ($record) => $record->nomor_hp ? 
                        'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->nomor_hp) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->nomor_hp)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'donatur-fundraiser-' . date('Y-m-d-H-i-s') . '.xlsx')
                        ])
                ]),
            ])
            ->emptyStateHeading('Tidak ada donatur')
            ->emptyStateDescription('Belum ada donatur untuk fundraiser ini pada periode yang dipilih.')
            ->emptyStateIcon('heroicon-o-users');
    }
    
    protected function applyTimePeriodFilterToQuery($query)
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
        return view('livewire.filament.widgets.donatur-fundraiser-table');
    }
}
