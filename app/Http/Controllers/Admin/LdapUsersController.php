<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Log;
use App\Ldap\Group as Group;

class LdapUsersController extends Controller
{

    /**
     * Pobierz kolejny wolny numer uidNumber lub gidNumber z LDAP
     */
    private function getNextNumber(string $attribute): int
    {
        $users = LdapUser::all();
        $numbers = $users->map(function ($user) use ($attribute) {
            return (int) $user->getFirstAttribute($attribute) ?: 0;
        })->filter()->toArray();
        $start = 1000;
        $next = $numbers ? (max($numbers) + 1) : $start;
        // Walidacja unikalności
        while (in_array($next, $numbers)) {
            $next++;
        }
        return $next;
    }

      public function getUsers()
    {
        try {
            $users = LdapUser::all()->map(function ($user) {
                return [
                    "cn" => $user->getFirstAttribute("cn"),
                    "givenname" => $user->getFirstAttribute("givenname"),
                    "sn" => $user->getFirstAttribute("sn"),
                    "mail" => $user->getFirstAttribute("mail"),
                    "uid" => $user->getFirstAttribute("uid"),
                ];
            });
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(["error" => "Nie można połączyć się z serwerem LDAP: " . $e->getMessage()], 500);
        }
    }
    public function create()
    {
        return view("admin.ldap-users-create");
    }

    public function store(Request $request)
    {
        // Log wejścia do metody store
        Log::info('Wejście do metody store()', ['request' => $request->all()]);
        $request->validate([
            "cn" => "required|string",
            "sn" => "required|string",
            "givenname" => "required|string",
            "uid" => "required|string",
            "mail" => "required|email",
            "userpassword" => "required|string|min:8",
        ]);

        try {
            $user = LdapUser::create([
                'cn'           => $request->cn,
                'sn'           => $request->sn,
                'givenname'    => $request->givenname,
                'mail'         => $request->mail,
                'displayname'  => $request->givenname . ' ' . $request->sn,
                'uidNumber' => $this->getNextNumber('uidNumber'),
                'gidNumber' => $this->getNextNumber('gidNumber'),
                'uid'          => $request->uid,
                'userpassword' => $request->userpassword,
                'homedirectory'=> '/home/uczniowie/' . $request->uid,
                'loginshell'   => '/bin/bash',
            ]);

            Log::info('Próba zapisu użytkownika do LDAP', ['attributes' => $user->getAttributes()]);
            $user->save();

            return redirect("admin/ldap/users")
                ->with("success", "Użytkownik LDAP został pomyślnie utworzony.");
        } catch (\Exception $e) {
            Log::error("Błąd podczas tworzenia użytkownika LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with("error", "Wystąpił błąd podczas tworzenia użytkownika LDAP: " . $e->getMessage());
        }
    }

    public function delete($cn)
    {
        try {
            if (strpos($cn, '%20') !== false) {
                $cn = str_replace('%20', ' ', $cn);
            }
            $user = LdapUser::destroy('cn='.$cn.",dc=ptibb,dc=edu,dc=pl");

            
        } catch (\Exception $e) {
            Log::error("Błąd podczas pobierania użytkownika LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with("error", "Wystąpił błąd podczas tworzenia użytkownika LDAP: " . $e->getMessage());
        }
    }
}