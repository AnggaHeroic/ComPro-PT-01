<?php

use Illuminate\Support\Facades\Route;
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
