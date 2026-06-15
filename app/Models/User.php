<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

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

    protected $casts = [
        'last_active_at' => 'datetime',
        'password' => 'hashed',
    ];
}
