<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPenerimaan extends Model
{
    protected $table = 'tr_penerimaan';
    protected $primaryKey = 'id_Tr_penerimaan';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
}