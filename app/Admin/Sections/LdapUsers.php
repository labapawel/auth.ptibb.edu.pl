<?php
namespace App\Admin\Sections;

use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Section;
use App\Ldap\User as LdapUser;

class LdapUsers extends Section
{
    protected $title = 'Użytkownicy LDAP';
    protected $checkAccess = false;

    public function onDisplay()
    {
        $display = \AdminDisplay::datatablesAsync()
            ->setColumns([
                \AdminColumn::text('cn', 'CN'),
                \AdminColumn::text('givenname', 'Imię'),
                \AdminColumn::text('sn', 'Nazwisko'),
                \AdminColumn::text('mail', 'Email'),
                \AdminColumn::text('samaccountname', 'Login'),
            ])
            ->setDisplaySearch(true)
            ->paginate(20);

        // Pobierz użytkowników LDAP i przekształć do tablicy
        $users = LdapUser::all()->map(function ($user) {
            return [
                'cn' => $user->getCn(),
                'givenname' => $user->getGivenName(),
                'sn' => $user->getSn(),
                'mail' => $user->getMail(),
                'samaccountname' => $user->getSamAccountName(),
            ];
        });

        $display->setCollection(collect($users));

        return $display;
    }

    public function isDeletable($model)
    {
        return false;
    }
}