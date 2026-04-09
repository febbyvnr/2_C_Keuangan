<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPenerimaan extends Model
{
    protected $table = 'tr_penerimaan';
    protected $primaryKey = 'ID_TR_PENERIMAAN';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_TR_PENERIMAAN',
        'ID_REF_PENERIMAAN',
        'ID_REF_DANA',
        'DESKRIPSI_TR_PENERIMAAN',
        'TANGGAL_TR_PENERIMAAN',
        'JUMLAH_TR_PENERIMAAN',
        'NIP_PENERIMA',
    ];

    protected $casts = [
        'JUMLAH_TR_PENERIMAAN' => 'float',
    ];

    public function refPenerimaan()
    {
        return $this->belongsTo(RefPenerimaan::class, 'ID_REF_PENERIMAAN', 'ID_REF_PENERIMAAN');
    }

    public function refSumberDana()
    {
        return $this->belongsTo(RefSumberDana::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }

    public function penerima()
    {
        return $this->belongsTo(MstKaryawan::class, 'NIP_PENERIMA', 'NIP_KARYAWAN');
    }
}
