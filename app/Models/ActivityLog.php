<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
