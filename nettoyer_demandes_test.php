<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                                                          ║\n";
echo "║     🧹 NETTOYAGE DES DONNÉES DE TEST - CSAR            ║\n";
echo "║                                                          ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. Afficher les données actuelles
echo "📊 DONNÉES ACTUELLES:\n";
echo "════════════════════════════════════════════════════════════\n";
$publicRequestsCount = DB::table('public_requests')->count();
$demandesCount = DB::table('demandes')->count();
echo "Table 'public_requests' : {$publicRequestsCount} demande(s)\n";
echo "Table 'demandes'        : {$demandesCount} demande(s)\n\n";

// 2. Afficher les demandes de test
echo "⚠️  DEMANDES DE TEST IDENTIFIÉES:\n";
echo "════════════════════════════════════════════════════════════\n";
$testDemandes = DB::table('demandes')
    ->where(function($q) {
        $q->where('nom', 'like', '%test%')
          ->orWhere('prenom', 'like', '%test%')
          ->orWhere('nom', 'like', '%demo%')
          ->orWhere('nom', 'like', '%fake%');
    })
    ->get();

if ($testDemandes->count() > 0) {
    foreach ($testDemandes as $dem) {
        echo "  ❌ ID {$dem->id}: {$dem->nom} {$dem->prenom} (Code: " . ($dem->tracking_code ?? 'N/A') . ")\n";
    }
    echo "\n";
} else {
    echo "  ✅ Aucune donnée de test trouvée.\n\n";
}

// 3. Demander confirmation
echo "════════════════════════════════════════════════════════════\n";
echo "⚠️  ATTENTION: Cette action va SUPPRIMER définitivement\n";
echo "   toutes les données de test identifiées ci-dessus.\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "Options disponibles:\n";
echo "  [1] Supprimer UNIQUEMENT les données de test dans 'demandes'\n";
echo "  [2] Supprimer TOUTES les demandes dans 'demandes'\n";
echo "  [3] Supprimer TOUTES les demandes dans 'public_requests'\n";
echo "  [4] Supprimer TOUT (les deux tables)\n";
echo "  [5] ANNULER - Ne rien faire\n\n";

$choice = readline("Votre choix (1-5): ");

switch($choice) {
    case '1':
        echo "\n🧹 Suppression des données de test dans 'demandes'...\n";
        $deleted = DB::table('demandes')
            ->where(function($q) {
                $q->where('nom', 'like', '%test%')
                  ->orWhere('prenom', 'like', '%test%')
                  ->orWhere('nom', 'like', '%demo%')
                  ->orWhere('nom', 'like', '%fake%');
            })
            ->delete();
        echo "✅ {$deleted} demande(s) de test supprimée(s).\n";
        break;
        
    case '2':
        echo "\n🧹 Suppression de TOUTES les demandes dans 'demandes'...\n";
        $deleted = DB::table('demandes')->delete();
        echo "✅ {$deleted} demande(s) supprimée(s).\n";
        // Réinitialiser l'auto-increment
        DB::statement('ALTER TABLE demandes AUTO_INCREMENT = 1');
        echo "✅ Compteur réinitialisé.\n";
        break;
        
    case '3':
        echo "\n🧹 Suppression de TOUTES les demandes dans 'public_requests'...\n";
        $deleted = DB::table('public_requests')->delete();
        echo "✅ {$deleted} demande(s) supprimée(s).\n";
        // Réinitialiser l'auto-increment
        DB::statement('ALTER TABLE public_requests AUTO_INCREMENT = 1');
        echo "✅ Compteur réinitialisé.\n";
        break;
        
    case '4':
        echo "\n🧹 Suppression de TOUTES les demandes (les deux tables)...\n";
        $deleted1 = DB::table('demandes')->delete();
        $deleted2 = DB::table('public_requests')->delete();
        echo "✅ {$deleted1} demande(s) supprimée(s) de 'demandes'.\n";
        echo "✅ {$deleted2} demande(s) supprimée(s) de 'public_requests'.\n";
        // Réinitialiser les auto-increments
        DB::statement('ALTER TABLE demandes AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE public_requests AUTO_INCREMENT = 1');
        echo "✅ Compteurs réinitialisés.\n";
        break;
        
    case '5':
        echo "\n❌ Opération annulée. Aucune modification effectuée.\n";
        exit(0);
        
    default:
        echo "\n❌ Choix invalide. Opération annulée.\n";
        exit(1);
}

// 4. Nettoyer le cache
echo "\n🧹 Nettoyage du cache...\n";
Artisan::call('cache:clear');
Artisan::call('view:clear');
Artisan::call('config:clear');
echo "✅ Cache nettoyé.\n";

// 5. Afficher le résultat final
echo "\n════════════════════════════════════════════════════════════\n";
echo "📊 RÉSULTAT FINAL:\n";
echo "════════════════════════════════════════════════════════════\n";
$newPublicRequestsCount = DB::table('public_requests')->count();
$newDemandesCount = DB::table('demandes')->count();
echo "Table 'public_requests' : {$newPublicRequestsCount} demande(s)\n";
echo "Table 'demandes'        : {$newDemandesCount} demande(s)\n";
echo "Total                   : " . ($newPublicRequestsCount + $newDemandesCount) . " demande(s)\n\n";

echo "✅ NETTOYAGE TERMINÉ AVEC SUCCÈS!\n\n";
echo "🔄 PROCHAINES ÉTAPES:\n";
echo "   1. Actualisez votre navigateur (Ctrl+F5)\n";
echo "   2. Videz le cache du navigateur si nécessaire\n";
echo "   3. Les compteurs devraient maintenant être corrects!\n\n";




































