<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'nim',
        'email',
        'password',
        'prodi',
        'kelas',
        'photo_url',
        'photo_path',
        'bio',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
