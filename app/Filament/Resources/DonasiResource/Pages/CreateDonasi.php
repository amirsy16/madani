<?php

namespace App\Filament\Resources\DonasiResource\Pages;

use App\Filament\Resources\DonasiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDonasi extends CreateRecord
{
    protected static string $resource = DonasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Jejak verifikasi diset server-side, bukan dari field Hidden klien.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $status = $data['status_konfirmasi'] ?? 'pending';
        $data['dikofirmasi_oleh_user_id'] = $status === 'pending' ? null : auth()->id();
        $data['dikonfirmasi_pada'] = $status === 'pending' ? null : now();

        return $data;
    }
}
