<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Middleware générique pour les portails dédiés par direction (CPM, DPSE, DTL, ...).
 * Autorise l'accès si l'utilisateur est connecté sur le guard admin (accès total)
 * OU sur le guard spécifique de la direction (accès restreint à sa direction).
 *
 * Usage: ->middleware('direction:cpm')
 */
class DirectionPortalMiddleware
{
    public function handle(Request $request, Closure $next, string $direction)
    {
        // 1. Session du portail dédié à la direction : prioritaire.
        //    Un utilisateur connecté via /{direction}/login reste dans
        //    SON espace, même si une session admin est également active
        //    dans le même navigateur.
        if (Auth::guard($direction)->check()) {
            $user = Auth::guard($direction)->user();

            if (!in_array($user->role, [$direction, 'admin'])) {
                Auth::guard($direction)->logout();
                return redirect()->route("{$direction}.login")->with('error', 'Accès réservé à la direction ' . strtoupper($direction) . '.');
            }

            if (!$user->is_active) {
                Auth::guard($direction)->logout();
                return redirect()->route("{$direction}.login")->with('error', 'Votre compte a été désactivé.');
            }

            Auth::shouldUse($direction);
            $user->update(['last_activity' => Carbon::now()]);

            return $next($request);
        }

        // 2. Session admin : accès total à toutes les directions
        //    (supervision, archives DFC sans portail dédié).
        if (Auth::guard('admin')->check()) {
            $adminUser = Auth::guard('admin')->user();
            if (in_array($adminUser->role, ['admin', 'super_admin', 'dg', 'directeur_general']) && $adminUser->is_active) {
                Auth::shouldUse('admin');
                return $next($request);
            }
        }

        return redirect()->route("{$direction}.login")->with('error', 'Connectez-vous pour accéder à cet espace.');
    }
}
