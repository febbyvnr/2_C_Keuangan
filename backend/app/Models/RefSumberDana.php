<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSumberDana extends Model
{
    protected $table = 'ref_sumber_dana';
    protected $primaryKey = 'ID_REF_DANA';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $guarded = [];

    protected $fillable = [
        'ID_REF_DANA',
        'REF_ID_REF_DANA',
        'DESKRIPSI_SUMBER_DANA',
    ];

    protected $casts = [
        'ID_REF_DANA' => 'integer',
        'REF_ID_REF_DANA' => 'integer',
        'DESKRIPSI_SUMBER_DANA' => 'string'
    ];

    public function dtlProgramKerja()
    {
        return $this->hasMany(DtlProgramKerja::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }

    public function parent()
    {
        return $this->belongsTo(RefSumberDana::class, 'REF_ID_REF_DANA', 'ID_REF_DANA');
    }

    public function children()
    {
        return $this->hasMany(RefSumberDana::class, 'REF_ID_REF_DANA', 'ID_REF_DANA');
    }

    public function trPenerimaan()
    {
        return $this->hasMany(TrPenerimaan::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }
}
