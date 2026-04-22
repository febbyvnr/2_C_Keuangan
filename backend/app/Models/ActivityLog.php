<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    protected $primaryKey = 'ID_ACTIVITY_LOG';
    public $timestamps = false; // Matikan timestamp bawaan

    protected $fillable = [
        'ID_ACCESS_LOG',
        'EVENT_TIME',
        'ACTOR_USERNAME',
        'ACTOR_ROLE',
        'ACTIVITY_NAME',
        'RELATED_DATA',
        'ACTIVITY_DESCRIPTION'
    ];

    public function actor()
    {
        // Asumsi kamu punya model MstKaryawan untuk tabel mst_karyawan
        return $this->belongsTo(MstKaryawan::class, 'ACTOR_USERNAME', 'NIP_KARYAWAN');
    }
}