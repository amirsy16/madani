<?php

namespace App\Filament\Resources\JenisDonasiResource\Pages;

use App\Filament\Resources\JenisDonasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJenisDonasi extends EditRecord
{
    protected static string $resource = JenisDonasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
