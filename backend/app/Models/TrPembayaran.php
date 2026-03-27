<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrPembayaran extends Model
{
    use HasFactory;

    protected $table = 'tr_pembayaran';
    protected $primaryKey = 'ID_PEMBAYARAN';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'ID_PEMBAYARAN',
        'ID_SISWA_TETAP',
        'KODE_TA',
        'ID_JENIS_PEMBAYARAN',
        'ID_TAGIHAN_SISWA',
        'REF_ID_JENIS_PEMBAYARAN',
        'TGL_BAYAR',
        'JUMLAH_BAYAR',
        'LINK_BUKTI_BAYAR',
        'NIP_VALIDATOR_PEMBAYARAN',
    ];

    public function cicilan()
    {
        return $this->hasMany(TrCicilan::class, 'ID_PEMBAYARAN', 'ID_PEMBAYARAN');
    }
}