<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtlFpd extends Model
{
    protected $table = 'dtl_fpd';
    protected $primaryKey = 'ID_DT_FPD';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = []; 

    protected $casts = [
        'ID_DT_FPD' => 'integer',
        'ID_FPD' => 'integer',
        'ID_DT_PROGKER' => 'integer',
        'QTY' => 'integer',
        'HARGA_SATUAN' => 'double',
        'VOLUME' => 'integer',
        'TOTAL' => 'double',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ID_DT_FPD) {
                $maxId = static::max('ID_DT_FPD') ?? 0;
                $model->ID_DT_FPD = $maxId + 1;
            }
        });
    }

    public function fpd(): BelongsTo
    {
        return $this->belongsTo(FpdAnggaran::class, 'ID_FPD', 'ID_FPD');
    }

    public function detailProgram(): BelongsTo
    {
        return $this->belongsTo(DtlProgramKerja::class, 'ID_DT_PROGKER', 'ID_DT_PROGKER');
    }
}