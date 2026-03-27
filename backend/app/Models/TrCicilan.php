<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrCicilan extends Model
{
    use HasFactory;

    protected $table = 'tr_cicilan';
    protected $primaryKey = 'ID_TR_CICILAN';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];

    protected $fillable = [
        'ID_TR_CICILAN',
        'ID_PEMBAYARAN',
        'TGL_CICILAN',
        'JUMLAH_CICILAN',
        'CICILAN_KE'
    ];

    public function pembayaran()
    {
        return $this->belongsTo(TrPembayaran::class, 'ID_PEMBAYARAN', 'ID_PEMBAYARAN');
    }
}