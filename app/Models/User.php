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
    const PERMISSION_VPN_CLIENT = 1;   // 0001
    const PERMISSION_TASK = 2;         // 0010
    const PERMISSION_PC = 4;           // 0100
    const PERMISSION_EMAIL = 8;        // 1000
    const PERMISSION_ADMIN = 32;       // 100000

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
        return (bool) ($this->permission & $permission);
    }

    public function isVPNclient()
    {
        return $this->hasPermission(self::PERMISSION_VPN_CLIENT);
    }

    public function isTaskPermission()
    {
        return $this->hasPermission(self::PERMISSION_TASK);
    }

    public function isPC()
    {
        return $this->hasPermission(self::PERMISSION_PC);
    }

    public function isEmailPermission()
    {
        return $this->hasPermission(self::PERMISSION_EMAIL);
    }

    public function isAdmin()
    {
        return $this->hasPermission(self::PERMISSION_ADMIN);
    }
    public function addPermission($permission)
    {
        $this->permission |= $permission;
        return $this;
    }

    public function removePermission($permission)
    {
        $this->permission &= ~$permission;
        return $this;
    }
    
    public function setPermissions(array $permissions): self
    {
        $this->permission = 0; // Resetuj istniejące uprawnienia
        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }
        return $this;
    }

}
