<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * city_id pada donaturs menyimpan regencies.id (bukan tabel alias cities).
 * FK lama menunjuk tabel cities yang bukan sumber data form — di-drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donaturs', function (Blueprint $table) {
            $foreignKeys = Schema::getForeignKeys('donaturs');

            foreach ($foreignKeys as $fk) {
                if (in_array('city_id', $fk['columns'])) {
                    $table->dropForeign($fk['name']);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('donaturs', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }
};
