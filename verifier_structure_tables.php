<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Vérification de la structure des tables...\n\n";

try {
    // Vérifier la structure de public_requests
    echo "📋 Structure de la table 'public_requests':\n";
    $columns = \Schema::getColumnListing('public_requests');
    foreach ($columns as $column) {
        echo "   - $column\n";
    }
    echo "\n";

    // Vérifier la structure de warehouses
    echo "🏢 Structure de la table 'warehouses':\n";
    $columns = \Schema::getColumnListing('warehouses');
    foreach ($columns as $column) {
        echo "   - $column\n";
    }
    echo "\n";

    // Vérifier la structure de stock_movements
    echo "📦 Structure de la table 'stock_movements':\n";
    $columns = \Schema::getColumnListing('stock_movements');
    foreach ($columns as $column) {
        echo "   - $column\n";
    }
    echo "\n";

    // Vérifier la structure de personnel
    echo "👥 Structure de la table 'personnel':\n";
    $columns = \Schema::getColumnListing('personnel');
    foreach ($columns as $column) {
        echo "   - $column\n";
    }
    echo "\n";

    // Vérifier la structure de users
    echo "👤 Structure de la table 'users':\n";
    $columns = \Schema::getColumnListing('users');
    foreach ($columns as $column) {
        echo "   - $column\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}



































