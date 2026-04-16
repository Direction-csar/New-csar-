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
        if (!Auth::check()) {
            return redirect()->route('drh.login')->with('error', 'Connectez-vous pour accéder à cet espace.');
        }

        $user = Auth::user();

        if (!in_array($user->role, ['drh', 'ctc', 'admin'])) {
            Auth::logout();
            return redirect()->route('drh.login')->with('error', 'Accès réservé à la DRH.');
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('drh.login')->with('error', 'Votre compte a été désactivé.');
        }

        $user->update(['last_activity' => Carbon::now()]);

        return $next($request);
    }
}
