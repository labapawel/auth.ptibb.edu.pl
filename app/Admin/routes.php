<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapUsersController;
use App\Http\Controllers\LdapOuController;
// Group all admin routes with the admin middleware
Route::middleware('admin')->group(function () {
	// Dashboard route
	Route::get('', ['as' => 'admin.dashboard', function () {
		$content = __('lang.admin.dashboard');
		return AdminSection::view($content, 'Dashboard');
	}]);
	// Trasy LDAP dla administratorów
	Route::get('ldap/users', function () {
		$users = app(\App\Http\Controllers\Admin\LdapUsersController::class)->getUsers()->getData();
		return AdminSection::view(view('admin.ldap-users', ['users' => $users])->render());
	})->name('ldap.users.index');

	Route::get('ldap/users/create', function () {
		$organizationalUnits = app(\App\Http\Controllers\Admin\LdapOuController::class)->getOrganizationalUnits()->getData();
		return AdminSection::view(view('admin.ldap-users-create', ['organizationalUnits' => $organizationalUnits])->render());
	})->name('ldap.users.create');

	Route::post('ldap/users', function () {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->store(request());
	})->name('ldap.users.store');

	Route::delete('ldap/users/{distinguishedName}', function ($distinguishedName) {
		$response = app(\App\Http\Controllers\Admin\LdapUsersController::class)->delete($distinguishedName);
		return redirect('admin/ldap/users')->with('status', $response);
	})->name('ldap.users.delete');

	// Route::get('ldap/users/{distinguishedName}', function ($distinguishedName) {
	//     return app(\App\Http\Controllers\Admin\LdapUsersController::class)->show($distinguishedName);
	// })->name('ldap.users.show');

	// Route::get('ldap/users/{distinguishedName}/edit', function ($distinguishedName) {
	//     return app(\App\Http\Controllers\Admin\LdapUsersController::class)->edit($distinguishedName);
	// })->name('ldap.users.edit');

	Route::get('ldap/organizational-units', function () {
		$organizationalUnits = app(\App\Http\Controllers\Admin\LdapOuController::class)->getOrganizationalUnits()->getData();
		return AdminSection::view(view('admin.ldap-ou', ['organizationalUnits' => $organizationalUnits])->render());
	})->name('ldap.organizational-units.index');

	Route::get('ldap/organizational-units/create', function () {
		$users = app(\App\Http\Controllers\Admin\LdapUsersController::class)->getUsers()->getData();
		return AdminSection::view(view('admin.ldap-ou-create', ['users' => $users])->render());
	})->name('ldap.organizational-units.create');

	Route::post('ldap/organizational-units', function () {
		return app(\App\Http\Controllers\Admin\LdapOuController::class)->store(request());
	})->name('ldap.organizational-units.store');

	Route::get('ldap/organizational-units/{ou}', function ($ou) {
		return app(\App\Http\Controllers\Admin\LdapOuController::class)->show($ou);
	})->name('ldap.organizational-units.show');



	Route::post('ldap/users/{userDn}/assign-group', function ($userDn) {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->assignToGroup($userDn, request());
	})->name('ldap.users.assign-ou');

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