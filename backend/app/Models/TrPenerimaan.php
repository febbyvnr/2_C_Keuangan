<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrPenerimaan extends Model
{
    protected $table = 'tr_penerimaan';
    protected $primaryKey = 'ID_TR_PENERIMAAN';
    
    // Primary Key sekarang Auto Increment
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_REF_PENERIMAAN',
        'ID_REF_DANA',
        'DESKRIPSI_TR_PENERIMAAN',
        'TANGGAL_TR_PENERIMAAN',
        'JUMLAH_TR_PENERIMAAN',
        'NIP_PENERIMA',
    ];

    // Relasi ke tabel Referensi Penerimaan [cite: 307]
    public function refPenerimaan(): BelongsTo
    {
        return $this->belongsTo(RefPenerimaan::class, 'ID_REF_PENERIMAAN', 'ID_REF_PENERIMAAN');
    }

    // Relasi ke tabel Sumber Dana [cite: 305]
    public function refSumberDana(): BelongsTo
    {
        return $this->belongsTo(RefSumberDana::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }

    // Relasi ke Karyawan/Penerima [cite: 303]
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(MstKaryawan::class, 'NIP_PENERIMA', 'NIP_KARYAWAN');
    }
}