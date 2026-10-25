<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Fundraiser extends Model
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
            ->useLogName('fundraiser')
            ->logOnlyDirty()
            ->logAll();
    }

    protected $fillable = [
        'nama_fundraiser', 'nomor_identitas', 'nomor_hp',
        'alamat', 'user_id', 'aktif',
    ];

    protected $casts = ['aktif' => 'boolean'];

    public function user(): BelongsTo // Relasi ke User (jika fundraiser adalah user)
    {return $this->belongsTo(User::class, 'user_id');
    }

    public function donasis(): HasMany
    {
        return $this->hasMany(Donasi::class, 'fundraiser_id');
    }
}
