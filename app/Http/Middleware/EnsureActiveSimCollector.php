<?php

namespace App\Http\Middleware;

use App\Models\SimCollector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API mobile SIM : le jeton Sanctum doit appartenir à un collecteur actif.
 * Un compte suspendu après connexion perd immédiatement l'accès, et un
 * jeton de personnel (users) ne donne pas accès aux endpoints collecteur.
 */
class EnsureActiveSimCollector
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof SimCollector || !$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux collecteurs SIM actifs.',
            ], 403);
        }

        return $next($request);
    }
}
