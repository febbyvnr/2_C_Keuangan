<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJenisPembayaran extends Model
{
    protected $table = 'ref_jenis_pembayaran';
    protected $primaryKey = 'id_jenis_pembayaran';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = ['ID_JENIS_PEMBAYARAN','deskripsi_jenis_pembayaran'];
}
