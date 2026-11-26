<?php
// Jeu de données DE FICHES pour la validation locale (sqlite, APP_ENV=local).
// À ne jamais exécuter sur la production.
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, Warehouse, StockMovement};
use Illuminate\Support\Facades\Hash;

$wh1 = Warehouse::firstOrCreate(['name' => 'DEMO Magasin Dakar Centre'], [
    'region' => 'Dakar', 'city' => 'Dakar', 'address' => 'Adresse fictive de démonstration',
    'latitude' => 14.7167, 'longitude' => -17.4677,
    'capacity' => 5000, 'current_stock' => 0, 'status' => 'active', 'is_active' => true,
]);
$wh2 = Warehouse::firstOrCreate(['name' => 'DEMO Magasin Thiès'], [
    'region' => 'Thiès', 'city' => 'Thiès', 'address' => 'Adresse fictive de démonstration',
    'latitude' => 14.7878, 'longitude' => -16.9260,
    'capacity' => 3000, 'current_stock' => 0, 'status' => 'active', 'is_active' => true,
]);

$mk = function ($email, $name, $role, $wh = null) {
    $u = User::firstOrCreate(['email' => $email], [
        'name' => $name, 'password' => Hash::make('Demo2026!'), 'is_active' => true,
    ]);
    $u->role = $role;
    if ($wh) $u->warehouse_id = $wh->id;
    $u->save();
    return $u;
};

$dg   = $mk('demo.dg@csar.test', 'DEMO DG', 'dg');
$dtl  = $mk('demo.dtl@csar.test', 'DEMO DTL', 'dtl');
$insp = $mk('demo.inspecteur@csar.test', 'DEMO Inspecteur', 'inspecteur');
$mag1 = $mk('demo.magasinier@csar.test', 'DEMO Magasinier Dakar', 'magasinier', $wh1);
$mag2 = $mk('demo.magasinier2@csar.test', 'DEMO Magasinier Thies', 'magasinier', $wh2);

$seed = function (Warehouse $w, array $items) use ($dg) {
    foreach ($items as [$lbl, $sacs, $fmt]) {
        $ref = 'DEMO-INIT-' . $w->id . '-' . md5($lbl);
        if (StockMovement::where('reference', $ref)->exists()) continue;
        $product = trim(preg_replace('/\s*\[.*$/', '', $lbl));
        $stockType = \App\Models\StockType::firstOrCreate(
            ['name' => 'denrees'],
            ['display_name' => 'Denrées alimentaires', 'unit' => 'contenants']
        );
        $stockRow = \App\Models\Stock::firstOrCreate(
            ['warehouse_id' => $w->id, 'item_name' => $product],
            ['stock_type_id' => $stockType->id, 'quantity' => 0, 'min_quantity' => 0, 'is_active' => true]
        );
        StockMovement::create([
            'warehouse_id' => $w->id, 'stock_id' => $stockRow->id,
            'type' => 'entry', 'quantity' => $sacs,
            'quantity_before' => 0, 'quantity_after' => $sacs,
            'reason' => "$lbl — DEMO stock initial",
            'reference' => $ref, 'created_by' => $dg->id,
            'status' => 'verified', 'validated_by' => $dg->id, 'validated_at' => now(),
        ]);
        $stockRow->update(['quantity' => $sacs]);
    }
    $w->update(['current_stock' => collect($items)->sum(fn($i) => $i[1])]);
};
$seed($wh1, [['Riz [50kg]', 692, 50], ['Huile [20L]', 200, 20]]);
$seed($wh2, [['Riz [50kg]', 150, 50]]);

// Affectations inspecteur ↔ magasins (périmètre de contrôle)
foreach ([$wh1, $wh2] as $w) {
    \App\Models\InspectorWarehouseAssignment::firstOrCreate(
        ['inspector_id' => $insp->id, 'warehouse_id' => $w->id],
        ['assigned_by' => $dtl->id, 'is_active' => true]
    );
}

echo "wh1={$wh1->id} wh2={$wh2->id} dg={$dg->id} dtl={$dtl->id} insp={$insp->id} mag1={$mag1->id} mag2={$mag2->id}\n";
