<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LdapUsersController;
use App\Http\Controllers\LdapOuController;
use App\Ldap\OrganizationalUnit;
use App\Ldap\User as LdapUser;
// Group all admin routes with the admin middleware
Route::middleware('admin')->group(function () {
	// Dashboard route
	Route::get('', ['as' => 'admin.dashboard', function () {
		$content = __('lang.admin.dashboard');
		return AdminSection::view($content, 'Dashboard');
	}]);
	// Trasy LDAP dla administratorów
	Route::get('ldap/users', function () {
		$users = App(\App\Http\Controllers\Admin\LdapUsersController::class)->getUsers()->getData();
		return AdminSection::view(view('admin.ldap-users', ['users' => $users])->render());
	})->name('ldap.users.index');

	Route::get('ldap/users/create', function () {
		$organizationalUnits = App(\App\Http\Controllers\Admin\LdapOuController::class)->getOrganizationalUnits()->getData();
		return AdminSection::view(view('admin.ldap-users-create', ['organizationalUnits' => $organizationalUnits])->render());
	})->name('ldap.users.create');

	Route::post('ldap/users', function () {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->store(request());
	})->name('ldap.users.store');

	Route::delete('ldap/users/{distinguishedName}', function ($distinguishedName) {
		$user = LdapUser::where('cn', '=', str_replace('%20', ' ', $distinguishedName))->first();
		if ($user) {
			$user->delete();
		}
		return redirect('admin/ldap/users')->with('success', 'Użytkownik '. $user .' został usunięty.');
	})->name('ldap.users.delete');

	// Route::get('ldap/users/{distinguishedName}', function ($distinguishedName) {
	//     return app(\App\Http\Controllers\Admin\LdapUsersController::class)->show($distinguishedName);
	// })->name('ldap.users.show');

	Route::get('ldap/users/{uid}/edit', function ($uid) {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->edit($uid);
	})->name('ldap.users.edit');

	Route::put('ldap/users/{uid}', function ($uid) {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->update(request(), $uid);
	})->name('ldap.users.update');








	Route::get('ldap/organizational-units', function () {
		$organizationalUnits = App(App\Http\Controllers\Admin\LdapOuController::class)->getOrganizationalUnits()->getData();
		return AdminSection::view(view('admin.ldap-ou', ['organizationalUnits' => $organizationalUnits])->render());
	})->name('ldap.organizational-units.index');



	Route::get('ldap/organizational-units/create', function () {
		$users = App(\App\Http\Controllers\Admin\LdapUsersController::class)->getUsers()->getData();
		return AdminSection::view(view('admin.ldap-ou-create', ['users' => $users])->render());
	})->name('ldap.organizational-units.create');



	Route::post('ldap/organizational-units', function () {
		return app(\App\Http\Controllers\Admin\LdapOuController::class)->store(request());
	})->name('ldap.organizational-units.store');

	Route::get('ldap/organizational-units/{ou}', function ($ou) {
		return app(\App\Http\Controllers\Admin\LdapOuController::class)->show($ou);
	})->name('ldap.organizational-units.show');

	Route::get('ldap/organizational-units/{ou}/edit', function ($ou) {
		$organizationalUnit = OrganizationalUnit::where("ou", "=", $ou)->first()->getAttributes();
		$users = LdapUser::all()->map(function ($user) {
			return [
				'uid' => $user->getFirstAttribute('uid'),
				'cn' => $user->getFirstAttribute('cn'),
				'mail' => $user->getFirstAttribute('mail'),
			];
		});
		return AdminSection::view(view('admin.ldap-ou-edit', ['organizationalUnit' => $organizationalUnit, 'users' => $users])->render());
	})->name('ldap.organizational-units.edit');

	Route::put('ldap/organizational-units/{ou}', function ($ou) {
		return app(\App\Http\Controllers\Admin\LdapOuController::class)->update(request(), $ou);
	})->name('ldap.organizational-units.update');


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