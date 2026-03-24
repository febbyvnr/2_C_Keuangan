<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstProgramKerja extends Model
{
    protected $table = 'mst_program_kerja';
    protected $primaryKey = 'ID_PROGRAM_KERJA';
    public $timestamps = false;

    protected $fillable = [
        'ID_TA_ANGGARAN',
        'ID_UNIT',
        'ID_TAN',
        'ID_MASTER_COA',
        'ID_KEGIATAN',
        'NOMINAL',
        'INDIKATOR',
        'SASARAN',
        'WAKTU_AWAL',
        'WAKTU_AKHIR',
        'KELUARAN_PROGKER',
        'PROGRAM_KERJA',
        'NIP_PENANGGUNG_JAWAB',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_PROGRAM_KERJA' => 'integer',
        'ID_TA_ANGGARAN' => 'integer',
        'ID_UNIT' => 'integer',
        'ID_TAN' => 'integer',
        'ID_MASTER_COA' => 'integer',
        'ID_KEGIATAN' => 'integer',
        'NOMINAL' => 'double',
        'WAKTU_AWAL' => 'date',
        'WAKTU_AKHIR' => 'date',
        'IS_DELETE' => 'boolean',
    ];

    /**
     * Relasi ke ref_tahun_anggaran
     */
    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RefTahunAnggaran::class, 'ID_TA_ANGGARAN', 'ID_TA_ANGGARAN');
    }

    /**
     * Relasi ke mst_unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(MstUnit::class, 'ID_UNIT', 'ID_UNIT');
    }

    /**
     * Relasi ke ref_tan
     */
    public function tan(): BelongsTo
    {
        return $this->belongsTo(RefTan::class, 'ID_TAN', 'ID_TAN');
    }

    /**
     * Relasi ke mst_coa
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'ID_MASTER_COA', 'ID_MASTER_COA');
    }

    /**
     * Relasi ke mst_kegiatan
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(MstKegiatan::class, 'ID_KEGIATAN', 'ID_KEGIATAN');
    }

    /**
     * Relasi ke dtl_program_kerja
     */
    public function detailProgramKerja(): HasMany
    {
        return $this->hasMany(DtlProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    /**
     * Relasi ke tr_pm
     */
    public function trPm(): HasMany
    {
        return $this->hasMany(TrPm::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    /**
     * Scope data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('IS_DELETE', 0);
    }
}