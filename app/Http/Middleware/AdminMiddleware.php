<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminMiddleware
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
        if (!Auth::guard('admin')->check()) {
            Log::warning('Tentative d\'accès Admin sans authentification', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => Carbon::now()
            ]);
            
            return redirect()->route('admin.login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::guard('admin')->user();

        // Vérifier si l'utilisateur a le rôle admin
        if ($user->role !== 'admin') {
            Log::warning('Tentative d\'accès Admin avec un rôle non autorisé', [
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

        // Vérifier si le compte admin est actif
        if (!$user->is_active) {
            Log::warning('Tentative d\'accès Admin avec un compte inactif', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);
            
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Votre compte a été désactivé.');
        }

        // Log de l'accès autorisé
        Log::info('Accès Admin autorisé', [
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