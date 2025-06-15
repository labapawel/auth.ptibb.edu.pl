<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use App\Ldap\Group;
use Illuminate\Support\Facades\Log;

class LdapGroupController extends Controller
{
    /**
     * Pobierz kolejny wolny numer gidNumber z LDAP
     */
    private function getNextGidNumber(): int
    {
        try {
            $groups = Group::all();
            $numbers = $groups->map(function ($group) {
                $value = $group->getFirstAttribute('gidnumber');
                return (int) $value ?: 0;
            })->filter()->toArray();
            
            $start = 1000;
            $next = $numbers ? (max($numbers) + 1) : $start;
            
            // Walidacja unikalności
            while (in_array($next, $numbers)) {
                $next++;
            }
            
            Log::info("Generated next gidnumber: $next", ['existing_numbers' => $numbers]);
            return $next;
        } catch (\Exception $e) {
            Log::error("Error generating next gidnumber: " . $e->getMessage());
            return rand(1000, 9999);
        }
    }

    public function getGroups()
    {
        try {
            $groups = Group::all()->map(function ($group) {
                return [
                    "cn" => $group->getFirstAttribute("cn"),
                    "description" => $group->getFirstAttribute("description"),
                    "gidnumber" => $group->getFirstAttribute("gidnumber"),
                    "members" => $group->getAttribute("memberuid") ?: [],
                    "memberCount" => count($group->getAttribute("memberuid") ?: []),
                ];
            });
            return response()->json($groups);
        } catch (\Exception $e) {
            return response()->json(["error" => "Nie można połączyć się z serwerem LDAP: " . $e->getMessage()], 500);
        }
    }    public function show($cn)
    {
        $group = Group::where('cn', '=', $cn)->first();
        if (!$group) {
            return redirect()->route('ldap.groups.index')->with('error', 'Grupa nie została znaleziona.');
        }

        $users = $group->getMembers();
        $groupAttributes = $group->getAttributes();
        
        return \AdminSection::view(view('admin.ldap-group-show', [
            'group' => $groupAttributes,
            'users' => $users
        ])->render());
    }

    public function create()
    {
        try {
            $users = LdapUser::all();
            return view('admin.ldap-group-create', compact('users'));
        } catch (\Exception $e) {
            return back()->with('error', 'Nie można pobrać listy użytkowników: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        Log::info('Wejście do metody store()', ['request' => $request->all()]);
        $request->validate([
            'group_name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'users' => 'nullable|array',
            'users.*' => 'string|max:255',
        ]);

        // Sprawdzenie czy grupa już istnieje
        if (Group::where('cn', $request->group_name)->exists()) {
            return redirect()->back()->withInput()->with('error', 'Grupa o tej nazwie już istnieje.');
        }
     
        try {
            $group = Group::create([
                'cn' => $request->group_name,
                'description' => $request->description,
                'gidnumber' => $this->getNextGidNumber(),
            ]);
            
            Log::info('Próba zapisu grupy do LDAP', ['attributes' => $group->getAttributes()]);
            $group->save();

            // Handle user assignments if provided
            if ($request->filled('users')) {
                foreach ($request->users as $uid) {
                    $user = LdapUser::where('uid', '=', $uid)->first();
                    if ($user) {
                        $group->addMember($user);
                        Log::info('Dodano użytkownika do grupy', ['user' => $uid, 'group' => $request->group_name]);
                    }
                }
            }

            return redirect()->route('ldap.groups.index')
                ->with('success', 'Grupa LDAP została pomyślnie utworzona.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas tworzenia grupy LDAP: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas tworzenia grupy LDAP: ' . $e->getMessage());
        }
    }

    public function edit($cn)
    {
        $group = Group::where('cn', '=', $cn)->first();
        if (!$group) {
            return redirect()->route('ldap.groups.index')->with('error', 'Grupa nie została znaleziona.');
        }        $users = LdapUser::all()->map(function ($user) {
            return [
                'uid' => $user->getFirstAttribute('uid'),
                'cn' => $user->getFirstAttribute('cn'),
                'mail' => $user->getFirstAttribute('mail'),
            ];
        });
        
        $groupAttributes = $group->getAttributes();
        
        return \AdminSection::view(view('admin.ldap-group-edit', [
            'group' => $groupAttributes,
            'users' => $users
        ])->render());
    }

    public function update(Request $request, $cn)
    {
        $group = Group::where('cn', '=', $cn)->first();
        if (!$group) {
            return redirect()->route('ldap.groups.index')->with('error', 'Grupa nie została znaleziona.');
        }

        $request->validate([
            'description' => 'nullable|string|max:255',
            'users' => 'nullable|array',
            'users.*' => 'string',
        ]);

        try {
            $group->description = $request->description ?? '';
            
            Log::info('Próba aktualizacji grupy LDAP', ['attributes' => $group->getAttributes()]);
            $group->save();

            // Handle user assignments
            if ($request->filled('users')) {
                Log::info('Aktualizacja przypisań użytkowników do grupy', [
                    'group' => $cn,
                    'users' => $request->users
                ]);

                // Clear existing members
                $group->setAttribute('memberuid', []);
                
                // Add new members
                foreach ($request->users as $uid) {
                    $user = LdapUser::where('uid', '=', $uid)->first();
                    if ($user) {
                        $group->addMember($user);
                        Log::info('Użytkownik przypisany do grupy podczas aktualizacji', ['user' => $uid, 'group' => $cn]);
                    } else {
                        Log::warning('Nie znaleziono użytkownika podczas aktualizacji grupy', ['user' => $uid]);
                    }
                }
            } else {
                // If no users selected, clear all members
                Log::info('Usuwanie wszystkich użytkowników z grupy');
                $group->setAttribute('memberuid', []);
                $group->save();
            }

            return redirect()->route('ldap.groups.index')
                ->with('success', 'Grupa LDAP została pomyślnie zaktualizowana.');
        } catch (\Exception $e) {
            Log::error("Błąd podczas aktualizacji grupy LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas aktualizacji grupy LDAP: ' . $e->getMessage());
        }
    }

    public function destroy($cn)
    {
        try {
            $group = Group::where('cn', '=', $cn)->first();
            if ($group) {
                $group->delete();
                Log::info('Grupa LDAP została usunięta', ['group' => $cn]);
                return redirect()->route('ldap.groups.index')->with('success', 'Grupa została pomyślnie usunięta.');
            } else {
                return redirect()->route('ldap.groups.index')->with('error', 'Grupa nie została znaleziona.');
            }
        } catch (\Exception $e) {
            Log::error('Błąd podczas usuwania grupy LDAP: ' . $e->getMessage());
            return redirect()->route('ldap.groups.index')->with('error', 'Wystąpił błąd podczas usuwania grupy.');
        }
    }
}
