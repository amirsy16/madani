<?php

namespace App\Filament\Widgets;

use App\Models\Fundraiser;
use Carbon\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class FundraiserPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Performa Fundraiser';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    // Filter properties
    public string $timePeriod = 'all_time';  // Changed default to all_time

    public int $limit = 10;

    // Jangan tampilkan di dashboard utama, hanya di halaman Analisis Data
    protected static bool $isDiscovered = false;

    /**
     * Judul dibaca saat render dari state filter ter-hydrate sehingga
     * langsung mengikuti nilai terbaru.
     */
    private function timePeriodAktif(): string
    {
        return (string) ($this->tableFilters['time_period']['value'] ?? $this->timePeriod);
    }

    private function limitAktif(): int
    {
        return (int) ($this->tableFilters['limit']['value'] ?? $this->limit);
    }

    public function getHeading(): string
    {
        $periodLabel = match ($this->timePeriodAktif()) {
            'current_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'last_3_months' => '3 Bulan Terakhir',
            'last_6_months' => '6 Bulan Terakhir',
            'current_year' => 'Tahun Ini',
            'all_time' => 'Semua Waktu',
            default => 'Semua Waktu'
        };

        $limitAktif = $this->limitAktif();
        $limitLabel = $limitAktif > 0 ? "Top {$limitAktif}" : 'Semua';

        return "Performa Fundraiser - {$limitLabel} ({$periodLabel})";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->badge()
                    ->color(fn ($rowLoop) => match ($rowLoop->iteration) {
                        1 => 'warning', // Gold
                        2 => 'gray',    // Silver
                        3 => 'danger',  // Bronze
                        default => 'primary'
                    }),

                TextColumn::make('nama_fundraiser')
                    ->label('Nama Fundraiser')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('nomor_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('aktif')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->toggleable(),

                TextColumn::make('total_dana_terkumpul')
                    ->label('Total Dana')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('total_transaksi')
                    ->label('Transaksi')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->action(
                        Action::make('lihatTransaksi')
                            ->modalHeading(fn ($record) => "Daftar Transaksi - {$record->nama_fundraiser}")
                            ->modalDescription(fn ($record) => "Total {$record->total_transaksi} transaksi dengan total dana Rp ".number_format($record->total_dana_terkumpul ?? 0, 0, ',', '.'))
                            ->modalWidth('7xl')
                            ->modalContent(fn ($record) => view('filament.widgets.fundraiser-transaksi-modal', [
                                'fundraiser' => $record,
                                'timePeriod' => $this->timePeriod,
                            ]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->slideOver()
                    ),

                TextColumn::make('total_donatur_unique')
                    ->label('Donatur')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->action(
                        Action::make('lihatDonatur')
                            ->modalHeading(fn ($record) => "Daftar Donatur - {$record->nama_fundraiser}")
                            ->modalDescription(fn ($record) => "Total {$record->total_donatur_unique} donatur")
                            ->modalWidth('7xl')
                            ->modalContent(fn ($record) => view('filament.widgets.fundraiser-donatur-modal', [
                                'fundraiser' => $record,
                                'timePeriod' => $this->timePeriod,
                            ]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->slideOver()
                    ),

                TextColumn::make('rata_rata_donasi')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('efisiensi')
                    ->label('Dana/Donatur')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('time_period')
                    ->label('Periode Waktu')
                    ->options([
                        'all_time' => 'Semua Waktu',
                        'current_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                        'last_3_months' => '3 Bulan Terakhir',
                        'last_6_months' => '6 Bulan Terakhir',
                        'current_year' => 'Tahun Ini',
                    ])
                    ->default('all_time')
                    ->query(function ($query, array $data) {
                        $this->timePeriod = $data['value'] ?? 'all_time';

                        // Diterapkan pada fase render (bukan ON-clause join yang
                        // dibangun saat boot) agar nilai baru berlaku seketika.
                        return $this->terapkanPeriodeWaktu($query, $this->timePeriod);
                    }),

                SelectFilter::make('status')
                    ->label('Status Fundraiser')
                    ->options([
                        'all' => 'Semua',
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->default('all')
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'active') {
                            return $query->where('fundraisers.aktif', true);
                        } elseif ($data['value'] === 'inactive') {
                            return $query->where('fundraisers.aktif', false);
                        }

                        return $query;
                    }),

                SelectFilter::make('limit')
                    ->label('Jumlah Data')
                    ->options([
                        5 => 'Top 5',
                        10 => 'Top 10',
                        15 => 'Top 15',
                        20 => 'Top 20',
                        50 => 'Top 50',
                        0 => 'Semua',
                    ])
                    ->default(10)
                    ->query(fn ($query, array $data) => $query)
                    // Limit pada base query agar berlaku seketika (lihat
                    // catatan TopDonaturWidget); 0 (Semua) menghapus limit.
                    ->baseQuery(function ($query, array $data) {
                        $this->limit = (int) ($data['value'] ?? 10);

                        return $query->limit($this->limit > 0 ? $this->limit : null);
                    }),
            ])
            ->defaultSort('total_dana_terkumpul', 'desc')
            ->striped()
            ->paginated(false);
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Fundraiser::query()
            ->select([
                'fundraisers.id',
                'fundraisers.nama_fundraiser',
                'fundraisers.nomor_hp',
                'fundraisers.aktif',
                DB::raw('COUNT(DISTINCT donasis.id) as total_transaksi'),
                DB::raw('COUNT(DISTINCT donasis.donatur_id) as total_donatur_unique'),
                DB::raw('SUM(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as total_dana_terkumpul'),
                DB::raw('AVG(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) as rata_rata_donasi'),
                DB::raw('CASE WHEN COUNT(DISTINCT donasis.donatur_id) > 0 THEN SUM(COALESCE(donasis.jumlah, 0) + COALESCE(donasis.perkiraan_nilai_barang, 0)) / COUNT(DISTINCT donasis.donatur_id) ELSE 0 END as efisiensi'),
            ])
            ->leftJoin('donasis', function ($join) {
                $join->on('fundraisers.id', '=', 'donasis.fundraiser_id')
                    ->where('donasis.status_konfirmasi', '=', 'verified')
                     // Paritas kartu statistik: Penyaluran Langsung tidak masuk kas.
                    ->whereNotIn('donasis.jenis_donasi_id', \App\Models\JenisDonasi::where('nama', 'Penyaluran Langsung')->select('id'));

            });

        $baseQuery = $query
            ->groupBy('fundraisers.id', 'fundraisers.nama_fundraiser', 'fundraisers.nomor_hp', 'fundraisers.aktif')
            ->having('total_transaksi', '>', 0)  // Hanya tampilkan yang punya transaksi
            ->orderByDesc('total_dana_terkumpul');

        // Limit bawaan saat boot; nilai filter terbaru ditimpa closure
        // filter limit pada fase render.
        if ($this->limit > 0) {
            $baseQuery->limit($this->limit);
        }

        return $baseQuery;
    }

    /**
     * Terapkan filter periode sebagai WHERE pada query final (bukan pada
     * ON-clause join yang dibangun saat boot) agar nilai baru berlaku
     * seketika tanpa lag.
     */
    protected function terapkanPeriodeWaktu($query, string $periode)
    {
        switch ($periode) {
            case 'current_month':
                return $query->whereBetween('donasis.tanggal_donasi', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            case 'last_month':
                return $query->whereBetween('donasis.tanggal_donasi', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
            case 'current_year':
                return $query->whereBetween('donasis.tanggal_donasi', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
            case 'last_3_months':
                return $query->where('donasis.tanggal_donasi', '>=', Carbon::now()->subMonths(3));
            case 'last_6_months':
                return $query->where('donasis.tanggal_donasi', '>=', Carbon::now()->subMonths(6));
            case 'all_time':
            default:
                // Tanpa filter tambahan — semua fundraiser yang punya donasi
                return $query;
        }
    }
}
