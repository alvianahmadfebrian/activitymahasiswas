<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DriveFile extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'drive_files';

    protected $fillable = [
        'user_id',
        'folder',
        'name',
        'original_name',
        'mime_type',
        'size',
        'path',
        'url',
        'is_folder',
    ];
}
