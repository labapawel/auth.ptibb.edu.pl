<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'permission',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user has a specific permission
     * 
     * @param int $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return ($this->permissions & $permission) === $permission;
    }

    public function isVPNclient()
    {
        return $this->hasPermission(1);//   true | false
    }

    public function isTaskPermission()
    {
        return $this->hasPermission(2);//   true | false
    }
    
    public function isPC()
    {
        return $this->hasPermission(3);//   true | false
    }

    public function isEmailPermission()
    {
        return $this->hasPermission(4);//   true | false
    }

    public function isAdmin()
    {
        return $this->hasPermission(32);//   true | false
    }
}
