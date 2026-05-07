<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrPm extends Model
{
    use RecordsActivity;

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
        'ID_VISI_MISI' => 'integer',
        'TGL_PM' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ID_PM) {
                $maxId = static::max('ID_PM') ?? 0;
                $model->ID_PM = $maxId + 1;
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

    public function refVisiMisi(): BelongsTo
    {
        return $this->belongsTo(RefVisiMisi::class, 'ID_VISI_MISI', 'ID_VISI_MISI');
    }
}
