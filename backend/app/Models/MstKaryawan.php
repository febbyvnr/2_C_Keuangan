<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstKaryawan extends Model
{
    protected $table = 'mst_karyawan';
    protected $primaryKey = 'NIP_KARYAWAN';

    // REVISI: Sekarang diatur ke true karena database sudah Auto Increment
    public $incrementing = true; 
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_UNIT',
        'NAMA_KARYAWAN',
        'NAMA_LENGKAP_GELAR',
        'PASSWORD_KARYAWAN',
        'EMAIL_KARYAWAN',
        'IS_DELETE',
    ];

    protected $hidden = [
        'PASSWORD_KARYAWAN',
    ];

    protected $casts = [
        'ID_UNIT'   => 'integer',
        'IS_DELETE' => 'boolean',
    ];

    // ── Relasi Dasar ──────────────────────────────────────────────

    /**
     * Relasi ke unit kerja (misal: Tata Usaha, Sarpras, dll)
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(MstUnit::class, 'ID_UNIT', 'ID_UNIT');
    }

    /**
     * Relasi ke transaksi penerimaan yang ditangani karyawan ini
     */
    public function trPenerimaan(): HasMany
    {
        return $this->hasMany(TrPenerimaan::class, 'NIP_PENERIMA', 'NIP_KARYAWAN');
    }

    // ── Catatan RBAC ──────────────────────────────────────────────
    /* Bagian relasi trJabatans() dan fungsi hasRole() dihapus sementara 
       karena ketergantungan pada tabel ref_jabatan yang ada di branch lain.
       Silakan aktifkan kembali jika branch tersebut sudah di-merge.
    */
}