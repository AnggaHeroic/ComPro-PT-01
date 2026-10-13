<?php

namespace App\Providers;

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

        View::composer(['components.navbar', 'components.footer', 'pages.home'], function ($view) {
            $view->with('navCategories', app('nav.categories'));
        });
    }
}
