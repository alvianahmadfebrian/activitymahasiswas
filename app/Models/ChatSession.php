<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ChatSession extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'chat_sessions';

    protected $fillable = [
        'user_id',
        'title',
    ];
}
