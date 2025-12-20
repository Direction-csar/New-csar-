<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       TEST AUTH::ATTEMPT EXACT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$email = 'dg@csar.sn';
$password = 'password';

// Simuler exactement ce que fait le contrôleur
$credentials = [
    'email' => $email,
    'password' => $password,
];

echo "🔍 Test avec Auth::attempt()...\n";
echo "   Email: {$email}\n";
echo "   Password: {$password}\n\n";

// Test 1: Auth::attempt standard
$result1 = Auth::attempt($credentials, false);
echo "1. Auth::attempt(\$credentials, false): " . ($result1 ? '✅ RÉUSSI' : '❌ ÉCHOUÉ') . "\n";

if ($result1) {
    $user = Auth::user();
    echo "   ✅ Utilisateur authentifié: {$user->email}\n";
    echo "   Rôle: {$user->role}\n";
    Auth::logout();
} else {
    echo "   ❌ Échec de l'authentification\n";
    
    // Diagnostic
    $user = User::where('email', $email)->first();
    if ($user) {
        echo "\n   🔍 Diagnostic:\n";
        echo "   - Utilisateur existe: ✅\n";
        echo "   - Hash::check: " . (Hash::check($password, $user->password) ? '✅' : '❌') . "\n";
        echo "   - Rôle: {$user->role}\n";
        echo "   - Actif: " . ($user->is_active ? '✅' : '❌') . "\n";
        
        // Test direct avec le hash
        echo "\n   🔍 Test direct du hash:\n";
        $hash = $user->password;
        echo "   Hash (20 premiers chars): " . substr($hash, 0, 20) . "...\n";
        $directCheck = Hash::check($password, $hash);
        echo "   Hash::check('password', hash): " . ($directCheck ? '✅ OUI' : '❌ NON') . "\n";
        
        if (!$directCheck) {
            echo "\n   ⚠️  Le hash ne correspond pas!\n";
            echo "   Réinitialisation du mot de passe...\n";
            $user->password = Hash::make($password);
            $user->save();
            echo "   ✅ Mot de passe réinitialisé\n";
            
            // Réessayer
            echo "\n   🔄 Nouvelle tentative...\n";
            $result2 = Auth::attempt($credentials, false);
            echo "   Auth::attempt() après reset: " . ($result2 ? '✅ RÉUSSI' : '❌ ÉCHOUÉ') . "\n";
        }
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✅ Test terminé\n";
echo "═══════════════════════════════════════════════════════════════\n";




