<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use App\Ldap\Group;
use Illuminate\Support\Facades\Log;
use App\Jobs\CreateLdapUserJob as CreateLdapUser;
use Illuminate\Support\Str;
class LdapUsersController extends Controller
{


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

    public static function store(Request $request)
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
        CreateLdapUser::dispatch($request->all());

        return redirect()->route('ldap.users.index')->with('success', 'Zadanie tworzenia użytkownika zostało dodane do kolejki.');
        
    }


    public function createUser(Request $request){
        CreateLdapUser::dispatch($request->all());
        return redirect()->route('ldap.users.index')->with('success', 'Zadanie tworzenia użytkownika zostało dodane do kolejki.');
    }


    public function createByCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
        try {
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            $header = array_shift($data);
            foreach ($data as $row) {
                $row = array_combine($header, $row);
                // Rozdziel klucz i wartość, jeśli są tabulatorami oddzielone
                foreach ($row as $key => $value) {
                    // Jeśli klucz zawiera tabulatory, rozbij na nagłówki
                    if (strpos($key, "\t") !== false) {
                        $values = explode("\t", $value);
                        break;
                    }
                }
                list($imie, $nazwisko) = array_pad(explode(' ', $values[0] ?? ''), 2, '');
                $klasa = $values[1] ?? '';
                $email = $values[2] ?? '';                
                $values = [
                    'cn' => trim($imie . '' . $nazwisko),
                    'sn' => $nazwisko,
                    'givenname' => $imie,
                    'mail' => $email,
                    'uid' => $email,
                    'userpassword' => Str::random(50),
                    'groups' => ["RDP",$klasa]
                ];
                CreateLdapUser::dispatch($values);
                Log::info('Zadanie tworzenia użytkownika z CSV dodane do kolejki', ['user' => $row]);
            }


            return redirect()->route('ldap.users.index')->with('success', 'Użytkownicy zostali pomyślnie dodani.');
        } catch (\Exception $e) {
            Log::error("Błąd podczas dodawania użytkowników z pliku CSV: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with("error", "Wystąpił błąd podczas dodawania użytkowników z pliku CSV: " . $e->getMessage());
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
            $groups = cache()->remember('ldap_groups_for_edit', 300, function () {
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
            cache()->forget("user_groups_{$uid}");
            cache()->forget("user_groups_array_{$uid}");
        }
    }



    public function changePassword(Request $request, $uid)
    {
        $user = LdapUser::where('uid', '=', $uid)->first();
        if (!$user) {
            return redirect()->route('ldap.users.index')->with('error', 'Użytkownik nie został znaleziony.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user->setPassword($request->new_password);
            Log::info('Próba zmiany hasła użytkownika LDAP', ['user' => $uid]);
            $user->save();

            return redirect()->route('ldap.users.index')
                ->with('success', 'Hasło użytkownika LDAP zostało pomyślnie zmienione.');
        } catch (\Exception $e) {
            Log::error("Błąd podczas zmiany hasła użytkownika LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas zmiany hasła użytkownika LDAP: ' . $e->getMessage());
        }
    }

}