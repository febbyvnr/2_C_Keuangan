<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
// 1. UBAH INI: Gunakan Authenticatable agar bisa login
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// 2. TAMBAH INI: Wajib untuk membuat Bearer Token API
use Laravel\Sanctum\HasApiTokens; 

// 3. UBAH EXTENDS: Dari Model menjadi Authenticatable
class MstKaryawan extends Authenticatable 
{
    // 4. TAMBAH TRAIT INI
    use HasApiTokens; 

    protected $table = 'mst_karyawan';
    protected $primaryKey = 'NIP_KARYAWAN';

    // PK bertipe string (NIP), bukan integer
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'NIP_KARYAWAN',
        'ID_UNIT',
        'NAMA_KARYAWAN',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_UNIT'   => 'integer',
        'IS_DELETE' => 'boolean',
    ];

    // ── Relasi Bawaan Kamu ──────────────────────────────────────

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MstUnit::class, 'ID_UNIT', 'ID_UNIT');
    }

    public function trPenerimaan(): HasMany
    {
        return $this->hasMany(TrPenerimaan::class, 'NIP_PENERIMA', 'NIP_KARYAWAN');
    }

    public function programKerja(): HasMany
    {
        return $this->hasMany(MstProgramKerja::class, 'NIP_PENANGGUNG_JAWAB', 'NIP_KARYAWAN');
    }

    public function fpdAnggaran(): HasMany
    {
        return $this->hasMany(FpdAnggaran::class, 'NIP_VALIDATOR_FPD', 'NIP_KARYAWAN');
    }

    public function trPembayaran(): HasMany
    {
        return $this->hasMany(TrPembayaran::class, 'NIP_VALIDATOR_PEMBAYARAN', 'NIP_KARYAWAN');
    }

    // ── Relasi Baru untuk RBAC (Login & Hak Akses) ──────────────

    /**
     * Karyawan punya riwayat jabatan.
     * Relasi ke tr_jabatan
     */
    public function trJabatans(): HasMany
    {
        return $this->hasMany(TrJabatan::class, 'NIP_KARYAWAN', 'NIP_KARYAWAN');
    }

    /**
     * Mengecek apakah karyawan memiliki 1 jabatan tertentu.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->trJabatans()->whereHas('refJabatan', function ($query) use ($roleName) {
            $query->where('DESKRIPSI_JABATAN', $roleName);
        })->exists();
    }
    
    /**
     * Mengecek apakah karyawan memiliki salah satu dari banyak jabatan.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->trJabatans()->whereHas('refJabatan', function ($query) use ($roles) {
            $query->whereIn('DESKRIPSI_JABATAN', $roles);
        })->exists();
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('IS_DELETE', 0);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('NAMA_KARYAWAN', 'like', "%{$keyword}%")
              ->orWhere('NIP_KARYAWAN', 'like', "%{$keyword}%");
        });
    }
}