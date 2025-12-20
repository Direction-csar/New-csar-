<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       CORRECTION FINALE DU COMPTE DG\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$email = 'dg@csar.sn';
$password = 'password';

// 1. Nettoyer tous les caches et rate limits
echo "1️⃣ Nettoyage des caches...\n";
Cache::flush();
RateLimiter::clear('dg-login:127.0.0.1');
RateLimiter::clear('dg-login:::1');
echo "   ✅ Caches nettoyés\n\n";

// 2. Vérifier et corriger le compte
echo "2️⃣ Vérification du compte DG...\n";
$user = User::where('email', $email)->first();

if (!$user) {
    die("❌ Compte non trouvé!\n");
}

echo "   ✅ Compte trouvé: {$user->email}\n";

// 3. Réinitialiser le mot de passe directement dans la DB (sans passer par le modèle pour éviter le double hash)
echo "3️⃣ Réinitialisation du mot de passe...\n";
$newHash = Hash::make($password);
DB::table('users')
    ->where('id', $user->id)
    ->update([
        'password' => $newHash,
        'role' => 'dg',
        'role_id' => 2,
        'is_active' => true,
        'status' => 'active',
        'updated_at' => now(),
    ]);

echo "   ✅ Mot de passe réinitialisé\n\n";

// 4. Vérifier que ça fonctionne
echo "4️⃣ Vérification finale...\n";
$user->refresh();
$testHash = $user->password;

if (Hash::check($password, $testHash)) {
    echo "   ✅ Le mot de passe fonctionne correctement\n";
} else {
    echo "   ❌ Le mot de passe ne fonctionne toujours pas\n";
    // Essayer avec password_hash directement
    $directHash = password_hash($password, PASSWORD_BCRYPT);
    DB::table('users')
        ->where('id', $user->id)
        ->update(['password' => $directHash]);
    echo "   ✅ Mot de passe réinitialisé avec password_hash()\n";
}

echo "\n";

// 5. Afficher les identifiants
echo "═══════════════════════════════════════════════════════════════\n";
echo "       IDENTIFIANTS DE CONNEXION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "URL       : http://localhost:8000/dg/login\n";
echo "Email     : {$email}\n";
echo "Password  : {$password}\n\n";

echo "💡 Instructions:\n";
echo "   1. Videz le cache de votre navigateur (Ctrl+Shift+Delete)\n";
echo "   2. Ou utilisez une fenêtre de navigation privée\n";
echo "   3. Essayez de vous connecter avec les identifiants ci-dessus\n";
echo "   4. Si ça ne fonctionne toujours pas, attendez 5 minutes\n";
echo "      (le rate limiting se réinitialise automatiquement)\n\n";

echo "═══════════════════════════════════════════════════════════════\n";




