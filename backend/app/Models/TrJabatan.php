<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrJabatan extends Model
{
    protected $table = 'tr_jabatan';
    protected $primaryKey = 'ID_TR_JABATAN'; 
    public $timestamps = false; // Karena di SQL nggak ada created_at / updated_at

    // MASUKKAN KE-6 FIELD SESUAI SQL-MU
    protected $fillable = [
        'ID_JABATAN',
        'NIP_KARYAWAN',
        'TGL_MULAI_JABATAN',
        'TGL_SELESAI_JABATAN',
        'NO_SK_JABATAN'
    ];

    /**
     * Relasi ke Karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(MstKaryawan::class, 'NIP_KARYAWAN', 'NIP_KARYAWAN');
    }

    /**
     * Relasi ke Master Jabatan (Referensi RBAC)
     */
    public function refJabatan(): BelongsTo
    {
        return $this->belongsTo(RefJabatanStr::class, 'ID_JABATAN', 'ID_JABATAN');
    }
}