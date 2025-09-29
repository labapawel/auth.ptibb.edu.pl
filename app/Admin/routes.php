<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LdapUsersController;
use App\Http\Controllers\Admin\LdapGroupController;
use App\Ldap\Group;
use App\Ldap\User as LdapUser;
// Group all admin routes with the admin middleware
Route::middleware('admin')->group(function () {
	// Dashboard route
	Route::get('', ['as' => 'admin.dashboard', function () {
		return AdminSection::view(view('admin.dashboard')->render());
	}]);
	// Trasy LDAP dla administratorów
	Route::get('ldap/users', function () {
		$users = App(\App\Http\Controllers\Admin\LdapUsersController::class)->getUsers()->getData();
		return AdminSection::view(view('admin.ldap-users', ['users' => $users])->render());
	})->name('ldap.users.index');

	Route::get('ldap/users/create', function () {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->create();
	})->name('ldap.users.create');

	Route::get('ldap/users/create-by-csv', function () {
		return AdminSection::view(view('admin.ldap-users-create-by-csv')->render());
	})->name('ldap.users.createByCsv');
	Route::post('ldap/users/create-by-csv', function () {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->createByCsv(request());
	})->name('ldap.users.createByCsv');
	Route::post('ldap/users', function () {
		return app(\App\Http\Controllers\Admin\LdapUsersController::class)->createUser(request());
	})->name('ldap.users.store');

	Route::delete('ldap/users/{distinguishedName}', function ($distinguishedName) {
		$user = LdapUser::where('cn', '=', str_replace('%20', ' ', $distinguishedName))->first();
		if ($user) {
			// Usuń użytkownika ze wszystkich grup
			$groups = Group::whereHas('members', function ($query) use ($user) {
				$query->where('dn', '=', $user->getDn());
			})->get();

			foreach ($groups as $group) {
				$group->members()->detach($user);
				$group->save();
			}

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








	// Trasy LDAP dla grup
	Route::get('ldap/groups', function () {
		$groups = App(\App\Http\Controllers\Admin\LdapGroupController::class)->getGroups()->getData();
		return AdminSection::view(view('admin.ldap-groups', ['groups' => $groups])->render());
	})->name('ldap.groups.index');

	Route::get('ldap/groups/create', function () {
		$users = App(\App\Http\Controllers\Admin\LdapUsersController::class)->getUsers()->getData();
		return AdminSection::view(view('admin.ldap-group-create', ['users' => $users])->render());
	})->name('ldap.groups.create');

	Route::post('ldap/groups', function () {
		return app(\App\Http\Controllers\Admin\LdapGroupController::class)->store(request());
	})->name('ldap.groups.store');

	Route::get('ldap/groups/{cn}', function ($cn) {
		return app(\App\Http\Controllers\Admin\LdapGroupController::class)->show($cn);
	})->name('ldap.groups.show');

	Route::get('ldap/groups/{cn}/edit', function ($cn) {
		return app(\App\Http\Controllers\Admin\LdapGroupController::class)->edit($cn);
	})->name('ldap.groups.edit');

	Route::put('ldap/groups/{cn}', function ($cn) {
		return app(\App\Http\Controllers\Admin\LdapGroupController::class)->update(request(), $cn);
	})->name('ldap.groups.update');

	Route::delete('ldap/groups/{cn}', function ($cn) {
		return app(\App\Http\Controllers\Admin\LdapGroupController::class)->destroy($cn);
	})->name('ldap.groups.destroy');


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