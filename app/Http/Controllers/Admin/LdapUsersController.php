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
     * Pobierz kolejny wolny numer uidNumber lub gidNumber z LDAP (zoptymalizowana wersja)
     */
    private function getNextNumber(string $attribute): int
    {
        // Cache numbers for better performance
        static $cachedNumbers = [];
        
        if (!isset($cachedNumbers[$attribute])) {
            $users = LdapUser::select(['uidnumber', 'gidnumber'])->get(); // Pobierz tylko potrzebne atrybuty
            $numbers = $users->map(function ($user) use ($attribute) {
                return (int) $user->getFirstAttribute($attribute) ?: 0;
            })->filter()->toArray();
            
            $cachedNumbers[$attribute] = $numbers;
        }
        
        $numbers = $cachedNumbers[$attribute];
        $start = 1000;
        $next = $numbers ? (max($numbers) + 1) : $start;
        
        // Walidacja unikalności
        while (in_array($next, $numbers)) {
            $next++;
        }
        
        // Update cache
        $cachedNumbers[$attribute][] = $next;
        
        return $next;
    }

    /**
     * Pobierz użytkowników LDAP (zoptymalizowana wersja)
     */
    public function getUsers()
    {
        try {
            // Pobierz tylko potrzebne atrybuty dla lepszej wydajności
            $users = LdapUser::select(['cn', 'givenname', 'sn', 'mail', 'uid'])->get();
            
            $usersData = $users->map(function ($user) {
                // Pobierz wszystkie atrybuty jednocześnie, zamiast osobno
                $attributes = $user->getAttributes();
                return [
                    "cn" => $attributes['cn'][0] ?? '',
                    "givenname" => $attributes['givenname'][0] ?? '',
                    "sn" => $attributes['sn'][0] ?? '',
                    "mail" => $attributes['mail'][0] ?? '',
                    "uid" => $attributes['uid'][0] ?? '',
                ];
            });
            
            return response()->json($usersData);
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania użytkowników LDAP: ' . $e->getMessage());
            return response()->json(["error" => "Nie można połączyć się z serwerem LDAP: " . $e->getMessage()], 500);
        }
    }

    /**
     * Pobierz użytkowników dla widoku (bez JSON response)
     */
    public function getUsersForView()
    {
        try {
            // Cache na 5 minut dla widoku
            return cache()->remember('ldap_users_list', 300, function () {
                $users = LdapUser::select(['cn', 'givenname', 'sn', 'mail', 'uid'])->get();
                
                return $users->map(function ($user) {
                    $attributes = $user->getAttributes();
                    return (object) [
                        "cn" => $attributes['cn'][0] ?? '',
                        "givenname" => $attributes['givenname'][0] ?? '',
                        "sn" => $attributes['sn'][0] ?? '',
                        "mail" => $attributes['mail'][0] ?? '',
                        "uid" => $attributes['uid'][0] ?? '',
                    ];
                });
            });
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania użytkowników LDAP dla widoku: ' . $e->getMessage());
            return collect(); // Zwróć pustą kolekcję w przypadku błędu
        }
    }
    public function create()
    {
        try {
            // Cache grup na 10 minut
            $groups = cache()->remember('ldap_groups_for_create', 600, function () {
                return Group::select(['cn', 'description'])->get()->map(function ($group) {
                    $attributes = $group->getAttributes();
                    return [
                        "cn" => $attributes['cn'][0] ?? '',
                        "description" => $attributes['description'][0] ?? '',
                    ];
                });
            });
            
            return \AdminSection::view(view("admin.ldap-users-create", compact('groups'))->render());
        } catch (\Exception $e) {
            Log::error('Błąd podczas ładowania formularza tworzenia użytkownika: ' . $e->getMessage());
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
        } finally {
            // Wyczyść cache po dodaniu użytkownika
            $this->clearLdapCache();
        }
    }

    public function edit($uid)
    {
        try {
            $user = LdapUser::where('uid', '=', $uid)->first();
            if (!$user) {
                return redirect()->route('ldap.users.index')->with('error', 'Użytkownik nie został znaleziony.');
            }

            // Cache grup dla edycji na 10 minut
            $groups = cache()->remember('ldap_groups_for_edit', 600, function () {
                return app(\App\Http\Controllers\Admin\LdapGroupController::class)->getGroups()->getData();
            });
            
            return \AdminSection::view(view('admin.ldap-users-edit', compact('user', 'groups'))->render());
        } catch (\Exception $e) {
            Log::error('Błąd podczas ładowania formularza edycji użytkownika: ' . $e->getMessage());
            return redirect()->route('ldap.users.index')->with('error', 'Wystąpił błąd podczas ładowania danych użytkownika.');
        }
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

            // Handle group assignments (zoptymalizowane)
            if ($request->filled('groups')) {
                Log::info('Aktualizacja przypisań użytkownika do grup', [
                    'user' => $uid,
                    'groups' => $request->groups
                ]);

                // Cache all groups to avoid multiple LDAP calls
                $allGroups = cache()->remember('all_ldap_groups', 300, function () {
                    return Group::all();
                });

                // First, remove user from all current groups
                foreach ($allGroups as $existingGroup) {
                    $existingGroup->removeMember($user);
                }

                // Then add to selected groups
                foreach ($request->groups as $groupName) {
                    $group = $allGroups->firstWhere('cn', $groupName);
                    if ($group) {
                        $group->addMember($user);
                        Log::info('Użytkownik przypisany do grupy podczas aktualizacji', ['user' => $uid, 'group' => $groupName]);
                    } else {
                        Log::warning('Nie znaleziono grupy podczas aktualizacji', ['group' => $groupName]);
                    }
                }
            } else {
                // If no groups selected, remove user from all (zoptymalizowane)
                Log::info('Usuwanie użytkownika ze wszystkich grup');
                $allGroups = cache()->remember('all_ldap_groups', 300, function () {
                    return Group::all();
                });
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
        } finally {
            // Wyczyść cache po aktualizacji użytkownika
            $this->clearLdapCache();
            cache()->forget("user_groups_{$uid}");
            cache()->forget("user_groups_array_{$uid}");
        }
    }

    /**
     * Wyczyść cache LDAP po zmianach
     */
    private function clearLdapCache()
    {
        cache()->forget('ldap_users_list');
        cache()->forget('ldap_groups_for_create');
        cache()->forget('ldap_groups_for_edit');
        cache()->forget('all_ldap_groups');
        cache()->forget('dashboard_ldap_users_count');
        cache()->forget('dashboard_ldap_groups_count');
    }

    /**
     * Endpoint do ręcznego czyszczenia cache
     */
    public function clearCache()
    {
        $this->clearLdapCache();
        
        // Wyczyść wszystkie cache użytkowników
        $cacheKeys = [
            'ldap_users_list',
            'ldap_groups_for_create', 
            'ldap_groups_for_edit',
            'all_ldap_groups',
            'dashboard_ldap_users_count',
            'dashboard_ldap_groups_count',
            'ldap_groups_list',
            'ldap_users_for_group_create'
        ];
        
        foreach ($cacheKeys as $key) {
            cache()->forget($key);
        }
        
        // Wyczyść cache grup użytkowników (wszystkie klucze z prefiksem)
        $allCacheKeys = cache()->getRedis()->keys('*user_groups*');
        foreach ($allCacheKeys as $key) {
            cache()->forget($key);
        }
        
        return redirect()->back()->with('success', 'Cache LDAP został wyczyszczony.');
    }

    /**
     * Pobierz wszystkie grupy, do których należy użytkownik (zoptymalizowane)
     */
    public function getUserGroups($uid)
    {
        try {
            $user = LdapUser::where('uid', '=', $uid)->first();
            if (!$user) {
                return response()->json(['error' => 'Użytkownik nie został znaleziony'], 404);
            }

            // Cache grup na 5 minut
            $userGroups = cache()->remember("user_groups_{$uid}", 300, function () use ($uid) {
                $allGroups = Group::all();
                $userGroups = [];

                foreach ($allGroups as $group) {
                    $members = $group->getAttribute('memberuid') ?: [];
                    if (in_array($uid, $members)) {
                        $attributes = $group->getAttributes();
                        $userGroups[] = [
                            'cn' => $attributes['cn'][0] ?? '',
                            'description' => $attributes['description'][0] ?? '',
                            'gidnumber' => $attributes['gidnumber'][0] ?? '',
                        ];
                    }
                }

                return $userGroups;
            });

            Log::info('Pobrano grupy użytkownika', ['user' => $uid, 'groups_count' => count($userGroups)]);
            
            return response()->json([
                'user' => $uid,
                'groups' => $userGroups,
                'groups_count' => count($userGroups)
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania grup użytkownika: ' . $e->getMessage(), ['user' => $uid]);
            return response()->json(['error' => 'Wystąpił błąd podczas pobierania grup użytkownika: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Pobierz tablicę grup użytkownika (bez response JSON, zoptymalizowane)
     */
    public function getUserGroupsArray($uid)
    {
        try {
            return cache()->remember("user_groups_array_{$uid}", 300, function () use ($uid) {
                $user = LdapUser::where('uid', '=', $uid)->first();
                if (!$user) {
                    return [];
                }

                $userGroups = [];
                $allGroups = Group::all();

                foreach ($allGroups as $group) {
                    $members = $group->getAttribute('memberuid') ?: [];
                    if (in_array($uid, $members)) {
                        $attributes = $group->getAttributes();
                        $userGroups[] = [
                            'cn' => $attributes['cn'][0] ?? '',
                            'description' => $attributes['description'][0] ?? '',
                            'gidnumber' => $attributes['gidnumber'][0] ?? '',
                        ];
                    }
                }

                return $userGroups;
            });
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania grup użytkownika (array): ' . $e->getMessage(), ['user' => $uid]);
            return [];
        }
    }
}
