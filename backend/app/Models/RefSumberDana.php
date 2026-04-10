<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSumberDana extends Model
{
    protected $table = 'ref_sumber_dana';
    protected $primaryKey = 'ID_REF_DANA';
    public $timestamps = false;

    protected $fillable = [
        'REF_ID_REF_DANA',
<<<<<<< HEAD

=======
        // CATATAN: Tabel ref_sumber_dana di SQL hanya memiliki ID_REF_DANA dan REF_ID_REF_DANA.
        // Jika tim lain menambah kolom nama/deskripsi, tambahkan di sini.
>>>>>>> f9e142d751fcf7626c4242bcd958d4dff3d280e8
    ];

    public function parent()
    {
        return $this->belongsTo(RefSumberDana::class, 'REF_ID_REF_DANA', 'ID_REF_DANA');
    }

    public function children()
    {
        return $this->hasMany(RefSumberDana::class, 'REF_ID_REF_DANA', 'ID_REF_DANA');
    }

    public function transaksiPenerimaan()
    {
        return $this->hasMany(TrPenerimaan::class, 'ID_REF_DANA', 'ID_REF_DANA');
    }
}
