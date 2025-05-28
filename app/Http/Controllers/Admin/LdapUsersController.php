<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;

class LdapUsersController extends Controller
{
    public function index()
    {
        try {
            $users = LdapUser::all()->map(function ($user) {
                return [
                    'cn' => $user->getCn(),
                    'givenname' => $user->getGivenName(),
                    'sn' => $user->getSn(),
                    'mail' => $user->getMail(),
                    'samaccountname' => $user->getSamAccountName(),
                ];
            });
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Nie można połączyć się z serwerem LDAP: ' . $e->getMessage()], 500);
        }
    }
}
