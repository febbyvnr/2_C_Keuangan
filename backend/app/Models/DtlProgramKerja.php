<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DtlProgramKerja extends Model
{
    protected $table = 'dtl_program_kerja';
    protected $primaryKey = 'ID_DT_PROGKER';
    public $timestamps = false;

    protected $fillable = [
        'ID_PROGRAM_KERJA',
        'ID_REF_DANA',
        'NOMINAL',
        'TGL_AWAL',
        'TGL_AKHIR',
        'QTY',
        'HARGA_SATUAN',
        'VOLUME',
        'SATUAN',
    ];

    protected $casts = [
        'ID_DT_PROGKER' => 'integer',
        'ID_PROGRAM_KERJA' => 'integer',
        'ID_REF_DANA' => 'integer',
        'NOMINAL' => 'double',
        'TGL_AWAL' => 'date',
        'TGL_AKHIR' => 'date',
        'QTY' => 'integer',
        'HARGA_SATUAN' => 'double',
        'VOLUME' => 'integer',
    ];

    /**
     * Relasi ke mst_program_kerja
     */
    public function programKerja(): BelongsTo
    {
        return $this->belongsTo(MstProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    /**
     * Relasi ke ref_sumber_dana
     */
    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(RefSumberDana::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }

    /**
     * Relasi ke dtl_fpd
     */
    public function detailFpd(): HasMany
    {
        return $this->hasMany(DtlFpd::class, 'ID_DT_PROGKER', 'ID_DT_PROGKER');
    }
}