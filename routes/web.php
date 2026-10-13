<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;

Route::view('/', 'pages.home')
    ->name('home');

Route::view('/tentang-kami', 'pages.about')
    ->name('about');

Route::get('/layanan-jasa', [ServiceController::class, 'index'])
    ->name('services');

Route::get('/layanan-jasa/{category}', [ServiceController::class, 'category'])
    ->name('services.category');

Route::get('/layanan-jasa/{category}/{activity}', [ServiceController::class, 'detail'])
    ->name('services.detail');

Route::view('/kontak-kami', 'pages.contact')
    ->name('contact');

require __DIR__.'/auth.php';

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    });
