<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscussionMessage extends Model
{
    protected $table = 'discussion_messages';

    protected $fillable = [
        'room_id',
        'user_id',
        'user_name',
        'message',
    ];
}
