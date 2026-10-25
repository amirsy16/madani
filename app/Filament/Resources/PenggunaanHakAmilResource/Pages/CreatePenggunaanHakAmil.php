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

        // Serialisasi pengecekan: kunci baris penggunaan hak amil dalam
        // transaksi agar dua input bersamaan tidak melewati sisa (T1).
        \Illuminate\Support\Facades\DB::transaction(function () use ($jumlah): void {
            \App\Models\PenggunaanHakAmil::lockForUpdate()->get();
            app(DanaService::class)->assertCukupHakAmil($jumlah);
        });
    }
}
