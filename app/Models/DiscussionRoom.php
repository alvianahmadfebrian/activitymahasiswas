<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscussionRoom extends Model
{
    protected $table = 'discussion_rooms';

    protected $fillable = [
        'user_id',
        'title',
        'course',
        'description',
        'type',
        'private_key',
        'member_ids',
    ];

    protected $casts = [
        'member_ids' => 'array',
    ];
}
