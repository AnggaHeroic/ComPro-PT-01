<?php

namespace App\Providers;

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Support\PageContents;
use App\Models\ActivityLog;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Navbar, footer, dan kartu layanan beranda memakai daftar kategori yang sama;
        // query hanya sekali per request.
        $this->app->scoped(
            'nav.categories',
            fn () => ServiceCategory::published()->ordered()->get(['id', 'name', 'slug', 'description', 'icon', 'icon_image_path'])
        );

        // Riwayat aktivitas: masuk dan keluar dari dashboard.
        Event::listen(Login::class, fn (Login $event) => ActivityLogger::record(ActivityLog::ACTION_LOGIN, 'Akun', 'Masuk ke dashboard', $event->user));
        Event::listen(Logout::class, fn (Logout $event) => $event->user ? ActivityLogger::record(ActivityLog::ACTION_LOGOUT, 'Akun', 'Keluar dari dashboard', $event->user) : null);

        // Sidebar admin: jumlah draft sebagai penanda.
        View::composer('layouts.navigation', function ($view) {
            $view->with('draftCounts', [
                'categories' => ServiceCategory::where('is_published', false)->count(),
                'activities' => BusinessActivity::where('is_published', false)->count(),
            ]);
        });

        View::composer('pages.home', fn ($view) => $view->with(['content' => PageContents::for('home'), 'contact' => PageContents::for('contact')]));
        View::composer('services.index', fn ($view) => $view->with('content', PageContents::for('services')));
        View::composer('pages.contact', fn ($view) => $view->with('content', PageContents::for('contact')));
        View::composer('pages.about', fn ($view) => $view->with('content', PageContents::for('about')));

        // Logo, deskripsi footer, dan data kontak dikelola dari menu Kontak Kami.
        View::composer(['components.navbar', 'components.footer'], fn ($view) => $view->with('siteInfo', PageContents::for('contact')));

        View::composer(['components.navbar', 'components.footer', 'pages.home'], function ($view) {
            $view->with('navCategories', app('nav.categories'));
        });
    }
}
