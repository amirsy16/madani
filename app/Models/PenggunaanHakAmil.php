<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PenggunaanHakAmil extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tanggal',
        'jenis_penggunaan_hak_amil_id',
        'keterangan', // Tambahkan ini jika ada
        'jumlah',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jenisPenggunaanHakAmil(): BelongsTo // Relasi ke master data
    {
        return $this->belongsTo(JenisPenggunaanHakAmil::class);
    }

    /**
     * Audit trail: pencatatan pelaku & waktu untuk create/update/delete penggunaan hak amil.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('hak-amil')
            ->logOnlyDirty()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'menambahkan',
                'updated' => 'memperbarui',
                'deleted' => 'menghapus',
                default => $eventName,
            });
    }
}
