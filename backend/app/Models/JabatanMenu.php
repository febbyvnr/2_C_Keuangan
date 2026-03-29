<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JabatanMenu extends Model
{
    protected $table = 'jabatan_menu';
    protected $primaryKey = 'ID_HAK_AKSES';
    public $timestamps = false;

    protected $fillable = [
        'ID_HAK_AKSES',
        'ID_SI_ROLE_MENU',
        'ID_JABATAN',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(RefJabatanStr::class, 'ID_JABATAN', 'ID_JABATAN');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MstSiMenu::class, 'ID_SI_ROLE_MENU', 'ID_SI_ROLE_MENU');
    }
}