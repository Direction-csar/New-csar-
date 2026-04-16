<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DRHLoginController extends Controller
{
    protected $guard = 'drh';

    public function showLoginForm()
    {
        if (Auth::guard($this->guard)->check() && in_array(Auth::guard($this->guard)->user()->role, ['drh', 'ctc', 'admin'])) {
            return redirect()->route('admin.drh.tabaski.index');
        }
        return view('auth.drh-login');
    }

    public function login(Request $request)
    {
        $key = 'drh-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::guard($this->guard)->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::guard($this->guard)->user();

            if (!in_array($user->role, ['drh', 'ctc', 'admin'])) {
                Auth::guard($this->guard)->logout();
                RateLimiter::hit($key, 300);
                throw ValidationException::withMessages([
                    'email' => 'Ces identifiants ne correspondent pas à un compte DRH.',
                ]);
            }

            if (!$user->is_active) {
                Auth::guard($this->guard)->logout();
                RateLimiter::hit($key, 300);
                throw ValidationException::withMessages([
                    'email' => 'Votre compte a été désactivé.',
                ]);
            }

            $request->session()->regenerate();
            RateLimiter::clear($key);

            return redirect()->route('admin.drh.tabaski.index')
                ->with('success', 'Bienvenue, Direction des Ressources Humaines !');
        }

        RateLimiter::hit($key, 300);
        throw ValidationException::withMessages([
            'email' => 'Identifiants incorrects.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard($this->guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('drh.login')->with('success', 'Déconnexion réussie.');
    }
}
