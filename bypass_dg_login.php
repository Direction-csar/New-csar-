<?php
/**
 * Script de connexion directe DG (bypass)
 * Utilisez ce script si le formulaire ne fonctionne pas
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$email = 'dg@csar.sn';
$password = 'password';

echo "Connexion directe DG...\n";

$user = User::where('email', $email)->first();

if ($user && Hash::check($password, $user->password)) {
    Auth::login($user);
    echo "✅ Connexion réussie!\n";
    echo "Redirection vers: http://localhost:8000/dg/dashboard\n";
    echo "\nOuvrez cette URL dans votre navigateur:\n";
    echo "http://localhost:8000/dg/dashboard\n";
} else {
    echo "❌ Échec de la connexion\n";
}




