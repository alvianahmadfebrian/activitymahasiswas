<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $table = 'chat_sessions';

    protected $fillable = [
        'user_id',
        'title',
    ];
}
