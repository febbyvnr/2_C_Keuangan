<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    protected $primaryKey = 'ID_ACTIVITY_LOG';
    public $timestamps = false;

    protected $fillable = [
        'ID_ACTIVITY_LOG',
        'ID_ACCESS_LOG',
        'EVENT_TIME',
        'ACTOR_USERNAME',
        'ACTOR_ROLE',
        'ACTIVITY_NAME',
        'RELATED_DATA',
        'ACTIVITY_DESCRIPTION',
    ];

    protected $casts = [
        'EVENT_TIME' => 'date',
    ];
}