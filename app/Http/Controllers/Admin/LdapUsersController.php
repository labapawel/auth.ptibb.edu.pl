<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use App\Ldap\Group;
use Illuminate\Support\Facades\Log;

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
        try {
            $groups = Group::all()->map(function ($group) {
                return [
                    "cn" => $group->getFirstAttribute("cn"),
                    "description" => $group->getFirstAttribute("description"),
                ];
            });
            return \AdminSection::view(view("admin.ldap-users-create", compact('groups'))->render());
        } catch (\Exception $e) {
            return \AdminSection::view(view("admin.ldap-users-create", ['groups' => []])->render());
        }
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
            "groups" => "nullable|array",
            "groups.*" => "string|distinct",
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

            // Przypisz użytkownika do wybranych grup
            if ($request->filled('groups')) {
                Log::info('Przypisywanie użytkownika do grup', [
                    'user' => $request->uid,
                    'groups' => $request->groups
                ]);
                
                foreach ($request->groups as $groupName) {
                    $group = Group::where('cn', '=', $groupName)->first();
                    if ($group) {
                        $group->addMember($user);
                        Log::info('Użytkownik przypisany do grupy', ['user' => $request->uid, 'group' => $groupName]);
                    } else {
                        Log::warning('Nie znaleziono grupy', ['group' => $groupName]);
                    }
                }
            } else {
                Log::info('Brak grup do przypisania');
            }

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

        $groups = app(\App\Http\Controllers\Admin\LdapGroupController::class)->getGroups()->getData();
        
        return \AdminSection::view(view('admin.ldap-users-edit', compact('user', 'groups'))->render());
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
            'groups' => 'nullable|array',
        ]);

        try {
            $user->cn = $request->cn;
            $user->sn = $request->sn;
            $user->givenname = $request->givenname;
            $user->mail = $request->mail;
            $user->displayname = $request->givenname . ' ' . $request->sn;
            
            Log::info('Próba aktualizacji użytkownika LDAP', ['attributes' => $user->getAttributes()]);
            $user->save();

            // Handle group assignments
            if ($request->filled('groups')) {
                Log::info('Aktualizacja przypisań użytkownika do grup', [
                    'user' => $uid,
                    'groups' => $request->groups
                ]);

                // First, remove user from all current groups
                $allGroups = Group::all();
                foreach ($allGroups as $existingGroup) {
                    $existingGroup->removeMember($user);
                }

                // Then add to selected groups
                foreach ($request->groups as $groupName) {
                    $group = Group::where('cn', '=', $groupName)->first();
                    if ($group) {
                        $group->addMember($user);
                        Log::info('Użytkownik przypisany do grupy podczas aktualizacji', ['user' => $uid, 'group' => $groupName]);
                    } else {
                        Log::warning('Nie znaleziono grupy podczas aktualizacji', ['group' => $groupName]);
                    }
                }
            } else {
                // If no groups selected, remove user from all
                Log::info('Usuwanie użytkownika ze wszystkich grup');
                $allGroups = Group::all();
                foreach ($allGroups as $existingGroup) {
                    $existingGroup->removeMember($user);
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