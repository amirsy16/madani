<?php

namespace App\Filament\Resources\DonasiResource\Pages;

use App\Filament\Resources\DonasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

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
        // Pencatat & nomor transaksi asli dipertahankan — nilai dari klien
        // tidak dipercaya (temuan AK-03).
        $data['dicatat_oleh_user_id'] = $this->record->dicatat_oleh_user_id;
        $data['nomor_transaksi_unik'] = $this->record->nomor_transaksi_unik;

        $statusLama = $this->record->status_konfirmasi;
        $statusBaru = $data['status_konfirmasi'] ?? $statusLama;

        // Ditolak lalu diverifikasi wajib mencatat alasan (BG-14).
        if ($statusLama === 'rejected' && $statusBaru === 'verified'
            && blank($data['catatan_konfirmasi'] ?? null)) {
            throw ValidationException::withMessages([
                'catatan_konfirmasi' => 'Wajib mencatat alasan saat donasi yang ditolak diverifikasi.',
            ]);
        }

        if ($statusBaru !== $statusLama) {
            $data['dikofirmasi_oleh_user_id'] = $statusBaru === 'pending' ? null : auth()->id();
            $data['dikonfirmasi_pada'] = $statusBaru === 'pending' ? null : now();
        } else {
            unset($data['dikofirmasi_oleh_user_id'], $data['dikonfirmasi_pada']);
        }

        return $data;
    }
}
