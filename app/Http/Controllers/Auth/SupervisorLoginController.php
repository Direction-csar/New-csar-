<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class SupervisorLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['superviseur', 'admin'])) {
            return redirect()->route('supervisor.dashboard');
        }
        return view('auth.supervisor-login');
    }

    public function login(Request $request)
    {
        $key = 'supervisor-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required'    => "L'adresse email est requise.",
            'email.email'       => "Format d'email invalide.",
            'password.required' => 'Le mot de passe est requis.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        RateLimiter::hit($key, 300);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            Log::warning('Échec connexion Superviseur', ['email' => $request->email, 'ip' => $request->ip()]);
            throw ValidationException::withMessages([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        $user = Auth::user();

        if (!in_array($user->role, ['superviseur', 'admin'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Accès réservé aux superviseurs SIM.',
            ]);
        }

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Ce compte est désactivé.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        Log::info('Connexion Superviseur réussie', ['email' => $user->email, 'ip' => $request->ip()]);

        return redirect()->route('supervisor.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('supervisor.login');
    }
}
