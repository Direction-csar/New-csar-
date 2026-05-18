<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Utiliser la pagination CSAR personnalisée par défaut
        Paginator::defaultView('vendor.pagination.csar');

        // Forcer HTTPS en production (corrige mixed-content TinyMCE/upload)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
