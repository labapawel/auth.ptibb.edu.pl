<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapUsersController;

// Group all admin routes with the admin middleware
Route::middleware('admin')->group(function () {
	// Dashboard route
	Route::get('', ['as' => 'admin.dashboard', function () {
		$content = __('lang.admin.dashboard');
		return AdminSection::view($content, 'Dashboard');
	}]);


Route::get('/ldap/users', function () {
    $users = app(\App\Http\Controllers\Admin\LdapUsersController::class)->index()->getData();
    // Render a Blade view styled like the dashboard/PC management
    return AdminSection::view(view('admin.ldap-users', ['users' => $users])->render());
});

	// PC management route
	Route::get('pc', ['as' => 'admin.pc', function () {
		$content = 'PC Management';
		return AdminSection::view($content, 'PC');
	}]);

	// Logout route
	Route::get('logout', ['as' => 'admin.logout', function () {
		Auth::logout();
		session()->invalidate();
		session()->regenerateToken();
		return redirect()->route('login');
	}]);
});