<?php

namespace App\Filament\Resources\KategoriInfaqTerikatResource\Pages;

use App\Filament\Resources\KategoriInfaqTerikatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriInfaqTerikats extends ListRecords
{
    protected static string $resource = KategoriInfaqTerikatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
