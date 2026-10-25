<?php

namespace App\Filament\Resources\PengaturanHakAmilResource\Pages;

use App\Filament\Resources\PengaturanHakAmilResource;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanHakAmils extends ListRecords
{
    protected static string $resource = PengaturanHakAmilResource::class;

    protected function getHeaderActions(): array
    {
        // Master ini hanya untuk mengedit persentase hak amil per sumber dana
        // yang sudah ada — tombol Tambah rusak karena nama sumber dana
        // required sekaligus disabled di form (temuan WP-D).
        return [];
    }
}
