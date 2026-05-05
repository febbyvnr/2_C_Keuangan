<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstUnit extends Model
{
    protected $table = 'mst_unit';
    protected $primaryKey = 'ID_UNIT';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_UNIT',
    ];

    protected $casts = [
        'ID_UNIT' => 'integer',
    ];
}
