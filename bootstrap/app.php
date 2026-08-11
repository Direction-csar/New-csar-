<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::middleware('api')
                ->prefix('mobile')
                ->group(base_path('routes/mobile-api.php'));

            Route::middleware('api')
                ->prefix('api/warehouse')
                ->group(base_path('routes/warehouse-api.php'));
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Dans Laravel 12, les groupes "web" et "api" sont déjà prédéfinis
        // Nous ajoutons nos middleware personnalisés à ces groupes existants
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->api(append: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Alias pour les middlewares personnalisés
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'dg' => \App\Http\Middleware\DGMiddleware::class,
            'drh-access' => \App\Http\Middleware\DRHMiddleware::class,
            'collector' => \App\Http\Middleware\CollectorMiddleware::class,
            'ctc-admin' => \App\Http\Middleware\CTCAdminMiddleware::class,
            'supervisor' => \App\Http\Middleware\SupervisorMiddleware::class,
            'enforce.2fa' => \App\Http\Middleware\EnforceTwoFactor::class,
            'http-cache' => \App\Http\Middleware\HttpCache::class,
            'workflow.role' => \App\Http\Middleware\CheckWorkflowRole::class,
            'direction' => \App\Http\Middleware\DirectionPortalMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Intégration Sentry (capture des exceptions en production)
        // Activée uniquement si le DSN est configuré dans .env (SENTRY_LARAVEL_DSN)
        if (app()->bound('sentry') && config('sentry.dsn')) {
            $exceptions->reportable(function (\Throwable $e) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            });
        }

        // Notification email des erreurs critiques en production (sans Sentry)
        if (app()->environment('production') && !app()->bound('sentry')) {
            $exceptions->reportable(function (\Throwable $e) {
                if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException
                    || $e instanceof \Illuminate\Validation\ValidationException
                    || $e instanceof \Illuminate\Auth\AuthenticationException) {
                    return;
                }

                try {
                    $adminEmail = config('mail.admin_email', 'admin@csar.sn');
                    \Illuminate\Support\Facades\Mail::raw(
                        "Erreur critique sur " . config('app.name') . "\n\n"
                        . "Type: " . get_class($e) . "\n"
                        . "Message: " . $e->getMessage() . "\n"
                        . "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n"
                        . "URL: " . request()->fullUrl() . "\n"
                        . "IP: " . request()->ip() . "\n"
                        . "Timestamp: " . now()->toISOString(),
                        function ($message) use ($adminEmail) {
                            $message->to($adminEmail)
                                ->subject('[CSAR] Erreur critique - ' . now()->format('d/m/Y H:i'));
                        }
                    );
                } catch (\Throwable $mailError) {
                    \Illuminate\Support\Facades\Log::error('Failed to send critical error email', [
                        'error' => $mailError->getMessage(),
                    ]);
                }
            });
        }
    })->create();
