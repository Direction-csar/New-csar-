<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Force l'activation de la 2FA pour les rôles sensibles (admin/dg/drh/ctc).
 *
 * À placer APRÈS le middleware d'authentification du guard concerné.
 * Si l'utilisateur n'a pas activé la 2FA, il est redirigé vers la page de setup.
 *
 * Usage : route middleware → 'enforce.2fa:admin'
 */
class EnforceTwoFactor
{
    /**
     * Mapping guard → route de setup 2FA.
     */
    private const SETUP_ROUTES = [
        'admin' => 'admin.2fa.setup',
        'dg'    => 'dg.2fa.setup',
        'drh'   => 'drh.2fa.setup',
        'ctc'   => 'ctc.2fa.setup',
    ];

    public function handle(Request $request, Closure $next, string $guard = 'admin')
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return $next($request);
        }

        // Déjà activée → on laisse passer
        if (!empty($user->two_factor_enabled) && !empty($user->two_factor_secret)) {
            return $next($request);
        }

        // Routes liées au setup/recovery 2FA → toujours autorisées (sinon boucle)
        $allowedRoutes = [
            $guard . '.2fa.setup',
            $guard . '.2fa.enable',
            $guard . '.2fa.recovery',
            $guard . '.2fa.disable',
            $guard . '.logout',
        ];

        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, $allowedRoutes, true)) {
            return $next($request);
        }

        // Redirection vers la page de configuration 2FA
        $setupRoute = self::SETUP_ROUTES[$guard] ?? null;
        if (!$setupRoute) {
            return $next($request);
        }

        return redirect()->route($setupRoute)->with(
            'warning',
            'Pour des raisons de sécurité, vous devez activer la double authentification (2FA) avant de continuer.'
        );
    }
}
