<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Afficher le formulaire de demande de réinitialisation
     */
    public function showLinkRequestForm(Request $request)
    {
        $backUrl = $this->getBackUrl($request);
        $view = $this->getViewName($request);
        
        return view($view, compact('backUrl'));
    }

    /**
     * Envoyer le lien de réinitialisation
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.exists' => 'Cette adresse email n\'existe pas dans notre système.'
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Même si l'utilisateur n'existe pas, on affiche le même message pour la sécurité
            return back()->with('status', 'Si cet email existe, un lien de réinitialisation vous a été envoyé.');
        }

        // Générer un token sécurisé
        $token = Str::random(64);
        
        // Supprimer les anciens tokens
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        
        // Insérer le nouveau token (valide 1 heure)
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        // Envoyer l'email
        $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($email));
        
        // En développement, afficher le lien directement
        if (config('app.env') === 'local') {
            // Log du lien de réinitialisation
            Log::info('Password reset link generated (DEV MODE)', [
                'email' => $email,
                'user_id' => $user->id,
                'reset_url' => $resetUrl,
                'ip' => $request->ip()
            ]);

            // Stocker le lien dans la session pour l'afficher
            return back()->with([
                'status' => 'Lien de réinitialisation généré avec succès ! (Mode développement)',
                'reset_url' => $resetUrl
            ]);
        }
        
        // En production, envoyer l'email
        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'token' => $token
            ], function ($message) use ($user, $email) {
                $message->to($email, $user->name)
                        ->subject('Réinitialisation de votre mot de passe – CSAR');
            });

            // Log de l'envoi
            Log::info('Password reset email sent', [
                'email' => $email,
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return back()->with('status', 'Si cet email existe, un lien de réinitialisation vous a été envoyé.');
            
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('status', 'Si cet email existe, un lien de réinitialisation vous a été envoyé.');
        }
    }

    /**
     * Afficher le formulaire de réinitialisation
     */
    public function showResetForm(Request $request, $token = null)
    {
        $email = $request->query('email');
        
        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
        ], [
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.exists' => 'Cette adresse email n\'existe pas dans notre système.'
        ]);

        $email = $request->email;
        $token = $request->token;
        
        // Vérifier le token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'Le token de réinitialisation est invalide ou a expiré.']);
        }

        // Vérifier que le token n'a pas plus d'1 heure
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['email' => 'Le token de réinitialisation a expiré.']);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Log de la réinitialisation
        Log::info('Password reset completed', [
            'email' => $email,
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé avec succès.');
    }

    /**
     * Déterminer l'URL de retour basée sur le referer
     */
    private function getBackUrl(Request $request)
    {
        $referer = $request->header('referer');
        
        if ($referer) {
            if (strpos($referer, '/admin/login') !== false) {
                return route('admin.login');
            } elseif (strpos($referer, '/dg/login') !== false) {
                return route('dg.login');
            } else            if (strpos($referer, '/drh/login') !== false) {
                return url('/');
            } elseif (strpos($referer, '/agent/login') !== false) {
                return url('/');
            } elseif (strpos($referer, '/responsable/login') !== false) {
                return url('/');
            }
        }
        
        // Par défaut, retourner vers admin login
        return route('admin.login');
    }

    /**
     * Déterminer le nom de la vue basée sur le referer
     */
    private function getViewName(Request $request)
    {
        $referer = $request->header('referer');
        
        if ($referer) {
            if (strpos($referer, '/admin/login') !== false) {
                return 'auth.admin-forgot-password';
            } elseif (strpos($referer, '/dg/login') !== false) {
                return 'auth.dg-forgot-password';
            } elseif (strpos($referer, '/drh/login') !== false || strpos($referer, '/agent/login') !== false || strpos($referer, '/responsable/login') !== false) {
                return 'auth.interface-desactivee';
            }
        }
        
        // Par défaut, utiliser la vue admin
        return 'auth.admin-forgot-password';
    }
}







