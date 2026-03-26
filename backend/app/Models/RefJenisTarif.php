<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefJenisTarif extends Model
{
    protected $table = 'REF_JENIS_TARIF';

    protected $primaryKey = 'ID_JENIS_TARIF';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ID_JENIS_TARIF',
        'DESKRIPSI_JENIS_TARIF',
    ];

    protected $casts = [
        'ID_JENIS_TARIF' => 'integer',
    ];

    /**
     * Relasi ke REF_TARIF
     */
    public function tarif(): HasMany
    {
        return $this->hasMany(
            RefTarif::class,
            'ID_JENIS_TARIF',
            'ID_JENIS_TARIF'
        );
    }

    /**
     * Scope: search berdasarkan deskripsi
     */
    public function scopeSearch(Builder $query, $keyword): Builder
    {
        return $query->where('DESKRIPSI_JENIS_TARIF', 'like', "%{$keyword}%");
    }

    /**
     * Scope: urut terbaru
     */
    public function scopeLatestData(Builder $query): Builder
    {
        return $query->orderBy('ID_JENIS_TARIF', 'desc');
    }

    /**
     * Helper: cek apakah sudah dipakai di REF_TARIF
     */
    public function isUsed(): bool
    {
        return $this->tarif()->exists();
    }

    /**
     * Accessor: label untuk frontend
     */
    public function getLabelAttribute(): string
    {
        return $this->DESKRIPSI_JENIS_TARIF;
    }
}