<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstSiMenu extends Model
{
    protected $table = 'mst_si_menu';
    protected $primaryKey = 'ID_SI_ROLE_MENU';
    public $timestamps = false;

    protected $fillable = [
        'ID_SI_ROLE_MENU',
        'ID_SI',
        'NAMA_MENU',
        'DESKRIPSI_MENU',
        'IS_DELETE',
    ];

    protected $casts = [
        'IS_DELETE' => 'boolean',
    ];

    public function sistem(): BelongsTo
    {
        return $this->belongsTo(MstSi::class, 'ID_SI', 'ID_SI');
    }

    public function jabatanMenus(): HasMany
    {
        return $this->hasMany(JabatanMenu::class, 'ID_SI_ROLE_MENU', 'ID_SI_ROLE_MENU');
    }
}