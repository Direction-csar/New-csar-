<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

/*
 * Produits de sécurité alimentaire CSAR.
 * Les produits solides utilisent des formats en kg : 5, 10, 25, 50, 100.
 * Les produits liquides (huile) utilisent des formats en litres : 1, 5, 10, 20, 25.
 * Les formats sont stockés en JSON dans la colonne description
 * (cles "formats_kg" / "formats_l").
 * Le script met a jour les produits existants (unite + formats).
 */
$formatsKg = [5, 10, 25, 50, 100];
$formatsL  = [1, 5, 10, 20, 25];

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
$updated = 0;

DB::beginTransaction();
try {
    foreach ($products as $p) {
        $isLiquid = $p['unit'] === 'L';
        $description = json_encode(
            $isLiquid
                ? ['formats_l' => $formatsL]
                : ['formats_kg' => $formatsKg]
        );

        $existing = DB::table('products')->where('name', $p['name'])->first();
        if ($existing) {
            DB::table('products')->where('id', $existing->id)->update([
                'unit'        => $p['unit'],
                'description' => $description,
                'updated_at'  => now(),
            ]);
            $updated++;
            continue;
        }

        DB::table('products')->insert([
            'name'        => $p['name'],
            'type'        => $p['category'],
            'unit'        => $p['unit'],
            'category'    => $p['category'],
            'description' => $description,
            'unit_price'  => 0,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $created++;
    }
    DB::commit();
    echo "Produits créés : {$created}\n";
    echo "Produits mis à jour : {$updated}\n";
    echo "Total produits en base : " . DB::table('products')->count() . "\n";
    echo "Formats solides : " . implode(', ', array_map(fn($f) => "{$f}kg", $formatsKg)) . "\n";
    echo "Formats liquides : " . implode(', ', array_map(fn($f) => "{$f}L", $formatsL)) . "\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERREUR : " . $e->getMessage() . "\n";
}
