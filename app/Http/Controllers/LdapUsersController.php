<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LdapRecord\Connection;
use App\Ldap\User as LdapUser;
use Illuminate\Support\Facades\Log;
use LdapRecord\Container;

class LdapUsersController extends Controller
{
    protected $title = 'Użytkownicy LDAP';
    protected $alias = 'ldap.users';
    
    /**
     * Wyświetla listę użytkowników LDAP.
     */
    public function index()
    {
        try {
            // Pobierz użytkowników LDAP używając naszego modelu LdapUser
            $users = LdapUser::get();
            
            return view('admin.ldap-users', compact('users'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania użytkowników LDAP: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Wystąpił błąd podczas pobierania użytkowników LDAP: ' . $e->getMessage());
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
            // Utwórz atrybuty użytkownika
            $attributes = [
                'cn' => $request->cn,
                'givenName' => $request->givenName,
                'sn' => $request->sn,
                'mail' => $request->mail,
                'samAccountName' => $request->samAccountName,
                'unicodePwd' => $this->encodePassword($request->password),
                'displayName' => $request->givenName . ' ' . $request->sn,
            ];
            
            // Utwórz użytkownika
            $user = LdapUser::createUser($attributes);

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
     * Koduje hasło dla Active Directory.
     */
    protected function encodePassword($password)
    {
        $password = '"' . $password . '"';
        $encoded = mb_convert_encoding($password, 'UTF-16LE');
        
        return $encoded;
    }
}
