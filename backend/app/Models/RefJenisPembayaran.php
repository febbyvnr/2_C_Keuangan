<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class RefJenisPembayaran extends Model
{
    use RecordsActivity;

    protected $table = 'ref_metode_pembayaran';

    protected $primaryKey = 'ID_METODE_PEMBAYARAN';
    public $timestamps = false;
    public $incrementing = true;
    protected $fillable = [
        'DESKRIPSI_METODE_PEMBAYARAN'
    ];
}
