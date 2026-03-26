<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSumberDana extends Model
{
    protected $table = 'ref_sumber_dana';
    protected $primaryKey = 'ID_REF_DANA';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'ID_REF_DANA' => 'integer',
        'REF_ID_REF_DANA' => 'integer',
    ];
}
