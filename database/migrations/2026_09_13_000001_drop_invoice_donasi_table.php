<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur invoice WhatsApp dihapus beserta tracking-nya.
 * Tabel invoice_donasi tidak pernah berisi record (fitur tidak pernah hidup).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('invoice_donasi');
    }

    public function down(): void
    {
        Schema::create('invoice_donasi', function ($table) {
            $table->id();
            $table->foreignId('donasi_id')->constrained('donasis')->onDelete('cascade');
            $table->string('nomor_invoice')->unique();
            $table->string('delivery_method')->nullable();
            $table->string('delivery_status')->default('pending');
            $table->string('pdf_file_path')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['donasi_id', 'delivery_status']);
        });
    }
};
