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
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalPerson',
        'user',
        'inetOrgPerson',
        'posixAccount',
        'shadowAccount',
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cn',
        'givenName',
        'sn',
        'mail',
        'samAccountName',
        'userPrincipalName',
        'displayName',
    ];

    // /**
    //  * The attributes that should be cast to native types.
    //  *
    //  * @var array
    //  */
    // protected $casts = [
    //     'uidNumber' => 'int',
    //     'gidNumber' => 'int',
    // ];

    // /**
    //  * The attributes that should be mutated to dates.
    //  *
    //  * @var array
    //  */
    // protected $dates = [
    //     'createTimestamp',
    //     'modifyTimestamp',
    // ];


        /**
     * The attributes that represents the user's cn.
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
     * The attributes that represents the user's sn.
     */
    public function getSn(): ?string
    {
        return $this->getFirstAttribute('sn');
    }

    /**
     * The attributes that represents the user's mail.
     */
    public function getMail(): ?string
    {
        return $this->getFirstAttribute('mail');
    }

    /**
     * The attributes that represents the user's samaccountname.
     */
    public function getSamAccountName(): ?string
    {
        return $this->getFirstAttribute('samaccountname');
    }
    
    /**
     * The attributes that represents the user's distinguished name.
     */
    public function getDistinguishedName(): ?string
    {
        return $this->getFirstAttribute('distinguishedname');
    }
    
    /**
     * Create a new LDAP user.
     *
     * @param array $attributes
     * @return static
     */
    public static function createUser(array $attributes)
    {
        $user = new static();
        
        foreach ($attributes as $key => $value) {
            $user->$key = $value;
        }
        
        // Domyślne wymagane atrybuty
        if (!isset($attributes['displayName'])) {
            $user->displayName = $attributes['givenName'] . ' ' . $attributes['sn'];
        }
        
        if (!isset($attributes['userPrincipalName']) && isset($attributes['samAccountName'])) {
            $baseDn = config('ldap.connections.default.base_dn');
            $domain = implode('.', array_map(function($part) {
                return substr($part, 3); // Usuwa "dc=" z każdej części
            }, explode(',', $baseDn)));
            
            $user->userPrincipalName = $attributes['samAccountName'] . '@' . $domain;
        }
        
        $user->save();
        
        return $user;
    }
}
