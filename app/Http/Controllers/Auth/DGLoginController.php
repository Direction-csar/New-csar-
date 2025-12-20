<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DGLoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion DG
     */
    public function showLoginForm()
    {
        return view('auth.dg-login');
    }

    /**
     * Traiter la connexion DG
     */
    public function login(Request $request)
    {
        // Rate limiting pour la sécurité (temporairement désactivé pour debug)
        $key = 'dg-login:' . $request->ip();
        
        // Réinitialiser le rate limit si l'email est dg@csar.sn (pour debug)
        if ($request->email === 'dg@csar.sn') {
            RateLimiter::clear($key);
        }
        
        if (RateLimiter::tooManyAttempts($key, 10)) { // Augmenté à 10 tentatives
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Trop de tentatives de connexion DG', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'seconds_remaining' => $seconds,
                'timestamp' => Carbon::now()
            ]);
            
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
            ]);
        }

        // Validation des données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        // Tentative de connexion
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Vérifier que l'utilisateur est bien un DG
            if ($user->role !== 'dg') {
                Auth::logout();
                RateLimiter::hit($key, 300); // 5 minutes
                
                Log::warning('Tentative de connexion DG avec un compte non-DG', [
                    'email' => $request->email,
                    'user_role' => $user->role,
                    'ip' => $request->ip(),
                    'timestamp' => Carbon::now()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Ces identifiants ne correspondent pas à un compte Directeur Général.',
                ]);
            }

            // Vérifier que le compte est actif
            if (!$user->is_active) {
                Auth::logout();
                RateLimiter::hit($key, 300);
                
                Log::warning('Tentative de connexion DG avec un compte inactif', [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'timestamp' => Carbon::now()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Votre compte Directeur Général a été désactivé.',
                ]);
            }

            // Connexion réussie
            $request->session()->regenerate();
            RateLimiter::clear($key);
            
            // Mettre à jour les informations de connexion
            $user->update([
                'last_login' => Carbon::now(),
                'last_login_ip' => $request->ip(),
                'last_activity' => Carbon::now()
            ]);

            Log::info('Connexion DG réussie', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);

            return redirect()->intended(route('dg.dashboard'))->with('success', 'Connexion réussie !');
        }

        // Échec de la connexion
        RateLimiter::hit($key, 300);
        
        Log::warning('Échec de connexion DG', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()
        ]);

        throw ValidationException::withMessages([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ]);
    }

    /**
     * Déconnexion DG
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('Déconnexion DG', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dg.login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}




