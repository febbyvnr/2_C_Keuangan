<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rka extends Model
{
    use RecordsActivity;
    protected $table = 'dtl_program_kerja';
    protected $primaryKey = 'ID_DT_PROGKER';
    public $incrementing = true; // Diubah ke true karena DB sudah Auto Increment
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_DT_PROGKER',
        'ID_PROGRAM_KERJA',
        'ID_REF_DANA',
        'NOMINAL',
        'TGL_AWAL',
        'TGL_AKHIR',
        'QTY',
        'HARGA_SATUAN',
        'VOLUME',
        'SATUAN'
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
        'SATUAN' => 'string'
    ];

    public function rkt(): BelongsTo
    {
        return $this->belongsTo(MstProgramKerja::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }

    public function refDana(): BelongsTo
    {
        return $this->belongsTo(RefSumberDana::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }
}
