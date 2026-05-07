<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefVisiMisi extends Model
{
    use RecordsActivity;

    protected $table = 'ref_visi_misi';
    protected $primaryKey = 'ID_VISI_MISI';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'ID_VISI_MISI' => 'integer',
        'IS_ACTIVE' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ID_VISI_MISI) {
                $maxId = static::max('ID_VISI_MISI') ?? 0;
                $model->ID_VISI_MISI = $maxId + 1;
            }
        });
    }

    public function trPm(): HasMany
    {
        return $this->hasMany(TrPm::class, 'ID_VISI_MISI', 'ID_VISI_MISI');
    }
}
