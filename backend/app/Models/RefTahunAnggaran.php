<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefTahunAnggaran extends Model
{
    protected $table = 'REF_TAHUN_ANGGARAN';

    protected $primaryKey = 'ID_TA_ANGGARAN';

    public $incrementing = false; // 

    public $timestamps = false;

    protected $fillable = [
        'ID_TA_ANGGARAN',
        'DESKRIPSI_TAHUN_ANGGARAN',
        'IS_CURRENT',
    ];

    protected $casts = [
        'ID_TA_ANGGARAN' => 'integer',
        'IS_CURRENT' => 'boolean',
    ];

    /**
     * Relasi ke MST_PROGRAM_KERJA
     */
    public function programKerja(): HasMany
    {
        return $this->hasMany(
            MstProgramKerja::class,
            'ID_TA_ANGGARAN',
            'ID_TA_ANGGARAN'
        );
    }

    /**
     * Scope: hanya yang aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('IS_CURRENT', 1);
    }

    /**
     * Scope: urut terbaru
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('ID_TA_ANGGARAN', 'desc');
    }

    /**
     * Helper: ambil 1 tahun anggaran aktif
     */
    public static function getActive()
    {
        return self::active()->first();
    }

    /**
     * Accessor: label untuk frontend
     */
    public function getLabelAttribute(): string
    {
        return $this->DESKRIPSI_TAHUN_ANGGARAN;
    }
}