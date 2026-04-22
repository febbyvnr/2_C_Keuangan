<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefPenerimaan extends Model
{
    protected $table = 'ref_penerimaan';
    protected $primaryKey = 'ID_REF_PENERIMAAN';
    public $timestamps = false;

    protected $fillable = [
        'DESKRIPSI_REF_PENERIMAAN',
    ];
}