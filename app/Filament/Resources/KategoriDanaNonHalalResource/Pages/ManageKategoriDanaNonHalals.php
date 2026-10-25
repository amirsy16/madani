<?php

namespace App\Filament\Resources\KategoriDanaNonHalalResource\Pages;

use App\Filament\Resources\KategoriDanaNonHalalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKategoriDanaNonHalals extends ManageRecords
{
    protected static string $resource = KategoriDanaNonHalalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
