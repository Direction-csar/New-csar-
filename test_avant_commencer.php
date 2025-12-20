<?php
/**
 * Test de vérification avant de commencer à ajouter des produits
 * Ouvrez ce fichier dans votre navigateur : http://localhost/csar/test_avant_commencer.php
 */

// Désactiver l'affichage des erreurs pour un affichage propre
error_reporting(0);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Configuration - CSAR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .test-item:last-child {
            border-bottom: none;
        }
        
        .test-icon {
            font-size: 32px;
            margin-right: 15px;
            min-width: 40px;
            text-align: center;
        }
        
        .test-info {
            flex: 1;
        }
        
        .test-info h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .test-info p {
            font-size: 13px;
            color: #666;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.ok {
            background: #d4edda;
            color: #155724;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .summary {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .summary.success {
            background: #d4edda;
            border: 2px solid #28a745;
        }
        
        .summary.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
        }
        
        .summary h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .summary.success h2 {
            color: #155724;
        }
        
        .summary.error h2 {
            color: #721c24;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn.secondary {
            background: #6c757d;
        }
        
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .instructions h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .instructions ol {
            margin-left: 20px;
            line-height: 1.8;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>🔍 Test de Configuration</h1>
                <p>Vérification avant d'ajouter des produits</p>
            </div>
            
            <div class="content">
                <?php
                $tests = [];
                $errors = 0;
                $warnings = 0;
                
                // Test 1 : PHP fonctionne
                $tests[] = [
                    'icon' => '✅',
                    'title' => 'PHP est actif',
                    'description' => 'Version PHP : ' . phpversion(),
                    'status' => 'ok'
                ];
                
                // Test 2 : Fichier Laravel existe
                if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                    $tests[] = [
                        'icon' => '✅',
                        'title' => 'Laravel est installé',
                        'description' => 'Les dépendances sont présentes',
                        'status' => 'ok'
                    ];
                    
                    require __DIR__.'/vendor/autoload.php';
                    $app = require_once __DIR__.'/bootstrap/app.php';
                    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
                    
                    use App\Models\Stock;
                    use App\Models\Warehouse;
                    use App\Models\StockType;
                    use Illuminate\Support\Facades\DB;
                    
                    // Test 3 : Connexion base de données
                    try {
                        DB::connection()->getPdo();
                        $tests[] = [
                            'icon' => '✅',
                            'title' => 'Connexion à la base de données',
                            'description' => 'MySQL est accessible',
                            'status' => 'ok'
                        ];
                        
                        // Test 4 : Table warehouses
                        try {
                            $warehouseCount = Warehouse::count();
                            if ($warehouseCount > 0) {
                                $tests[] = [
                                    'icon' => '✅',
                                    'title' => 'Entrepôts disponibles',
                                    'description' => "$warehouseCount entrepôt(s) trouvé(s)",
                                    'status' => 'ok'
                                ];
                            } else {
                                $tests[] = [
                                    'icon' => '⚠️',
                                    'title' => 'Aucun entrepôt',
                                    'description' => 'Un entrepôt sera créé automatiquement',
                                    'status' => 'warning'
                                ];
                                $warnings++;
                            }
                        } catch (\Exception $e) {
                            $tests[] = [
                                'icon' => '❌',
                                'title' => 'Erreur table warehouses',
                                'description' => $e->getMessage(),
                                'status' => 'error'
                            ];
                            $errors++;
                        }
                        
                        // Test 5 : Table stock_types
                        try {
                            $typeCount = StockType::count();
                            if ($typeCount > 0) {
                                $tests[] = [
                                    'icon' => '✅',
                                    'title' => 'Types de stock disponibles',
                                    'description' => "$typeCount type(s) trouvé(s)",
                                    'status' => 'ok'
                                ];
                            } else {
                                $tests[] = [
                                    'icon' => '⚠️',
                                    'title' => 'Aucun type de stock',
                                    'description' => 'Les types seront créés automatiquement',
                                    'status' => 'warning'
                                ];
                                $warnings++;
                            }
                        } catch (\Exception $e) {
                            $tests[] = [
                                'icon' => '❌',
                                'title' => 'Erreur table stock_types',
                                'description' => $e->getMessage(),
                                'status' => 'error'
                            ];
                            $errors++;
                        }
                        
                        // Test 6 : Table stocks
                        try {
                            $stockCount = Stock::where('is_active', true)->count();
                            if ($stockCount > 0) {
                                $tests[] = [
                                    'icon' => '✅',
                                    'title' => 'Produits déjà présents',
                                    'description' => "$stockCount produit(s) actif(s) dans le système",
                                    'status' => 'ok'
                                ];
                            } else {
                                $tests[] = [
                                    'icon' => 'ℹ️',
                                    'title' => 'Aucun produit',
                                    'description' => 'Normal - Vous allez les ajouter maintenant',
                                    'status' => 'warning'
                                ];
                            }
                        } catch (\Exception $e) {
                            $tests[] = [
                                'icon' => '❌',
                                'title' => 'Erreur table stocks',
                                'description' => $e->getMessage(),
                                'status' => 'error'
                            ];
                            $errors++;
                        }
                        
                    } catch (\Exception $e) {
                        $tests[] = [
                            'icon' => '❌',
                            'title' => 'Erreur de connexion MySQL',
                            'description' => 'Vérifiez que MySQL est démarré dans XAMPP',
                            'status' => 'error'
                        ];
                        $errors++;
                    }
                    
                } else {
                    $tests[] = [
                        'icon' => '❌',
                        'title' => 'Laravel non trouvé',
                        'description' => 'Exécutez "composer install"',
                        'status' => 'error'
                    ];
                    $errors++;
                }
                
                // Test 7 : Fichiers créés
                $files = [
                    'START_HERE.html',
                    'ajouter_produit_manuel.php',
                    'gestion_produits.php'
                ];
                
                $filesOk = 0;
                foreach ($files as $file) {
                    if (file_exists(__DIR__ . '/' . $file)) {
                        $filesOk++;
                    }
                }
                
                if ($filesOk === count($files)) {
                    $tests[] = [
                        'icon' => '✅',
                        'title' => 'Fichiers de gestion présents',
                        'description' => 'Tous les fichiers nécessaires sont disponibles',
                        'status' => 'ok'
                    ];
                } else {
                    $tests[] = [
                        'icon' => '⚠️',
                        'title' => 'Fichiers manquants',
                        'description' => "$filesOk/" . count($files) . " fichiers trouvés",
                        'status' => 'warning'
                    ];
                    $warnings++;
                }
                
                // Afficher le résumé
                if ($errors === 0) {
                    echo '<div class="summary success">';
                    echo '<h2>🎉 Tout est prêt !</h2>';
                    echo '<p>Vous pouvez commencer à ajouter vos produits</p>';
                    echo '</div>';
                } else {
                    echo '<div class="summary error">';
                    echo '<h2>❌ Problèmes détectés</h2>';
                    echo '<p>' . $errors . ' erreur(s) à corriger avant de continuer</p>';
                    echo '</div>';
                }
                
                // Afficher les tests
                foreach ($tests as $test) {
                    echo '<div class="test-item">';
                    echo '<div class="test-icon">' . $test['icon'] . '</div>';
                    echo '<div class="test-info">';
                    echo '<h3>' . $test['title'] . '</h3>';
                    echo '<p>' . $test['description'] . '</p>';
                    echo '</div>';
                    echo '<span class="status ' . $test['status'] . '">' . strtoupper($test['status']) . '</span>';
                    echo '</div>';
                }
                ?>
                
                <?php if ($errors === 0): ?>
                    <div class="instructions">
                        <h3>🚀 Prochaines étapes :</h3>
                        <ol>
                            <li>Cliquez sur le bouton "Ajouter un Produit" ci-dessous</li>
                            <li>Remplissez le formulaire avec VOS produits</li>
                            <li>Validez et répétez pour chaque produit</li>
                            <li>Testez dans l'application : Admin → Gestion des Stocks</li>
                        </ol>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="ajouter_produit_manuel.php" class="btn">✏️ Ajouter un Produit</a>
                        <a href="gestion_produits.php" class="btn secondary">📋 Voir tous les produits</a>
                    </div>
                <?php else: ?>
                    <div class="instructions">
                        <h3>⚠️ Actions requises :</h3>
                        <ol>
                            <li>Ouvrez XAMPP Control Panel</li>
                            <li>Démarrez Apache (bouton Start)</li>
                            <li>Démarrez MySQL (bouton Start)</li>
                            <li>Vérifiez que les voyants sont en vert</li>
                            <li>Rafraîchissez cette page (F5)</li>
                        </ol>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="?" class="btn">🔄 Relancer le test</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="text-align: center; color: white; font-size: 12px;">
            <p>💡 Besoin d'aide ? Consultez le fichier 📦_LIRE_MOI_PRODUITS.txt</p>
        </div>
    </div>
</body>
</html>




































