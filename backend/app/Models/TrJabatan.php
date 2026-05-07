<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrJabatan extends Model
{
    protected $table = 'tr_jabatan';
    protected $primaryKey = 'ID_TR_JABATAN';
    public $timestamps = false;

    protected $fillable = [
        'NIP_KARYAWAN',
        'ID_JABATAN_STR',
        'TGL_MULAI_MENJABAT',
        'TGL_AKHIR_MENJABAT'
    ];

    public function refJabatan()
    {
        return $this->belongsTo(RefJabatanStr::class, 'ID_JABATAN', 'ID_JABATAN');
    }
}