<?php

/**
 * Smoke test du module carburant (FuelService) — base locale uniquement.
 * Usage : php scripts/fuel_smoke.php
 * Crée de vraies lignes de test dans la BDD locale (tag FUEL-SMOKE).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, Warehouse, FuelEntry, FuelExit, FuelReimbursement, StockMovement};
use App\Services\Comptable\FuelService;

$svc = app(FuelService::class);
$user = User::where('is_active', true)->first() ?? User::first();
if (!$user) { exit("ERREUR : aucun utilisateur.\n"); }

$warehouse = Warehouse::first();
if (!$warehouse) {
    $warehouse = Warehouse::create([
        'name' => 'Cuve FUEL-SMOKE',
        'address' => 'CSAR (test)',
        'latitude' => 14.6937,
        'longitude' => -17.4441,
        'region' => 'Dakar',
        'city' => 'Dakar',
    ]);
    echo "Magasin de test cree : {$warehouse->id}\n";
}
echo "User #{$user->id} | Warehouse #{$warehouse->id} {$warehouse->name}\n\n";

/* ---------- ENTRÉE ---------- */
$entry = $svc->createEntry([
    'operation_date' => '2026-10-29',
    'warehouse_id' => $warehouse->id,
    'supplier' => 'FUEL-SMOKE Fournisseur',
    'fuel_type' => 'gasoil',
    'quantity' => 500,
    'unit_price' => 800,
    'invoice_number' => 'SMK-001',
], $user);
echo "[ENTRY] creee : {$entry->reference} total={$entry->total_amount}\n";

// Idempotence
$dup = $svc->createEntry([
    'operation_date' => '2026-10-29', 'warehouse_id' => $warehouse->id,
    'supplier' => 'FUEL-SMOKE Fournisseur', 'fuel_type' => 'gasoil',
    'quantity' => 500, 'unit_price' => 800,
], $user, 'smoke-key-1');
$dup2 = $svc->createEntry([
    'operation_date' => '2026-10-29', 'warehouse_id' => $warehouse->id,
    'supplier' => 'FUEL-SMOKE Fournisseur', 'fuel_type' => 'gasoil',
    'quantity' => 500, 'unit_price' => 800,
], $user, 'smoke-key-1');
echo "[IDEMPOTENCE] " . ($dup->id === $dup2->id ? "OK (meme entree {$dup->reference})" : "ECHEC") . "\n";

$r = $svc->validateEntry($entry, $user);
echo "[ENTRY VALIDATE] {$r['message']}\n";
$r2 = $svc->validateEntry($entry->fresh(), $user);
echo "[DOUBLE VALIDATE] " . ($r2['ok'] ? "OK idempotent" : "ECHEC : {$r2['message']}") . "\n";
echo "[STOCK] mouvements carburant : " . StockMovement::where('unite', 'L')->count() . "\n";
echo "[STOCK DISPO] " . $svc->availableFuelStock($warehouse->id, 'gasoil') . " L\n\n";

/* ---------- SORTIE ---------- */
$exit = $svc->createExit([
    'operation_date' => '2026-10-29',
    'warehouse_id' => $warehouse->id,
    'fuel_type' => 'gasoil',
    'beneficiary_name' => 'FUEL-SMOKE Chauffeur',
    'vehicle_plate' => 'DK-0000-SM',
    'purpose' => 'Mission test',
    'quantity_requested' => 50,
], $user);
echo "[EXIT] cree : {$exit->exit_number}\n";

// Valider avant autorisation doit echouer
$r = $svc->validateExit($exit, 50, $user);
echo "[VALIDATE SANS AUTORISATION] " . (!$r['ok'] ? "OK refuse : {$r['message']}" : "ECHEC : {$r['message']}") . "\n";

// Autoriser au-dela du stock doit echouer
$r = $svc->authorizeExit($exit, 9999, $user);
echo "[AUTORISATION > STOCK] " . (!$r['ok'] ? "OK refuse : {$r['message']}" : "ECHEC") . "\n";

$r = $svc->authorizeExit($exit, 50, $user);
echo "[AUTHORIZE] {$r['message']}\n";
$r = $svc->validateExit($exit, 50, $user);
echo "[EXIT VALIDATE] {$r['message']}\n";
echo "[STOCK DISPO APRES] " . $svc->availableFuelStock($warehouse->id, 'gasoil') . " L\n\n";

/* ---------- REMBOURSEMENT ---------- */
$reimb = $svc->createReimbursement([
    'agent_name' => 'FUEL-SMOKE Agent',
    'expense_date' => '2026-10-28',
    'purpose' => 'Carburant mission test',
    'amount_claimed' => 25000,
    'amount_requested' => 25000,
    'advance_amount' => 20000,
    'receipt_ref' => 'RECU-SMK-1',
], $user);
echo "[REIMB] creee : {$reimb->reference}\n";

// Détection doublon
$reimb2 = $svc->createReimbursement([
    'agent_name' => 'FUEL-SMOKE Agent',
    'expense_date' => '2026-10-28',
    'purpose' => 'Autre motif',
    'amount_claimed' => 25000,
    'amount_requested' => 25000,
    'receipt_ref' => 'RECU-SMK-1',
], $user);
echo "[DOUBLON] " . ($reimb2->duplicate_of_ref === $reimb->reference ? "OK detecte ({$reimb2->duplicate_of_ref})" : "ECHEC") . "\n";

echo "[SUBMIT] " . $svc->submitReimbursement($reimb, $user)['message'] . "\n";
echo "[REVIEW] " . $svc->reviewReimbursement($reimb->fresh(), 'RAS', $user)['message'] . "\n";

// approuver au-dela du demande doit echouer
$r = $svc->approveReimbursement($reimb->fresh(), 99999, $user);
echo "[APPROUVER > DEMANDE] " . (!$r['ok'] ? "OK refuse : {$r['message']}" : "ECHEC") . "\n";

$r = $svc->approveReimbursement($reimb->fresh(), 25000, $user);
echo "[APPROVE] {$r['message']}\n";
$r = $svc->payReimbursement($reimb->fresh(), 'VRT-SMK-1', '2026-10-29', $user);
echo "[PAY] {$r['message']}\n";
$paid = $reimb->fresh();
echo "[REGULARISATION] avance=20000 paye={$paid->amount_paid} complement={$paid->complement_amount} etat={$paid->regularizationState()}\n";
echo "[CLOSE] " . $svc->closeReimbursement($paid, $user)['message'] . "\n";

/* ---------- AGREGATS ---------- */
echo "\n[STOCK SUMMARY] " . count($svc->fuelStockSummary()) . " produit(s)\n";
echo "[STOCK VALUE] " . json_encode($svc->fuelStockValue()) . "\n";
echo "[PENDING] " . json_encode($svc->pendingCounts()) . "\n";
echo "\n=== SMOKE TEST TERMINE ===\n";
