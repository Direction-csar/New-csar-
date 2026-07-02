<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckWorkflowRole
{
    /**
     * Rôles autorisés pour les actions workflow.
     *
     * @param  array<string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin et super_admin peuvent tout faire
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // Vérifier si le rôle de l'utilisateur est dans la liste autorisée
        if (!in_array($user->role, $roles)) {
            abort(403, 'Vous n\'avez pas les permissions nécessaires pour effectuer cette action.');
        }

        return $next($request);
    }
}
