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
}
