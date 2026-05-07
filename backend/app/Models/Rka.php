<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rka extends Model
{
    use RecordsActivity;

    protected $table = 'mst_program_kerja';
    protected $primaryKey = 'ID_PROGRAM_KERJA';
    public $incrementing = true; // Diubah ke true karena DB sudah Auto Increment
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_TA_ANGGARAN',
        'ID_UNIT',
        'ID_TAN',
        'ID_MASTER_COA',
        'ID_KEGIATAN',
        'TOTAL_PROGKER',
        'INDIKATOR',
        'SASARAN',
        'WAKTU_AWAL',
        'WAKTU_AKHIR',
        'KELUARAN_PROGKER',
        'PROGRAM_KERJA',
        'NIP_PENANGGUNG_JAWAB',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_PROGRAM_KERJA' => 'integer',
        'ID_TA_ANGGARAN' => 'integer',
        'ID_UNIT' => 'integer',
        'ID_TAN' => 'integer',
        'ID_MASTER_COA' => 'integer',
        'ID_KEGIATAN' => 'integer',
        'TOTAL_PROGKER' => 'double',
        'WAKTU_AWAL' => 'datetime',
        'WAKTU_AKHIR' => 'datetime',
        'IS_DELETE' => 'boolean',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(RkaDetail::class, 'ID_PROGRAM_KERJA', 'ID_PROGRAM_KERJA');
    }
}
