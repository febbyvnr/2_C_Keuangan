<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstProgramKerja extends Model
{
    protected $table = 'mst_program_kerja';
    protected $primaryKey = 'ID_PROGRAM_KERJA';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'ID_PROGRAM_KERJA',
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
        'NIP_VALIDATOR_PROGKER',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_PROGRAM_KERJA' => 'integer',
        'ID_TA_ANGGARAN' => 'integer',
        'ID_UNIT' => 'integer',
        'ID_TAN' => 'integer',
        'ID_MASTER_COA' => 'integer',
        'ID_KEGIATAN' => 'integer',
        'NOMINAL' => 'float',
        'WAKTU_AWAL' => 'date',
        'WAKTU_AKHIR' => 'date',
        'IS_DELETE' => 'boolean',
    ];

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RefTahunAnggaran::class, 'ID_TA_ANGGARAN', 'ID_TA_ANGGARAN');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MstUnit::class, 'ID_UNIT', 'ID_UNIT');
    }

    public function tan(): BelongsTo
    {
        return $this->belongsTo(RefTan::class, 'ID_TAN', 'ID_TAN');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'ID_MASTER_COA', 'ID_MASTER_COA');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(MstKegiatan::class, 'ID_KEGIATAN', 'ID_KEGIATAN');
    }

    public function detailProgramKerja(): HasMany
    {
        return $this->hasMany(DtlProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    public function trPm(): HasMany
    {
        return $this->hasMany(TrPm::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('IS_DELETE', 0);
    }
}