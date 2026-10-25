<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class KategoriInfaqTerikat extends Model
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
            ->useLogName('kategori-infaq')
            ->logOnlyDirty()
            ->logAll();
    }

    protected $table = 'kategori_infaq_terikats';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'aktif',
        'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Scope untuk kategori aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope untuk pengurutan
     */
    public function scopeUrutan($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('nama_kategori', 'asc');
    }
}
