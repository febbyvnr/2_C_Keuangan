<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefTarif extends Model
{
    use RecordsActivity;

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
        'NOMINAL' => 'integer',
        'TGL_PENETAPAN' => 'date',
    ];

    /**
     * Relasi ke REF_JENIS_TARIF
     */
    public function jenisTarif(): BelongsTo
    {
        return $this->belongsTo(
            RefJenisTarif::class,
            'ID_JENIS_TARIF',
            'ID_JENIS_TARIF'
        );
    }

    /**
     * Relasi ke REF_TAHUN_ANGGARAN (opsional)
     */
    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(
            RefTahunAnggaran::class,
            'ID_TA_ANGGARAN',
            'ID_TA_ANGGARAN'
        );
    }

    /**
     * Scope: berdasarkan jenis tarif
     */
    public function scopeByJenis(Builder $query, $idJenis): Builder
    {
        return $query->where('ID_JENIS_TARIF', $idJenis);
    }

    /**
     * Scope: berdasarkan tahun anggaran
     */
    public function scopeByTahun(Builder $query, $idTahun): Builder
    {
        return $query->where('ID_TA_ANGGARAN', $idTahun);
    }

    /**
     * Scope: urut terbaru berdasarkan tanggal penetapan
     */
    public function scopeLatestData(Builder $query): Builder
    {
        return $query->orderBy('TGL_PENETAPAN', 'desc');
    }

    /**
     * Helper: ambil tarif terbaru per jenis
     */
    public static function getLatestByJenis($idJenis)
    {
        return self::byJenis($idJenis)
            ->orderBy('TGL_PENETAPAN', 'desc')
            ->first();
    }
}
