<?php

namespace App\Ldap;

use LdapRecord\Models\OpenLDAP\OrganizationalUnit as Model;

class OrganizationalUnit extends Model
{
    /**
     * The object classes of the LDAP organizational unit model.
     */
    public static array $objectClasses = [
        'top',
        'organizationalUnit',
    ];
    protected $fillable = [
        'ou',
        'description',
    ];
}
