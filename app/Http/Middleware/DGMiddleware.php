<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DGMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::guard('dg')->check()) {
            Log::warning('Tentative d\'accès DG sans authentification', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => Carbon::now()
            ]);
            
            return redirect()->route('dg.login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::guard('dg')->user();

        // Vérifier si l'utilisateur a le rôle DG
        if ($user->role !== 'dg') {
            Log::warning('Tentative d\'accès DG avec un rôle non autorisé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => Carbon::now()
            ]);
            
            return redirect()->to('/')->with('error', 'Accès refusé. Vous n\'avez pas les permissions nécessaires.');
        }

        // Vérifier si le compte DG est actif
        if (!$user->is_active) {
            Log::warning('Tentative d\'accès DG avec un compte inactif', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);
            
            Auth::guard('dg')->logout();
            return redirect()->route('dg.login')->with('error', 'Votre compte a été désactivé.');
        }

        Auth::shouldUse('dg');

        // Log de l'accès autorisé
        Log::info('Accès DG autorisé', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'timestamp' => Carbon::now()
        ]);

        // Mettre à jour la dernière activité
        $user->update(['last_activity' => Carbon::now()]);

        return $next($request);
    }
}