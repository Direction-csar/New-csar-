<?php

/**
 * Script pour réinitialiser le mot de passe de l'utilisateur admin
 * Usage: php scripts/reset_admin_password.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔐 Réinitialisation du mot de passe admin\n";
echo "==========================================\n\n";

// Vérifier si l'utilisateur admin existe
$admin = User::where('email', 'admin@csar.sn')->first();

if (!$admin) {
    echo "❌ Utilisateur admin@csar.sn non trouvé.\n";
    echo "📝 Création de l'utilisateur admin...\n";
    
    // Créer l'utilisateur admin
    $admin = User::create([
        'name' => 'Administrateur CSAR',
        'email' => 'admin@csar.sn',
        'password' => Hash::make('password'),
        'role_id' => 1,
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    echo "✅ Utilisateur admin créé avec succès !\n\n";
} else {
    echo "✅ Utilisateur admin trouvé : {$admin->name}\n";
    echo "📧 Email : {$admin->email}\n";
    echo "🆔 ID : {$admin->id}\n";
    echo "👤 Rôle : {$admin->role} (role_id: {$admin->role_id})\n";
    echo "🔓 Statut : " . ($admin->is_active ? 'Actif' : 'Inactif') . "\n\n";
    
    // Réinitialiser le mot de passe
    echo "🔄 Réinitialisation du mot de passe...\n";
    $admin->password = Hash::make('password');
    $admin->is_active = true;
    $admin->save();
    
    echo "✅ Mot de passe réinitialisé avec succès !\n\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "🔐 IDENTIFIANTS DE CONNEXION\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📧 Email    : admin@csar.sn\n";
echo "🔑 Password : password\n";
echo "🌐 URL      : https://www.csar.sn/admin\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "⚠️  IMPORTANT : Changez ce mot de passe après la première connexion !\n";

