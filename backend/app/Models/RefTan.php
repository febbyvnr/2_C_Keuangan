<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefTan extends Model
{
    protected $table = 'ref_tan';
    protected $primaryKey = 'ID_TAN';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'ID_TAN',
        'TAHUN',
        'IS_CURRENT',
        'DESKRIPSI_TAN',
    ];

    protected $casts = [
        'ID_TAN' => 'integer',
        'IS_CURRENT' => 'boolean',
    ];

    public function programKerja(): HasMany
    {
        return $this->hasMany(MstProgramKerja::class, 'ID_TAN', 'ID_TAN');
    }
}