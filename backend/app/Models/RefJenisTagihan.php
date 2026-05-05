<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefJenisTagihan extends Model
{
    protected $table = 'ref_jenis_tagihan';
    protected $primaryKey = 'ID_JENIS_TAGIHAN';
    public $timestamps = false;

    protected $fillable = [
        'DESKRIPSI_JENIS_TAGIHAN',
    ];

    protected $casts = [
        'ID_JENIS_TAGIHAN' => 'integer',
    ];

    public function tagihanSiswa(): HasMany
    {
        return $this->hasMany(
            TagihanSiswa::class,
            'ID_JENIS_TAGIHAN',
            'ID_JENIS_TAGIHAN'
        );
    }
}