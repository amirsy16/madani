<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramPenyaluran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_program_penyaluran',
        'nama_program',
        'tanggal_penyaluran',
        'jumlah_dana',
        'sumber_dana_penyaluran_id',
        'jenis_donasi_id',
        'asnaf_id',
        'bidang_program_id',
        'penerima_manfaat_individu',
        'penerima_manfaat_lembaga',
        'jumlah_penerima_manfaat',
        'lokasi_penyaluran',
        'keterangan',
        'bukti_penyaluran',
        'dicatat_oleh_id',
    ];

    protected $casts = [
        'tanggal_penyaluran' => 'date',
        'jumlah_dana' => 'decimal:2',
        'jumlah_penerima_manfaat' => 'integer',
    ];

    /**
     * Relasi ke Sumber Dana Penyaluran (REQUIRED)
     * Sumber dana utama dari mana dana ini diambil
     */
    public function sumberDanaPenyaluran()
    {
        return $this->belongsTo(SumberDanaPenyaluran::class, 'sumber_dana_penyaluran_id');
    }

    /**
     * Relasi ke Jenis Donasi (OPTIONAL)
     * Jenis donasi spesifik jika penyaluran menggunakan dana dari jenis donasi tertentu
     */
    public function jenisDonasi()
    {
        return $this->belongsTo(JenisDonasi::class, 'jenis_donasi_id');
    }

    /**
     * Relasi ke Asnaf (CONDITIONAL - Required untuk Dana Zakat)
     * Kategori penerima zakat sesuai 8 golongan asnaf
     */
    public function asnaf()
    {
        return $this->belongsTo(Asnaf::class, 'asnaf_id');
    }

    /**
     * Relasi ke Bidang Program (REQUIRED)
     * Bidang/kategori program penyaluran
     */
    public function bidangProgram()
    {
        return $this->belongsTo(BidangProgram::class, 'bidang_program_id');
    }

    /**
     * Relasi ke User yang mencatat (REQUIRED)
     * User yang membuat/mencatat transaksi penyaluran
     */
    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_id');
    }

    /**
     * Accessor untuk mendapatkan nama penerima manfaat
     * Prioritas: individu > lembaga > 'N/A'
     */
    public function getNamaPenerimaManfaatAttribute(): string
    {
        if ($this->penerima_manfaat_individu) {
            return $this->penerima_manfaat_individu;
        }
        
        if ($this->penerima_manfaat_lembaga) {
            return $this->penerima_manfaat_lembaga;
        }
        
        return 'N/A';
    }

    /**
     * Accessor untuk mendapatkan tipe penerima manfaat
     */
    public function getTipePenerimaManfaatAttribute(): string
    {
        if ($this->penerima_manfaat_individu) {
            return 'individu';
        }
        
        if ($this->penerima_manfaat_lembaga) {
            return 'lembaga';
        }
        
        return 'unknown';
    }

    /**
     * Scope untuk filter berdasarkan sumber dana
     */
    public function scopeBySumberDana($query, $sumberDanaId)
    {
        return $query->where('sumber_dana_penyaluran_id', $sumberDanaId);
    }

    /**
     * Scope untuk filter berdasarkan asnaf
     */
    public function scopeByAsnaf($query, $asnafId)
    {
        return $query->where('asnaf_id', $asnafId);
    }

    /**
     * Scope untuk filter berdasarkan bidang program
     */
    public function scopeByBidangProgram($query, $bidangProgramId)
    {
        return $query->where('bidang_program_id', $bidangProgramId);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByPeriode($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_penyaluran', [$startDate, $endDate]);
    }
}
