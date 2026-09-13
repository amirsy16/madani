<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index kerja untuk tabel donasis.
 *
 * Migration lama (2025_06_11_005856) memakai `CREATE INDEX IF NOT EXISTS`
 * yang tidak valid di MySQL dan error-nya tertelan try/catch — tidak ada
 * satu pun index yang terbuat. Migration ini idempoten: cek index yang
 * sudah ada dulu, buat yang belum ada.
 *
 * Catatan: nomor_transaksi_unik sudah punya unique index bawaan, tidak
 * dibuat ulang. Kolom `dikofirmasi_oleh_user_id` (typo memang nama kolomnya)
 * di-index dengan nama benar di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'idx_donasis_status_tanggal' => ['status_konfirmasi', 'tanggal_donasi'],
            'idx_donasis_donatur_status' => ['donatur_id', 'status_konfirmasi'],
            'idx_donasis_jenis_status' => ['jenis_donasi_id', 'status_konfirmasi'],
            'idx_donasis_tanggal_status' => ['tanggal_donasi', 'status_konfirmasi'],
            'idx_donasis_metode_pembayaran' => ['metode_pembayaran_id'],
            'idx_donasis_fundraiser' => ['fundraiser_id'],
            'idx_donasis_hamba_allah' => ['atas_nama_hamba_allah'],
            'idx_donasis_dikofirmasi_oleh' => ['dikofirmasi_oleh_user_id'],
            'idx_donasis_dicatat_oleh' => ['dicatat_oleh_user_id'],
        ];

        $existing = Schema::getIndexes('donasis');
        $existingNames = array_column($existing, 'name');

        foreach ($indexes as $name => $columns) {
            if (in_array($name, $existingNames)) {
                continue;
            }

            // Skip jika kombinasi kolom yang identik sudah ter-index dengan nama lain
            $columnsNormalized = implode(',', $columns);
            $duplicate = false;
            foreach ($existing as $index) {
                if (implode(',', $index['columns']) === $columnsNormalized) {
                    $duplicate = true;
                    break;
                }
            }

            if (! $duplicate) {
                Schema::table('donasis', function (Blueprint $table) use ($columns, $name) {
                    $table->index($columns, $name);
                });
            }
        }

        // Index pendukung laporan yang sering dipakai
        if (! in_array('idx_penggunaan_hak_amils_tanggal', array_column(Schema::getIndexes('penggunaan_hak_amils'), 'name'))) {
            Schema::table('penggunaan_hak_amils', function (Blueprint $table) {
                $table->index('tanggal', 'idx_penggunaan_hak_amils_tanggal');
            });
        }

        if (! in_array('idx_program_penyalurans_tanggal', array_column(Schema::getIndexes('program_penyalurans'), 'name'))) {
            Schema::table('program_penyalurans', function (Blueprint $table) {
                $table->index('tanggal_penyaluran', 'idx_program_penyalurans_tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('donasis', function (Blueprint $table) {
            foreach ([
                'idx_donasis_status_tanggal',
                'idx_donasis_donatur_status',
                'idx_donasis_jenis_status',
                'idx_donasis_tanggal_status',
                'idx_donasis_metode_pembayaran',
                'idx_donasis_fundraiser',
                'idx_donasis_hamba_allah',
                'idx_donasis_dikofirmasi_oleh',
                'idx_donasis_dicatat_oleh',
            ] as $name) {
                $table->dropIndex($name);
            }
        });

        Schema::table('penggunaan_hak_amils', function (Blueprint $table) {
            $table->dropIndex('idx_penggunaan_hak_amils_tanggal');
        });

        Schema::table('program_penyalurans', function (Blueprint $table) {
            $table->dropIndex('idx_program_penyalurans_tanggal');
        });
    }
};
