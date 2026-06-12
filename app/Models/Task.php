<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'tasks';

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
}
