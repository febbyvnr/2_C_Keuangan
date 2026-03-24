<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstKegiatan extends Model
{
    protected $table = 'mst_kegiatan';
    protected $primaryKey = 'ID_KEGIATAN';
    public $timestamps = false;
    public $incrementing = false;

    // protected $fillable = [
    //     'MST_ID_KEGIATAN',
    //     'DESKRIPSI_KEGIATAN',
    //     'IS_DELETE',
    // ];

    protected $fillable = [
        'ID_KEGIATAN',
        'MST_ID_KEGIATAN',
        'DESKRIPSI_KEGIATAN',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_KEGIATAN' => 'integer',
        'MST_ID_KEGIATAN' => 'integer',
        'IS_DELETE' => 'boolean',
    ];

    /**
     * Relasi ke parent kegiatan
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'MST_ID_KEGIATAN', 'ID_KEGIATAN');
    }

    /**
     * Relasi ke child kegiatan
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'MST_ID_KEGIATAN', 'ID_KEGIATAN');
    }

    /**
     * Relasi ke mst_program_kerja
     * Satu kegiatan bisa dipakai di banyak program kerja
     */
    public function programKerja(): HasMany
    {
        return $this->hasMany(MstProgramKerja::class, 'ID_KEGIATAN', 'ID_KEGIATAN');
    }

    /**
     * Scope data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('IS_DELETE', 0);
    }
}