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

        // Définir le répertoire temporaire pour contourner le problème PHP-FPM
        putenv('TMPDIR=' . storage_path('tmp'));
        putenv('TMP=' . storage_path('tmp'));
        putenv('TEMP=' . storage_path('tmp'));

        // Créer le répertoire temporaire s'il n'existe pas
        $tmpDir = storage_path('tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
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
