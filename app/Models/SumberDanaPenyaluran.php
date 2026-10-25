<?php

// app/Models/SumberDanaPenyaluran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SumberDanaPenyaluran extends Model
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
            ->useLogName('sumber-dana')
            ->logOnlyDirty()
            ->logAll();
    }

    protected $fillable = [
        'nama_sumber_dana',
        'deskripsi',
        'aktif',
        'persentase_hak_amil',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'persentase_hak_amil' => 'decimal:1',
    ];

    public function programPenyalurans()
    {
        return $this->hasMany(ProgramPenyaluran::class);
    }

    public function jenisDonasis()
    {
        return $this->hasMany(JenisDonasi::class);
    }
    //
}
