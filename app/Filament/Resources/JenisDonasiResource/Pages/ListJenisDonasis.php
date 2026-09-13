<?php

namespace App\Filament\Resources\JenisDonasiResource\Pages;

use App\Filament\Resources\JenisDonasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisDonasis extends ListRecords
{
    protected static string $resource = JenisDonasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
