<?php

namespace Tests\Feature;

use App\Models\Donatur;
use App\Support\ImporTransaksional;
use EightyNine\ExcelImport\Exceptions\ImportStoppedException;
use Tests\TestCase;

/**
 * Impor Excel transaksional (BG-01): seluruh berkas dalam SATU transaksi —
 * satu baris gagal berarti seluruh impor dibatalkan, bukan error 500 dengan
 * data setengah masuk.
 */
class ImporTransaksionalTest extends TestCase
{
    public function test_satu_baris_gagal_membatalkan_seluruh_impor(): void
    {
        $baris = collect([
            collect(['nama' => 'Donatur Baru Impor', 'gender' => 'male']),
            collect(['gender' => 'female']), // tanpa nama → gagal constraint NOT NULL
        ]);

        try {
            ImporTransaksional::proses(Donatur::class, $baris);
            $this->fail('Impor dengan baris cacat harus melempar ImportStoppedException.');
        } catch (ImportStoppedException $e) {
            $this->assertStringContainsString('1 dari 2 baris gagal', $e->getUserMessage());
            // Baris 1 Excel berisi judul kolom — data mulai baris 2,
            // sehingga baris cacat kedua dilaporkan sebagai baris 3.
            $this->assertStringContainsString('Baris 3', $e->getUserMessage());
        }

        // Baris pertama yang valid ikut ter-rollback — tidak ada impor parsial.
        $this->assertDatabaseMissing('donaturs', ['nama' => 'Donatur Baru Impor']);
    }

    public function test_semua_baris_valid_tersimpan_semua(): void
    {
        $baris = collect([
            collect(['nama' => 'Donatur Impor A', 'gender' => 'male']),
            collect(['nama' => 'Donatur Impor B', 'gender' => 'female']),
        ]);

        $hasil = ImporTransaksional::proses(Donatur::class, $baris);

        $this->assertSame(2, $hasil->count());
        $this->assertDatabaseHas('donaturs', ['nama' => 'Donatur Impor A']);
        $this->assertDatabaseHas('donaturs', ['nama' => 'Donatur Impor B']);
    }
}
