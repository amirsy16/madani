<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Fundraiser;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FundraiserResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FundraiserResource extends Resource
{
    protected static ?string $model = Fundraiser::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Fundraiser';
    
    protected static ?string $pluralModelLabel = 'Fundraiser';
    
    protected static ?string $modelLabel = 'Fundraiser';
    
    public static function getSlug(): string
    {
        return 'fundraiser';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation.groups.program');
    }

    /**
     * Navigation badge untuk menampilkan total fundraiser
     */
    public static function getNavigationBadge(): ?string
    {
        $totalFundraiser = static::getModel()::count();
        return $totalFundraiser > 0 ? (string) $totalFundraiser : null;
    }

    /**
     * Warna badge navigation
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_fundraiser')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Fundraiser'),
                    
                Forms\Components\TextInput::make('nomor_identitas')
                    ->maxLength(50)
                    ->label('Nomor Identitas (KTP/SIM)'),
                    
                Forms\Components\TextInput::make('nomor_hp')
                    ->tel()
                    ->maxLength(20)
                    ->label('Nomor HP'),
                    
                Forms\Components\Textarea::make('alamat')
                    ->columnSpanFull()
                    ->label('Alamat'),
                    
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('User Akun (Opsional)')
                    ->placeholder('Pilih user jika fundraiser memiliki akun'),
                    
                Forms\Components\Toggle::make('aktif')
                    ->default(true)
                    ->label('Status Aktif'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_fundraiser')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Fundraiser'),
                    
                Tables\Columns\TextColumn::make('nomor_identitas')
                    ->searchable()
                    ->label('Nomor Identitas'),
                    
                Tables\Columns\TextColumn::make('nomor_hp')
                    ->searchable()
                    ->label('Nomor HP'),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('User Akun'),
                    
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->sortable()
                    ->label('Status Aktif'),
                    
                Tables\Columns\TextColumn::make('donatur_count')
                    ->getStateUsing(function ($record) {
                        return $record->donasis()->distinct('donatur_id')->count('donatur_id');
                    })
                    ->label('Jumlah Donatur')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->label('Tgl Daftar')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                    
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('User Akun'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Remove the DonasisRelationManager reference
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundraisers::route('/'),
            'create' => Pages\CreateFundraiser::route('/create'),
            'edit' => Pages\EditFundraiser::route('/{record}/edit'),
            // Remove the ViewFundraiser reference
        ];
    }
}

