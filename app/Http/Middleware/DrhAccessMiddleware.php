<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DrhAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            Log::warning('Accès DRH refusé - non authentifié', ['ip' => $request->ip()]);
            return redirect()->route('admin.login')->with('error', 'Veuillez vous connecter.');
        }

        $allowed = ['admin', 'super_admin', 'drh'];
        if (!in_array($user->role, $allowed)) {
            Log::warning('Accès DRH refusé - rôle insuffisant', [
                'user_id' => $user->id,
                'role' => $user->role,
                'ip' => $request->ip(),
            ]);
            return redirect()->route('admin.dashboard')->with('error', 'Accès réservé à la Direction des Ressources Humaines.');
        }

        return $next($request);
    }
}
