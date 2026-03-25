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

    public function siswaTetap()
    {
        return $this->belongsTo(SiswaTetap::class, 'ID_SISWA_TETAP', 'id'); 
    }

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'ID_JENIS_PEMBAYARAN', 'id');
    }
}