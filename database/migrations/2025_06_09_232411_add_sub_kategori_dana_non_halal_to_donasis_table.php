<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Duplikat dari migration 2025_06_09_232000 — jadikan no-op jika kolom sudah ada
        if (Schema::hasColumn('donasis', 'sub_kategori_dana_non_halal')) {
            return;
        }

        Schema::table('donasis', function (Blueprint $table) {
            $table->text('sub_kategori_dana_non_halal')->nullable()->after('keterangan_infak_khusus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('donasis', 'sub_kategori_dana_non_halal')) {
            return;
        }

        Schema::table('donasis', function (Blueprint $table) {
            $table->dropColumn('sub_kategori_dana_non_halal');
        });
    }
};
