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

    public function getMembers(): array
    {
        return $this->getAttribute('member') ?? [];
    }

    /**
     * Dodaj użytkownika do jednostki organizacyjnej.
     */
    public function addMember($user)
    {
        $dn = is_string($user) ? $user : ($user->getDn() ?? $user['dn'] ?? null);
        if ($dn) {
            $members = $this->getMembers();
            if (!in_array($dn, $members)) {
                $members[] = $dn;
                $this->setAttribute('member', $members);
                $this->save();
            }
        }
    }

    /**
     * Usuń użytkownika z jednostki organizacyjnej.
     */
    public function removeMember($user)
    {
        $dn = is_string($user) ? $user : ($user->getDn() ?? $user['dn'] ?? null);
        if ($dn) {
            $members = $this->getMembers();
            $members = array_filter($members, fn($m) => $m !== $dn);
            $this->setAttribute('member', $members);
            $this->save();
        }
    }
}
