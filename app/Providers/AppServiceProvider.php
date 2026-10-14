<?php

namespace App\Providers;

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
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

        // Sidebar admin: jumlah draft sebagai penanda.
        View::composer('layouts.navigation', function ($view) {
            $view->with('draftCounts', [
                'categories' => ServiceCategory::where('is_published', false)->count(),
                'activities' => BusinessActivity::where('is_published', false)->count(),
            ]);
        });

        View::composer(['components.navbar', 'components.footer', 'pages.home'], function ($view) {
            $view->with('navCategories', app('nav.categories'));
        });
    }
}
