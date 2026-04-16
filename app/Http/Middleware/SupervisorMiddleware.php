<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('supervisor.login');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['superviseur', 'admin'])) {
            Auth::logout();
            return redirect()->route('supervisor.login')->with('error', 'Accès réservé aux superviseurs SIM.');
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('supervisor.login')->with('error', 'Compte désactivé.');
        }

        return $next($request);
    }
}
