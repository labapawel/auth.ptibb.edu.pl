<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LdapUsersController extends Controller
{
    protected $title = 'Użytkownicy LDAP';
    protected $alias = 'ldap.users';

    public function onDisplay()
    {
        $users = Adldap::search()->get();

        $ldapUsers = $users->map(function ($user) {
            return new LdapUser($user->getAttributes());
        });

        $display = \AdminDisplay::datatables()->setColumns([
            \AdminColumn::text('uid', 'UID')->setSearchable(true)->setOrderable(true),
            \AdminColumn::text('cn', 'Nazwa')->setSearchable(true)->setOrderable(true),
            \AdminColumn::email('mail', 'Email')->setSearchable(true)->setOrderable(true),
            // Dodaj inne kolumny odpowiadające atrybutom LDAP
        ]);

        return $display;
    }

    /**
     * Nie będziemy implementować edycji ani tworzenia, bo to są użytkownicy LDAP.
     */
    public function onEdit($id)
    {
        // Opcjonalnie, możesz dodać widok z informacjami o konkretnym użytkowniku LDAP
    }

    public function onCreate()
    {
        // Nie będziemy implementować tworzenia
    }

    public function onDelete($id)
    {
        // Nie będziemy implementować usuwania
    }
}
