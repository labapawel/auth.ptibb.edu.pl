<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasApiTokens, Notifiable, AuthenticatesWithLdap;

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
        return ($this->attributes["permission"] & $permission) == $permission;
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
    public function allPermissions()
    {
        $permissions = [];
        if ($this->isVPNclient()) {
            $permissions[] = self::PERMISSION_VPN_CLIENT;
        }
        if ($this->isTaskPermission()) {
            $permissions[] = self::PERMISSION_TASK;
        }
        if ($this->isPC()) {
            $permissions[] = self::PERMISSION_PC;
        }
        if ($this->isEmailPermission()) {
            $permissions[] = self::PERMISSION_EMAIL;
        }
        if ($this->isAdmin()) {
            $permissions[] = self::PERMISSION_ADMIN;
        }
        return $permissions;
    }
    public function setPermissionAttribute($permissions)
    {
        $this->attributes['permission'] = 0;
        foreach ($permissions as $permission) {
            $this->attributes['permission'] |= $permission;
        }
    }
    public function getPermissionAttribute()
    {
        $permissions = [];
        $i=1;
        while($i<255)
        {
            if(($this->attributes['permission'] & $i) == $i)
            {
                $permissions[] = $i;
            }
            $i<<= 1;
        }
        return $permissions;
    }
}
