<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Log;
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

    /**
     * Wyświetla formularz dodawania użytkownika LDAP.
     */
    public function create()
    {
        return view('admin.ldap-users-create');
    }

    /**
     * Zapisuje nowego użytkownika LDAP.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cn' => 'required|string',
            'givenName' => 'required|string',
            'sn' => 'required|string',
            'mail' => 'required|email',
            'samAccountName' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        try {

            // Utwórz użytkownika
            $user = new LdapUser;

            $user -> cn = $request->cn;
            $user -> givenName = $request->givenName;
            $user -> sn = $request->sn;
            $user -> mail = $request->mail;
            $user -> samAccountName = $request->samAccountName;
            $user -> userPassword = $request->password;
            $user -> displayName = $request->givenName . ' ' . $request->sn;

            $user ->save();
            // Przypisz użytkownika do grupy, jeśli podano
            if ($request->filled('group_cn')) {
                $group = (new \LdapRecord\Models\OpenLDAP\Group())->where('cn', '=', $request->group_cn)->first();
                if ($group) {
                    $group->addMember($user);
                }
            }

            return redirect()->route('admin.ldap.users.index')
                ->with('success', 'Użytkownik LDAP został pomyślnie utworzony.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas tworzenia użytkownika LDAP: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Wystąpił błąd podczas tworzenia użytkownika LDAP: ' . $e->getMessage());
        }
    }

    /**
     * Wyświetla formularz usuwania użytkownika LDAP.
     */
    public function delete($distinguishedName)
    {
        try {
            // Znajdź użytkownika po jego Distinguished Name
            $user = LdapUser::findByDn($distinguishedName);
            
            if (!$user) {
                return redirect()->route('admin.ldap.users.index')
                    ->with('error', 'Użytkownik LDAP nie został znaleziony.');
            }
            
            return view('admin.ldap-users-delete', compact('user'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania użytkownika LDAP: ' . $e->getMessage());
            return redirect()->route('admin.ldap.users.index')
                ->with('error', 'Wystąpił błąd podczas pobierania użytkownika LDAP: ' . $e->getMessage());
        }
    }

    /**
     * Usuwa użytkownika LDAP.
     */
    public function destroy($distinguishedName)
    {
        try {
            // Znajdź użytkownika po jego Distinguished Name
            $user = LdapUser::findByDn($distinguishedName);
            
            if (!$user) {
                return redirect()->route('admin.ldap.users.index')
                    ->with('error', 'Użytkownik LDAP nie został znaleziony.');
            }
            
            // Zapisz dane użytkownika do wyświetlenia w komunikacie
            $userName = $user->cn;
            
            // Usuń użytkownika
            $user->delete();
            
            return redirect()->route('admin.ldap.users.index')
                ->with('success', "Użytkownik LDAP '{$userName}' został pomyślnie usunięty.");
        } catch (\Exception $e) {
            Log::error('Błąd podczas usuwania użytkownika LDAP: ' . $e->getMessage());
            return redirect()->route('admin.ldap.users.index')
                ->with('error', 'Wystąpił błąd podczas usuwania użytkownika LDAP: ' . $e->getMessage());
        }
    }

    /**
     * Wyświetla formularz tworzenia grupy (klasy).
     */
    public function createGroup()
    {
        return view('admin.ldap-group-create');
    }

    /**
     * Zapisuje nową grupę (klasę) w LDAP.
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'group_cn' => 'required|string|unique:ldap_groups,cn',
        ]);
        try {
            $group = new \LdapRecord\Models\OpenLDAP\Group();
            $group->setDn('cn=' . $request->group_cn . ',ou=groups,dc=example,dc=com'); // Zmień na odpowiednią DN
            $group->cn = $request->group_cn;
            $group->description = $request->input('description', '');
            $group->save();
            return redirect()->route('admin.ldap.groups.index')
                ->with('success', 'Grupa została utworzona.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas tworzenia grupy LDAP: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Wystąpił błąd podczas tworzenia grupy: ' . $e->getMessage());
        }
    }

    /**
     * Przypisuje użytkownika do grupy (klasy).
     */
    public function assignToGroup(Request $request, $userDn)
    {
        $request->validate([
            'group_cn' => 'required|string',
        ]);
        try {
            $user = LdapUser::findByDn($userDn);
            if (!$user) {
                return redirect()->back()->with('error', 'Nie znaleziono użytkownika LDAP.');
            }
            $group = (new \LdapRecord\Models\OpenLDAP\Group())->where('cn', '=', $request->group_cn)->first();
            if (!$group) {
                return redirect()->back()->with('error', 'Nie znaleziono grupy LDAP.');
            }
            $group->addMember($user);
            return redirect()->back()->with('success', 'Użytkownik został przypisany do grupy.');
        } catch (\Exception $e) {
            Log::error('Błąd podczas przypisywania do grupy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Wystąpił błąd podczas przypisywania do grupy: ' . $e->getMessage());
        }
    }
}
