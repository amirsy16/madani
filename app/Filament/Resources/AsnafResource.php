<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsnafResource\Pages;
use App\Models\Asnaf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AsnafResource extends Resource
{
    protected static ?string $model = Asnaf::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Asnaf';

    protected static ?string $pluralModelLabel = 'Asnaf';

    protected static ?string $modelLabel = 'Asnaf';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan Master Data';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Asnaf')
                    ->schema([
                        Forms\Components\TextInput::make('nama_asnaf')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Asnaf')
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(2),
                        Forms\Components\Toggle::make('aktif')
                            ->default(true)
                            ->label('Status Aktif'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_asnaf')
                    ->label('Nama Asnaf')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),
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
            'index' => Pages\ManageAsnafs::route('/'),
        ];
    }
}
