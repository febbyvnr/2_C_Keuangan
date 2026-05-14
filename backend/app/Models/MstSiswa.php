<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // Ganti ini
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Jika pakai Sanctum

class MstSiswa extends Authenticatable // Ganti ini
{
    use HasApiTokens, Notifiable;

    protected $table = 'mst_siswa';
    protected $primaryKey = 'ID_SISWA_TETAP'; // Pakai PK yang kamu punya
    public $timestamps = false;
    
    // PERHATIKAN: Jika di DB sudah auto-increment, set ke true. 
    // Di file SQL-mu sebelumnya, biasanya PK int itu auto-increment.
    public $incrementing = true; 
    protected $keyType = 'int';

    protected $fillable = [
        'NISN_SISWA',
        'PASSWORD',            // Tambahkan untuk login
        'NO_HP_SISWA',
        'PEKERJAAN_AYAH_SISWA',
        'PEKERJAAN_IBU_SISWA',
        'NAMA_WALI_SISWA',
    ];

    protected $hidden = [
        'PASSWORD', // Sembunyikan saat data di-return JSON
    ];

    // Beritahu Laravel kalau kolom password-nya bernama 'PASSWORD' (uppercase)
    public function getAuthPassword()
    {
        return $this->PASSWORD;
    }
}
