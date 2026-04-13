<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJenisPembayaran extends Model
{
    protected $table = 'ref_jenis_pembayaran';

    protected $primaryKey = 'ID_JENIS_PEMBAYARAN';
    public $timestamps = false;

    protected $fillable = [

        'ID_JENIS_PEMBAYARAN',
        'DESKRIPSI_JENIS_PEMBAYARAN'
    ];
}