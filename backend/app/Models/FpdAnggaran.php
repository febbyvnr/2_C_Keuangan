<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FpdAnggaran extends Model
{
    protected $table = 'fpd_anggaran';
    protected $primaryKey = 'ID_FPD';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded =[];

    protected $casts = [
        'ID_FPD' => 'integer',
        'ID_PROGRAM_KERJA' => 'integer',
        'TGL_FPD' => 'date',
        'NOMINAL_ANGGARAN' => 'double',
        'NOMINAL_FPD' => 'double',
        'NOMINAL_SISA' => 'double',
    ];

    public function detailFpd(): HasMany
    {
        return $this->hasMany(DtlFpd::class, 'ID_FPD', 'ID_FPD');
    }

    public function programKerja(): BelongsTo
    {
        return $this->belongsTo(ProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }
}
