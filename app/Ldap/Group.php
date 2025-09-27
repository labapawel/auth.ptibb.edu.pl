<?php

namespace App\Ldap;

use LdapRecord\Models\OpenLDAP\Group as Model;

class Group extends Model
{
    /**
     * The object classes of the LDAP group model.
     */
    public static array $objectClasses = [
        'top',
        'posixGroup',
    ];
    
    protected $fillable = [
        'cn',
        'description',
        'gidnumber',
        'memberuid',
    ];

    public function getMembers(): array
    {
        return $this->getAttribute('memberuid') ?? [];
    }

    /**
     * Dodaj użytkownika do grupy.
     */
    public function addMember($user)
    {
        $uid = is_string($user) ? $user : ($user->getFirstAttribute('uid') ?? null);
        if ($uid) {
            $members = $this->getMembers();
            if (!in_array($uid, $members)) {
                $members[] = $uid;
                $this->setAttribute('memberuid', $members);
                $this->save();
            }
        }
    }

    /**
     * Usuń użytkownika z grupy.
     */
    public function removeMember($user)
    {
        $uid = is_string($user) ? $user : ($user->getFirstAttribute('uid') ?? null);
        if ($uid) {
            $members = $this->getMembers();
            $members = array_filter($members, fn($m) => $m !== $uid);
            $this->setAttribute('memberuid', array_values($members));
            $this->save();
        }
    }
}
