<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\RecordsActivity;

class MstCoa extends Model
{

    use RecordsActivity;

    protected $table = 'mst_coa';
    protected $primaryKey = 'ID_MASTER_COA';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    // protected $fillable = [
    //     'MST_ID_MASTER_COA',
    //     'KODE_COA',
    //     'DESKRIPSI_COA',
    //     'IS_DELETE',
    // ];

    protected $fillable = [
    'ID_MASTER_COA',
        'MST_ID_MASTER_COA',
        'KODE_COA',
        'DESKRIPSI_COA',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_MASTER_COA' => 'integer',
        'MST_ID_MASTER_COA' => 'integer',
        'IS_DELETE' => 'boolean',
    ];

    /**
     * Relasi ke parent COA
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'MST_ID_MASTER_COA', 'ID_MASTER_COA');
    }

    /**
     * Relasi ke child COA
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'MST_ID_MASTER_COA', 'ID_MASTER_COA');
    }

    /**
     * Relasi ke mst_program_kerja
     * Satu COA bisa dipakai di banyak program kerja
     */
    public function programKerja(): HasMany
    {
        return $this->hasMany(MstProgramKerja::class, 'ID_MASTER_COA', 'ID_MASTER_COA');
    }

    /**
     * Scope data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('IS_DELETE', 0);
    }
}