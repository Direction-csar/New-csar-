<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WarehouseKeeperController extends Controller
{
    /**
     * Authentification magasinier (même guard que l'admin mais vérification du rôle)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $user = Auth::guard('admin')->user();

        // Accepter : magasinier, admin, super_admin
        if (!in_array($user->role, ['magasinier', 'admin', 'super_admin'])) {
            Auth::guard('admin')->logout();
            return response()->json(['message' => 'Accès réservé aux magasiniers.'], 403);
        }

        $token = $user->createToken('warehouse-keeper', ['warehouse'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Liste des magasins accessibles
     */
    public function getWarehouses(Request $request)
    {
        $user = $request->user();

        $query = Warehouse::where('status', 'active');

        // Si magasinier simple : seulement ses magasins assignés
        if ($user->role === 'magasinier') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        $warehouses = $query->select('id', 'name', 'address as location', 'capacity', 'current_stock', 'status')
            ->orderBy('name')
            ->get();

        return response()->json($warehouses);
    }

    /**
     * Fiche de stock (mouvements d'un magasin)
     */
    public function getStockSheet(Request $request, $warehouseId)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $query = StockMovement::with(['creator:id,name'])
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);

        $movements = $query->paginate(50);

        // Calcul du solde courant
        $balanceSacs = StockMovement::where('warehouse_id', $warehouseId)
            ->whereIn('type', ['entry', 'exit', 'adjustment'])
            ->sum(DB::raw('CASE WHEN type = "exit" THEN -quantity ELSE quantity END'));

        return response()->json([
            'movements' => $movements,
            'balance_sacs' => $balanceSacs,
            'balance_kg'  => $balanceSacs * 50, // Approximation : 1 sac = 50kg
        ]);
    }

    /**
     * Créer un mouvement (entrée ou sortie)
     */
    public function storeMovement(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_date' => 'required|date',
            'label' => 'required|string|max:255',
            'position' => 'nullable|string|max:50',
            'movement_type' => 'required|in:entry,exit,adjustment,report',
            'sacs' => 'required|numeric|min:0',
            'kg' => 'required|numeric|min:0',
            'observation' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $type = $request->movement_type;
        $sacs = (float) $request->sacs;
        $kg   = (float) $request->kg;

        // Vérifier solde suffisant pour sortie
        if (in_array($type, ['exit', 'adjustment'])) {
            $balance = $this->calculateBalance($request->warehouse_id);
            if ($sacs > $balance['sacs']) {
                return response()->json([
                    'message' => 'Solde insuffisant. Disponible : ' . $balance['sacs'] . ' sacs'
                ], 422);
            }
        }

        $ref = 'STK-' . strtoupper(Str::random(8));

        $entrySacs = in_array($type, ['entry', 'report']) ? $sacs : 0;
        $entryKg   = in_array($type, ['entry', 'report']) ? $kg : 0;
        $exitSacs  = in_array($type, ['exit']) ? $sacs : 0;
        $exitKg    = in_array($type, ['exit']) ? $kg : 0;

        // Recalculer les soldes
        $balance = $this->calculateBalance($request->warehouse_id);
        $newBalanceSacs = $balance['sacs'] + $entrySacs - $exitSacs;
        $newBalanceKg   = $balance['kg'] + $entryKg - $exitKg;

        $movement = StockMovement::create([
            'warehouse_id'    => $request->warehouse_id,
            'stock_id'        => null, // pas lié à un stock spécifique
            'type'            => $type,
            'quantity'        => $sacs,
            'quantity_before' => $balance['sacs'],
            'quantity_after'  => $newBalanceSacs,
            'reason'          => $request->label,
            'reference'       => $ref,
            'created_by'      => $user->id,
        ]);

        // Métadonnées spécifiques fiche de stock (colonne JSON ou table pivot)
        // On stocke dans metadata
        $movement->metadata = json_encode([
            'entry_sacs' => $entrySacs,
            'entry_kg'   => $entryKg,
            'exit_sacs'  => $exitSacs,
            'exit_kg'    => $exitKg,
            'balance_sacs' => $newBalanceSacs,
            'balance_kg'   => $newBalanceKg,
            'position'     => $request->position,
            'observation'  => $request->observation,
            'movement_date' => $request->movement_date,
            'photo_base64' => $request->photo_base64 ?? null,
            'signature_base64' => $request->signature_base64 ?? null,
        ]);
        $movement->save();

        // Mise à jour du stock courant du magasin
        Warehouse::where('id', $request->warehouse_id)
            ->update(['current_stock' => $newBalanceSacs]);

        // Journal d'audit
        Log::info('Mouvement de stock créé', [
            'user_id' => $user->id,
            'warehouse_id' => $request->warehouse_id,
            'reference' => $ref,
            'type' => $type,
            'sacs' => $sacs,
        ]);

        return response()->json([
            'message' => 'Mouvement enregistré avec succès.',
            'reference' => $ref,
            'balance_sacs' => $newBalanceSacs,
            'balance_kg' => $newBalanceKg,
            'movement' => $movement,
        ], 201);
    }

    /**
     * Statistiques du magasinier
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        $today = now()->format('Y-m-d');
        $entriesToday = StockMovement::where('created_by', $user->id)
            ->where('type', 'entry')
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $exitsToday = StockMovement::where('created_by', $user->id)
            ->where('type', 'exit')
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $totalMovements = StockMovement::where('created_by', $user->id)->count();

        return response()->json([
            'entries_today_sacs' => $entriesToday,
            'exits_today_sacs'   => $exitsToday,
            'total_movements'    => $totalMovements,
        ]);
    }

    /**
     * Synchronisation (batch)
     */
    public function syncMovements(Request $request)
    {
        $request->validate([
            'movements' => 'required|array',
            'movements.*.warehouse_id' => 'required|integer',
            'movements.*.movement_date' => 'required|date',
            'movements.*.label' => 'required|string',
            'movements.*.movement_type' => 'required|in:entry,exit,adjustment,report',
            'movements.*.sacs' => 'required|numeric|min:0',
            'movements.*.kg' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        $results = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->movements as $idx => $data) {
                $ref = 'STK-' . strtoupper(Str::random(8));

                $balance = $this->calculateBalance($data['warehouse_id']);
                $newBalanceSacs = $balance['sacs'];
                $newBalanceKg   = $balance['kg'];

                if ($data['movement_type'] === 'entry' || $data['movement_type'] === 'report') {
                    $newBalanceSacs += $data['sacs'];
                    $newBalanceKg   += $data['kg'];
                } else {
                    $newBalanceSacs -= $data['sacs'];
                    $newBalanceKg   -= $data['kg'];
                }

                $movement = StockMovement::create([
                    'warehouse_id'    => $data['warehouse_id'],
                    'type'            => $data['movement_type'],
                    'quantity'        => $data['sacs'],
                    'quantity_before' => $balance['sacs'],
                    'quantity_after'  => $newBalanceSacs,
                    'reason'          => $data['label'],
                    'reference'       => $ref,
                    'created_by'      => $user->id,
                    'metadata'        => json_encode([
                        'entry_sacs' => $data['movement_type'] === 'entry' ? $data['sacs'] : 0,
                        'entry_kg'   => $data['movement_type'] === 'entry' ? $data['kg'] : 0,
                        'exit_sacs'  => $data['movement_type'] === 'exit' ? $data['sacs'] : 0,
                        'exit_kg'    => $data['movement_type'] === 'exit' ? $data['kg'] : 0,
                        'balance_sacs' => $newBalanceSacs,
                        'balance_kg'   => $newBalanceKg,
                        'position'     => $data['position'] ?? null,
                        'observation'  => $data['observation'] ?? null,
                        'movement_date' => $data['movement_date'],
                    ]),
                ]);

                Warehouse::where('id', $data['warehouse_id'])
                    ->update(['current_stock' => $newBalanceSacs]);

                $results[] = ['index' => $idx, 'reference' => $ref, 'status' => 'synced'];
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur synchronisation : ' . $e->getMessage()], 500);
        }

        return response()->json([
            'synced' => count($results),
            'errors' => $errors,
            'results' => $results,
        ]);
    }

    /**
     * Liste des produits autorisés + formats de sacs (kg)
     */
    public function getProducts(Request $request)
    {
        $products = \App\Models\Product::where('is_active', 1)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'unit', 'description'])
            ->map(function ($p) {
                $formats = [];
                $decoded = json_decode($p->description ?? '', true);
                if (is_array($decoded) && isset($decoded['formats_kg'])) {
                    $formats = $decoded['formats_kg'];
                }
                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'category'   => $p->category,
                    'unit'       => $p->unit,
                    'formats_kg' => $formats,
                ];
            });

        return response()->json(['products' => $products]);
    }

    /**
     * Transfert inter-magasin
     */
    public function storeTransfer(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'movement_date' => 'required|date',
            'label' => 'required|string|max:255',
            'sacs' => 'required|numeric|min:1',
            'kg' => 'required|numeric|min:1',
            'observation' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $sacs = (float) $request->sacs;
        $kg   = (float) $request->kg;

        $balanceSource = $this->calculateBalance($request->warehouse_id);
        if ($sacs > $balanceSource['sacs']) {
            return response()->json([
                'message' => 'Solde insuffisant pour le transfert. Disponible : ' . $balanceSource['sacs'] . ' sacs'
            ], 422);
        }

        $ref = 'TRF-' . strtoupper(Str::random(8));
        $numeroTransfert = 'T-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        DB::beginTransaction();
        try {
            // Sortie du magasin source
            $newBalanceSource = $balanceSource['sacs'] - $sacs;
            $movementSource = StockMovement::create([
                'warehouse_id'    => $request->warehouse_id,
                'stock_id'        => null,
                'type'            => 'transfer',
                'quantity'        => $sacs,
                'quantity_before' => $balanceSource['sacs'],
                'quantity_after'  => $newBalanceSource,
                'reason'          => $request->label . ' (Transfert vers: ' . Warehouse::find($request->destination_warehouse_id)->name . ')',
                'reference'       => $ref,
                'numero_transfert' => $numeroTransfert,
                'entrepot_destination_id' => $request->destination_warehouse_id,
                'created_by'      => $user->id,
            ]);
            $movementSource->metadata = json_encode([
                'transfer_type' => 'out',
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'destination_warehouse_name' => Warehouse::find($request->destination_warehouse_id)->name,
                'entry_sacs' => 0,
                'entry_kg'   => 0,
                'exit_sacs'  => $sacs,
                'exit_kg'    => $kg,
                'balance_sacs' => $newBalanceSource,
                'balance_kg'   => $newBalanceSource * ($kg / $sacs),
                'observation'  => $request->observation,
                'movement_date' => $request->movement_date,
                'numero_transfert' => $numeroTransfert,
            ]);
            $movementSource->save();

            // Entrée dans le magasin destination
            $balanceDest = $this->calculateBalance($request->destination_warehouse_id);
            $newBalanceDest = $balanceDest['sacs'] + $sacs;
            $movementDest = StockMovement::create([
                'warehouse_id'    => $request->destination_warehouse_id,
                'stock_id'        => null,
                'type'            => 'entry',
                'quantity'        => $sacs,
                'quantity_before' => $balanceDest['sacs'],
                'quantity_after'  => $newBalanceDest,
                'reason'          => $request->label . ' (Transfert depuis: ' . Warehouse::find($request->warehouse_id)->name . ')',
                'reference'       => $ref . '-IN',
                'numero_transfert' => $numeroTransfert,
                'entrepot_destination_id' => $request->warehouse_id,
                'created_by'      => $user->id,
            ]);
            $movementDest->metadata = json_encode([
                'transfer_type' => 'in',
                'source_warehouse_id' => $request->warehouse_id,
                'source_warehouse_name' => Warehouse::find($request->warehouse_id)->name,
                'entry_sacs' => $sacs,
                'entry_kg'   => $kg,
                'exit_sacs'  => 0,
                'exit_kg'    => 0,
                'balance_sacs' => $newBalanceDest,
                'balance_kg'   => $newBalanceDest * ($kg / $sacs),
                'observation'  => $request->observation,
                'movement_date' => $request->movement_date,
                'numero_transfert' => $numeroTransfert,
            ]);
            $movementDest->save();

            // Mise à jour des stocks courants
            Warehouse::where('id', $request->warehouse_id)
                ->update(['current_stock' => $newBalanceSource]);
            Warehouse::where('id', $request->destination_warehouse_id)
                ->update(['current_stock' => $newBalanceDest]);

            DB::commit();

            return response()->json([
                'message' => 'Transfert effectué avec succès.',
                'reference' => $ref,
                'numero_transfert' => $numeroTransfert,
                'source_balance_sacs' => $newBalanceSource,
                'dest_balance_sacs' => $newBalanceDest,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur transfert : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Inventaire physique (comptage réel vs théorique)
     */
    public function storeInventory(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_date' => 'required|date',
            'label' => 'required|string|max:255',
            'theoretical_sacs' => 'required|numeric|min:0',
            'actual_sacs' => 'required|numeric|min:0',
            'observation' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $theoretical = (float) $request->theoretical_sacs;
        $actual = (float) $request->actual_sacs;
        $diff = $actual - $theoretical;

        $ref = 'INV-' . strtoupper(Str::random(8));

        $movement = StockMovement::create([
            'warehouse_id'    => $request->warehouse_id,
            'stock_id'        => null,
            'type'            => 'adjustment',
            'quantity'        => abs($diff),
            'quantity_before' => $theoretical,
            'quantity_after'  => $actual,
            'reason'          => $request->label . ' (Inventaire)',
            'reference'       => $ref,
            'raison_ajustement' => 'Inventaire physique',
            'created_by'      => $user->id,
        ]);

        $movement->metadata = json_encode([
            'inventory_type' => true,
            'theoretical_sacs' => $theoretical,
            'actual_sacs' => $actual,
            'difference_sacs' => $diff,
            'observation' => $request->observation,
            'movement_date' => $request->movement_date,
        ]);
        $movement->save();

        Warehouse::where('id', $request->warehouse_id)
            ->update(['current_stock' => $actual]);

        return response()->json([
            'message' => 'Inventaire enregistré.',
            'reference' => $ref,
            'theoretical_sacs' => $theoretical,
            'actual_sacs' => $actual,
            'difference_sacs' => $diff,
            'movement' => $movement,
        ], 201);
    }

    /**
     * État du stock par produit/format (agrégation depuis mouvements)
     */
    public function getStockStatus(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $warehouseId = $request->input('warehouse_id');

        // Récupérer tous les mouvements pour ce magasin
        $movements = StockMovement::where('warehouse_id', $warehouseId)
            ->whereIn('type', ['entry', 'exit', 'adjustment', 'transfer'])
            ->get(['type', 'quantity', 'reason', 'metadata']);

        // Analyser les métadonnées pour extraire produit et format
        $stockByProduct = [];
        foreach ($movements as $m) {
            $meta = json_decode($m->metadata ?? '{}', true);
            $reason = $m->reason ?? '';
            // Extraire nom produit depuis le reason (ex: "Riz [25kg]")
            $productName = 'Non spécifié';
            $formatKg = 50;
            if (preg_match('/^(.+?)\s*\[(\d+)kg\]/', $reason, $matches)) {
                $productName = trim($matches[1]);
                $formatKg = (int) $matches[2];
            } elseif (preg_match('/Produit:\s*(.+?)\s*\|/', $reason, $matches)) {
                $productName = trim($matches[1]);
                if (isset($meta['format_kg'])) $formatKg = (int) $meta['format_kg'];
            }

            $key = $productName . '|' . $formatKg;
            if (!isset($stockByProduct[$key])) {
                $stockByProduct[$key] = [
                    'product_name' => $productName,
                    'format_kg' => $formatKg,
                    'total_sacs' => 0,
                    'total_kg' => 0,
                ];
            }

            if (in_array($m->type, ['entry', 'report'])) {
                $stockByProduct[$key]['total_sacs'] += $m->quantity;
            } elseif ($m->type === 'exit') {
                $stockByProduct[$key]['total_sacs'] -= $m->quantity;
            } elseif ($m->type === 'adjustment') {
                $stockByProduct[$key]['total_sacs'] = $m->quantity_after ?? 0;
            } elseif ($m->type === 'transfer') {
                $meta = json_decode($m->metadata ?? '{}', true);
                if (isset($meta['transfer_type']) && $meta['transfer_type'] === 'out') {
                    $stockByProduct[$key]['total_sacs'] -= $m->quantity;
                } elseif (isset($meta['transfer_type']) && $meta['transfer_type'] === 'in') {
                    $stockByProduct[$key]['total_sacs'] += $m->quantity;
                }
            }
        }

        // Recalculer KG
        foreach ($stockByProduct as $key => &$item) {
            $item['total_kg'] = $item['total_sacs'] * $item['format_kg'];
            $item['total_sacs'] = max(0, $item['total_sacs']);
            $item['total_kg'] = max(0, $item['total_kg']);
        }

        $warehouse = Warehouse::find($warehouseId);

        return response()->json([
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name ?? '',
            'warehouse_region' => $warehouse->region ?? '',
            'total_current_sacs' => $warehouse->current_stock ?? 0,
            'products' => array_values($stockByProduct),
        ]);
    }

    /**
     * Alertes de seuil (stock bas)
     */
    public function getAlerts(Request $request)
    {
        $user = $request->user();
        $threshold = $request->input('threshold', 100); // Seuil par défaut 100 sacs

        $query = Warehouse::where('status', 'active');

        if ($user->role === 'magasinier') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        $warehouses = $query->get(['id', 'name', 'region', 'capacity', 'current_stock']);
        $alerts = [];

        foreach ($warehouses as $wh) {
            $percentage = $wh->capacity > 0 ? ($wh->current_stock / $wh->capacity) * 100 : 0;
            if ($wh->current_stock <= $threshold || $percentage <= 10) {
                $alerts[] = [
                    'warehouse_id' => $wh->id,
                    'warehouse_name' => $wh->name,
                    'region' => $wh->region,
                    'current_stock' => $wh->current_stock,
                    'capacity' => $wh->capacity,
                    'percentage' => round($percentage, 1),
                    'severity' => $percentage <= 5 ? 'critical' : ($percentage <= 10 ? 'warning' : 'info'),
                ];
            }
        }

        return response()->json([
            'alerts' => $alerts,
            'threshold' => $threshold,
            'count' => count($alerts),
        ]);
    }

    /**
     * Reçu d'un mouvement (traçabilité)
     */
    public function getReceipt(Request $request, $reference)
    {
        $movement = StockMovement::with(['creator:id,name', 'warehouse:id,name,region'])
            ->where('reference', $reference)
            ->first();

        if (!$movement) {
            return response()->json(['message' => 'Mouvement non trouvé.'], 404);
        }

        $meta = json_decode($movement->metadata ?? '{}', true);

        return response()->json([
            'receipt' => [
                'reference' => $movement->reference,
                'numero_transfert' => $movement->numero_transfert,
                'type' => $movement->type,
                'type_label' => $this->movementTypeLabel($movement->type),
                'warehouse_name' => $movement->warehouse->name ?? '',
                'warehouse_region' => $movement->warehouse->region ?? '',
                'created_by' => $movement->creator->name ?? '',
                'created_at' => $movement->created_at->format('d/m/Y H:i:s'),
                'movement_date' => $meta['movement_date'] ?? $movement->created_at->format('Y-m-d'),
                'reason' => $movement->reason,
                'quantity_sacs' => $movement->quantity,
                'quantity_before' => $movement->quantity_before,
                'quantity_after' => $movement->quantity_after,
                'metadata' => $meta,
            ],
        ]);
    }

    private function movementTypeLabel($type)
    {
        $labels = [
            'entry' => 'Entrée de stock',
            'exit' => 'Sortie de stock',
            'adjustment' => 'Ajustement',
            'transfer' => 'Transfert',
            'report' => 'Report',
        ];
        return $labels[$type] ?? $type;
    }

    private function calculateBalance($warehouseId)
    {
        $entries = StockMovement::where('warehouse_id', $warehouseId)
            ->whereIn('type', ['entry', 'report'])
            ->sum('quantity');

        $exits = StockMovement::where('warehouse_id', $warehouseId)
            ->where('type', 'exit')
            ->sum('quantity');

        $adjustments = StockMovement::where('warehouse_id', $warehouseId)
            ->where('type', 'adjustment')
            ->sum('quantity');

        $sacs = $entries - $exits + $adjustments;

        return [
            'sacs' => max(0, $sacs),
            'kg'   => max(0, $sacs * 50),
        ];
    }
}
