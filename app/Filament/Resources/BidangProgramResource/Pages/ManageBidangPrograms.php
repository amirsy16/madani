<?php

namespace App\Filament\Resources\BidangProgramResource\Pages;

use App\Filament\Resources\BidangProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBidangPrograms extends ManageRecords
{
    protected static string $resource = BidangProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
