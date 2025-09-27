<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', __('lang.admin.login_required'));
        }
        
        // Check if the authenticated user is an admin
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('welcome')->with('error', __('lang.admin.access_denied'));
        }
        
        return $next($request);
    }
}