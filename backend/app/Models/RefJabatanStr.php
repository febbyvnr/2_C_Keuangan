<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefJabatanStr extends Model
{
    protected $table = 'ref_jabatan_str';
    protected $primaryKey = 'ID_JABATAN';
    
    // Matikan timestamps karena dari file SQL-mu sepertinya tidak pakai created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'DESKRIPSI_JABATAN',
        'IS_VALID_JABATAN',
    ];

    /**
     * Relasi ke transaksi jabatan (Siapa saja yang memegang jabatan ini)
     */
    public function trJabatans(): HasMany
    {
        return $this->hasMany(TrJabatan::class, 'ID_JABATAN', 'ID_JABATAN');
    }
}