<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FpdAnggaran extends Model
{
    use RecordsActivity;

    protected $table = 'fpd_anggaran';
    protected $primaryKey = 'ID_FPD';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_PROGRAM_KERJA',
        'TGL_FPD',
        'NOMINAL_ANGGARAN',
        'NOMINAL_FPD',
        'NOMINAL_SISA',
        'NIP_VALIDATOR_FPD',
    ];

    protected $casts = [
        'ID_FPD' => 'integer',
        'ID_PROGRAM_KERJA' => 'integer',
        'TGL_FPD' => 'datetime',
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
        return $this->belongsTo(MstProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    public function validator()
    {
        return $this->belongsTo(MstKaryawan::class, 'NIP_VALIDATOR_FPD', 'NIP_KARYAWAN');
    }
}
