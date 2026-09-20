<?php

namespace App\Filament\Resources\DonasiResource\Pages;

use App\Filament\Resources\DonasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonasi extends EditRecord
{
    protected static string $resource = DonasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            // Donasi terverifikasi tidak boleh dihapus.
            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->status_konfirmasi !== 'verified'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Jejak verifikasi diset server-side hanya saat status benar-benar
     * berubah — nilai dari klien tidak dipercaya. Saat status tidak berubah
     * (mis. mengedit catatan donasi verified) jejak verifikator asli dipertahankan.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $statusLama = $this->record->status_konfirmasi;
        $statusBaru = $data['status_konfirmasi'] ?? $statusLama;

        if ($statusBaru !== $statusLama) {
            $data['dikofirmasi_oleh_user_id'] = $statusBaru === 'pending' ? null : auth()->id();
            $data['dikonfirmasi_pada'] = $statusBaru === 'pending' ? null : now();
        } else {
            unset($data['dikofirmasi_oleh_user_id'], $data['dikonfirmasi_pada']);
        }

        return $data;
    }
}
