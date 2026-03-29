<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrJabatan extends Model
{
    protected $table = 'tr_jabatan';
    protected $primaryKey = 'ID_TR_JABATAN';
    public $timestamps = false;

    protected $fillable = [
        'ID_TR_JABATAN',
        'ID_JABATAN',
        'NIP_KARYAWAN',
        'TGL_MULAI_JABATAN',
        'TGL_SELESAI_JABATAN',
        'NO_SK_JABATAN',
    ];

    protected $casts = [
        'TGL_MULAI_JABATAN' => 'date',
        'TGL_SELESAI_JABATAN' => 'date',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(RefJabatanStr::class, 'ID_JABATAN', 'ID_JABATAN');
    }
}