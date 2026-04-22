<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Permettre l'accès aux admins (guard 'admin')
        if (Auth::guard('admin')->check()) {
            $adminUser = Auth::guard('admin')->user();
            if ($adminUser && $adminUser->role === 'admin' && $adminUser->is_active) {
                return $next($request);
            }
        }

        if (!Auth::guard('supervisor')->check()) {
            return redirect()->route('supervisor.login');
        }

        $user = Auth::guard('supervisor')->user();

        if (!in_array($user->role, ['superviseur', 'admin'])) {
            Auth::guard('supervisor')->logout();
            return redirect()->route('supervisor.login')->with('error', 'Accès réservé aux superviseurs SIM.');
        }

        if (!$user->is_active) {
            Auth::guard('supervisor')->logout();
            return redirect()->route('supervisor.login')->with('error', 'Compte désactivé.');
        }

        Auth::shouldUse('supervisor');
        return $next($request);
    }
}
