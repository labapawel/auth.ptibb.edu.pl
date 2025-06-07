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
	// Trasy LDAP dla administratorów
	Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {

		Route::get('ldap/users', function () {
			$users = app(\App\Http\Controllers\Admin\LdapUsersController::class)->index()->getData();
			return AdminSection::view(view('ldap-users', ['users' => $users])->render());
		})->name('ldap.users.index');

		Route::get('ldap/users/create', function () {
			return AdminSection::view(view('admin.ldap-users-create')->render(), 'Create LDAP User');
		})->name('ldap.users.create');

		Route::post('ldap/users', [\App\Http\Controllers\Admin\LdapUsersController::class, 'store'])->name('ldap.users.store');

		Route::delete('ldap/users/{distinguishedName}', function ($distinguishedName) {
			$response = app(\App\Http\Controllers\Admin\LdapUsersController::class)->delete($distinguishedName);
			return redirect('admin/ldap/users')->with('status', $response);
		})->name('ldap.users.delete');

		
		Route::get('ldap/groups/create', [\App\Http\Controllers\Admin\LdapUsersController::class, 'createGroup'])->name('ldap.groups.create');
		Route::post('ldap/groups', [\App\Http\Controllers\Admin\LdapUsersController::class, 'storeGroup'])->name('ldap.groups.store');
		Route::post('ldap/users/{userDn}/assign-group', [\App\Http\Controllers\Admin\LdapUsersController::class, 'assignToGroup'])->name('ldap.users.assignGroup');
	});

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