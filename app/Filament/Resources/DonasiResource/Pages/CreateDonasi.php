<?php

namespace App\Filament\Resources\DonasiResource\Pages;

use App\Filament\Resources\DonasiResource;
use App\Models\Donasi;
use Filament\Resources\Pages\CreateRecord;

class CreateDonasi extends CreateRecord
{
    protected static string $resource = DonasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Kolom ketertelusuran di-set server-side; nilai dari field Hidden
     * klien tidak dipercaya (temuan AK-02/AK-03). Pembuat tanpa izin
     * verifikasi (update_donasi) tidak bisa memverifikasi donasinya
     * sendiri — status dipaksa menunggu verifikasi (temuan AK-07).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dicatat_oleh_user_id'] = auth()->id();
        $data['nomor_transaksi_unik'] = Donasi::generateNomorTransaksiUnik();

        if (! auth()->user()->can('update_donasi')) {
            $data['status_konfirmasi'] = 'pending';
            $data['dikofirmasi_oleh_user_id'] = null;
            $data['dikonfirmasi_pada'] = null;
            unset($data['catatan_konfirmasi']);

            return $data;
        }

        $status = $data['status_konfirmasi'] ?? 'pending';
        $data['dikofirmasi_oleh_user_id'] = $status === 'pending' ? null : auth()->id();
        $data['dikonfirmasi_pada'] = $status === 'pending' ? null : now();

        return $data;
    }
}
