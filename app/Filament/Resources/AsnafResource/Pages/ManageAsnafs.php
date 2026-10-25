<?php

namespace App\Filament\Resources\AsnafResource\Pages;

use App\Filament\Resources\AsnafResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAsnafs extends ManageRecords
{
    protected static string $resource = AsnafResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
