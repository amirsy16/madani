<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FK donasis ke tabel yang dibuat belakangan (jenis_donasis, metode_pembayarans,
     * fundraisers). FK inline di file create membuat `migrate:fresh` MySQL gagal
     * dengan errno 1824 karena tabel target belum ada saat donasis dibuat.
     */
    public function up(): void
    {
        Schema::table('donasis', function (Blueprint $table) {
            if (! $this->punyaForeignKey('jenis_donasis')) {
                $table->foreign('jenis_donasi_id')->references('id')->on('jenis_donasis')->onDelete('restrict');
            }

            if (! $this->punyaForeignKey('metode_pembayarans')) {
                $table->foreign('metode_pembayaran_id')->references('id')->on('metode_pembayarans')->onDelete('set null');
            }

            if (! $this->punyaForeignKey('fundraisers')) {
                $table->foreign('fundraiser_id')->references('id')->on('fundraisers')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donasis', function (Blueprint $table) {
            $table->dropForeign(['jenis_donasi_id']);
            $table->dropForeign(['metode_pembayaran_id']);
            $table->dropForeign(['fundraiser_id']);
        });
    }

    /**
     * Database lama membuat FK ini secara inline di file create; guard ini
     * membuat migrasi tetap aman dijalankan pada database yang sudah ada.
     */
    private function punyaForeignKey(string $referencedTable): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.REFERENTIAL_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            ['donasis', $referencedTable]
        );

        return (bool) ($result->total ?? 0);
    }
};
