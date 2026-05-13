<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstInventaris extends Model
{
    use HasFactory;

    protected $table = 'mst_inventaris';
    protected $primaryKey = 'ID_INVENTARIS';
    public $timestamps = false;

    protected $fillable = [
        'ID_KAT_BARANG',
        'KODE_INVENTARIS',
        'NAMA_INVENTARIS',
        'NILAI_INVENTARIS',
        'TGL_HABIS_GARANSI',
        'LINK_FOTO_BARANG',
        'MEREK_INV',
        'NO_SERI_INV',
        'DIMENSI_INV',
        'KETERANGAN_INV',
        'TGL_BELI_INV',
        'KONDISI_TERAKHIR_INV',
        'STATUS_INV',
        'IS_DELETE',
    ];

    protected $casts = [
        'ID_INVENTARIS' => 'integer',
        'ID_KAT_BARANG' => 'integer',
        'NILAI_INVENTARIS' => 'decimal:2',
        'TGL_HABIS_GARANSI' => 'date',
        'TGL_BELI_INV' => 'date',
        'STATUS_INV' => 'integer',
        'IS_DELETE' => 'integer',
    ];
}