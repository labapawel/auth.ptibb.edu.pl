<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Added HTTP facade
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Ldap\User;
use App\Http\Controllers\TokenController;


class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */


    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $tokenData = TokenController::findToken($request);

        if (!$tokenData) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Token jest niepoprawny lub adres e-mail jest błędny.')]);
        }
        
        // Czas ważności tokena to 60 minut.
        if (TokenController::isTokenValid($tokenData) === false) {
            TokenController::deleteToken($request->email);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Token wygasł. Wygeneruj nowy link do resetowania hasła.')]);
        }
        try {
            try {
                // Połączenie z LDAP
                $ldapConnection = User::getLdapConnection();
            } catch (\Exception $e) {
                return back()->withErrors(['email' => __('Nie można połączyć się z serwerem LDAP. Sprawdź konfigurację.')]);
            }
            $ldapUser = User::where('uid', $request->input('email'))->first();

            if (!$ldapUser) {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __('Email jest niepoprawny lub użytkownik nie istnieje.')]);
            }
                $ldapUser->setLdapPassword($request->password);
                $ldapUser->save(); 
                TokenController::deleteToken($request->email);

                return redirect()->route('login')->with('status', __('Twoje hasło zostało zresetowane pomyślnie.'));

        } catch (\Exception $e) {
                // Ogólny błąd (np. brak uprawnień, problem z połączeniem)
                // W dewelopmencie możesz użyć dd($e->getMessage())
                return back()->withErrors(['email' => __('Wystąpił błąd podczas zmiany hasła w LDAP. Sprawdź logi.')]);
        }
    }
}

