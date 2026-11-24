<?php
// Restaure l'état démo propre : force-delete des mouvements de test,
// recrée les mouvements initiaux vérifiés, recalcule current_stock.
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, Warehouse, StockMovement, Stock, StockType};
use App\Services\Magasin\WarehouseStockService;
use Illuminate\Support\Facades\Hash;

$dg = User::where('email', 'demo.dg@csar.test')->first();

// 1. Purge définitive des mouvements de démo (y compris soft-deletés)
$patterns = ['DEMO-ENTRY-%','DEMO-EXIT-%','DEMO-TRF-%','DEMO-OFFLINE-%','DEMO-HACK-%','DEMO-INV-%','CURL-%'];
$q = StockMovement::withTrashed();
$q->where(function ($w) use ($patterns) {
    foreach ($patterns as $i => $p) {
        $i ? $w->orWhere('reference', 'like', $p) : $w->where('reference', 'like', $p);
    }
})->forceDelete();
echo "mouvements purgés\n";

// 2. Recrée les stocks initiaux vérifiés (logique du seed, sans toucher current_stock)
$wh1 = Warehouse::where('name', 'like', 'DEMO Magasin Dakar%')->first();
$wh2 = Warehouse::where('name', 'like', 'DEMO Magasin Thiès%')->first();
$mkInit = function (Warehouse $w, array $items) use ($dg) {
    foreach ($items as [$lbl, $sacs]) {
        $ref = 'DEMO-INIT-' . $w->id . '-' . md5($lbl);
        $existing = StockMovement::withTrashed()->where('reference', $ref)->first();
        if ($existing) { $existing->restore(); continue; }
        $product = trim(preg_replace('/\s*\[.*$/', '', $lbl));
        $stockType = StockType::firstOrCreate(['name' => 'denrees'], ['display_name' => 'Denrées alimentaires', 'unit' => 'contenants']);
        $stockRow = Stock::withTrashed()->firstOrCreate(
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
    }
};
$mkInit($wh1, [['Riz [50kg]', 692], ['Huile [20L]', 200]]);
$mkInit($wh2, [['Riz [50kg]', 150]]);
echo "mouvements initiaux restaurés\n";

// 3. Recalcule warehouses.current_stock = solde officiel (mouvements vérifiés)
$svc = app(WarehouseStockService::class);
foreach ([$wh1, $wh2] as $w) {
    $b = $svc->balance($w->id);
    $w->update(['current_stock' => $b['sacs']]);
    echo "{$w->name}: current_stock={$b['sacs']}\n";
}
