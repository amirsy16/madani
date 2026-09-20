<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom + FK + index sudah dibuat oleh 2025_06_18_000002; migrasi ini
        // duplikatnya. Guard membuat `migrate:fresh` MySQL tidak gagal
        // dengan "Duplicate column name" (1060), sementara di SQLite test
        // (000002 di-skip) kolom tetap dibuat di sini.
        if (Schema::hasColumn('jenis_penggunaan_hak_amils', 'sumber_dana_penyaluran_id')) {
            return;
        }

        Schema::table('jenis_penggunaan_hak_amils', function (Blueprint $table) {
            // Tambahkan kolom sumber dana penyaluran
            $table->foreignId('sumber_dana_penyaluran_id')->nullable()->after('jenis_donasi_id')->constrained('sumber_dana_penyalurans')->onDelete('cascade');

            // Index untuk optimasi query
            $table->index(['sumber_dana_penyaluran_id', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::table('jenis_penggunaan_hak_amils', function (Blueprint $table) {
            $table->dropIndex(['sumber_dana_penyaluran_id', 'aktif']);
            $table->dropForeign(['sumber_dana_penyaluran_id']);
            $table->dropColumn('sumber_dana_penyaluran_id');
        });
    }
};
