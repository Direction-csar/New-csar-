<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== STRUCTURE DE LA TABLE WAREHOUSES ===\n\n";

try {
    $columns = DB::select('DESCRIBE warehouses');
    
    echo "Colonnes de la table 'warehouses':\n";
    foreach($columns as $col) {
        echo "  - {$col->Field} ({$col->Type}) - {$col->Null} - {$col->Key}\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}



































