<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstSiswa extends Model
{
    protected $table = 'mst_siswa';
    protected $primaryKey = 'ID_SISWA_TETAP';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'NO_HP_SISWA',
        'PEKERJAAN_AYAH_SISWA',
        'PEKERJAAN_IBU_SISWA',
        'NAMA_WALI_SISWA',
    ];
}