<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefJabatanStr extends Model
{
    protected $table = 'ref_jabatan_str';
    protected $primaryKey = 'ID_JABATAN';
    public $timestamps = false;

    protected $fillable = [
        'ID_JABATAN',
        'DESKRIPSI_JABATAN',
        'IS_VALID_JABATAN',
    ];

    protected $casts = [
        'IS_VALID_JABATAN' => 'boolean',
    ];

    public function trJabatan(): HasMany
    {
        return $this->hasMany(TrJabatan::class, 'ID_JABATAN', 'ID_JABATAN');
    }

    public function jabatanMenus(): HasMany
    {
        return $this->hasMany(JabatanMenu::class, 'ID_JABATAN', 'ID_JABATAN');
    }
}