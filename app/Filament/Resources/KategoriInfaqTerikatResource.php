<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriInfaqTerikatResource\Pages;
use App\Filament\Resources\KategoriInfaqTerikatResource\RelationManagers;
use App\Models\KategoriInfaqTerikat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;

class KategoriInfaqTerikatResource extends Resource
{
    protected static ?string $model = KategoriInfaqTerikat::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationLabel = 'Kategori Infaq Terikat';
    
    protected static ?string $pluralModelLabel = 'Kategori Infaq Terikat';
    
    protected static ?string $modelLabel = 'Kategori Infaq Terikat';
    
    protected static ?int $navigationSort = 1;
    
    public static function getSlug(): string
    {
        return 'kategori-infaq';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan Master Data';
    }
    
    /**
     * Navigation badge untuk menampilkan total kategori aktif
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
    //     return 'success';
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kategori')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Kategori')
                            ->placeholder('Contoh: Pendidikan, Kesehatan, Panti Asuhan')
                            ->unique(ignoreRecord: true)
                            ->helperText('Nama kategori harus unik'),
                            
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi singkat tentang kategori ini')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(1),
                    
                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('urutan')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->label('Urutan Tampilan')
                            ->helperText('Urutan tampilan di dropdown (semakin kecil semakin atas)')
                            ->minValue(0),
                            
                        Forms\Components\Toggle::make('aktif')
                            ->required()
                            ->default(true)
                            ->label('Status Aktif')
                            ->helperText('Hanya kategori aktif yang akan muncul di form donasi'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                    
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('urutan', 'asc')
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
            'index' => Pages\ListKategoriInfaqTerikats::route('/'),
            'create' => Pages\CreateKategoriInfaqTerikat::route('/create'),
            'edit' => Pages\EditKategoriInfaqTerikat::route('/{record}/edit'),
        ];
    }
}
