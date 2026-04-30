<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class CTCLoginController extends Controller
{
    /**
     * Réinitialiser la limite de tentatives de connexion pour l'IP actuelle
     */
    public function resetRateLimit(Request $request)
    {
        $key = 'ctc-login:' . $request->ip();
        RateLimiter::clear($key);
        return redirect()->route('ctc.login')->with('success', 'Limite réinitialisée. Vous pouvez réessayer de vous connecter.');
    }

    /**
     * Afficher le formulaire de connexion CTC
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('ctc')->check()) {
            return redirect()->route('ctc.dashboard');
        }
        return view('auth.ctc-login');
    }

    /**
     * Traiter la connexion CTC (Conseil Technique de la Communication)
     */
    public function login(Request $request)
    {
        $key = 'ctc-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('ctc')->attempt($credentials, $remember)) {
            $user = Auth::guard('ctc')->user();

            if ($user->role !== 'ctc') {
                Auth::guard('ctc')->logout();
                RateLimiter::hit($key, 300);
                throw ValidationException::withMessages([
                    'email' => 'Ces identifiants ne correspondent pas à un compte CTC. Utilisez le bon portail de connexion.',
                ]);
            }

            if (!$user->is_active) {
                Auth::guard('ctc')->logout();
                RateLimiter::hit($key, 300);
                throw ValidationException::withMessages([
                    'email' => 'Votre compte a été désactivé.',
                ]);
            }

            $request->session()->regenerate();
            RateLimiter::clear($key);

            $user->update(['last_login_at' => Carbon::now()]);

            Log::info('Connexion CTC réussie', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);

            return redirect()->route('ctc.dashboard')->with('success', 'Connexion réussie !');
        }

        RateLimiter::hit($key, 300);
        throw ValidationException::withMessages([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ]);
    }

    /**
     * Déconnexion CTC
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('ctc')->user();
        if ($user) {
            Log::info('Déconnexion CTC', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);
        }

        Auth::guard('ctc')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ctc.login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
