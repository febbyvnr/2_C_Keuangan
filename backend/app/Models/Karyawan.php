<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Karyawan extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'mst_karyawan';
    
    // Karena NIP pakai string, pastikan settingan ini benar
    protected $primaryKey = 'NIP_KARYAWAN';
    public $incrementing = false; 
    protected $keyType = 'string';
    
    public $timestamps = false; // Karena di tabelmu sepertinya tidak ada created_at/updated_at

    // NAH INI DIA POSISINYA
    // Sembunyikan password saat data dikirim ke frontend
    protected $hidden = [
        'PASSWORD_KARYAWAN', 
    ];

    // Relasi Many-to-Many ke ref_jabatan_str melalui tabel pivot tr_jabatan
    public function jabatans()
    {
        return $this->belongsToMany(
            RefJabatanStr::class, 
            'tr_jabatan',         
            'NIP_KARYAWAN',       
            'ID_JABATAN'      
        );
    }
}