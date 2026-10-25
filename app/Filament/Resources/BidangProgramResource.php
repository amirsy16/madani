<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BidangProgramResource\Pages;
use App\Models\BidangProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BidangProgramResource extends Resource
{
    protected static ?string $model = BidangProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Bidang Program';

    protected static ?string $pluralModelLabel = 'Bidang Program';

    protected static ?string $modelLabel = 'Bidang Program';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan Master Data';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bidang Program')
                    ->schema([
                        Forms\Components\TextInput::make('nama_bidang')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Bidang')
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
                Tables\Columns\TextColumn::make('nama_bidang')
                    ->label('Nama Bidang')
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
            'index' => Pages\ManageBidangPrograms::route('/'),
        ];
    }
}
