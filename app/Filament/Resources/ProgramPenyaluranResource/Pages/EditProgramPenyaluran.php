<?php

namespace App\Filament\Resources\ProgramPenyaluranResource\Pages;

use App\Filament\Resources\ProgramPenyaluranResource;
use App\Models\ProgramPenyaluran;
use App\Services\DanaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditProgramPenyaluran extends EditRecord
{
    protected static string $resource = ProgramPenyaluranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Guard edit: cek ulang saldo di dalam transaction dengan lock.
     * Nilai lama record dikembalikan ke saldo bila sumber dana tak berubah.
     */
    protected function beforeSave(): void
    {
        $sumberDanaId = $this->data['sumber_dana_penyaluran_id'] ?? null;
        $jumlah = floatval($this->data['jumlah_dana'] ?? 0);

        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah_dana' => 'Jumlah dana penyaluran harus lebih dari 0.',
            ]);
        }

        if (! $sumberDanaId) {
            return;
        }

        $record = $this->record;
        $jumlahLama = ($record && (int) $record->sumber_dana_penyaluran_id === (int) $sumberDanaId)
            ? (float) $record->jumlah_dana
            : null;

        DB::transaction(function () use ($sumberDanaId, $jumlah, $jumlahLama) {
            ProgramPenyaluran::where('sumber_dana_penyaluran_id', $sumberDanaId)
                ->lockForUpdate()
                ->get();

            app(DanaService::class)->assertCukupSaldo($sumberDanaId, $jumlah, $jumlahLama);
        });
    }
}
