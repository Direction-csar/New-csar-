<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\DirectionLoginController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Autorise les routes partagées entre portails (ex : gestion des dossiers
 * d'archives) à tout utilisateur authentifié sur un portail direction
 * (CPM, DPSE, DTL, Inspecteur, Comptable, DRH) OU sur le guard admin.
 *
 * Usage : ->middleware('any-portal')
 */
class AnyPortalMiddleware
{
    /**
     * Guards de portail acceptés : toutes les directions dédiées + DRH.
     */
    private const PORTAL_GUARDS = [
        'cpm', 'dpse', 'dtl', 'inspecteur', 'comptable', 'drh',
    ];

    public function handle(Request $request, Closure $next)
    {
        // 1. Sessions des portails direction (prioritaires, chacun dans son espace)
        foreach (self::PORTAL_GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if (!$user->is_active) {
                    Auth::guard($guard)->logout();
                    continue;
                }

                Auth::shouldUse($guard);
                $user->update(['last_activity' => Carbon::now()]);

                return $next($request);
            }
        }

        // 2. Session admin : accès de supervision
        if (Auth::guard('admin')->check()) {
            $adminUser = Auth::guard('admin')->user();
            if (in_array($adminUser->role, ['admin', 'super_admin'], true) && $adminUser->is_active) {
                Auth::shouldUse('admin');
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(401);
        }

        return redirect()->back()->with('error', 'Connectez-vous pour effectuer cette action.');
    }
}
