<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DRHMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('drh')->check()) {
            return redirect()->route('drh.login')->with('error', 'Connectez-vous pour accéder à cet espace.');
        }

        $user = Auth::guard('drh')->user();

        if (!in_array($user->role, ['drh', 'ctc', 'admin'])) {
            Auth::guard('drh')->logout();
            return redirect()->route('drh.login')->with('error', 'Accès réservé à la DRH.');
        }

        if (!$user->is_active) {
            Auth::guard('drh')->logout();
            return redirect()->route('drh.login')->with('error', 'Votre compte a été désactivé.');
        }

        Auth::shouldUse('drh');
        $user->update(['last_activity' => Carbon::now()]);

        return $next($request);
    }
}
