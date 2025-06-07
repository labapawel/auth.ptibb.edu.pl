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
            dd($user);

            Log::info('Próba zapisu użytkownika do LDAP', ['attributes' => $user->getAttributes()]);
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

    public function createGroup()
    {
        return view("admin.ldap-group-create");
    }

    // public function storeGroup(Request $request)
    // {
    //     $request->validate([
    //         "group_cn" => "required|string",
    //     ]);
    //     try {
    //         $group = new Group();
            
    //         // Utwórz pełny DN dla grupy używając metody pomocniczej
    //         $groupDn = $this->getEntityDn("groups", "cn", $request->group_cn);
    //         $group->setDn($groupDn);
    //         $group->cn = $request->group_cn;
    //         $group->description = $request->input("description", "");
    //         $group->save();
    //         return redirect()->route("admin.ldap.groups.index")
    //             ->with("success", "Grupa została utworzona.");
    //     } catch (\Exception $e) {
    //         Log::error("Błąd podczas tworzenia grupy LDAP: " . $e->getMessage());
    //         return redirect()->back()->withInput()->with("error", "Wystąpił błąd podczas tworzenia grupy: " . $e->getMessage());
    //     }
    // }

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
