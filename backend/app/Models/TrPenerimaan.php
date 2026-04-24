<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPenerimaan extends Model
{
    protected $table = 'tr_penerimaan';
    protected $primaryKey = 'ID_TR_PENERIMAAN';
    public $timestamps = false;

    protected $fillable = [
        'ID_REF_PENERIMAAN',
        'ID_REF_DANA',
        'DESKRIPSI_TR_PENERIMAAN',
        'TANGGAL_TR_PENERIMAAN',
        'JUMLAH_TR_PENERIMAAN',
        'NIP_PENERIMA',
    ];
}