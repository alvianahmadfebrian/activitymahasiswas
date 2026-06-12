<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ChatMessage extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'chat_messages';

    protected $fillable = [
        'user_id',
        'chat_session_id',
        'role',
        'message',
        'file_url',
        'file_path',
        'file_type',
        'file_name',
    ];
}
