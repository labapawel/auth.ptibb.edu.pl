<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            // Prefer the dashboard route name if available
            if (app('router')->has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            if (app('router')->has('admin')) {
                return redirect()->route('admin');
            }
        }
        return view('welcome');
    }
}
