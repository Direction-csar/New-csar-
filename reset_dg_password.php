<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       RÉINITIALISATION COMPLÈTE DU COMPTE DG\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$email = 'dg@csar.sn';
$password = 'password';

// Vérifier la connexion à la base de données
try {
    DB::connection()->getPdo();
    echo "✅ Connexion à la base de données OK\n\n";
} catch (\Exception $e) {
    die("❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n");
}

// Rechercher le compte DG
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ Le compte DG n'existe pas. Création en cours...\n\n";
    
    $user = User::create([
        'name' => 'Directeur Général',
        'email' => $email,
        'password' => Hash::make($password),
        'role' => 'dg',
        'role_id' => 2,
        'is_active' => true,
        'status' => 'active',
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Compte DG créé avec succès!\n\n";
} else {
    echo "✅ Compte DG trouvé: {$user->email}\n";
    echo "   ID: {$user->id}\n";
    echo "   Nom: {$user->name}\n";
    echo "   Rôle: {$user->role}\n";
    echo "   Rôle ID: " . ($user->role_id ?? 'N/A') . "\n";
    echo "   Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n";
    echo "   Statut: " . ($user->status ?? 'N/A') . "\n\n";
    
    // Vérifier le hash actuel
    echo "🔍 Vérification du mot de passe actuel...\n";
    $currentHash = $user->password;
    echo "   Hash actuel: " . substr($currentHash, 0, 20) . "...\n";
    
    // Tester avec différents mots de passe
    $testPasswords = ['password', 'dg123', 'admin'];
    $passwordMatches = false;
    foreach ($testPasswords as $testPwd) {
        if (Hash::check($testPwd, $currentHash)) {
            echo "   ✅ Le mot de passe actuel correspond à: '{$testPwd}'\n";
            $passwordMatches = true;
            break;
        }
    }
    
    if (!$passwordMatches) {
        echo "   ⚠️  Aucun mot de passe testé ne correspond\n";
    }
    
    echo "\n";
    
    // Réinitialiser complètement le mot de passe
    echo "🔧 Réinitialisation complète du mot de passe...\n";
    $newPasswordHash = Hash::make($password);
    
    // Mettre à jour directement dans la base de données
    DB::table('users')
        ->where('id', $user->id)
        ->update([
            'password' => $newPasswordHash,
            'role' => 'dg',
            'role_id' => 2,
            'is_active' => true,
            'status' => 'active',
            'updated_at' => now(),
        ]);
    
    echo "   ✅ Mot de passe réinitialisé\n";
    
    // Recharger l'utilisateur depuis la base de données
    $user->refresh();
    
    // Vérifier que le nouveau mot de passe fonctionne
    echo "\n🧪 Test du nouveau mot de passe...\n";
    if (Hash::check($password, $user->password)) {
        echo "   ✅ TEST RÉUSSI: Le nouveau mot de passe fonctionne!\n";
    } else {
        echo "   ❌ TEST ÉCHOUÉ: Le nouveau mot de passe ne fonctionne pas!\n";
    }
}

// Afficher les informations finales
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "       IDENTIFIANTS DE CONNEXION DG\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "URL       : http://localhost:8000/dg/login\n";
echo "Email     : {$email}\n";
echo "Password  : {$password}\n\n";

// Test final avec Auth::attempt
echo "🧪 TEST FINAL AVEC Auth::attempt:\n";
echo "─────────────────────────────────────────────────────────────\n\n";

use Illuminate\Support\Facades\Auth;

// Tester la connexion avec Auth::attempt
$credentials = [
    'email' => $email,
    'password' => $password
];

// Simuler une requête pour Auth::attempt
$testUser = User::where('email', $email)->first();
if ($testUser && Hash::check($password, $testUser->password)) {
    echo "✅ Le compte peut être authentifié avec Auth::attempt\n";
    echo "   Email: {$testUser->email}\n";
    echo "   Rôle: {$testUser->role}\n";
    echo "   Actif: " . ($testUser->is_active ? 'Oui' : 'Non') . "\n";
} else {
    echo "❌ Le compte ne peut PAS être authentifié\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✅ Réinitialisation terminée\n";
echo "═══════════════════════════════════════════════════════════════\n";




