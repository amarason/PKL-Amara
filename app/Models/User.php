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
        'login_id', 
        'role_id',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    public function getRememberTokenName()
    {
        return null;
    }

    /**
        * Relasi ke User (Admin yang melakukan koreksi)
    */
    public function admin()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}