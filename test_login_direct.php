<?php
/**
 * Script de test direct de connexion DG
 * Simule exactement ce que fait le formulaire
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       TEST DE CONNEXION DIRECTE DG\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$email = 'dg@csar.sn';
$password = 'password';

// 1. Vérifier l'utilisateur dans la DB
echo "1️⃣ Vérification directe dans la base de données...\n";
$dbUser = DB::table('users')->where('email', $email)->first();

if (!$dbUser) {
    echo "   ❌ Utilisateur non trouvé dans la DB!\n";
    echo "   Création du compte...\n";
    
    $hashedPassword = Hash::make($password);
    $userId = DB::table('users')->insertGetId([
        'name' => 'Directeur Général',
        'email' => $email,
        'password' => $hashedPassword,
        'role' => 'dg',
        'role_id' => 2,
        'is_active' => 1,
        'status' => 'active',
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "   ✅ Compte créé avec ID: {$userId}\n";
    $dbUser = DB::table('users')->where('id', $userId)->first();
} else {
    echo "   ✅ Utilisateur trouvé: {$dbUser->email}\n";
    echo "   ID: {$dbUser->id}\n";
    echo "   Rôle: {$dbUser->role}\n";
    echo "   Actif: " . ($dbUser->is_active ? 'Oui' : 'Non') . "\n";
}

// 2. Vérifier et corriger le mot de passe
echo "\n2️⃣ Vérification du mot de passe...\n";
$dbHash = $dbUser->password;
$passwordCheck = Hash::check($password, $dbHash);

if (!$passwordCheck) {
    echo "   ⚠️  Le mot de passe ne correspond pas!\n";
    echo "   Réinitialisation...\n";
    
    $newHash = Hash::make($password);
    DB::table('users')
        ->where('id', $dbUser->id)
        ->update([
            'password' => $newHash,
            'role' => 'dg',
            'role_id' => 2,
            'is_active' => 1,
            'status' => 'active',
            'updated_at' => now(),
        ]);
    
    echo "   ✅ Mot de passe réinitialisé\n";
    $dbUser = DB::table('users')->where('id', $dbUser->id)->first();
    $passwordCheck = Hash::check($password, $dbUser->password);
}

echo "   Hash check: " . ($passwordCheck ? '✅ OUI' : '❌ NON') . "\n";

// 3. Test avec le modèle User
echo "\n3️⃣ Test avec le modèle User...\n";
$user = User::find($dbUser->id);

if ($user) {
    echo "   ✅ Modèle User chargé\n";
    echo "   Email: {$user->email}\n";
    echo "   Rôle: {$user->role}\n";
    
    // Test du hash avec le modèle
    $modelPasswordCheck = Hash::check($password, $user->password);
    echo "   Hash check avec modèle: " . ($modelPasswordCheck ? '✅ OUI' : '❌ NON') . "\n";
} else {
    echo "   ❌ Impossible de charger le modèle User\n";
}

// 4. Test Auth::attempt
echo "\n4️⃣ Test Auth::attempt()...\n";
$credentials = [
    'email' => $email,
    'password' => $password,
];

// Nettoyer toute session existante
Auth::logout();

$attempt = Auth::attempt($credentials, false);

if ($attempt) {
    $authUser = Auth::user();
    echo "   ✅ AUTH::ATTEMPT RÉUSSI!\n";
    echo "   Utilisateur authentifié: {$authUser->email}\n";
    echo "   Rôle: {$authUser->role}\n";
    Auth::logout();
} else {
    echo "   ❌ AUTH::ATTEMPT ÉCHOUÉ\n";
    
    // Diagnostic approfondi
    echo "\n   🔍 Diagnostic approfondi:\n";
    
    // Vérifier si l'email existe
    $emailExists = User::where('email', $email)->exists();
    echo "   - Email existe: " . ($emailExists ? '✅' : '❌') . "\n";
    
    if ($emailExists) {
        $testUser = User::where('email', $email)->first();
        echo "   - Utilisateur trouvé: ✅\n";
        echo "   - Hash::check: " . (Hash::check($password, $testUser->password) ? '✅' : '❌') . "\n";
        echo "   - Rôle: {$testUser->role}\n";
        echo "   - Actif: " . ($testUser->is_active ? '✅' : '❌') . "\n";
        
        // Test avec password_verify directement
        $directVerify = password_verify($password, $testUser->password);
        echo "   - password_verify direct: " . ($directVerify ? '✅' : '❌') . "\n";
        
        if (!$directVerify) {
            echo "\n   ⚠️  Le hash ne correspond vraiment pas!\n";
            echo "   Création d'un nouveau hash...\n";
            
            // Créer un nouveau hash avec password_hash
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            DB::table('users')
                ->where('id', $testUser->id)
                ->update(['password' => $newHash]);
            
            echo "   ✅ Nouveau hash créé\n";
            
            // Réessayer
            echo "\n   🔄 Nouvelle tentative Auth::attempt()...\n";
            Auth::logout();
            $attempt2 = Auth::attempt($credentials, false);
            echo "   Résultat: " . ($attempt2 ? '✅ RÉUSSI' : '❌ ÉCHOUÉ') . "\n";
        }
    }
}

// 5. Afficher les identifiants finaux
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "       IDENTIFIANTS FINAUX\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "URL       : http://localhost:8000/dg/login\n";
echo "Email     : {$email}\n";
echo "Password  : {$password}\n\n";

// Vérification finale
$finalUser = User::where('email', $email)->first();
if ($finalUser && Hash::check($password, $finalUser->password)) {
    echo "✅ Le compte est prêt pour la connexion!\n";
} else {
    echo "❌ Il y a encore un problème avec le compte\n";
}

echo "\n";




