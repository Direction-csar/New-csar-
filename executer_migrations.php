<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🚀 EXÉCUTION DES MIGRATIONS LARAVEL                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test de connexion
echo "🔍 Test de connexion MySQL...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Connexion MySQL réussie\n\n";
} catch (\Exception $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "\n";
    echo "\n⚠️  Vérifiez que MySQL est démarré avec : fix_mysql_start.bat\n";
    exit(1);
}

// Nettoyage du cache
echo "🧹 Nettoyage du cache...\n";
Artisan::call('config:clear');
Artisan::call('cache:clear');
Artisan::call('route:clear');
Artisan::call('view:clear');
echo "✅ Cache nettoyé\n\n";

// Exécution des migrations
echo "📊 Exécution des migrations...\n";
echo "   (Cela peut prendre quelques minutes)\n\n";

try {
    Artisan::call('migrate', ['--force' => true]);
    
    $output = Artisan::output();
    echo $output;
    
    echo "\n✅✅✅ MIGRATIONS TERMINÉES AVEC SUCCÈS ! ✅✅✅\n\n";
    
    echo "🌐 Votre application est maintenant accessible sur :\n";
    echo "   http://localhost:8000\n\n";
    
    echo "💡 Prochaines étapes :\n";
    echo "   1. Testez l'application dans votre navigateur\n";
    echo "   2. Si besoin de données de test : php artisan db:seed\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Erreur lors des migrations : " . $e->getMessage() . "\n";
    exit(1);
}


