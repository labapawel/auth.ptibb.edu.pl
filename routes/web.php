<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FallbackController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\LdapGroupController;
use App\Http\Controllers\Admin\LdapUsersController;
use App\Http\Controllers\Admin\PcController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::get('lang/{lang}', [LangController::class, 'switch'])->name('lang.switch');

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('welcome');

    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('ldap/users', [LdapUsersController::class, 'index'])->name('ldap.users.index');
    Route::get('ldap/users/create', [LdapUsersController::class, 'create'])->name('ldap.users.create');
    Route::get('ldap/users/create-by-csv', [LdapUsersController::class, 'createByCsvForm'])->name('ldap.users.createByCsv');
    Route::post('ldap/users/create-by-csv', [LdapUsersController::class, 'createByCsv'])->name('ldap.users.createByCsv.store');
    Route::post('ldap/users', [LdapUsersController::class, 'createUser'])->name('ldap.users.store');
    Route::delete('ldap/users/{distinguishedName}', [LdapUsersController::class, 'destroyByDn'])->name('ldap.users.delete');
    Route::get('ldap/users/{uid}/edit', [LdapUsersController::class, 'edit'])->name('ldap.users.edit');
    Route::put('ldap/users/{uid}', [LdapUsersController::class, 'update'])->name('ldap.users.update');
    Route::post('ldap/users/{userDn}/assign-group', [LdapUsersController::class, 'assignToGroup'])->name('ldap.users.assign-ou');

    Route::get('ldap/groups', [LdapGroupController::class, 'index'])->name('ldap.groups.index');
    Route::get('ldap/groups/create', [LdapGroupController::class, 'create'])->name('ldap.groups.create');
    Route::post('ldap/groups', [LdapGroupController::class, 'store'])->name('ldap.groups.store');
    Route::get('ldap/groups/{cn}', [LdapGroupController::class, 'show'])->name('ldap.groups.show');
    Route::get('ldap/groups/{cn}/edit', [LdapGroupController::class, 'edit'])->name('ldap.groups.edit');
    Route::put('ldap/groups/{cn}', [LdapGroupController::class, 'update'])->name('ldap.groups.update');
    Route::delete('ldap/groups/{cn}', [LdapGroupController::class, 'destroy'])->name('ldap.groups.destroy');

    Route::get('pc', [PcController::class, 'index'])->name('admin.pc');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
require __DIR__.'/auth.php';

// Fallback for any unmatched web routes: redirect guests to login, users to home
Route::fallback(FallbackController::class);

// Extra catch-all as a safety net (ensures redirect even when fallback is bypassed by web server quirks)
Route::any('{any}', FallbackController::class)->where('any', '.*');
