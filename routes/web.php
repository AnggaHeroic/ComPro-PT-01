<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BusinessActivityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\ServiceCategoryController;
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

        // Harus sebelum resource: "reorder" bukan id.
        Route::put('categories/reorder', [ServiceCategoryController::class, 'reorder'])->name('categories.reorder');
        Route::put('activities/reorder', [BusinessActivityController::class, 'reorder'])->name('activities.reorder');

        Route::resource('categories', ServiceCategoryController::class)
            ->parameters(['categories' => 'category'])
            ->except(['show']);

        Route::resource('activities', BusinessActivityController::class)
            ->parameters(['activities' => 'activity'])
            ->except(['show']);

        Route::controller(GalleryController::class)
            ->prefix('gallery')
            ->name('gallery.')
            ->scopeBindings()
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{activity}', 'show')->name('show');
                Route::post('/{activity}/images', 'store')->name('store');
                Route::put('/{activity}/order', 'reorder')->name('reorder');
                Route::patch('/{activity}/images/{image}', 'update')->name('update');
                Route::delete('/{activity}/images/{image}', 'destroy')->name('destroy');
            });

        Route::controller(PageContentController::class)
            ->prefix('pages')
            ->name('pages.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{page}', 'edit')->name('edit');
                Route::put('/{page}', 'update')->name('update');
                Route::delete('/{page}', 'reset')->name('reset');
                Route::post('/{page}/preview/{section}', 'preview')->name('preview');
            });

        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    });

// Tanpa rute fallback, URL yang tidak cocok dengan rute mana pun dijawab 404 tanpa middleware web
// (tanpa sesi), sehingga tombol "Kembali ke Dashboard Admin" tidak muncul bagi admin di halaman itu.
Route::any('{path}', fn () => abort(404))->where('path', '.*')->fallback();
