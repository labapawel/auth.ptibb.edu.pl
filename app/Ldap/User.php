<?php

namespace App\Ldap;

use LdapRecord\Models\Model;
use LdapRecord\Models\Concerns\CanAuthenticate;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Model implements Authenticatable
{
    use CanAuthenticate;

    /**
     * The object classes of the LDAP model.
     * These define the types of objects this model represents in LDAP,
     * which in turn dictates what attributes can be stored.
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalPerson',
        'inetOrgPerson',
        'posixAccount',
    ];

    /**
     * Attribute mappings for LDAP attributes to model properties.
     */
    protected array $attributes = [
        'uid' => 'objectguid',
    ];

    protected $fillable = [
            'objectClass',
            'entryUUID',
            'objectguid',
            'cn',
            'sn',
            'givenName',
            'uid',
            'uidNumber',
            'gidNumber',
            'homeDirectory',
            'loginShell',
            'mail',
            'userPassword',

        ];


    }