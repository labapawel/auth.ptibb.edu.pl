<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LdapUsersController;
use App\Http\Controllers\Admin\LdapGroupController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PcController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Ldap\Group;
use App\Ldap\User as LdapUser;
// Group all admin routes with the admin middleware
Route::middleware('admin')->group(function () {
	// Dashboard route
	Route::get('', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
	// Trasy LDAP dla administratorów
	Route::get('ldap/users', [LdapUsersController::class, 'index'])->name('ldap.users.index');

	Route::get('ldap/users/create', [LdapUsersController::class, 'create'])->name('ldap.users.create');

	Route::get('ldap/users/create-by-csv', [LdapUsersController::class, 'createByCsvForm'])->name('ldap.users.createByCsv');
	Route::post('ldap/users/create-by-csv', [LdapUsersController::class, 'createByCsv'])->name('ldap.users.createByCsv.store');
	Route::post('ldap/users', [LdapUsersController::class, 'createUser'])->name('ldap.users.store');

	Route::delete('ldap/users/{distinguishedName}', [LdapUsersController::class, 'destroyByDn'])->name('ldap.users.delete');

	// (route placeholder removed — use controller action if needed)

	Route::get('ldap/users/{uid}/edit', [LdapUsersController::class, 'edit'])->name('ldap.users.edit');

	Route::put('ldap/users/{uid}', [LdapUsersController::class, 'update'])->name('ldap.users.update');








	// Trasy LDAP dla grup
	Route::get('ldap/groups', [LdapGroupController::class, 'index'])->name('ldap.groups.index');

	Route::get('ldap/groups/create', [LdapGroupController::class, 'create'])->name('ldap.groups.create');

	Route::post('ldap/groups', [LdapGroupController::class, 'store'])->name('ldap.groups.store');

	Route::get('ldap/groups/{cn}', [LdapGroupController::class, 'show'])->name('ldap.groups.show');

	Route::get('ldap/groups/{cn}/edit', [LdapGroupController::class, 'edit'])->name('ldap.groups.edit');

	Route::put('ldap/groups/{cn}', [LdapGroupController::class, 'update'])->name('ldap.groups.update');

	Route::delete('ldap/groups/{cn}', [LdapGroupController::class, 'destroy'])->name('ldap.groups.destroy');


	Route::post('ldap/users/{userDn}/assign-group', [LdapUsersController::class, 'assignToGroup'])->name('ldap.users.assign-ou');

	// PC management route
	Route::get('pc', [PcController::class, 'index'])->name('admin.pc');

	// Logout route
	Route::get('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});