<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== STRUCTURE DE LA TABLE PERSONNEL ===\n\n";

try {
    $columns = DB::select('DESCRIBE personnel');
    
    echo "Colonnes de la table 'personnel':\n";
    foreach($columns as $col) {
        echo "  - {$col->Field} ({$col->Type}) - {$col->Null} - {$col->Key}\n";
    }
    
    echo "\n=== DONNÉES EXISTANTES ===\n";
    $existingData = DB::table('personnel')->first();
    if ($existingData) {
        echo "Premier enregistrement:\n";
        foreach($existingData as $key => $value) {
            echo "  - $key: $value\n";
        }
    } else {
        echo "Aucune donnée dans la table personnel\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}



































