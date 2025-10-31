<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FallbackController;
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
require __DIR__.'/auth.php';

// Fallback for any unmatched web routes: redirect guests to login, users to home
Route::fallback(FallbackController::class);

// Extra catch-all as a safety net (ensures redirect even when fallback is bypassed by web server quirks)
Route::any('{any}', FallbackController::class)->where('any', '.*');
