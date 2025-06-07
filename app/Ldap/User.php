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
     * This defines which attributes can be set via an array (e.g., User::create($data)).
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

    // Your get* methods are fine as is.
    // They provide explicit accessors but you can also use $user->cn directly.
    
    /**
     * The attributes that represents the user's common name.
     */
    public function getCn(): ?string
    {
        return $this->getFirstAttribute('cn');
    }

    /**
     * The attributes that represents the user's given name.
     */
    public function getGivenName(): ?string
    {
        return $this->getFirstAttribute('givenname');
    }

    /**
     * The attributes that represents the user's surname.
     */
    public function getSn(): ?string
    {
        return $this->getFirstAttribute('sn');
    }

    /**
     * The attributes that represents the user's mail address.
     */
    public function getMail(): ?string
    {
        return $this->getFirstAttribute('mail');
    }

    /**
     * The attributes that represents the user's uid (User ID).
     * This is the primary identifier for OpenLDAP users.
     */
    public function getUid(): ?string
    {
        return $this->getFirstAttribute('uid');
    }
    
    /**
     * The attributes that represents the user's distinguished name (DN).
     * This is the unique identifier for the entry in the LDAP directory.
     */
    public function getDistinguishedName(): ?string
    {
        return $this->getDn(); // LdapRecord's Model already has a getDn() method.
    }
    
    /**
     * Create a new LDAP user with specified attributes.
     *
     * @param array $attributes The attributes to set for the new user.
     * @return static The created LdapUser instance.
     */
    public static function createUser(array $attributes)
    {
        // Use the model's constructor for mass assignment if $fillable is used,
        // otherwise assign attributes one by one.
        $user = new static(); 
        
        // This loop works fine for direct assignment.
        foreach ($attributes as $key => $value) {
            $user->$key = $value;
        }
        
        // Default required attributes if not provided in the input.
        if (!isset($attributes['displayName'])) {
            $user->displayName = ($attributes['givenName'] ?? '') . ' ' . ($attributes['sn'] ?? '');
        }
        
        $user->save();
        
        return $user;
    }
    
    public function getUidNumber(): ?string
    {
        return $this->getFirstAttribute('uidNumber');
    }
    public function getGidNumber(): ?string
    {
        return $this->getFirstAttribute('gidNumber');
    }
    public function getHomeDirectory(): ?string
    {
        return $this->getFirstAttribute('homeDirectory');
    }
    public function getLoginShell(): ?string
    {
        return $this->getFirstAttribute('loginShell');
    }
}