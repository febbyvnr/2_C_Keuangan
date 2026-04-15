<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefTarif extends Model
{
    protected $table = 'ref_tarif';

    protected $primaryKey = 'ID_REF_TARIF';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_JENIS_TARIF',
        'ID_TA_ANGGARAN',
        'DESKRIPSI_TARIF',
        'NOMINAL',
        'TGL_PENETAPAN',
    ];

    protected $casts = [
        'ID_JENIS_TARIF' => 'integer',
        'ID_TA_ANGGARAN' => 'integer',
        'NOMINAL' => 'double', // Di DB baru pakenya double
        'TGL_PENETAPAN' => 'date',
    ];

    public function jenisTarif(): BelongsTo
    {
        return $this->belongsTo(RefJenisTarif::class, 'ID_JENIS_TARIF', 'ID_JENIS_TARIF');
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(RefTahunAnggaran::class, 'ID_TA_ANGGARAN', 'ID_TA_ANGGARAN');
    }

    public function scopeLatestData(Builder $query): Builder
    {
        return $query->orderBy('TGL_PENETAPAN', 'desc');
    }
}