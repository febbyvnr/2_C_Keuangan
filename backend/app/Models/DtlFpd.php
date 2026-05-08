<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtlFpd extends Model
{
    use RecordsActivity;

    protected $table = 'dtl_fpd';
    protected $primaryKey = 'ID_DT_FPD';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_FPD',
        'ID_DT_PROGKER',
        'QTY',
        'HARGA_SATUAN',
        'VOLUME',
        'SATUAN',
        'TOTAL',
        'LINK_BUKTI_NOTA_FPD',
    ];

    protected $casts = [
        'ID_DT_FPD' => 'integer',
        'ID_FPD' => 'integer',
        'ID_DT_PROGKER' => 'integer',
        'QTY' => 'integer',
        'HARGA_SATUAN' => 'double',
        'VOLUME' => 'integer',
        'TOTAL' => 'double',
    ];

    public function fpd(): BelongsTo
    {
        return $this->belongsTo(FpdAnggaran::class, 'ID_FPD', 'ID_FPD');
    }

    public function detailProgram(): BelongsTo
    {
        return $this->belongsTo(DtlProgramKerja::class, 'ID_DT_PROGKER', 'ID_DT_PROGKER');
    }
}
