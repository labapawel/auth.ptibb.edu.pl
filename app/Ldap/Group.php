<?php

namespace App\Ldap;

use LdapRecord\Models\Model;


class Group extends Model 
{
    /**
     * The object classes of the LDAP group model.
     */
    public static array $objectClasses = [
        'top',
        'groupOfNames',
    ];

    /**
     * Pobierz CN grupy.
     */
    public function getCn(): ?string
    {
        return $this->getFirstAttribute('cn');
    }

    /**
     * Pobierz opis grupy.
     */
    public function getDescription(): ?string
    {
        return $this->getFirstAttribute('description');
    }

    /**
     * Pobierz członków grupy (tablica DN).
     */
    public function getMembers(): array
    {
        return $this->getAttribute('member') ?? [];
    }

    /**
     * Dodaj użytkownika do grupy.
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
     * Usuń użytkownika z grupy.
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
