<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\OpenLDAP\Group;

class LdapUsersController extends Controller
{
    /**
     * Helper method to get the proper DN for LDAP entities
     * 
     * @param string $type Type of entity (users, groups)
     * @param string $identifier Unique identifier (uid for users, cn for groups)
     * @param string $value Value of the identifier
     * @return string Full DN for the entity
     */
    private function getEntityDn(string $type, string $identifier, string $value): string
    {
        $baseDn = config("ldap.connections.default.base_dn");
        
        // Check if the base has organizational units for users and groups
        // If not, use the base DN directly
        try {
            if (in_array($type, ["users", "groups"])) {
                return $identifier . "=" . $value . ",ou=" . $type . "," . $baseDn;
            }
        } catch (\Exception $e) {
            Log::warning("Error determining DN structure: " . $e->getMessage());
        }
        
        // Fallback - use directly in the base DN
        return $identifier . "=" . $value . "," . $baseDn;
    }

    public function index()
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
        $request->validate([
            "cn" => "required|string",
            "sn" => "required|string",
            "givenname" => "required|string",
            "uid" => "required|string",
            "uidnumber" => "required|numeric",
            "gidnumber" => "required|numeric",
            "homedirectory" => "required|string",
            "loginshell" => "required|string",
            "mail" => "required|email",
            "userpassword" => "required|string|min:8",
        ]);

        try {
            $user = new LdapUser;

            $user->objectClass = ['top','person','organizationalPerson','inetOrgPerson','posixAccount'];
            $user->cn = $request->cn;
            $user->sn = $request->sn;
            $user->givenName = $request->givenname;
            $user->uid = $request->uid;
            $user->uidNumber = $request->uidnumber;
            $user->gidNumber = $request->gidnumber;
            $user->homeDirectory = 'uczniowie/' . $request->uid;
            $user->loginShell = '/bin/bash';
            $user->mail = $request->mail;
            $user->userPassword = $request->userpassword;
            $user->displayname = $request->givenname . " " . $request->sn;

            \Log::info($user);
            // Utwórz pełny DN dla użytkownika używając metody pomocniczej
            $userDn = $this->getEntityDn("users", "uid", $request->uid);
            $user->setDn($userDn);
        
            $user->save();
            if ($request->filled("group_cn")) {
                $group = (new Group())->where("cn", "=", $request->group_cn)->first();
                if ($group) {
                    $group->addMember($user);
                }
            }

            return redirect()->route("admin.ldap.users.index")
                ->with("success", "Użytkownik LDAP został pomyślnie utworzony.");
        } catch (\Exception $e) {
            Log::error("Błąd podczas tworzenia użytkownika LDAP: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with("error", "Wystąpił błąd podczas tworzenia użytkownika LDAP: " . $e->getMessage());
        }
    }

    public function delete($distinguishedName)
    {
        try {
            
            $user = LdapUser::findByDn($distinguishedName);

            if (!$user) {
                return redirect()->route("admin.ldap.users.index")
                    ->with("error", "Użytkownik LDAP nie został znaleziony.");
            }

            return view("admin.ldap-users-delete", compact("user"));
        } catch (\Exception $e) {
            Log::error("Błąd podczas pobierania użytkownika LDAP: " . $e->getMessage());
            return redirect()->route("admin.ldap.users.index")
                ->with("error", "Wystąpił błąd podczas pobierania użytkownika LDAP: " . $e->getMessage());
        }
    }

    public function destroy($distinguishedName)
    {
        try {
            $user = LdapUser::findByDn($distinguishedName);

            if (!$user) {
                return redirect()->route("admin.ldap.users.index")
                    ->with("error", "Użytkownik LDAP nie został znaleziony.");
            }

            $userName = $user->getFirstAttribute("cn");
            $user->delete();

            return redirect()->route("admin.ldap.users.index")
            ->with("success", "Użytkownik LDAP \"" . $userName . "\" został pomyślnie usunięty.");
        } catch (\Exception $e) {
            Log::error("Błąd podczas usuwania użytkownika LDAP: " . $e->getMessage());
            return redirect()->route("admin.ldap.users.index")
                ->with("error", "Wystąpił błąd podczas usuwania użytkownika LDAP: " . $e->getMessage());
        }
    }

    public function createGroup()
    {
        return view("admin.ldap-group-create");
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            "group_cn" => "required|string",
        ]);
        try {
            $group = new Group();
            
            // Utwórz pełny DN dla grupy używając metody pomocniczej
            $groupDn = $this->getEntityDn("groups", "cn", $request->group_cn);
            $group->setDn($groupDn);
            $group->cn = $request->group_cn;
            $group->description = $request->input("description", "");
            $group->save();
            return redirect()->route("admin.ldap.groups.index")
                ->with("success", "Grupa została utworzona.");
        } catch (\Exception $e) {
            Log::error("Błąd podczas tworzenia grupy LDAP: " . $e->getMessage());
            return redirect()->back()->withInput()->with("error", "Wystąpił błąd podczas tworzenia grupy: " . $e->getMessage());
        }
    }

    public function assignToGroup(Request $request, $userDn)
    {
        $request->validate([
            "group_cn" => "required|string",
        ]);
        try {
            $user = LdapUser::findByDn($userDn);
            if (!$user) {
                return redirect()->back()->with("error", "Nie znaleziono użytkownika LDAP.");
            }
            $group = (new Group())->where("cn", "=", $request->group_cn)->first();
            if (!$group) {
                return redirect()->back()->with("error", "Nie znaleziono grupy LDAP.");
            }
            $group->addMember($user);
            return redirect()->back()->with("success", "Użytkownik został przypisany do grupy.");
        } catch (\Exception $e) {
            Log::error("Błąd podczas przypisywania do grupy: " . $e->getMessage());
            return redirect()->back()->with("error", "Wystąpił błąd podczas przypisywania do grupy: " . $e->getMessage());
        }
    }
}
