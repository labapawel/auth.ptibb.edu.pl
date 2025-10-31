<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class FallbackController extends Controller
{
    /**
     * Handle all unmatched web routes and redirect appropriately.
     */
    public function __invoke(Request $request)
    {
        // If user is authenticated, send them to the main dashboard/home.
        if (Auth::check()) {
            // Prefer a named "welcome" route if it exists; otherwise fallback to "/".
            if (Route::has('welcome')) {
                return redirect()->route('welcome');
            }

            return redirect('/');
        }

        // Guests go to login
        return redirect()->route('login');
    }
}
