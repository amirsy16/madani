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
        Schema::table('activity_log', function (Blueprint $table) {
            // Halaman Log Aktivitas memfilter event/causer_id dan mengurutkan
            // created_at — tabel ini tumbuh terus, tanpa index akan full scan.
            $table->index('event');
            $table->index('created_at');
            $table->index('causer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['event']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['causer_id']);
        });
    }
};
