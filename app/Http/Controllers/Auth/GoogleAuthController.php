<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    /**
     * Rediriger vers Google pour l'authentification
     */
    public function redirect()
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            Log::warning('Google OAuth non configuré - GOOGLE_CLIENT_ID et GOOGLE_CLIENT_SECRET requis dans .env');
            return redirect()->route('admin.login')
                ->with('error', 'Connexion Google non configurée. Utilisez le formulaire classique.');
        }

        return Socialite::driver('google')
            ->scopes(['email', 'profile'])
            ->redirect();
    }

    /**
     * Gérer le callback Google après authentification
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Erreur Google OAuth callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('admin.login')
                ->with('error', 'Échec de la connexion avec Google. Veuillez réessayer.');
        }

        $email = $googleUser->getEmail();

        // Vérifier si l'email est autorisé (liste des admins)
        $allowedEmails = array_filter(array_map('trim', explode(',', env('ADMIN_ALLOWED_EMAILS', ''))));
        if (!empty($allowedEmails) && !in_array($email, $allowedEmails)) {
            Log::warning('Tentative de connexion Google avec email non autorisé', [
                'email' => $email,
                'allowed' => $allowedEmails,
            ]);
            return redirect()->route('admin.login')
                ->with('error', 'Votre adresse email n\'est pas autorisée à accéder à l\'administration.');
        }

        // Chercher ou créer l'utilisateur admin
        $user = User::where('email', $email)->first();

        if ($user) {
            // Mettre à jour google_id et avatar si nécessaire
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'last_login_at' => Carbon::now(),
            ]);
        } else {
            // Créer un nouvel admin (uniquement si l'email est dans la liste ou si la liste est vide pour le premier admin)
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getEmail(),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(32)),
                'role' => 'admin',
                'status' => 'actif',
                'is_active' => true,
                'last_login_at' => Carbon::now(),
            ]);
        }

        // Vérifier que l'utilisateur a le rôle admin
        if ($user->role !== 'admin') {
            Log::warning('Connexion Google avec compte non-admin', ['email' => $email, 'role' => $user->role]);
            return redirect()->route('admin.login')
                ->with('error', 'Ces identifiants ne correspondent pas à un compte administrateur.');
        }

        if (!$user->is_active) {
            return redirect()->route('admin.login')
                ->with('error', 'Votre compte administrateur a été désactivé.');
        }

        Auth::login($user, true);

        Log::info('Connexion Admin via Google réussie', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->intended(route('admin.dashboard'))->with('success', 'Connexion réussie !');
    }
}
