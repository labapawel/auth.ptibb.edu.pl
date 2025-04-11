<?php

use Illuminate\Support\Facades\Route;

// Group all admin routes with the admin middleware
Route::middleware('admin')->group(function () {
	// Dashboard route
	Route::get('', ['as' => 'admin.dashboard', function () {
		$content = __('lang.admin.dashboard');
		return AdminSection::view($content, 'Dashboard');
	}]);


	// VPN management route
	Route::get('vpn', ['as' => 'admin.vpn', function () {
		$content = 'VPN Management';
		return AdminSection::view($content, 'VPN');
	}]);

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