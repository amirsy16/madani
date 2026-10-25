<?php

namespace App\Filament\Resources\JenisPenggunaanHakAmilResource\Pages;

use App\Filament\Resources\JenisPenggunaanHakAmilResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisPenggunaanHakAmils extends ManageRecords
{
    protected static string $resource = JenisPenggunaanHakAmilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
