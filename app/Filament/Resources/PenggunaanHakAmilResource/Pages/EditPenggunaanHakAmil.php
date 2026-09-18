<?php

namespace App\Filament\Resources\PenggunaanHakAmilResource\Pages;

use App\Filament\Resources\PenggunaanHakAmilResource;
use App\Services\DanaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenggunaanHakAmil extends EditRecord
{
    protected static string $resource = PenggunaanHakAmilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Guard edit: nilai record sendiri dikecualikan dari sisa.
     */
    protected function beforeSave(): void
    {
        $jumlah = floatval($this->data['jumlah'] ?? 0);
        if ($jumlah > 0) {
            app(DanaService::class)->assertCukupHakAmil($jumlah, $this->record?->id);
        }
    }
}
