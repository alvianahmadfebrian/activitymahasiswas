<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DiscussionMessage extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'discussion_messages';

    protected $fillable = [
        'room_id',
        'user_id',
        'user_name',
        'message',
    ];
}
