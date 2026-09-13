<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetodePembayaranResource\Pages;
use App\Filament\Resources\MetodePembayaranResource\RelationManagers;
use App\Models\MetodePembayaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Get;

class MetodePembayaranResource extends Resource
{
    protected static ?string $model = MetodePembayaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Metode Pembayaran';
    
    protected static ?string $pluralModelLabel = 'Metode Pembayaran';
    
    protected static ?string $modelLabel = 'Metode Pembayaran';
    
    protected static ?int $navigationSort = 2;
    
    public static function getSlug(): string
    {
        return 'metode-bayar';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan Master Data';
    }
    
    /**
     * Navigation badge untuk menampilkan total metode pembayaran aktif
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
                Forms\Components\Section::make('Informasi Metode Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Metode Pembayaran')
                            ->placeholder('Contoh: Transfer BSI, QRIS, LinkAja')
                            ->unique(ignoreRecord: true)
                            ->helperText('Nama metode harus unik'),
                            
                        Forms\Components\TextInput::make('kode')
                            ->maxLength(255)
                            ->label('Kode')
                            ->placeholder('Contoh: BSI, QRIS, LNK')
                            ->unique(ignoreRecord: true)
                            ->helperText('Kode singkat untuk identifikasi (opsional)')
                            ->alphaDash(),
                            
                        Forms\Components\Select::make('tipe')
                            ->required()
                            ->label('Tipe Pembayaran')
                            ->options([
                                'digital' => 'Digital / E-Wallet (QRIS, LinkAja, dll)',
                                'transfer_bank' => 'Transfer Bank',
                                'tunai' => 'Tunai',
                            ])
                            ->default('digital')
                            ->live()
                            ->helperText('Pilih tipe pembayaran'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Informasi Rekening Bank')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->maxLength(255)
                            ->label('Nama Bank')
                            ->placeholder('Contoh: BSI, Muamalat, Mandiri Syariah'),
                            
                        Forms\Components\TextInput::make('nomor_rekening')
                            ->maxLength(255)
                            ->label('Nomor Rekening')
                            ->placeholder('Contoh: 1234567890'),
                            
                        Forms\Components\TextInput::make('atas_nama_rekening')
                            ->maxLength(255)
                            ->label('Atas Nama Rekening')
                            ->placeholder('Contoh: Yayasan Insan Madani'),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('tipe') === 'transfer_bank')
                    ->collapsible(),
                    
                Forms\Components\Section::make('Instruksi & Pengaturan')
                    ->schema([
                        Forms\Components\Textarea::make('instruksi_pembayaran')
                            ->label('Instruksi Pembayaran')
                            ->placeholder('Instruksi khusus untuk donatur saat menggunakan metode ini')
                            ->rows(4)
                            ->helperText('Contoh: "Setelah transfer, mohon kirim bukti ke WhatsApp: 08123456789"')
                            ->columnSpanFull(),
                            
                        Forms\Components\Toggle::make('aktif')
                            ->required()
                            ->default(true)
                            ->label('Status Aktif')
                            ->helperText('Hanya metode aktif yang akan muncul di form donasi'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Metode')
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
                    
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'digital' => 'info',
                        'transfer_bank' => 'success',
                        'tunai' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'digital' => 'Digital',
                        'transfer_bank' => 'Transfer Bank',
                        'tunai' => 'Tunai',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('-'),
                    
                Tables\Columns\TextColumn::make('nomor_rekening')
                    ->label('No. Rekening')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('-')
                    ->copyable()
                    ->copyMessage('Nomor rekening disalin!'),
                    
                Tables\Columns\TextColumn::make('atas_nama_rekening')
                    ->label('Atas Nama')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                    
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
                SelectFilter::make('tipe')
                    ->label('Tipe Pembayaran')
                    ->options([
                        'digital' => 'Digital',
                        'transfer_bank' => 'Transfer Bank',
                        'tunai' => 'Tunai',
                    ]),
                    
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
            'index' => Pages\ListMetodePembayarans::route('/'),
            'create' => Pages\CreateMetodePembayaran::route('/create'),
            'edit' => Pages\EditMetodePembayaran::route('/{record}/edit'),
        ];
    }
}
