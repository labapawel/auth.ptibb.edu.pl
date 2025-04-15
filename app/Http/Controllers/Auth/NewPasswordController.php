<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; // Added HTTP facade
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

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

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            
            //
            // Send POST request to Samba service with username and password
            try {
                // Configure Samba service URL in .env file with SAMBA_SERVICE_URL
                $sambaUrl = config('SAMBA_SERVICE_URL', 'http://localhost:3000/user/addToSamba');
                
                // // Debug data
                // dd([
                // 'samba_url' => $sambaUrl,
                // 'request_data' => [
                //     'username' => $user->email,
                //     'password' => $request->password
                // ]
                // ]);
                
                Http::timeout(5)->post($sambaUrl, [
                'username' => $user->email,
                'password' => $request->password
                ]);
            } catch (\Exception $e) {
                // Log any errors but continue with password reset flow
                \Log::error('Failed to connect to Samba service: ' . $e->getMessage());
            }
            // Remove debug statement to allow normal execution flow
            event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }
        
        return back()->withInput($request->only('email'))
                     ->withErrors(['email' => __($status)]);
    }
}
