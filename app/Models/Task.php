<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'deadline',
        'status',
        'file_url',
        'file_path',
        'file_name',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];
}
