<?php

namespace App\Filament\Resources\PenggunaanHakAmilResource\Pages;

use App\Filament\Resources\PenggunaanHakAmilResource;
use App\Services\DanaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePenggunaanHakAmil extends CreateRecord
{
    protected static string $resource = PenggunaanHakAmilResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Tolak penggunaan yang melebihi sisa hak amil.
     */
    protected function beforeCreate(): void
    {
        $jumlah = floatval($this->data['jumlah'] ?? 0);
        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah penggunaan hak amil harus lebih dari 0.',
            ]);
        }

        app(DanaService::class)->assertCukupHakAmil($jumlah);
    }
}
