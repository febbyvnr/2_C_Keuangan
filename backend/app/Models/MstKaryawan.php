<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstKaryawan extends Model
{
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

    // ── Relasi ──────────────────────────────────────────────────

    /**
     * Karyawan ini memimpin/tergabung dalam unit kerja tertentu.
     * Relasi ke mst_unit via ID_UNIT.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(MstUnit::class, 'ID_UNIT', 'ID_UNIT');
    }

    /**
     * Karyawan ini sebagai penerima pada transaksi penerimaan (F82-F86).
     * NIP_PENERIMA di tr_penerimaan → NIP_KARYAWAN di mst_karyawan.
     */
    public function trPenerimaan(): HasMany
    {
        return $this->hasMany(TrPenerimaan::class, 'NIP_PENERIMA', 'NIP_KARYAWAN');
    }

    /**
     * Karyawan ini sebagai penanggung jawab program kerja / RKT (F58-F63).
     * NIP_PENANGGUNG_JAWAB di mst_program_kerja → NIP_KARYAWAN.
     */
    public function programKerja(): HasMany
    {
        return $this->hasMany(MstProgramKerja::class, 'NIP_PENANGGUNG_JAWAB', 'NIP_KARYAWAN');
    }

    /**
     * Karyawan ini sebagai validator FPD (F96).
     * NIP_VALIDATOR_FPD di fpd_anggaran → NIP_KARYAWAN.
     */
    public function fpdAnggaran(): HasMany
    {
        return $this->hasMany(FpdAnggaran::class, 'NIP_VALIDATOR_FPD', 'NIP_KARYAWAN');
    }

    /**
     * Karyawan ini sebagai validator pembayaran siswa (F101).
     * NIP_VALIDATOR_PEMBAYARAN di tr_pembayaran → NIP_KARYAWAN.
     */
    public function trPembayaran(): HasMany
    {
        return $this->hasMany(TrPembayaran::class, 'NIP_VALIDATOR_PEMBAYARAN', 'NIP_KARYAWAN');
    }

    // ── Scopes ──────────────────────────────────────────────────

    /**
     * Scope: hanya karyawan yang aktif (belum dihapus).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('IS_DELETE', 0);
    }

    /**
     * Scope: cari berdasarkan nama atau NIP.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('NAMA_KARYAWAN', 'like', "%{$keyword}%")
              ->orWhere('NIP_KARYAWAN', 'like', "%{$keyword}%");
        });
    }
}
