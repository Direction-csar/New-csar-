<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Contrôleur d'authentification générique pour les portails dédiés par direction
 * (CPM, DPSE, DTL, ...). Chaque direction utilise son propre guard de session,
 * mais partage la même logique de connexion.
 */
class DirectionLoginController extends Controller
{
    public const DIRECTIONS = [
        'cpm' => 'Cellule Passation des Marchés',
        'dpse' => 'Direction Planification & Suivi Évaluation',
        'dtl' => 'Direction Technique et Logistique',
    ];

    public function showLoginForm(string $direction)
    {
        $this->assertValidDirection($direction);

        if (Auth::guard($direction)->check() && in_array(Auth::guard($direction)->user()->role, [$direction, 'admin'])) {
            return redirect()->route("{$direction}.dashboard");
        }

        return view('auth.direction-login', [
            'direction' => $direction,
            'directionLabel' => self::DIRECTIONS[$direction],
        ]);
    }

    public function login(Request $request, string $direction)
    {
        $this->assertValidDirection($direction);

        $key = "{$direction}-login:" . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::guard($direction)->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::guard($direction)->user();

            if (!in_array($user->role, [$direction, 'admin'])) {
                Auth::guard($direction)->logout();
                RateLimiter::hit($key, 300);
                throw ValidationException::withMessages([
                    'email' => 'Ces identifiants ne correspondent pas à un compte ' . strtoupper($direction) . '.',
                ]);
            }

            if (!$user->is_active) {
                Auth::guard($direction)->logout();
                RateLimiter::hit($key, 300);
                throw ValidationException::withMessages([
                    'email' => 'Votre compte a été désactivé.',
                ]);
            }

            $request->session()->regenerate();
            RateLimiter::clear($key);

            return redirect()->route("{$direction}.dashboard")
                ->with('success', 'Bienvenue, ' . self::DIRECTIONS[$direction] . ' !');
        }

        RateLimiter::hit($key, 300);
        throw ValidationException::withMessages([
            'email' => 'Identifiants incorrects.',
        ]);
    }

    public function logout(Request $request, string $direction)
    {
        $this->assertValidDirection($direction);

        Auth::guard($direction)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("{$direction}.login")->with('success', 'Déconnexion réussie.');
    }

    private function assertValidDirection(string $direction): void
    {
        abort_unless(array_key_exists($direction, self::DIRECTIONS), 404);
    }
}
