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
     * The attributes that are mass assignable.
     * This 
s which attributes can be set via an array (e.g., User::create($data)).
     * Note: 'userPrincipalName' and 'samAccountName' are typically for Active Directory.
     * If using OpenLDAP without custom schema, consider removing them from here.
     */
    protected $fillable = [
            'objectClass',
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