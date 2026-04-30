<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefPm extends Model
{
    protected $table = 'ref_pm';
    protected $primaryKey = 'ID_REF_PM';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'ID_REF_PM' => 'integer',
        'REF_ID_REF_PM' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->ID_REF_PM) {
                $maxId = static::max('ID_REF_PM') ?? 0;
                $model->ID_REF_PM = $maxId + 1;
            }
        });
    }

    public function trPm(): HasMany
    {
        return $this->hasMany(TrPm::class, 'ID_REF_PM', 'ID_REF_PM');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'REF_ID_REF_PM', 'ID_REF_PM');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'REF_ID_REF_PM', 'ID_REF_PM');
    }
}