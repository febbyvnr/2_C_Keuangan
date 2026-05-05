<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefMetodePembayaran extends Model
{
    protected $table = 'ref_metode_pembayaran';
    protected $primaryKey = 'ID_METODE_PEMBAYARAN';
    public $timestamps = false;

    protected $fillable = [
        'DESKRIPSI_METODE_PEMBAYARAN',
    ];

    protected $casts = [
        'ID_METODE_PEMBAYARAN' => 'integer',
    ];

    public function pembayaran(): HasMany
    {
        return $this->hasMany(
            TrPembayaran::class,
            'ID_JENIS_PEMBAYARAN',
            'ID_METODE_PEMBAYARAN'
        );
    }
}