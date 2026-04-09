<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJenisPembayaran extends Model
{
    protected $table = 'ref_jenis_pembayaran';
    protected $primaryKey = 'id_jenis_pembayaran';
    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = ['deskripsi_jenis_pembayaran', 'id_jenis_pembayaran'];
}
