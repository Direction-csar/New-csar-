<?php
/**
 * CORRECTION FINALE DE TOUS LES COMPTES
 * Ce script corrige DG, Entrepôt et DRH pour qu'ils fonctionnent
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       CORRECTION FINALE DE TOUS LES COMPTES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Nettoyer tout
echo "🧹 Nettoyage complet...\n";
Cache::flush();
Artisan::call('config:clear');
Artisan::call('route:clear');
Artisan::call('view:clear');
echo "   ✅ Nettoyage terminé\n\n";

$comptes = [
    [
        'email' => 'dg@csar.sn',
        'name' => 'Directeur Général',
        'role' => 'dg',
        'role_id' => 2,
        'password' => 'password'
    ],
    [
        'email' => 'entrepot@csar.sn',
        'name' => 'Responsable Entrepôt',
        'role' => 'responsable',
        'role_id' => 4,
        'password' => 'password'
    ],
    [
        'email' => 'responsable@csar.sn',
        'name' => 'Responsable Entrepôt Principal',
        'role' => 'responsable',
        'role_id' => 4,
        'password' => 'password'
    ],
    [
        'email' => 'drh@csar.sn',
        'name' => 'Directeur RH',
        'role' => 'drh',
        'role_id' => 3,
        'password' => 'password'
    ]
];

foreach ($comptes as $index => $compte) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo ($index + 1) . ". " . strtoupper($compte['name']) . " ({$compte['email']})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Chercher ou créer l'utilisateur
    $user = User::where('email', $compte['email'])->first();
    
    if (!$user) {
        echo "   ⚠️  Création du compte...\n";
        DB::table('users')->insert([
            'name' => $compte['name'],
            'email' => $compte['email'],
            'password' => password_hash($compte['password'], PASSWORD_BCRYPT),
            'role' => $compte['role'],
            'role_id' => $compte['role_id'],
            'is_active' => 1,
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::where('email', $compte['email'])->first();
        echo "   ✅ Compte créé (ID: {$user->id})\n";
    } else {
        echo "   ✅ Compte existant (ID: {$user->id})\n";
    }
    
    // Forcer la mise à jour avec un nouveau hash
    $newHash = password_hash($compte['password'], PASSWORD_BCRYPT);
    DB::table('users')
        ->where('id', $user->id)
        ->update([
            'password' => $newHash,
            'role' => $compte['role'],
            'role_id' => $compte['role_id'],
            'is_active' => 1,
            'status' => 'active',
            'email_verified_at' => now(),
            'updated_at' => now(),
        ]);
    
    echo "   ✅ Mot de passe réinitialisé\n";
    
    // Test de vérification
    $testUser = User::find($user->id);
    $passwordWorks = password_verify($compte['password'], $testUser->password);
    
    if ($passwordWorks) {
        echo "   ✅ Vérification: OK\n";
    } else {
        echo "   ❌ Vérification: ÉCHOUÉE\n";
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "       URLS DE CONNEXION DIRECTE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Copiez-collez ces URLs dans votre navigateur:\n\n";
echo "🔐 DG:\n";
echo "   http://localhost:8000/dg/test-login\n\n";
echo "🔐 Entrepôt:\n";
echo "   http://localhost:8000/entrepot/test-login\n\n";
echo "🔐 DRH:\n";
echo "   http://localhost:8000/drh/test-login\n\n";
echo "🔐 Page avec tous les boutons:\n";
echo "   http://localhost:8000/connexion-tous.html\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Correction terminée - Vous pouvez maintenant vous connecter!\n";
echo "═══════════════════════════════════════════════════════════════\n";




