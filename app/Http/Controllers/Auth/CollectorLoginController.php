<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SimCollector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CollectorLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.collector-login');
    }

    public function login(Request $request)
    {
        $key = 'collector-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'email est requis.',
            'password.required' => 'Le mot de passe est requis.',
        ]);

        $collector = SimCollector::where('email', $request->email)->first();

        if (!$collector || !password_verify($request->password, $collector->password_hash)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        if (!$collector->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte n\'est pas actif. Contactez l\'administrateur.',
            ]);
        }

        RateLimiter::clear($key);

        session([
            'collector_id' => $collector->id,
            'collector_name' => $collector->name,
        ]);

        return redirect()->intended(route('collector.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['collector_id', 'collector_name']);
        return redirect()->route('collector.login');
    }
}
