<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisDonasiResource\Pages;
use App\Models\JenisDonasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JenisDonasiResource extends Resource
{
    protected static ?string $model = JenisDonasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Jenis Donasi';

    protected static ?string $pluralModelLabel = 'Jenis Donasi';

    protected static ?string $modelLabel = 'Jenis Donasi';

    protected static ?int $navigationSort = 3;

    public static function getSlug(): string
    {
        return 'jenis-donasi';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan Master Data';
    }

    /**
     * Navigation badge untuk menampilkan total jenis donasi aktif
     * DISABLED: Untuk performa
     */
    // public static function getNavigationBadge(): ?string
    // {
    //     $total = static::getModel()::where('aktif', true)->count();
    //     return $total > 0 ? (string) $total : null;
    // }

    // /**
    //  * Warna badge navigation
    //  */
    // public static function getNavigationBadgeColor(): ?string
    // {
    //     return 'primary';
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Jenis Donasi')
                            ->placeholder('Contoh: Zakat Fitrah, Zakat Maal, Infaq Umum, Sedekah')
                            ->unique(ignoreRecord: true)
                            ->helperText('Nama jenis donasi harus unik'),

                        Forms\Components\TextInput::make('kode')
                            ->maxLength(255)
                            ->label('Kode')
                            ->placeholder('Contoh: ZF, ZM, IFU')
                            ->unique(ignoreRecord: true)
                            ->helperText('Kode singkat untuk identifikasi (opsional)')
                            ->alphaDash()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('kode', strtoupper($state)))
                            ->live(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi singkat tentang jenis donasi ini')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Jenis Donasi')
                    ->schema([
                        Forms\Components\Toggle::make('apakah_barang')
                            ->label('Donasi Barang')
                            ->helperText('Aktifkan jika jenis ini untuk donasi barang (bukan uang)')
                            ->default(false)
                            ->live(),

                        Forms\Components\Toggle::make('membutuhkan_keterangan_tambahan')
                            ->label('Membutuhkan Keterangan Tambahan')
                            ->helperText('Aktifkan jika perlu form keterangan saat input donasi')
                            ->default(false),

                        Forms\Components\Select::make('sumber_dana_penyaluran_id')
                            ->label('Sumber Dana Penyaluran')
                            ->relationship('sumberDanaPenyaluran', 'nama_sumber_dana')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih sumber dana penyaluran')
                            ->helperText('Tentukan ke mana dana ini akan disalurkan')
                            // PSAK 109: dana non halal wajib dicatat terpisah
                            // dari dana halal (zakat/infaq/sedekah).
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $sumber = $value ? \App\Models\SumberDanaPenyaluran::find($value) : null;
                                    $sumberNonHalal = $sumber !== null
                                        && strcasecmp(trim($sumber->nama_sumber_dana), 'Dana Non Halal') === 0;

                                    if ($get('mengandung_dana_non_halal') && ! $sumberNonHalal) {
                                        $fail('Jenis donasi non halal wajib memakai sumber dana "Dana Non Halal" agar tidak bercampur dana halal.');
                                    }

                                    if (! $get('mengandung_dana_non_halal') && $sumberNonHalal) {
                                        $fail('Sumber dana "Dana Non Halal" khusus untuk jenis donasi non halal. Aktifkan "Mengandung Dana Non Halal".');
                                    }
                                };
                            }),

                        Forms\Components\Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->helperText('Hanya jenis aktif yang akan muncul di form donasi')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Dana Non Halal')
                    ->description('⚠️ Khusus untuk dana yang mengandung unsur non halal (riba, bunga bank, dll)')
                    ->schema([
                        Forms\Components\Toggle::make('mengandung_dana_non_halal')
                            ->label('Mengandung Dana Non Halal')
                            ->helperText('Aktifkan jika dana ini mengandung unsur non halal')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('keterangan_dana_non_halal')
                            ->label('Keterangan Dana Non Halal')
                            ->placeholder('Jelaskan alasan mengapa dana ini termasuk non halal')
                            ->rows(3)
                            ->visible(fn (Get $get): bool => $get('mengandung_dana_non_halal') === true)
                            ->required(fn (Get $get): bool => $get('mengandung_dana_non_halal') === true)
                            ->columnSpanFull()
                            ->helperText('Keterangan ini akan ditampilkan kepada admin saat input donasi'),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Jenis Donasi')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('apakah_barang')
                    ->label('Barang')
                    ->boolean()
                    ->trueIcon('heroicon-o-cube')
                    ->falseIcon('heroicon-o-banknotes')
                    ->trueColor('purple')
                    ->falseColor('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('sumberDanaPenyaluran.nama_sumber_dana')
                    ->label('Sumber Dana')
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('mengandung_dana_non_halal')
                    ->label('Dana Non Halal')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('membutuhkan_keterangan_tambahan')
                    ->label('Keterangan')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('aktif')
                    ->label('Status')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama', 'asc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJenisDonasis::route('/'),
            'create' => Pages\CreateJenisDonasi::route('/create'),
            'edit' => Pages\EditJenisDonasi::route('/{record}/edit'),
        ];
    }
}
