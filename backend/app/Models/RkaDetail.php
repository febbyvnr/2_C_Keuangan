<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkaDetail extends Model
{
    protected $table = 'dtl_program_kerja';
    protected $primaryKey = 'ID_DT_PROGKER';
    public $incrementing = true; // Diubah ke true
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_PROGRAM_KERJA',
        'ID_REF_DANA',
        'NOMINAL',
        'TGL_AWAL',
        'TGL_AKHIR',
        'QTY',
        'HARGA_SATUAN',
        'VOLUME',
        'SATUAN',
        'TOTAL_PROGKER',
    ];

    protected $casts = [
        'ID_DT_PROGKER' => 'integer',
        'ID_PROGRAM_KERJA' => 'integer',
        'ID_REF_DANA' => 'integer',
        'NOMINAL' => 'double',
        'TGL_AWAL' => 'datetime',
        'TGL_AKHIR' => 'datetime',
        'QTY' => 'integer',
        'HARGA_SATUAN' => 'double',
        'VOLUME' => 'integer',
        'TOTAL_PROGKER' => 'double',
    ];

    public function rka(): BelongsTo
    {
        return $this->belongsTo(Rka::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }
}