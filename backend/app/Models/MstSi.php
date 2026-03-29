<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstSi extends Model
{
    protected $table = 'mst_si';
    protected $primaryKey = 'ID_SI';
    public $timestamps = false;

    protected $fillable = [
        'ID_SI',
        'NAMA_SI',
        'DESKRIPSI_SI',
        'IS_DELETE',
    ];

    protected $casts = [
        'IS_DELETE' => 'boolean',
    ];

    public function menus(): HasMany
    {
        return $this->hasMany(MstSiMenu::class, 'ID_SI', 'ID_SI');
    }
}