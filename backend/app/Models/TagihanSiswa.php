<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanSiswa extends Model
{
    protected $table = 'tagihan_siswa';
    protected $primaryKey = 'ID_TAGIHAN_SISWA';
    public $timestamps = false;

    protected $fillable = [
        'ID_SISWA_TETAP',
        'ID_JENIS_PEMBAYARAN',
        'BULAN_TAGIHAN_SISWA',
        'TAHUN_TAGIHAN_SISWA',
        'JUMLAH_TAGIHAN_SISWA',
        'STATUS_TAGIHAN_SISWA',
        'DUEDATE_TAGIHAN_SISWA',
    ];

    protected $casts = [
        'ID_TAGIHAN_SISWA' => 'integer',
        'ID_SISWA_TETAP' => 'integer',
        'ID_JENIS_PEMBAYARAN' => 'integer',
        'JUMLAH_TAGIHAN_SISWA' => 'double',
        'DUEDATE_TAGIHAN_SISWA' => 'date',
    ];

    /**
     * Relasi ke mst_siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(MstSiswa::class, 'ID_SISWA_TETAP', 'ID_SISWA_TETAP');
    }

    /**
     * Relasi ke ref_jenis_pembayaran
     */
    public function jenisPembayaran(): BelongsTo
    {
        return $this->belongsTo(RefJenisPembayaran::class, 'ID_JENIS_PEMBAYARAN', 'ID_JENIS_PEMBAYARAN');
    }

    /**
     * Relasi ke tr_pembayaran
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(TrPembayaran::class, 'ID_TAGIHAN_SISWA', 'ID_TAGIHAN_SISWA');
    }
}