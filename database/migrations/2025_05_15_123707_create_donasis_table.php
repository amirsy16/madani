<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_donasis_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donatur_id')->nullable()->constrained('donaturs')->onDelete('set null');
            // FK jenis_donasi/metode_pembayaran/fundraiser ditambahkan di
            // 2025_05_15_134000 — tabel targetnya dibuat SETELAH file ini,
            // FK inline di sini membuat `migrate:fresh` MySQL gagal (errno 1824).
            $table->unsignedBigInteger('jenis_donasi_id');
            $table->unsignedBigInteger('metode_pembayaran_id')->nullable();
            $table->unsignedBigInteger('fundraiser_id')->nullable();

            $table->decimal('jumlah', 15, 2)->default(0); // Jumlah uang
            $table->text('keterangan_infak_khusus')->nullable(); // Jika jenis_donasi_id = infak khusus
            $table->text('deskripsi_barang')->nullable(); // Jika jenis_donasi_id = barang
            $table->decimal('perkiraan_nilai_barang', 15, 2)->nullable(); // Jika jenis_donasi_id = barang

            $table->string('bukti_pembayaran')->nullable(); // Path/URL ke file
            $table->text('catatan_donatur')->nullable();
            $table->date('tanggal_donasi')->useCurrent();
            $table->boolean('atas_nama_hamba_allah')->default(false);
            $table->string('nomor_transaksi_unik')->unique()->nullable(); // Nomor referensi/invoice

            $table->enum('status_konfirmasi', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('dikofirmasi_oleh_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->text('catatan_konfirmasi')->nullable();

            $table->foreignId('dicatat_oleh_user_id')->nullable()->constrained('users')->onDelete('set null'); // Siapa admin yang input
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donasis');
    }
};
