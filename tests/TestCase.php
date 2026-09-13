<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Migration yang di-skip saat refresh database testing.
     *
     * Alasan (semua gagal saat `migrate:fresh` dijalankan, bukan karena test):
     * - 2025_06_09_232411 : duplikat dari 2025_06_09_232000 — "duplicate column
     *   name: sub_kategori_dana_non_halal" pada DB apa pun yang fresh.
     * - 2025_06_15_113749 : memakai `ALTER TABLE ... MODIFY COLUMN ... ENUM(...)`
     *   yang hanya valid di MySQL.
     * - 2025_06_18_000002 : memanggil `$table->check(...)` — method
     *   `Blueprint::check()` tidak tersedia di Laravel 11 project ini.
     * - 2026_09_13_000002 : drop foreign key by name tidak didukung driver SQLite.
     */
    protected array $skippedMigrations = [
        '2025_06_09_232411_add_sub_kategori_dana_non_halal_to_donasis_table.php',
        '2025_06_15_113749_add_organization_to_donaturs_gender_enum.php',
        '2025_06_18_000002_add_persentase_to_jenis_penggunaan_hak_amils_table.php',
        '2026_09_13_000002_drop_donaturs_city_foreign_key.php',
    ];

    /**
     * Batasi migrasi yang dijalankan oleh `migrate:fresh` ke semua file
     * KECUALI yang ada di $skippedMigrations.
     *
     * Method ini sengaja dideklarasikan di kelas yang sama dengan
     * `use RefreshDatabase` — method kelas memiliki preseden lebih tinggi
     * daripada method trait, sehingga override ini selalu dipakai.
     */
    protected function migrateFreshUsing()
    {
        // Logika bawaan CanConfigureMigrationCommands (tidak bisa dipanggil
        // via parent:: karena trait method ini digantikan oleh method kelas).
        $seeder = $this->seeder();
        $options = array_merge(
            [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
            ],
            $seeder ? ['--seeder' => $seeder] : ['--seed' => $this->shouldSeed()]
        );

        $paths = [];
        foreach (glob(database_path('migrations').'/*.php') as $file) {
            if (! in_array(basename($file), $this->skippedMigrations, true)) {
                $paths[] = $file;
            }
        }

        $options['--path'] = $paths;
        $options['--realpath'] = true;

        return $options;
    }
}
