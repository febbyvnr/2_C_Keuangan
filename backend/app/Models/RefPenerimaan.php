<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefPenerimaan extends Model
{
    protected $table = 'REF_PENERIMAAN';
    protected $primaryKey = 'ID_REF_PENERIMAAN';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = [
        'ID_REF_PENERIMAAN',
        'REF_ID_REF_PENERIMAAN',
        'DESKRIPSI_REF_PENERIMAAN'
    ];
}