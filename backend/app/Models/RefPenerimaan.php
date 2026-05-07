<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class RefPenerimaan extends Model
{
    use RecordsActivity;

    protected $table = 'ref_penerimaan';
    protected $primaryKey = 'ID_REF_PENERIMAAN';
    public $timestamps = false;

    protected $fillable = [
        'REF_ID_REF_PENERIMAAN',
        'DESKRIPSI_REF_PENERIMAAN',
    ];

    public function parent()
    {
        return $this->belongsTo(RefPenerimaan::class, 'REF_ID_REF_PENERIMAAN', 'ID_REF_PENERIMAAN');
    }

    public function children()
    {
        return $this->hasMany(RefPenerimaan::class, 'REF_ID_REF_PENERIMAAN', 'ID_REF_PENERIMAAN');
    }

    public function trPenerimaan()
    {
        // Sesuaikan nama model TrPenerimaan jika berbeda
        return $this->hasMany(TrPenerimaan::class, 'ID_REF_PENERIMAAN', 'ID_REF_PENERIMAAN');
    }
}
