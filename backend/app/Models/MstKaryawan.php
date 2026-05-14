<?php

namespace App\Models;

// 1. Ganti import Model jadi Authenticatable
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 2. Wajib import ini
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MstKaryawan extends Authenticatable // 3. Ganti extends-nya
{
    use HasApiTokens, Notifiable; // 4. Tambahkan trait ini

    protected $table = 'mst_karyawan';
    protected $primaryKey = 'NIP_KARYAWAN';

    public $incrementing = false; // Karena NIP bukan angka auto-increment
    protected $keyType = 'string'; // Wajib didefinisikan kalau PK-nya varchar
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
        'remember_token',
    ];

    protected $casts = [
        'ID_UNIT'   => 'integer',
        'IS_DELETE' => 'boolean',
    ];

    // Memberitahu Laravel kalau kolom passwordmu namanya PASSWORD_KARYAWAN (bukan password)
    public function getAuthPassword()
    {
        return $this->PASSWORD_KARYAWAN;
    }

    // ── Relasi Dasar ──────────────────────────────────────────────

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MstUnit::class, 'ID_UNIT', 'ID_UNIT');
    }

    public function trPenerimaan(): HasMany
    {
        return $this->hasMany(TrPenerimaan::class, 'NIP_PENERIMA', 'NIP_KARYAWAN');
    }

    public function jabatan()
    {
        return $this->hasOne(TrJabatan::class, 'NIP_KARYAWAN', 'NIP_KARYAWAN');
    }

    public function jabatans(): BelongsToMany
    {
        return $this->belongsToMany(
            RefJabatanStr::class,
            'tr_jabatan',
            'NIP_KARYAWAN',
            'ID_JABATAN',
            'NIP_KARYAWAN',
            'ID_JABATAN'
        );
    }
}
