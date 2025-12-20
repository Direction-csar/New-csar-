<?php
/**
 * Correction de l'erreur 419 Page Expired
 * Ce script nettoie les sessions et régénère les clés
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       CORRECTION DE L'ERREUR 419 PAGE EXPIRED\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Nettoyer les sessions
echo "1️⃣ Nettoyage des sessions...\n";
$sessionPath = storage_path('framework/sessions');
if (File::exists($sessionPath)) {
    $files = File::files($sessionPath);
    foreach ($files as $file) {
        File::delete($file);
    }
    echo "   ✅ " . count($files) . " fichiers de session supprimés\n";
} else {
    File::makeDirectory($sessionPath, 0755, true);
    echo "   ✅ Dossier sessions créé\n";
}

// 2. Nettoyer le cache
echo "\n2️⃣ Nettoyage du cache...\n";
Artisan::call('cache:clear');
echo "   ✅ Cache vidé\n";

// 3. Nettoyer les vues compilées
echo "\n3️⃣ Nettoyage des vues...\n";
Artisan::call('view:clear');
echo "   ✅ Vues vidées\n";

// 4. Nettoyer la configuration
echo "\n4️⃣ Nettoyage de la configuration...\n";
Artisan::call('config:clear');
echo "   ✅ Configuration vidée\n";

// 5. Vérifier APP_KEY
echo "\n5️⃣ Vérification de APP_KEY...\n";
$appKey = config('app.key');
if (empty($appKey)) {
    echo "   ⚠️  APP_KEY manquante, génération...\n";
    Artisan::call('key:generate');
    echo "   ✅ APP_KEY générée\n";
} else {
    echo "   ✅ APP_KEY présente\n";
}

// 6. Vérifier les permissions
echo "\n6️⃣ Vérification des permissions...\n";
$dirs = [
    'storage/framework/sessions',
    'storage/framework/cache',
    'storage/framework/views',
    'storage/logs',
];

foreach ($dirs as $dir) {
    $fullPath = base_path($dir);
    if (!File::exists($fullPath)) {
        File::makeDirectory($fullPath, 0755, true);
        echo "   ✅ Créé: {$dir}\n";
    } else {
        echo "   ✅ Existe: {$dir}\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "       INSTRUCTIONS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "1. Fermez complètement votre navigateur\n";
echo "2. Rouvrez le navigateur\n";
echo "3. Allez sur http://localhost:8000/admin/login\n";
echo "4. Connectez-vous avec:\n";
echo "   Email: admin@csar.sn\n";
echo "   Mot de passe: password\n\n";

echo "Si l'erreur 419 persiste:\n";
echo "- Utilisez la navigation privée (Ctrl+Shift+N)\n";
echo "- Ou utilisez l'URL de test: http://localhost:8000/admin/test-login\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Correction terminée\n";
echo "═══════════════════════════════════════════════════════════════\n";




