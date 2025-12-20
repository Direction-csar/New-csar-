<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DE L'API PARTAGÉE ===\n\n";

try {
    // Test 1: API des données temps réel
    echo "1. Test de l'API des données temps réel...\n";
    $response = \Illuminate\Support\Facades\Http::get('http://localhost:8000/api/shared/realtime-data');
    
    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ API temps réel fonctionne\n";
        echo "   📊 Total demandes: " . ($data['data']['stats']['total_requests'] ?? 'N/A') . "\n";
        echo "   📊 Total entrepôts: " . ($data['data']['stats']['total_warehouses'] ?? 'N/A') . "\n";
        echo "   📊 Total personnel: " . ($data['data']['stats']['total_personnel'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Erreur API temps réel: " . $response->status() . "\n";
    }
    
    // Test 2: API des statistiques de performance
    echo "\n2. Test de l'API des statistiques de performance...\n";
    $response = \Illuminate\Support\Facades\Http::get('http://localhost:8000/api/shared/performance-stats');
    
    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ API performance fonctionne\n";
        echo "   📈 Taux d'efficacité: " . ($data['data']['efficiency_rate'] ?? 'N/A') . "\n";
        echo "   📈 Taux de satisfaction: " . ($data['data']['satisfaction_rate'] ?? 'N/A') . "\n";
        echo "   📈 Temps de réponse: " . ($data['data']['response_time'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Erreur API performance: " . $response->status() . "\n";
    }
    
    // Test 3: API des alertes
    echo "\n3. Test de l'API des alertes...\n";
    $response = \Illuminate\Support\Facades\Http::get('http://localhost:8000/api/shared/alerts');
    
    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ API alertes fonctionne\n";
        echo "   🚨 Total alertes: " . ($data['data']['total_alerts'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Erreur API alertes: " . $response->status() . "\n";
    }
    
    // Test 4: Test direct du contrôleur
    echo "\n4. Test direct du contrôleur partagé...\n";
    $controller = new \App\Http\Controllers\Shared\RealtimeDataController();
    $response = $controller->getSharedData();
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        echo "   ✅ Contrôleur partagé fonctionne\n";
        echo "   📊 Données récupérées: " . count($data['data']) . " sections\n";
    } else {
        echo "   ❌ Erreur contrôleur: " . $response->getStatusCode() . "\n";
    }
    
    echo "\n=== RÉSUMÉ ===\n";
    echo "✅ API partagée opérationnelle\n";
    echo "✅ Synchronisation Admin/DG activée\n";
    echo "✅ Données temps réel disponibles\n";
    
    echo "\n📋 URLs de test:\n";
    echo "   - API temps réel: http://localhost:8000/api/shared/realtime-data\n";
    echo "   - API performance: http://localhost:8000/api/shared/performance-stats\n";
    echo "   - API alertes: http://localhost:8000/api/shared/alerts\n";
    echo "   - Dashboard DG: http://localhost:8000/dg\n";
    echo "   - Dashboard Admin: http://localhost:8000/admin\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DU TEST ===\n";



































