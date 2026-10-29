<?php

namespace App\Models;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Donasi extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'donatur_id', 'jenis_donasi_id', 'metode_pembayaran_id', 'fundraiser_id',
        'jumlah', 'keterangan_infak_khusus', 'kategori_dana_non_halal_id', 'deskripsi_barang', 'perkiraan_nilai_barang',
        'bukti_pembayaran', 'catatan_donatur', 'tanggal_donasi', 'atas_nama_hamba_allah', 'nomor_transaksi_unik',
        'status_konfirmasi', 'dikofirmasi_oleh_user_id', 'dikonfirmasi_pada', 'catatan_konfirmasi',
        'dicatat_oleh_user_id',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'perkiraan_nilai_barang' => 'decimal:2',
        'tanggal_donasi' => 'date',
        'atas_nama_hamba_allah' => 'boolean',
        'dikonfirmasi_pada' => 'datetime',
    ];

    public function donatur(): BelongsTo
    {
        return $this->belongsTo(Donatur::class, 'donatur_id');
    }

    public function jenisDonasi(): BelongsTo
    {
        return $this->belongsTo(JenisDonasi::class, 'jenis_donasi_id');
    }

    public function metodePembayaran(): BelongsTo
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }

    public function fundraiser(): BelongsTo
    {
        return $this->belongsTo(Fundraiser::class, 'fundraiser_id');
    }

    public function kategoriDanaNonHalal(): BelongsTo
    {
        return $this->belongsTo(KategoriDanaNonHalal::class, 'kategori_dana_non_halal_id');
    }

    public function dikonfirmasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikofirmasi_oleh_user_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_user_id');
    }

    /**
     * Nomor transaksi unik dibuat di server (dipakai form create, relation
     * manager donatur, dan impor) — tidak boleh dipercaya dari klien.
     *
     * Format mengikuti nomor urut warisan LAZ: angka 6 digit ber-nol di
     * depan (001454, 001455, ...) yang melanjutkan nomor terbesar yang
     * sudah ada; nomor lama berformat TRX diabaikan otomatis (cast ke
     * angka bernilai 0).
     */
    public static function generateNomorTransaksiUnik(): string
    {
        // Kunci sementara mencegah dua petugas mendapat nomor sama
        // secara bersamaan; indeks unik basis data menjadi pengaman akhir.
        try {
            return Cache::lock('donasi:nomor-transaksi', 10)->block(5, fn () => static::nomorBerikutnya());
        } catch (LockTimeoutException) {
            return static::nomorBerikutnya();
        }
    }

    private static function nomorBerikutnya(): string
    {
        $terbesar = (int) static::query()->max(DB::raw('CAST(nomor_transaksi_unik AS UNSIGNED)'));

        return str_pad((string) ($terbesar + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Audit trail: setiap create/update/delete donasi tercatat
     * (pelaku, atribut yang berubah, waktu) di activity_log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('donasi')
            ->logOnlyDirty()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'menambahkan',
                'updated' => 'memperbarui',
                'deleted' => 'menghapus',
                default => $eventName,
            });
    }

    /**
     * Normalisasi uang vs barang: satu transaksi hanya boleh punya SATU nilai.
     * Mencegah double-count pada agregat `jumlah + perkiraan_nilai_barang`.
     * Berlaku untuk semua jalur (form, import, seeder) karena di level model.
     */
    protected static function booted(): void
    {
        static::saving(function (Donasi $donasi) {
            $jenis = $donasi->jenisDonasi ?? ($donasi->jenis_donasi_id ? JenisDonasi::find($donasi->jenis_donasi_id) : null);
            if (! $jenis) {
                return;
            }
            if ($jenis->apakah_barang) {
                $donasi->jumlah = 0;
            } else {
                $donasi->perkiraan_nilai_barang = null;
            }
        });
    }

    /**
     * Get total nilai donasi (cash + barang)
     */
    public function getTotalNilaiAttribute(): float
    {
        return ($this->jumlah ?? 0) + ($this->perkiraan_nilai_barang ?? 0);
    }

    /**
     * Get perhitungan hak amil berdasarkan pengaturan
     */
    public function getHakAmilAttribute(): float
    {
        $totalNilai = $this->total_nilai;

        if ($totalNilai <= 0) {
            return 0;
        }

        // Cari sumber dana penyaluran dari jenis donasi
        $sumberDanaId = $this->jenisDonasi ? $this->jenisDonasi->sumber_dana_penyaluran_id : null;
        $sumberDana = SumberDanaPenyaluran::find($sumberDanaId);

        $persentase = $sumberDana ? ($sumberDana->persentase_hak_amil ?? 0) : 0;

        return ($totalNilai * $persentase) / 100;
    }

    /**
     * Get sisa donasi setelah dikurangi hak amil
     */
    public function getSisaDonasiAttribute(): float
    {
        return $this->total_nilai - $this->hak_amil;
    }
}
