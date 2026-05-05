<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RefMetodePembayaran;
use App\Models\RefTahunAnggaran;
use App\Models\MstSiswa;
use App\Models\TagihanSiswa;
use App\Models\TrCicilan;

class TrPembayaran extends Model
{
    use HasFactory;

    protected $table = 'tr_pembayaran';
    protected $primaryKey = 'ID_PEMBAYARAN';
    public $timestamps = false;

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
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

    protected $casts = [
        'ID_PEMBAYARAN' => 'integer',
        'ID_SISWA_TETAP' => 'integer',
        'KODE_TA' => 'integer',
        'ID_JENIS_PEMBAYARAN' => 'integer',
        'ID_TAGIHAN_SISWA' => 'integer',
        'REF_ID_JENIS_PEMBAYARAN' => 'integer',
        'TGL_BAYAR' => 'datetime',
        'JUMLAH_BAYAR' => 'double',
    ];

    public function cicilan()
    {
        return $this->hasMany(
            TrCicilan::class,
            'ID_PEMBAYARAN',
            'ID_PEMBAYARAN'
        );
    }

    public function tahunAnggaran()
    {
        return $this->belongsTo(
            RefTahunAnggaran::class,
            'KODE_TA',
            'ID_TA_ANGGARAN'
        );
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(
            RefMetodePembayaran::class,
            'ID_JENIS_PEMBAYARAN',
            'ID_METODE_PEMBAYARAN'
        );
    }

    public function siswa()
    {
        return $this->belongsTo(
            MstSiswa::class,
            'ID_SISWA_TETAP',
            'ID_SISWA_TETAP'
        );
    }

    public function tagihan()
    {
        return $this->belongsTo(
            TagihanSiswa::class,
            'ID_TAGIHAN_SISWA',
            'ID_TAGIHAN_SISWA'
        );
    }
}