<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefTan extends Model
{
    protected $table = 'REF_TAN';
    protected $primaryKey = 'ID_TAN';
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'ID_TAN',
        'TAHUN',
        'IS_CURRENT',
        'DESKRIPSI_TAN'
    ];
}