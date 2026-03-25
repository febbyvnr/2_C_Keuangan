<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSumberDana extends Model
{
    protected $table = 'REF_SUMBER_DANA';
    protected $primaryKey = 'ID_REF_DANA';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = [
        'ID_REF_DANA',
        'REF_ID_REF_DANA'
    ];
}