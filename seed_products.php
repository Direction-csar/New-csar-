<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

/*
 * Produits de sécurité alimentaire CSAR.
 * Chaque produit dispose des mêmes formats de sacs : 5, 10, 25, 50, 100 kg.
 * Les formats sont stockés en JSON dans la colonne description sous la clé "formats".
 */
$formats = [5, 10, 25, 50, 100]; // kg

$products = [
    ['name' => 'Riz',      'category' => 'Céréales',     'unit' => 'kg'],
    ['name' => 'Mil',      'category' => 'Céréales',     'unit' => 'kg'],
    ['name' => 'Maïs',     'category' => 'Céréales',     'unit' => 'kg'],
    ['name' => 'Sorgho',   'category' => 'Céréales',     'unit' => 'kg'],
    ['name' => 'Niébé',    'category' => 'Légumineuses', 'unit' => 'kg'],
    ['name' => 'Arachide', 'category' => 'Légumineuses', 'unit' => 'kg'],
    ['name' => 'Huile',    'category' => 'Huiles',       'unit' => 'L'],
    ['name' => 'Sucre',    'category' => 'Divers',       'unit' => 'kg'],
    ['name' => 'Farine',   'category' => 'Céréales',     'unit' => 'kg'],
    ['name' => 'Sel',      'category' => 'Divers',       'unit' => 'kg'],
];

$created = 0;
$skipped = 0;

DB::beginTransaction();
try {
    foreach ($products as $p) {
        $exists = DB::table('products')->where('name', $p['name'])->exists();
        if ($exists) {
            $skipped++;
            continue;
        }

        DB::table('products')->insert([
            'name'        => $p['name'],
            'type'        => $p['category'],
            'unit'        => $p['unit'],
            'category'    => $p['category'],
            'description' => json_encode(['formats_kg' => $formats]),
            'unit_price'  => 0,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $created++;
    }
    DB::commit();
    echo "Produits créés : {$created}\n";
    echo "Ignorés (déjà existants) : {$skipped}\n";
    echo "Total produits en base : " . DB::table('products')->count() . "\n";
    echo "Formats disponibles : " . implode(', ', array_map(fn($f) => "{$f}kg", $formats)) . "\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERREUR : " . $e->getMessage() . "\n";
}
