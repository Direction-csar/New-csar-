<?php

namespace App\Http\Middleware;

use App\Models\SimCollector;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège les endpoints de géolocalisation des collecteurs.
 *
 * Accès autorisé :
 * - Token Sanctum détenu par un SimCollector ou un User à rôle staff
 * - Session portail staff (guards 'admin' et 'supervisor')
 *
 * Le guard 'sanctum' résout aussi les sessions 'web' (SPA stateful) :
 * on vérifie donc le TYPE de l'acteur, pas seulement le guard.
 * Toute autre requête (anonyme ou citoyen) reçoit 401 en JSON —
 * jamais de redirection HTML, ces endpoints sont consommés par
 * fetch / application mobile.
 */
class CollectorTrackingAuth
{
    /** Guards de session autorisés (rôles staff uniquement). */
    private const SESSION_GUARDS = ['admin', 'supervisor'];

    /** Rôles staff autorisés lorsque l'acteur est un User (token ou session). */
    private const STAFF_ROLES = [
        'admin', 'super_admin', 'supervisor',
        'dg', 'directeur_general', 'drh', 'ctc',
        'cpm', 'dpse', 'dtl',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Token Bearer Sanctum (app collecteur ou personnel API)
        //    Le guard sanctum retombe aussi sur la session 'web' : on filtre l'acteur.
        $sanctumUser = Auth::guard('sanctum')->user();
        if ($sanctumUser && $this->isAuthorizedActor($sanctumUser)) {
            Auth::shouldUse('sanctum');
            return $next($request);
        }

        // 2. Session staff (dashboard superviseur, admin)
        foreach (self::SESSION_GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Authentification requise.',
        ], 401);
    }

    /**
     * Collecteur terrain : toujours autorisé (accès restreint à sa propre
     * position dans le contrôleur). User : uniquement les rôles staff —
     * un citoyen authentifié ne lit ni n'écrit les positions.
     */
    private function isAuthorizedActor(mixed $actor): bool
    {
        if ($actor instanceof SimCollector) {
            return true;
        }

        if ($actor instanceof User) {
            return in_array($actor->role, self::STAFF_ROLES, true);
        }

        return false;
    }
}
