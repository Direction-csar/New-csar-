<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Refuse tout appel API d'un compte désactivé, même avec un token encore valide.
 */
class EnsureActiveApiUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && !$user->is_active) {
            $user->tokens()->delete();

            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        return $next($request);
    }
}
