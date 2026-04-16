<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CTCAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Autorise les rôles: admin et ctc (Conseil Technique de la Communication)
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('ctc')->check()) {
            Log::warning('Tentative d\'accès CTC sans authentification', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'timestamp' => Carbon::now()
            ]);
            return redirect()->route('ctc.login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::guard('ctc')->user();

        if (!in_array($user->role, ['admin', 'ctc'])) {
            Log::warning('Tentative d\'accès CTC avec un rôle non autorisé', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'url' => $request->fullUrl(),
                'timestamp' => Carbon::now()
            ]);
            Auth::guard('ctc')->logout();
            return redirect()->route('ctc.login')->with('error', 'Accès refusé. Vous n\'avez pas les permissions nécessaires pour l\'espace CTC.');
        }

        if (!$user->is_active) {
            Auth::guard('ctc')->logout();
            return redirect()->route('ctc.login')->with('error', 'Votre compte a été désactivé.');
        }

        Auth::shouldUse('ctc');
        $user->update(['last_activity' => Carbon::now()]);

        return $next($request);
    }
}
