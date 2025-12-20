<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                                                          ║\n";
echo "║     🧹 NETTOYAGE AUTOMATIQUE DES DONNÉES DE TEST        ║\n";
echo "║                                                          ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// 1. État initial
echo "📊 ÉTAT AVANT NETTOYAGE:\n";
echo "════════════════════════════════════════════════════════════\n";
$publicRequestsCount = DB::table('public_requests')->count();
$demandesCount = DB::table('demandes')->count();
echo "Table 'public_requests' : {$publicRequestsCount} demande(s)\n";
echo "Table 'demandes'        : {$demandesCount} demande(s)\n";
echo "Total                   : " . ($publicRequestsCount + $demandesCount) . " demande(s)\n\n";

// 2. Identifier et afficher les données de test
echo "⚠️  DONNÉES DE TEST IDENTIFIÉES:\n";
echo "════════════════════════════════════════════════════════════\n";
$testDemandes = DB::table('demandes')
    ->where(function($q) {
        $q->where('nom', 'like', '%test%')
          ->orWhere('prenom', 'like', '%test%')
          ->orWhere('nom', 'like', '%demo%')
          ->orWhere('nom', 'like', '%fake%')
          ->orWhere('email', 'like', '%test%')
          ->orWhere('email', 'like', '%example%');
    })
    ->get();

if ($testDemandes->count() > 0) {
    foreach ($testDemandes as $dem) {
        echo "  ❌ ID {$dem->id}: {$dem->nom} {$dem->prenom} (Email: {$dem->email})\n";
    }
    echo "\n";
    
    // 3. Supprimer les données de test
    echo "🧹 SUPPRESSION EN COURS...\n";
    echo "════════════════════════════════════════════════════════════\n";
    $deleted = DB::table('demandes')
        ->where(function($q) {
            $q->where('nom', 'like', '%test%')
              ->orWhere('prenom', 'like', '%test%')
              ->orWhere('nom', 'like', '%demo%')
              ->orWhere('nom', 'like', '%fake%')
              ->orWhere('email', 'like', '%test%')
              ->orWhere('email', 'like', '%example%');
        })
        ->delete();
    echo "✅ {$deleted} demande(s) de test supprimée(s) de 'demandes'\n\n";
} else {
    echo "  ✅ Aucune donnée de test trouvée dans 'demandes'.\n\n";
}

// Vérifier aussi public_requests
$testPublicRequests = DB::table('public_requests')
    ->where(function($q) {
        $q->where('full_name', 'like', '%test%')
          ->orWhere('email', 'like', '%test%')
          ->orWhere('email', 'like', '%example%');
    })
    ->get();

if ($testPublicRequests->count() > 0) {
    echo "⚠️  DONNÉES DE TEST DANS 'public_requests':\n";
    echo "════════════════════════════════════════════════════════════\n";
    foreach ($testPublicRequests as $req) {
        echo "  ❌ ID {$req->id}: {$req->full_name} (Email: {$req->email})\n";
    }
    
    $deletedPR = DB::table('public_requests')
        ->where(function($q) {
            $q->where('full_name', 'like', '%test%')
              ->orWhere('email', 'like', '%test%')
              ->orWhere('email', 'like', '%example%');
        })
        ->delete();
    echo "\n✅ {$deletedPR} demande(s) de test supprimée(s) de 'public_requests'\n\n";
}

// 4. Nettoyer le cache
echo "🧹 NETTOYAGE DU CACHE...\n";
echo "════════════════════════════════════════════════════════════\n";
try {
    Artisan::call('cache:clear');
    echo "✅ Cache applicatif nettoyé\n";
    
    Artisan::call('view:clear');
    echo "✅ Cache des vues nettoyé\n";
    
    Artisan::call('config:clear');
    echo "✅ Cache de configuration nettoyé\n";
    
    Artisan::call('route:clear');
    echo "✅ Cache des routes nettoyé\n";
} catch (\Exception $e) {
    echo "⚠️  Erreur lors du nettoyage du cache: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. État final
echo "════════════════════════════════════════════════════════════\n";
echo "📊 ÉTAT APRÈS NETTOYAGE:\n";
echo "════════════════════════════════════════════════════════════\n";
$newPublicRequestsCount = DB::table('public_requests')->count();
$newDemandesCount = DB::table('demandes')->count();
echo "Table 'public_requests' : {$newPublicRequestsCount} demande(s)\n";
echo "Table 'demandes'        : {$newDemandesCount} demande(s)\n";
echo "Total                   : " . ($newPublicRequestsCount + $newDemandesCount) . " demande(s)\n\n";

// 6. Afficher les demandes restantes
if ($newDemandesCount > 0) {
    echo "📋 DEMANDES RESTANTES DANS 'demandes':\n";
    echo "════════════════════════════════════════════════════════════\n";
    $remaining = DB::table('demandes')->select('id', 'nom', 'prenom', 'email', 'statut', 'created_at')->get();
    foreach ($remaining as $dem) {
        echo "  ✅ ID {$dem->id}: {$dem->nom} {$dem->prenom}\n";
        echo "     Email: {$dem->email} | Statut: {$dem->statut}\n";
    }
    echo "\n";
}

if ($newPublicRequestsCount > 0) {
    echo "📋 DEMANDES RESTANTES DANS 'public_requests':\n";
    echo "════════════════════════════════════════════════════════════\n";
    $remaining = DB::table('public_requests')->select('id', 'full_name', 'email', 'status', 'created_at')->get();
    foreach ($remaining as $req) {
        echo "  ✅ ID {$req->id}: {$req->full_name}\n";
        echo "     Email: {$req->email} | Statut: {$req->status}\n";
    }
    echo "\n";
}

echo "════════════════════════════════════════════════════════════\n";
echo "✅ NETTOYAGE TERMINÉ AVEC SUCCÈS!\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "🔄 PROCHAINES ÉTAPES:\n";
echo "   1. Actualisez votre navigateur (Ctrl+F5 ou Cmd+Shift+R)\n";
echo "   2. Videz le cache du navigateur si nécessaire\n";
echo "   3. Testez manuellement sur toute la plateforme:\n";
echo "      • Dashboard: http://localhost:8000/admin/dashboard\n";
echo "      • Demandes: http://localhost:8000/admin/demandes\n";
echo "      • Formulaire public: http://localhost:8000/demande\n";
echo "   4. Vérifiez que les compteurs sont corrects\n";
echo "   5. Testez la suppression et l'approbation\n\n";

echo "🎉 Vous pouvez maintenant tester la plateforme avec des données propres!\n\n";




































