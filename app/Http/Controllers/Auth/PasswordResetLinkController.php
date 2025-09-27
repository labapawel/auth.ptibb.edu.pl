<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Ldap\User;
use App\Mail\ForgotPasswordMail;
use App\Http\Controllers\TokenController;
class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }



    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);
        $ldapUser = User::where('uid', $request->input('email'))->first();

        if (!$ldapUser->exists) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Email jest niepoprawny lub użytkownik nie istnieje.')]);
        }else{
            TokenController::deleteExpiredTokens();
            $resetLink = TokenController::store($ldapUser);

            Mail::to(($ldapUser)->getAttributes()['uid'][0])->send(new ForgotPasswordMail($resetLink));

            return back()->with('status', __('Link do resetu hasła został wysłany na adres e-mail.'));
        }
    }
}
