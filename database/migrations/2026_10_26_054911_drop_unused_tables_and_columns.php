<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pembersihan tabel & kolom yang tak pernah dipakai jalur aktif:
     * - target_donasi: skema fitur target galang dana tanpa model/kode
     * - pengaturan_hak_amil: desain hak amil efektif-tanggal yang ditinggal
     *   (diganti kolom persentase_hak_amil di sumber_dana_penyalurans)
     * - imports/failed_import_rows/exports: milik ImportAction/ExportAction
     *   bawaan Filament yang tak dipakai (impor: eightynine; ekspor: pxlrbt
     *   yang antre ke job queue + storage, bukan tabel)
     * - notifications: notifikasi basis data tidak diaktifkan (reset password
     *   memakai channel mail)
     * - kolom mesin hak amil efektif-tanggal di jenis_penggunaan_hak_amils
     *   yang tak pernah dibaca perhitungan
     */
    public function up(): void
    {
        Schema::dropIfExists('target_donasi');
        Schema::dropIfExists('pengaturan_hak_amil');
        Schema::dropIfExists('failed_import_rows');
        Schema::dropIfExists('imports');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('notifications');

        // FK hanya ada di MySQL - lepas sebelum drop kolom; kegagalan diabaikan
        // karena SQLite testing tidak membuat FK ini.
        foreach (['jenis_donasi_id', 'sumber_dana_penyaluran_id'] as $fk) {
            if (Schema::hasColumn('jenis_penggunaan_hak_amils', $fk)) {
                try {
                    Schema::table('jenis_penggunaan_hak_amils', function (Blueprint $table) use ($fk) {
                        $table->dropForeign([$fk]);
                    });
                } catch (\Throwable $e) {
                    // FK tidak ada - lanjut
                }
            }
        }

        // Index komposit (SQLite membuatnya bermigrasi 000003) menempel
        // pada kolom yang di-drop — lepas dulu supaya dropColumn sukses.
        try {
            Schema::table('jenis_penggunaan_hak_amils', function (Blueprint $table) {
                $table->dropIndex('jenis_penggunaan_hak_amils_sumber_dana_penyaluran_id_aktif_index');
            });
        } catch (\Throwable $e) {
            // Index tidak ada (MySQL fresh sebelum 000003) — lanjut
        }

        Schema::table('jenis_penggunaan_hak_amils', function (Blueprint $table) {
            foreach ([
                'persentase_hak_amil',
                'jenis_donasi_id',
                'sumber_dana_penyaluran_id',
                'tanggal_berlaku_mulai',
                'tanggal_berlaku_berakhir',
            ] as $kolom) {
                if (Schema::hasColumn('jenis_penggunaan_hak_amils', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }

    /**
     * Rollback best-effort: bangun kembali skema minimal.
     */
    public function down(): void
    {
        if (! Schema::hasTable('target_donasi')) {
            Schema::create('target_donasi', function (Blueprint $table) {
                $table->id();
                $table->string('nama_target');
                $table->text('deskripsi')->nullable();
                $table->decimal('target_nominal', 15, 2);
                $table->decimal('nominal_terkumpul', 15, 2)->default(0);
                $table->decimal('persentase_hak_amil', 5, 2)->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_berakhir')->nullable();
                $table->enum('status', ['aktif', 'nonaktif', 'selesai'])->default('aktif');
                $table->foreignId('fundraiser_id')->nullable();
                $table->foreignId('dibuat_oleh_user_id')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('pengaturan_hak_amil')) {
            Schema::create('pengaturan_hak_amil', function (Blueprint $table) {
                $table->id();
                $table->string('nama_pengaturan');
                $table->text('deskripsi')->nullable();
                $table->decimal('persentase_hak_amil', 5, 2);
                $table->enum('tipe_pengaturan', ['global', 'per_jenis_donasi', 'per_fundraiser'])->default('global');
                $table->foreignId('jenis_donasi_id')->nullable();
                $table->foreignId('fundraiser_id')->nullable();
                $table->boolean('aktif')->default(true);
                $table->date('tanggal_berlaku_mulai')->nullable();
                $table->date('tanggal_berlaku_berakhir')->nullable();
                $table->foreignId('dibuat_oleh_user_id')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('imports')) {
            Schema::create('imports', function (Blueprint $table) {
                $table->id();
                $table->timestamp('completed_at')->nullable();
                $table->string('file_name');
                $table->string('file_path');
                $table->string('importer');
                $table->unsignedInteger('processed_rows')->default(0);
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('successful_rows')->default(0);
                $table->unsignedInteger('failed_rows_count')->default(0);
                $table->foreignId('user_id')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('failed_import_rows')) {
            Schema::create('failed_import_rows', function (Blueprint $table) {
                $table->id();
                $table->json('data');
                $table->foreignId('import_id')->nullable();
                $table->text('validation_error')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('exports')) {
            Schema::create('exports', function (Blueprint $table) {
                $table->id();
                $table->timestamp('completed_at')->nullable();
                $table->string('file_disk');
                $table->string('file_name');
                $table->string('exporter');
                $table->unsignedInteger('processed_rows')->default(0);
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('successful_rows')->default(0);
                $table->foreignId('user_id')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
        Schema::table('jenis_penggunaan_hak_amils', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_penggunaan_hak_amils', 'persentase_hak_amil')) {
                $table->decimal('persentase_hak_amil', 5, 2)->nullable();
            }
            if (! Schema::hasColumn('jenis_penggunaan_hak_amils', 'jenis_donasi_id')) {
                $table->unsignedBigInteger('jenis_donasi_id')->nullable();
            }
            if (! Schema::hasColumn('jenis_penggunaan_hak_amils', 'sumber_dana_penyaluran_id')) {
                $table->unsignedBigInteger('sumber_dana_penyaluran_id')->nullable();
            }
            if (! Schema::hasColumn('jenis_penggunaan_hak_amils', 'tanggal_berlaku_mulai')) {
                $table->date('tanggal_berlaku_mulai')->nullable();
            }
            if (! Schema::hasColumn('jenis_penggunaan_hak_amils', 'tanggal_berlaku_berakhir')) {
                $table->date('tanggal_berlaku_berakhir')->nullable();
            }
        });
    }
};
