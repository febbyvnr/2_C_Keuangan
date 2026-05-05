<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiRkt extends Model
{
    protected $table = 'tr_pm';
    protected $primaryKey = 'ID_PM';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'ID_PM' => 'integer',
        'ID_PROGRAM_KERJA' => 'integer',
        'ID_REF_PM' => 'integer',
        'TGL_PM' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ID_PM) {
                $model->ID_PM = (static::max('ID_PM') ?? 0) + 1;
            }
        });
    }

    public function programKerja(): BelongsTo
    {
        return $this->belongsTo(MstProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    public function refPm(): BelongsTo
    {
        return $this->belongsTo(RefPm::class, 'ID_REF_PM', 'ID_REF_PM');
    }
}
