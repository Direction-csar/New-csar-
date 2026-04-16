<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AdminLoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion Admin
     */
    protected $guard = 'admin';

    public function showLoginForm()
    {
        if (Auth::guard($this->guard)->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin-login');
    }

    /**
     * Traiter la connexion Admin
     */
    public function login(Request $request)
    {
        // Rate limiting pour la sécurité
        $key = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Trop de tentatives de connexion Admin', [
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

        if (Auth::guard($this->guard)->attempt($credentials, $remember)) {
            $user = Auth::guard($this->guard)->user();
            
            // Vérifier que l'utilisateur est bien un admin
            if ($user->role !== 'admin') {
                Auth::guard($this->guard)->logout();
                RateLimiter::hit($key, 300); // 5 minutes
                
                Log::warning('Tentative de connexion Admin avec un compte non-admin', [
                    'email' => $request->email,
                    'user_role' => $user->role,
                    'ip' => $request->ip(),
                    'timestamp' => Carbon::now()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Ces identifiants ne correspondent pas à un compte administrateur.',
                ]);
            }

            // Vérifier que le compte est actif
            if (!$user->is_active) {
                Auth::guard($this->guard)->logout();
                RateLimiter::hit($key, 300);
                
                Log::warning('Tentative de connexion Admin avec un compte inactif', [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'timestamp' => Carbon::now()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Votre compte administrateur a été désactivé.',
                ]);
            }

            // Connexion réussie
            $request->session()->regenerate();
            RateLimiter::clear($key);
            
            // Mettre à jour les informations de connexion
            $user->update([
                'last_login_at' => Carbon::now(),
            ]);

            Log::info('Connexion Admin réussie', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);

            return redirect()->intended(route('admin.dashboard'))->with('success', 'Connexion réussie !');
        }

        // Échec de la connexion
        RateLimiter::hit($key, 300);
        
        Log::warning('Échec de connexion Admin', [
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
     * Déconnexion Admin
     */
    public function logout(Request $request)
    {
        $user = Auth::guard($this->guard)->user();
        
        if ($user) {
            Log::info('Déconnexion Admin', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);
        }

        Auth::guard($this->guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}




