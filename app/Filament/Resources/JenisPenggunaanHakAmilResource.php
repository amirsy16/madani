<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisPenggunaanHakAmilResource\Pages;
use App\Models\JenisPenggunaanHakAmil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JenisPenggunaanHakAmilResource extends Resource
{
    protected static ?string $model = JenisPenggunaanHakAmil::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Jenis Penggunaan Hak Amil';

    protected static ?string $pluralModelLabel = 'Jenis Penggunaan Hak Amil';

    protected static ?string $modelLabel = 'Jenis Penggunaan Hak Amil';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan Master Data';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis Penggunaan Hak Amil')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Jenis Penggunaan')
                            ->unique(ignoreRecord: true),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Jenis Penggunaan')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJenisPenggunaanHakAmils::route('/'),
        ];
    }
}
