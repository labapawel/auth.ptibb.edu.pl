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
                ->with("success", "Użytkownik " . $request->cn ." został pomyślnie utworzony.");
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

    public function edit($uid)
    {
        $user = LdapUser::where('uid', '=', $uid)->first();
        if (!$user) {
            return redirect()->route('ldap.users.index')->with('error', 'Użytkownik nie został znaleziony.');
        }

        $organizationalUnits = app(\App\Http\Controllers\Admin\LdapOuController::class)->getOrganizationalUnits()->getData();
        
        return view('admin.ldap-users-edit', compact('user', 'organizationalUnits'));
    }

    public function update(Request $request, $uid)
    {
        $user = LdapUser::where('uid', '=', $uid)->first();
        if (!$user) {
            return redirect()->route('ldap.users.index')->with('error', 'Użytkownik nie został znaleziony.');
        }

        $request->validate([
            'cn' => 'required|string',
            'sn' => 'required|string',
            'givenname' => 'required|string',
            'mail' => 'required|email',
            'organizational_units' => 'nullable|array',
        ]);

        try {
            $user->cn = $request->cn;
            $user->sn = $request->sn;
            $user->givenname = $request->givenname;
            $user->mail = $request->mail;
            $user->displayname = $request->givenname . ' ' . $request->sn;
            
            Log::info('Próba aktualizacji użytkownika LDAP', ['attributes' => $user->getAttributes()]);
            $user->save();

            // Handle organizational unit assignments
            if ($request->filled('organizational_units')) {
                foreach ($request->organizational_units as $ouName) {
                    $ou = \App\Ldap\OrganizationalUnit::where('ou', '=', $ouName)->first();
                    if ($ou) {
                        $ou->addMember($user);
                    }
                }
            }

            return redirect()->route('ldap.users.index')
                ->with('success', 'Użytkownik LDAP został pomyślnie zaktualizowany.');
        } catch (\Exception $e) {
            Log::error("Błąd podczas aktualizacji użytkownika LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas aktualizacji użytkownika LDAP: ' . $e->getMessage());
        }
    }
}