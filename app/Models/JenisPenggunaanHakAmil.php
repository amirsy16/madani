<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPenggunaanHakAmil extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',

        'persentase_hak_amil',
        'jenis_donasi_id',
        'sumber_dana_penyaluran_id',
        'aktif',
        'tanggal_berlaku_mulai',
        'tanggal_berlaku_berakhir',
    ];

    protected $casts = [
        'persentase_hak_amil' => 'decimal:2',
        'aktif' => 'boolean',
        'tanggal_berlaku_mulai' => 'date',
        'tanggal_berlaku_berakhir' => 'date',
    ];

    // Relasi ke PenggunaanHakAmil
    public function penggunaanHakAmils(): HasMany
    {
        return $this->hasMany(PenggunaanHakAmil::class);
    }
}
