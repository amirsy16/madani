<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MetodePembayaran extends Model
{
    use HasFactory;
    use HasFactory, LogsActivity;

    /**
     * Jejak audit master keuangan: perubahan kolom ini menggeser angka
     * laporan, wajib tercatat di Log Aktivitas (BG-06/AK-10).
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('metode-pembayaran')
            ->logOnlyDirty()
            ->logAll();
    }

    protected $fillable = [
        'nama', 'kode', 'tipe', 'nomor_rekening',
        'atas_nama_rekening', 'bank_name', 'instruksi_pembayaran', 'aktif',
    ];

    protected $casts = ['aktif' => 'boolean'];

    public function donasis(): HasMany
    {
        return $this->hasMany(Donasi::class, 'metode_pembayaran_id');
    }
}
