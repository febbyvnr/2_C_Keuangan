<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefJabatanStr extends Model
{
    protected $table = 'ref_jabatan_str';
    
    // NAH INI YANG BIKIN ERROR, KITA UBAH JADI ID_JABATAN
    protected $primaryKey = 'ID_JABATAN'; 
    
    public $timestamps = false;

    protected $fillable = [
        'DESKRIPSI_JABATAN',
        'IS_VALID_JABATAN'
    ];
}