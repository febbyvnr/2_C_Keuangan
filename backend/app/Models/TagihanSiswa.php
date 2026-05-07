<?php

namespace App\Models;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use App\Models\RefJenisTagihan;

class TagihanSiswa extends Model
{
    use RecordsActivity;

    protected $table = 'tagihan_siswa';
    protected $primaryKey = 'ID_TAGIHAN_SISWA';
    public $timestamps = false;

    protected $fillable = [
        'ID_SISWA_TETAP',
        'ID_JENIS_TAGIHAN',
        'BULAN_TAGIHAN_SISWA',
        'TAHUN_TAGIHAN_SISWA',
        'JUMLAH_TAGIHAN_SISWA',
        'STATUS_TAGIHAN_SISWA',
        'DUEDATETIME_TAGIHAN_SISWA',
    ];

    protected $casts = [
        'ID_TAGIHAN_SISWA' => 'integer',
        'ID_SISWA_TETAP' => 'integer',
        'ID_JENIS_TAGIHAN' => 'integer',
        'JUMLAH_TAGIHAN_SISWA' => 'double',
        'DUEDATETIME_TAGIHAN_SISWA' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(
            MstSiswa::class,
            'ID_SISWA_TETAP',
            'ID_SISWA_TETAP'
        );
    }

    public function jenisTagihan(): BelongsTo
    {
        return $this->belongsTo(
            RefJenisTagihan::class,
            'ID_JENIS_TAGIHAN',
            'ID_JENIS_TAGIHAN'
        );
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(
            TrPembayaran::class,
            'ID_TAGIHAN_SISWA',
            'ID_TAGIHAN_SISWA'
        );
    }
}
