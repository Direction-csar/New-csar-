<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       TEST DE CONNEXION DG DÉTAILLÉ\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$email = 'dg@csar.sn';
$password = 'password';

// 1. Vérifier que l'utilisateur existe
echo "1️⃣ Vérification de l'utilisateur...\n";
$user = User::where('email', $email)->first();

if (!$user) {
    die("❌ Utilisateur non trouvé!\n");
}

echo "   ✅ Utilisateur trouvé: {$user->email}\n";
echo "   ID: {$user->id}\n";
echo "   Nom: {$user->name}\n";
echo "   Rôle: {$user->role}\n";
echo "   Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n\n";

// 2. Vérifier le hash du mot de passe
echo "2️⃣ Vérification du hash du mot de passe...\n";
$hash = $user->password;
echo "   Hash: " . substr($hash, 0, 30) . "...\n";
echo "   Longueur: " . strlen($hash) . " caractères\n";

$passwordCheck = Hash::check($password, $hash);
echo "   Hash::check('password', hash): " . ($passwordCheck ? '✅ OUI' : '❌ NON') . "\n\n";

if (!$passwordCheck) {
    echo "   ⚠️  Le mot de passe ne correspond pas au hash!\n";
    echo "   Réinitialisation du mot de passe...\n";
    $user->password = Hash::make($password);
    $user->save();
    echo "   ✅ Mot de passe réinitialisé\n\n";
    $user->refresh();
}

// 3. Test avec Auth::attempt (simulation)
echo "3️⃣ Test avec Auth::attempt...\n";

// Créer une requête factice
$request = new \Illuminate\Http\Request();
$request->merge([
    'email' => $email,
    'password' => $password,
]);

$credentials = [
    'email' => $email,
    'password' => $password,
];

// Tester Auth::attempt
try {
    $attempt = Auth::attempt($credentials, false);
    echo "   Auth::attempt() résultat: " . ($attempt ? '✅ RÉUSSI' : '❌ ÉCHOUÉ') . "\n";
    
    if ($attempt) {
        $authenticatedUser = Auth::user();
        echo "   ✅ Utilisateur authentifié: {$authenticatedUser->email}\n";
        echo "   Rôle: {$authenticatedUser->role}\n";
        Auth::logout();
    } else {
        echo "   ❌ Échec de l'authentification\n";
        
        // Vérifier pourquoi ça a échoué
        $testUser = User::where('email', $email)->first();
        if ($testUser) {
            echo "   Vérification manuelle:\n";
            echo "   - Utilisateur existe: ✅\n";
            echo "   - Hash check: " . (Hash::check($password, $testUser->password) ? '✅' : '❌') . "\n";
            echo "   - Rôle: {$testUser->role}\n";
            echo "   - Actif: " . ($testUser->is_active ? '✅' : '❌') . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n";

// 4. Vérifier la structure de la table users
echo "4️⃣ Vérification de la structure de la table users...\n";
$columns = DB::select("SHOW COLUMNS FROM users");
$columnNames = array_column($columns, 'Field');
echo "   Colonnes trouvées: " . implode(', ', $columnNames) . "\n";

// Vérifier les colonnes importantes
$importantColumns = ['id', 'email', 'password', 'role', 'is_active', 'status'];
foreach ($importantColumns as $col) {
    if (in_array($col, $columnNames)) {
        echo "   ✅ Colonne '{$col}' existe\n";
    } else {
        echo "   ❌ Colonne '{$col}' MANQUANTE\n";
    }
}

echo "\n";

// 5. Vérifier directement dans la base de données
echo "5️⃣ Vérification directe dans la base de données...\n";
$dbUser = DB::table('users')->where('email', $email)->first();
if ($dbUser) {
    echo "   ✅ Utilisateur trouvé dans la DB\n";
    echo "   Email DB: {$dbUser->email}\n";
    echo "   Rôle DB: " . ($dbUser->role ?? 'N/A') . "\n";
    echo "   Actif DB: " . (($dbUser->is_active ?? 0) ? 'Oui' : 'Non') . "\n";
    
    // Tester le hash directement
    $dbHash = $dbUser->password;
    $dbPasswordCheck = Hash::check($password, $dbHash);
    echo "   Hash check DB: " . ($dbPasswordCheck ? '✅ OUI' : '❌ NON') . "\n";
} else {
    echo "   ❌ Utilisateur non trouvé dans la DB\n";
}

echo "\n";

// 6. Test final avec toutes les informations
echo "═══════════════════════════════════════════════════════════════\n";
echo "       RÉSUMÉ ET RECOMMANDATIONS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$finalUser = User::where('email', $email)->first();
if ($finalUser && Hash::check($password, $finalUser->password)) {
    echo "✅ Le compte est correctement configuré\n";
    echo "   Email: {$finalUser->email}\n";
    echo "   Mot de passe: password (vérifié)\n";
    echo "   Rôle: {$finalUser->role}\n";
    echo "   Actif: " . ($finalUser->is_active ? 'Oui' : 'Non') . "\n\n";
    
    echo "💡 Si la connexion échoue toujours:\n";
    echo "   1. Videz le cache: php artisan cache:clear\n";
    echo "   2. Videz les sessions: php artisan session:clear\n";
    echo "   3. Vérifiez les logs: storage/logs/laravel.log\n";
    echo "   4. Essayez en navigation privée\n";
} else {
    echo "❌ Il y a un problème avec le compte\n";
}

echo "\n";




