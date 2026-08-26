<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Mail\BrevoApiTransport;

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
        Mail::extend('brevo-api', function () {
            return new BrevoApiTransport();
        });

        // Utiliser la pagination CSAR personnalisée par défaut
        Paginator::defaultView('vendor.pagination.csar');

        // Forcer HTTPS en production (corrige mixed-content TinyMCE/upload)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Partager la route courante avec toutes les vues utilisant le layout DRH
        View::composer('layouts.drh-portal', function ($view) {
            $view->with('currentRoute', request()->route()?->getName());
        });

        // Directive Blade @lazyImage pour images optimisées (lazy-loading + dimensions)
        // Usage : @lazyImage('/storage/images/photo.jpg', 'Description', ['class' => 'w-full', 'width' => 800, 'height' => 600])
        Blade::directive('lazyImage', function ($expression) {
            return "<?php
                \$args = [{$expression}];
                \$src = \$args[0] ?? '';
                \$alt = \$args[1] ?? '';
                \$attrs = \$args[2] ?? [];
                \$attrStr = '';
                foreach (\$attrs as \$k => \$v) {
                    \$attrStr .= ' ' . \$k . '=\"' . htmlspecialchars((string) \$v, ENT_QUOTES) . '\"';
                }
                echo '<img src=\"' . e(\$src) . '\" alt=\"' . e(\$alt) . '\" loading=\"lazy\" decoding=\"async\"' . \$attrStr . '>';
            ?>";
        });
    }
}
