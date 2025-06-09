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
            'organizational_unit' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
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
                'dn' => 'ou=' . $request->organizational_unit . ',dc=ptibb,dc=edu,dc=pl', 
                ]);

            // Log the attempt to save the organizational unit
            Log::info('Próba zapisu jednostki organizacyjnej do LDAP', ['attributes' => $organizationalUnit->getAttributes()]);
            $organizationalUnit->save();

            return redirect()->route('ldap.organizational-units.index')
                ->with('success', 'Jednostka organizacyjna LDAP została pomyślnie utworzona.');
        } catch (\Exception $e) {
            Log::error("Błąd podczas tworzenia jednostki organizacyjnej LDAP: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas tworzenia jednostki organizacyjnej LDAP: ' . $e->getMessage());
        }
    }
}
