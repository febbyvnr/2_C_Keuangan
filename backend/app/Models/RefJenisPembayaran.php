<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJenisPembayaran extends Model
{
    protected $table = 'ref_metode_pembayaran';

    protected $primaryKey = 'ID_METODE_PEMBAYARAN';
    public $timestamps = false;
    public $incrementing = true;
    protected $fillable = [
        'DESKRIPSI_METODE_PEMBAYARAN'
    ];
}