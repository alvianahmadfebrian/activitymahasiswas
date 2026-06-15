<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriveFile extends Model
{
    protected $table = 'drive_files';

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
