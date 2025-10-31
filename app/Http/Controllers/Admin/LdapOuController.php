<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LdapRecord\Models\OpenLDAP\OrganizationalUnit as Group;
use App\Ldap\User as LdapUser;
use App\Ldap\OrganizationalUnit;
use Illuminate\Support\Facades\Log;

class LdapOuController extends Controller
{
    public function getOrganizationalUnits()
    {
        try {
            $organizationalUnits = OrganizationalUnit::all()->map(function ($unit) {
                return [
                    "ou" => $unit->getFirstAttribute("ou"),
                    "description" => $unit->getFirstAttribute("description"),
                    "members" => $unit->getAttribute("member") ?: [],
                    "memberCount" => count($unit->getAttribute("member") ?: []),
                ];
            });
            return response()->json($organizationalUnits);
        } catch (\Exception $e) {
            return response()->json(["error" => "Nie można połączyć się z serwerem LDAP: " . $e->getMessage()], 500);
        }
    }

    public function show($ou)
    {
        $organizationalUnit = OrganizationalUnit::where('ou', '=', $ou)->first();
        if (!$organizationalUnit) {
            return redirect()->route('ldap.organizational-units.index')->with('error', 'Jednostka organizacyjna nie została znaleziona.');
        }

        $users = $organizationalUnit->getMembers();
        
        return view('admin.ldap-ou-show', compact('organizationalUnit', 'users'));
    }
   public function create()
    {
        return view('admin.ldap-ou-create');
    }
    public function store(Request $request)
    {
        Log::info('Wejście do metody store()', ['request' => $request->all()]);
        $request->validate([
            'organizational_unit' => 'required|string|min:3',
            'description' => 'nullable|string',
            'users' => 'nullable|array',
            'users.*' => 'string|max:255',
        ]);

        // Sprawdzenie czy jednostka organizacyjna już istnieje
        if (OrganizationalUnit::where('ou', $request->organizational_unit)->exists()) {
            return redirect()->back()->withInput()->with('error', 'Jednostka organizacyjna o tej nazwie już istnieje.');
        }
     
        try {
             $organizationalUnit = OrganizationalUnit::create([
                'ou' => $request->organizational_unit,
                'description' => $request->description,
            ]);
            // Log the attempt to save the organizational unit
            Log::info('Próba zapisu jednostki organizacyjnej do LDAP', ['attributes' => $organizationalUnit->getAttributes()]);
            $organizationalUnit->save();

            return redirect()->route('ldap.organizational-units.index')
                ->with('success', 'Jednostka organizacyjna LDAP została pomyślnie utworzona.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas tworzenia jednostki organizacyjnej LDAP: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas tworzenia jednostki organizacyjnej LDAP: ' . $e->getMessage());
        }
    }
    public function edit($ou)
    {
        $organizationalUnit = OrganizationalUnit::where('ou', '=', $ou)->first();
        if (!$organizationalUnit) {
            return redirect()->route('ldap.organizational-units.index')->with('error', 'Jednostka organizacyjna nie została znaleziona.');
        }

        $users = LdapUser::all()->map(function ($user) {
            return [
                'uid' => $user->getFirstAttribute('uid'),
                'cn' => $user->getFirstAttribute('cn'),
                'mail' => $user->getFirstAttribute('mail'),
            ];
        });
        
        return view('admin.ldap-ou-edit', compact('organizationalUnit', 'users'));
    }

    public function update(Request $request, $ou)
    {
        $organizationalUnit = OrganizationalUnit::where('ou', '=', $ou)->first();
        if (!$organizationalUnit) {
            return redirect()->route('ldap.organizational-units.index')->with('error', 'Jednostka organizacyjna nie została znaleziona.');
        }

        $request->validate([
            'description' => 'nullable|string|max:255',
            'users' => 'nullable|array',
            'users.*' => 'string',
        ]);

        try {
            $organizationalUnit->description = $request->description ?? '';
            
            Log::info('Próba aktualizacji jednostki organizacyjnej LDAP', ['attributes' => $organizationalUnit->getAttributes()]);
            $organizationalUnit->save();

            // Handle user assignments
            if ($request->filled('users')) {
                // Clear existing members
                $organizationalUnit->setAttribute('member', []);
                
                // Add new members
                foreach ($request->users as $uid) {
                    $user = LdapUser::where('uid', '=', $uid)->first();
                    if ($user) {
                        $organizationalUnit->addMember($user);
                    }
                }
            }

            return redirect()->route('ldap.organizational-units.index')
                ->with('success', 'Jednostka organizacyjna LDAP została pomyślnie zaktualizowana.');
        } catch (\Exception $e) {
            Log::error("Błąd podczas aktualizacji jednostki organizacyjnej LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas aktualizacji jednostki organizacyjnej LDAP: ' . $e->getMessage());
        }
    }
}
