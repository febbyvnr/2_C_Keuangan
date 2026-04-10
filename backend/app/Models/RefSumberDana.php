<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSumberDana extends Model
{
    protected $table = 'ref_sumber_dana';
    protected $primaryKey = 'ID_REF_DANA';
    public $timestamps = false;

    protected $fillable = [
        'REF_ID_REF_DANA',

    ];

    public function parent()
    {
        return $this->belongsTo(RefSumberDana::class, 'REF_ID_REF_DANA', 'ID_REF_DANA');
    }

    public function children()
    {
        return $this->hasMany(RefSumberDana::class, 'REF_ID_REF_DANA', 'ID_REF_DANA');
    }

    public function transaksiPenerimaan()
    {
        return $this->hasMany(TrPenerimaan::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }
}
