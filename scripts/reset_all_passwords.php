<?php

/**
 * Script pour réinitialiser tous les mots de passe des utilisateurs
 * Usage: php scripts/reset_all_passwords.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔐 Réinitialisation de tous les mots de passe\n";
echo "==============================================\n\n";

$users = [
    ['email' => 'admin@csar.sn', 'name' => 'Administrateur CSAR', 'role' => 'admin', 'role_id' => 1],
    ['email' => 'dg@csar.sn', 'name' => 'Directrice Générale', 'role' => 'dg', 'role_id' => 2],
    ['email' => 'drh@csar.sn', 'name' => 'Directeur RH', 'role' => 'drh', 'role_id' => 5],
    ['email' => 'responsable@csar.sn', 'name' => 'Responsable Entrepôt', 'role' => 'responsable', 'role_id' => 3],
    ['email' => 'agent@csar.sn', 'name' => 'Agent CSAR', 'role' => 'agent', 'role_id' => 4],
];

$password = 'password';

foreach ($users as $userData) {
    $user = User::where('email', $userData['email'])->first();
    
    if (!$user) {
        echo "📝 Création de {$userData['name']}...\n";
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($password),
            'role_id' => $userData['role_id'],
            'role' => $userData['role'],
            'is_active' => true,
        ]);
        echo "✅ {$userData['name']} créé avec succès !\n\n";
    } else {
        echo "🔄 Réinitialisation du mot de passe pour {$userData['name']}...\n";
        $user->password = Hash::make($password);
        $user->is_active = true;
        $user->role_id = $userData['role_id'];
        $user->role = $userData['role'];
        $user->save();
        echo "✅ Mot de passe réinitialisé pour {$userData['name']} !\n\n";
    }
}

echo "═══════════════════════════════════════════════════════════\n";
echo "🔐 TOUS LES IDENTIFIANTS DE CONNEXION\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📧 Tous les comptes utilisent le mot de passe : password\n\n";
echo "👨‍💼 Admin       : admin@csar.sn       → https://www.csar.sn/admin\n";
echo "👔 DG          : dg@csar.sn          → https://www.csar.sn/dg\n";
echo "👤 DRH         : drh@csar.sn         → https://www.csar.sn/drh\n";
echo "📦 Responsable : responsable@csar.sn → https://www.csar.sn/entrepot\n";
echo "🚚 Agent       : agent@csar.sn       → https://www.csar.sn/agent\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "⚠️  IMPORTANT : Changez tous les mots de passe après la première connexion !\n";

