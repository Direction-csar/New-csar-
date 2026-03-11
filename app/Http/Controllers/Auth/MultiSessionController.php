<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class MultiSessionController extends Controller
{
    /**
     * Afficher la page de connexion avec sélection d'interface
     */
    public function showLoginForm(Request $request)
    {
        // Déterminer l'interface demandée
        $interface = $this->getInterfaceFromRequest($request);
        
        return view('auth.login', compact('interface'));
    }
    
    /**
     * Traiter la connexion pour une interface spécifique
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $credentials = $request->only('email', 'password');
        $interface = $request->input('interface', 'admin');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Vérifier que l'utilisateur a le bon rôle pour cette interface
            if ($this->canAccessInterface($user, $interface)) {
                // Créer une session spécifique pour cette interface
                $this->createInterfaceSession($user, $interface);
                
                // Rediriger vers l'interface appropriée
                return $this->redirectToInterface($interface);
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Vous n\'avez pas les permissions pour accéder à cette interface.',
                ]);
            }
        }
        
        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ]);
    }
    
    /**
     * Déconnexion d'une interface spécifique
     */
    public function logout(Request $request)
    {
        $interface = $this->getInterfaceFromRequest($request);
        
        // Nettoyer la session de cette interface
        $this->clearInterfaceSession($interface);
        
        Auth::logout();
        Session::flush();
        
        return redirect()->route('login')->with('success', 'Déconnexion réussie de l\'interface ' . ucfirst($interface));
    }
    
    /**
     * Déterminer l'interface à partir de la requête
     */
    private function getInterfaceFromRequest(Request $request): string
    {
        $path = $request->path();
        
        if (str_starts_with($path, 'admin')) {
            return 'admin';
        } elseif (str_starts_with($path, 'dg')) {
            return 'dg';
        } elseif (str_starts_with($path, 'responsable')) {
            return 'responsable';
        } elseif (str_starts_with($path, 'agent')) {
            return 'agent';
        } else {
            return 'admin'; // Interface par défaut
        }
    }
    
    /**
     * Vérifier si l'utilisateur peut accéder à l'interface
     */
    private function canAccessInterface(User $user, string $interface): bool
    {
        $allowedRoles = match($interface) {
            'admin' => ['admin'],
            'dg' => ['dg', 'admin'],
            'responsable' => ['responsable', 'admin'],
            'agent' => ['agent', 'admin'],
            default => ['admin', 'dg', 'responsable', 'agent']
        };
        
        return in_array($user->role, $allowedRoles);
    }
    
    /**
     * Créer une session spécifique pour l'interface
     */
    private function createInterfaceSession(User $user, string $interface): void
    {
        $sessionKey = 'csar_' . $interface . '_session';
        Session::put($sessionKey, [
            'user_id' => $user->id,
            'interface' => $interface,
            'logged_in_at' => now(),
        ]);
    }
    
    /**
     * Nettoyer la session d'une interface
     */
    private function clearInterfaceSession(string $interface): void
    {
        $sessionKey = 'csar_' . $interface . '_session';
        Session::forget($sessionKey);
    }
    
    /**
     * Rediriger vers l'interface appropriée
     */
    private function redirectToInterface(string $interface): \Illuminate\Http\RedirectResponse
    {
        return match($interface) {
            'responsable', 'agent' => redirect('/'),
            default => redirect()->route('home')
        };
    }
    
    /**
     * Vérifier si l'utilisateur est connecté à une interface spécifique
     */
    public function isLoggedInToInterface(string $interface): bool
    {
        $sessionKey = 'csar_' . $interface . '_session';
        return Session::has($sessionKey) && Auth::check();
    }
    
    /**
     * Obtenir la liste des interfaces où l'utilisateur est connecté
     */
    public function getActiveInterfaces(): array
    {
        $interfaces = ['admin', 'dg', 'responsable', 'agent'];
        $activeInterfaces = [];
        
        foreach ($interfaces as $interface) {
            if ($this->isLoggedInToInterface($interface)) {
                $activeInterfaces[] = $interface;
            }
        }
        
        return $activeInterfaces;
    }
}
