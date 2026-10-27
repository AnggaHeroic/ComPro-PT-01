<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// Registrasi publik, reset password via email, dan verifikasi email sengaja
// tidak disediakan: CMS hanya untuk admin yang dibuat lewat seeder.
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:60,1');
});

Route::middleware(['auth', 'admin.idle'])->group(function () {
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
