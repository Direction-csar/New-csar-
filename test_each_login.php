<?php
/**
 * Test de chaque connexion pour identifier le problème
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       TEST DE CHAQUE CONNEXION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$comptes = [
    ['email' => 'dg@csar.sn', 'role' => 'dg', 'route' => 'dg.test.login', 'dashboard' => 'dg.dashboard'],
    ['email' => 'entrepot@csar.sn', 'role' => 'responsable', 'route' => 'entrepot.test.login', 'dashboard' => 'responsable.dashboard'],
    ['email' => 'drh@csar.sn', 'role' => 'drh', 'route' => 'drh.test.login', 'dashboard' => 'drh.dashboard'],
];

foreach ($comptes as $compte) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "TEST: " . strtoupper($compte['role']) . " ({$compte['email']})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // 1. Vérifier que l'utilisateur existe
    $user = User::where('email', $compte['email'])->first();
    if (!$user) {
        echo "   ❌ Utilisateur non trouvé\n\n";
        continue;
    }
    echo "   ✅ Utilisateur trouvé (ID: {$user->id})\n";
    echo "   - Rôle: {$user->role}\n";
    echo "   - Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n";
    
    // 2. Vérifier le mot de passe
    $passwordCheck = Hash::check('password', $user->password);
    echo "   - Mot de passe: " . ($passwordCheck ? '✅ OK' : '❌ NOK') . "\n";
    
    // 3. Vérifier la route de test
    $routeExists = Route::has($compte['route']);
    echo "   - Route '{$compte['route']}': " . ($routeExists ? '✅ Existe' : '❌ Manquante') . "\n";
    
    // 4. Vérifier la route dashboard
    $dashboardExists = Route::has($compte['dashboard']);
    echo "   - Route '{$compte['dashboard']}': " . ($dashboardExists ? '✅ Existe' : '❌ Manquante') . "\n";
    
    // 5. Test Auth::attempt
    Auth::logout();
    $credentials = ['email' => $compte['email'], 'password' => 'password'];
    $attempt = Auth::attempt($credentials);
    echo "   - Auth::attempt: " . ($attempt ? '✅ Réussi' : '❌ Échoué') . "\n";
    
    if ($attempt) {
        Auth::logout();
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "Vérification terminée\n";
echo "═══════════════════════════════════════════════════════════════\n";




