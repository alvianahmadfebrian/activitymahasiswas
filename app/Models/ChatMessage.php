<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

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
