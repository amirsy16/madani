<?php

namespace App\Support;

use EightyNine\ExcelImport\Exceptions\ImportStoppedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Pengganti loop impor bawaan plugin eightynine/filament-excel-import
 * (dipasang via ->processCollectionUsing()): seluruh baris diproses dalam
 * SATU transaksi database.
 *
 * Sebelumnya baris disimpan satu per satu tanpa transaksi — satu baris gagal
 * di tengah berkas meninggalkan impor setengah jalan plus error 500 (temuan
 * BG-01). Dengan helper ini, satu saja baris gagal maka SELURUH impor
 * dibatalkan (rollback) dan pengguna menerima daftar baris bermasalah.
 */
class ImporTransaksional
{
    /**
     * Jumlah pesan kegagalan maksimum yang ditampilkan pada notifikasi.
     */
    private const BATAS_TAMPIL = 5;

    /**
     * Proses seluruh baris dalam satu transaksi; kumpulkan pesan kegagalan
     * per baris lalu batalkan semuanya bila ada yang gagal.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  \Closure(array<string, mixed>): array<string, mixed>|null  $mutator
     */
    public static function proses(string $model, Collection $baris, array $dataTambahan = [], ?\Closure $mutator = null): Collection
    {
        DB::transaction(function () use ($model, $baris, $dataTambahan, $mutator): void {
            $kegagalan = [];

            foreach ($baris as $indeks => $barisData) {
                // Baris 1 Excel berisi judul kolom, data dimulai baris 2.
                $nomorBaris = $indeks + 2;

                try {
                    $data = $barisData->toArray();
                    if ($dataTambahan !== []) {
                        $data = array_merge($data, $dataTambahan);
                    }
                    if ($mutator !== null) {
                        $data = $mutator($data);
                    }
                    $model::create($data);
                } catch (Throwable $e) {
                    $kegagalan[] = "Baris {$nomorBaris}: {$e->getMessage()}";
                }
            }

            if ($kegagalan !== []) {
                $ringkasan = implode("\n", array_slice($kegagalan, 0, self::BATAS_TAMPIL));
                if (count($kegagalan) > self::BATAS_TAMPIL) {
                    $ringkasan .= "\n... dan ".(count($kegagalan) - self::BATAS_TAMPIL).' baris lainnya.';
                }

                throw new ImportStoppedException(
                    count($kegagalan).' dari '.count($baris)." baris gagal diproses — tidak ada data yang disimpan. Perbaiki berkas lalu impor ulang:\n\n".$ringkasan
                );
            }
        });

        return $baris;
    }
}
