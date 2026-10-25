<?php

namespace App\Filament\Resources\ProgramPenyaluranResource\Pages;

use App\Filament\Resources\ProgramPenyaluranResource;
use App\Models\ProgramPenyaluran;
use App\Services\DanaService;
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

        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah_dana' => 'Jumlah dana penyaluran harus lebih dari 0.',
            ]);
        }

        if (! $sumberDanaId) {
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

    /**
     * Pencatat & kode program di-set server-side; nilai dari field Hidden
     * klien tidak dipercaya (AK-02). Kode dibuat saat menyimpan dengan
     * regenerasi otomatis — sebelumnya dihitung lastId+1 saat form dibuka
     * sehingga dua petugas bersamaan menghasilkan kode sama (BG-04).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dicatat_oleh_id'] = auth()->id();
        $data['kode_program_penyaluran'] = $this->generateKodeProgram();

        return $data;
    }

    private function generateKodeProgram(): string
    {
        $prefix = 'PYL';
        $date = now()->format('Ymd');
        $lastId = (int) (ProgramPenyaluran::withTrashed()->max('id') ?? 0);

        do {
            $lastId++;
            $kode = $prefix.$date.str_pad((string) $lastId, 4, '0', STR_PAD_LEFT);
        } while (ProgramPenyaluran::withTrashed()->where('kode_program_penyaluran', $kode)->exists());

        return $kode;
    }
}
