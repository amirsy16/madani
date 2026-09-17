<?php

namespace App\Filament\Resources\ProgramPenyaluranResource\Pages;

use App\Filament\Resources\ProgramPenyaluranResource;
use App\Models\ProgramPenyaluran;
use App\Services\DanaService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateProgramPenyaluran extends CreateRecord
{
    protected static string $resource = ProgramPenyaluranResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Re-check saldo di dalam transaction dengan lock, mencegah
     * dua penyaluran bersamaan melewati batas saldo (race condition).
     */
    protected function beforeCreate(): void
    {
        $sumberDanaId = $this->data['sumber_dana_penyaluran_id'] ?? null;
        $jumlah = floatval($this->data['jumlah_dana'] ?? 0);

        if (!$sumberDanaId || $jumlah <= 0) {
            return;
        }

        DB::transaction(function () use ($sumberDanaId, $jumlah) {
            // Kunci record penyaluran sumber dana ini — serialisasi pengecekan
            ProgramPenyaluran::where('sumber_dana_penyaluran_id', $sumberDanaId)
                ->lockForUpdate()
                ->get();

            app(DanaService::class)->assertCukupSaldo($sumberDanaId, $jumlah);
        });
    }
}
