<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DiscussionRoom extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'discussion_rooms';

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
