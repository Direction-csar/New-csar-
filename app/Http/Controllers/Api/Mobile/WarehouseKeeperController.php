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

        if ($resp = $this->denyWarehouseAccess($request->user(), (int) $warehouseId)) {
            return $resp;
        }

        $query = StockMovement::with(['creator:id,name'])
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);

        $movements = $query->paginate(50);

        // Solde réel : tous les types (entry/exit/in/out/transfer/adjustment),
        // unités calculées depuis les formats enregistrés dans les libellés
        $balance = $this->calculateBalance($warehouseId);

        return response()->json([
            'movements' => $movements,
            'balance_sacs' => $balance['sacs'],
            'balance_kg'  => $balance['kg'],
            'balance_units' => $balance['units'],
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
            'client_ref' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $type = $request->movement_type;
        $sacs = (float) $request->sacs;
        $kg   = (float) $request->kg;

        if ($resp = $this->denyWarehouseAccess($user, (int) $request->warehouse_id)) {
            return $resp;
        }

        // Idempotence : un client_ref déjà enregistré renvoie le mouvement existant
        $clientRef = $request->client_ref;
        if ($clientRef) {
            $existing = StockMovement::where('reference', $clientRef)->first();
            if ($existing) {
                $meta = json_decode($existing->metadata ?? '{}', true);
                return response()->json([
                    'message' => 'Mouvement déjà enregistré.',
                    'reference' => $existing->reference,
                    'balance_sacs' => $meta['balance_sacs'] ?? null,
                    'balance_kg' => $meta['balance_kg'] ?? null,
                    'movement' => $existing,
                    'duplicate' => true,
                ], 200);
            }
        }

        try {
            $result = DB::transaction(function () use ($request, $user, $type, $sacs, $kg, $clientRef) {
                // Verrou sur le magasin pour sérialiser les sorties concurrentes
                Warehouse::where('id', $request->warehouse_id)->lockForUpdate()->first();

                // Vérifier solde suffisant pour sortie / ajustement négatif
                if (in_array($type, ['exit', 'adjustment'])) {
                    $balance = $this->calculateBalance($request->warehouse_id);
                    if ($sacs > $balance['sacs']) {
                        abort(response()->json([
                            'message' => 'Solde insuffisant. Disponible : ' . $balance['sacs'] . ' contenants'
                        ], 422));
                    }
                }

                // Réutiliser la référence client (idempotence) sinon en générer une
                $ref = $clientRef ?: 'STK-' . strtoupper(Str::random(8));

                $entrySacs = in_array($type, ['entry', 'report']) ? $sacs : 0;
                $entryKg   = in_array($type, ['entry', 'report']) ? $kg : 0;
                $exitSacs  = in_array($type, ['exit']) ? $sacs : 0;
                $exitKg    = in_array($type, ['exit']) ? $kg : 0;

                $balance = $this->calculateBalance($request->warehouse_id);
                $newBalanceSacs = $balance['sacs'] + $entrySacs - $exitSacs;
                $newBalanceKg   = $balance['kg'] + $entryKg - $exitKg;

                $movement = StockMovement::create([
                    'warehouse_id'    => $request->warehouse_id,
                    'stock_id'        => null,
                    'type'            => $type,
                    'quantity'        => $sacs,
                    'quantity_before' => $balance['sacs'],
                    'quantity_after'  => $newBalanceSacs,
                    'reason'          => $request->label,
                    'reference'       => $ref,
                    'created_by'      => $user->id,
                ]);

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
                    'client_ref'   => $clientRef,
                ]);
                $movement->save();

                Warehouse::where('id', $request->warehouse_id)
                    ->update(['current_stock' => $newBalanceSacs]);

                Log::info('Mouvement de stock créé', [
                    'user_id' => $user->id,
                    'warehouse_id' => $request->warehouse_id,
                    'reference' => $ref,
                    'type' => $type,
                    'sacs' => $sacs,
                ]);

                return [
                    'ref' => $ref,
                    'movement' => $movement,
                    'balance_sacs' => $newBalanceSacs,
                    'balance_kg' => $newBalanceKg,
                ];
            });
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur enregistrement : ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Mouvement enregistré avec succès.',
            'reference' => $result['ref'],
            'balance_sacs' => $result['balance_sacs'],
            'balance_kg' => $result['balance_kg'],
            'movement' => $result['movement'],
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
            ->whereIn('type', ['entry', 'in'])
            ->whereDate('created_at', $today)
            ->sum('quantity');

        $exitsToday = StockMovement::where('created_by', $user->id)
            ->whereIn('type', ['exit', 'out'])
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
            'movements.*.client_ref' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $results = [];
        $errors = [];

        foreach ($request->movements as $idx => $data) {
            // Contrôle d'accès par mouvement
            if ($this->denyWarehouseAccess($user, (int) $data['warehouse_id']) !== null) {
                $errors[] = ['index' => $idx, 'status' => 'rejected', 'message' => 'Magasin non autorisé'];
                continue;
            }

            // Idempotence : client_ref déjà enregistré -> confirmé sans doublon
            $clientRef = $data['client_ref'] ?? null;
            if ($clientRef) {
                $existing = StockMovement::where('reference', $clientRef)->first();
                if ($existing) {
                    $results[] = ['index' => $idx, 'reference' => $clientRef, 'status' => 'duplicate'];
                    continue;
                }
            }

            try {
                $ref = DB::transaction(function () use ($data, $user, $clientRef) {
                    Warehouse::where('id', $data['warehouse_id'])->lockForUpdate()->first();

                    $balance = $this->calculateBalance($data['warehouse_id']);
                    $newBalanceSacs = $balance['sacs'];
                    $newBalanceKg   = $balance['kg'];

                    if ($data['movement_type'] === 'entry' || $data['movement_type'] === 'report') {
                        $newBalanceSacs += $data['sacs'];
                        $newBalanceKg   += $data['kg'];
                    } else {
                        if ($data['sacs'] > $balance['sacs']) {
                            throw new \RuntimeException('Solde insuffisant (' . $balance['sacs'] . ')');
                        }
                        $newBalanceSacs -= $data['sacs'];
                        $newBalanceKg   -= $data['kg'];
                    }

                    $ref = $clientRef ?: 'STK-' . strtoupper(Str::random(8));

                    StockMovement::create([
                        'warehouse_id'    => $data['warehouse_id'],
                        'type'            => $data['movement_type'],
                        'quantity'        => $data['sacs'],
                        'quantity_before' => $balance['sacs'],
                        'quantity_after'  => $newBalanceSacs,
                        'reason'          => $data['label'],
                        'reference'       => $ref,
                        'created_by'      => $user->id,
                        'metadata'        => json_encode([
                            'entry_sacs' => in_array($data['movement_type'], ['entry', 'report']) ? $data['sacs'] : 0,
                            'entry_kg'   => in_array($data['movement_type'], ['entry', 'report']) ? $data['kg'] : 0,
                            'exit_sacs'  => $data['movement_type'] === 'exit' ? $data['sacs'] : 0,
                            'exit_kg'    => $data['movement_type'] === 'exit' ? $data['kg'] : 0,
                            'balance_sacs' => $newBalanceSacs,
                            'balance_kg'   => $newBalanceKg,
                            'position'     => $data['position'] ?? null,
                            'observation'  => $data['observation'] ?? null,
                            'movement_date' => $data['movement_date'],
                            'client_ref'   => $clientRef,
                        ]),
                    ]);

                    Warehouse::where('id', $data['warehouse_id'])
                        ->update(['current_stock' => $newBalanceSacs]);

                    return $ref;
                });

                $results[] = ['index' => $idx, 'reference' => $ref, 'status' => 'synced'];
            } catch (\Throwable $e) {
                $errors[] = ['index' => $idx, 'status' => 'rejected', 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'synced' => count(array_filter($results, fn($r) => $r['status'] === 'synced')),
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
            ->get(['id', 'name', 'category', 'unit', 'description', 'image'])
            ->map(function ($p) {
                $u = mb_strtolower(trim((string) $p->unit));
                $isLiquid = $u === 'l' || str_starts_with($u, 'litre');

                $decoded = json_decode($p->description ?? '', true);
                $formats = [];
                if (is_array($decoded)) {
                    if ($isLiquid && isset($decoded['formats_l'])) {
                        $formats = $decoded['formats_l'];
                    } elseif (!$isLiquid && isset($decoded['formats_kg'])) {
                        $formats = $decoded['formats_kg'];
                    }
                }
                // Formats par défaut adaptés à l'unité du produit
                if (empty($formats)) {
                    $formats = $isLiquid ? [1, 5, 10, 20, 25] : [5, 10, 25, 50, 100];
                }

                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'category'   => $p->category,
                    'unit'       => $isLiquid ? 'L' : 'kg',
                    'formats_kg' => $formats,
                    'image_url'  => $p->image_url,
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
            'client_ref' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $sacs = (float) $request->sacs;
        $kg   = (float) $request->kg;

        if ($resp = $this->denyWarehouseAccess($user, (int) $request->warehouse_id)) {
            return $resp;
        }
        if ($resp = $this->denyWarehouseAccess($user, (int) $request->destination_warehouse_id)) {
            return $resp;
        }

        // Idempotence : un client_ref déjà utilisé renvoie le transfert existant
        $clientRef = $request->client_ref;
        if ($clientRef) {
            $existing = StockMovement::where('reference', $clientRef)->first();
            if ($existing) {
                return response()->json([
                    'message' => 'Transfert déjà enregistré.',
                    'reference' => $existing->reference,
                    'numero_transfert' => $existing->numero_transfert,
                    'duplicate' => true,
                ], 200);
            }
        }

        $ref = $clientRef ?: 'TRF-' . strtoupper(Str::random(8));
        $numeroTransfert = 'T-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        DB::beginTransaction();
        try {
            // Verrous sur les deux magasins (ordre déterministe pour éviter les deadlocks)
            Warehouse::whereIn('id', [$request->warehouse_id, $request->destination_warehouse_id])
                ->orderBy('id')->lockForUpdate()->get();

            $balanceSource = $this->calculateBalance($request->warehouse_id);
            if ($sacs > $balanceSource['sacs']) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Solde insuffisant pour le transfert. Disponible : ' . $balanceSource['sacs'] . ' contenants'
                ], 422);
            }
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
                'metadata'        => json_encode([
                    'transfer_type' => 'out',
                    'client_ref' => $clientRef,
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
                ]),
            ]);

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

        if ($resp = $this->denyWarehouseAccess($user, (int) $request->warehouse_id)) {
            return $resp;
        }

        $theoretical = (float) $request->theoretical_sacs;
        $actual = (float) $request->actual_sacs;
        $diff = $actual - $theoretical;

        $ref = 'INV-' . strtoupper(Str::random(8));

        try {
            $movement = DB::transaction(function () use ($request, $user, $theoretical, $actual, $diff, $ref) {
                Warehouse::where('id', $request->warehouse_id)->lockForUpdate()->first();

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

                return $movement;
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur inventaire : ' . $e->getMessage()], 500);
        }

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

        if ($resp = $this->denyWarehouseAccess($request->user(), (int) $warehouseId)) {
            return $resp;
        }

        // Tous les types de mouvements : entry/exit/adjustment/transfer/report
        // (mobile) et in/out (back-office web)
        $movements = StockMovement::where('warehouse_id', $warehouseId)
            ->get(['type', 'quantity', 'quantity_before', 'quantity_after', 'reason', 'metadata', 'stock_id']);

        // Analyser les métadonnées pour extraire produit et format
        $stockByProduct = [];
        foreach ($movements as $m) {
            $meta = json_decode($m->metadata ?? '{}', true);
            $reason = $m->reason ?? '';
            // Extraire nom produit et format depuis le reason
            // (ex: "Riz [25kg]", "Huile [20L]")
            $productName = 'Non spécifié';
            $formatKg = 50;
            $formatUnit = 'kg';
            if (preg_match('/^(.+?)\s*\[(\d+)\s*(kg|L|litres?)\]/i', $reason, $matches)) {
                $productName = trim($matches[1]);
                $formatKg = (int) $matches[2];
                $formatUnit = strtolower($matches[3]) === 'kg' ? 'kg' : 'L';
            } elseif (preg_match('/Produit:\s*(.+?)\s*\|/', $reason, $matches)) {
                $productName = trim($matches[1]);
                if (isset($meta['format_kg'])) $formatKg = (int) $meta['format_kg'];
                if (isset($meta['format_unit'])) $formatUnit = $meta['format_unit'];
            } elseif ($m->stock_id) {
                // Mouvements saisis au back-office : nom via le lot (stocks)
                $stock = \App\Models\Stock::find($m->stock_id);
                if ($stock) {
                    $productName = $stock->item_name ?: $productName;
                    // Quantité saisie au web = quantité directe (format 1)
                    $formatKg = 1;
                }
            }

            $key = $productName . '|' . $formatKg . '|' . $formatUnit;
            if (!isset($stockByProduct[$key])) {
                $stockByProduct[$key] = [
                    'product_name' => $productName,
                    'format_kg' => $formatKg,
                    'unit' => $formatUnit,
                    'total_sacs' => 0,
                    'total_kg' => 0,
                ];
            }

            // Agrégation signée et unifiée : in/entry/report = +, out/exit/transfer-out = -,
            // adjustment = écart signé (before→after)
            $stockByProduct[$key]['total_sacs'] += $this->signedQuantity($m);
        }

        // Recalculer le total dans l'unité du produit (kg ou L)
        foreach ($stockByProduct as $key => &$item) {
            $item['total_kg'] = $item['total_sacs'] * $item['format_kg'];
            $item['total_sacs'] = max(0, $item['total_sacs']);
            $item['total_kg'] = max(0, $item['total_kg']);
        }

        unset($item);

        foreach ($stockByProduct as &$item) {
            $item['image_url'] = $this->productImageUrl($item['product_name']);
            // Unité de secours : celle du produit en base si non détectée
            if (($item['unit'] ?? 'kg') === 'kg') {
                $unit = $this->productUnit($item['product_name']);
                if ($unit !== null) $item['unit'] = $unit;
            }
        }
        unset($item);

        // Lots proches de la péremption (table stocks)
        $expiring = [];
        try {
            $expiring = \App\Models\Stock::where('warehouse_id', $warehouseId)
                ->where('is_active', true)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date')
                ->get(['item_name', 'quantity', 'expiry_date'])
                ->map(function ($s) {
                    $days = (int) now()->startOfDay()->diffInDays($s->expiry_date->copy()->startOfDay(), false);
                    return [
                        'product_name' => $s->item_name,
                        'quantity'     => (float) $s->quantity,
                        'expiry_date'  => $s->expiry_date->format('Y-m-d'),
                        'days_left'    => $days,
                        'image_url'    => $this->productImageUrl($s->item_name),
                    ];
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Péremption indisponible : ' . $e->getMessage());
        }

        $warehouse = Warehouse::find($warehouseId);

        return response()->json([
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name ?? '',
            'warehouse_region' => $warehouse->region ?? '',
            'total_current_sacs' => $warehouse->current_stock ?? 0,
            'products' => array_values($stockByProduct),
            'expiring' => $expiring,
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

    /**
     * URL de la photo d'un produit à partir de son nom (correspondance exacte, puis par préfixe).
     */
    private function productImageUrl(?string $name): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = \App\Models\Product::whereNotNull('image')
                ->get(['name', 'image'])
                ->mapWithKeys(fn ($p) => [mb_strtolower(trim($p->name)) => $p->image_url])
                ->all();
        }

        $key = mb_strtolower(trim((string) $name));
        if ($key === '') return null;
        if (isset($map[$key])) return $map[$key];

        foreach ($map as $productName => $url) {
            if (str_starts_with($key, $productName) || str_starts_with($productName, $key)) {
                return $url;
            }
        }
        return null;
    }

    /**
     * Unité d'un produit ('kg' ou 'L') d'après son nom, null si inconnu.
     */
    private function productUnit(?string $name): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = \App\Models\Product::get(['name', 'unit'])
                ->mapWithKeys(function ($p) {
                    $u = mb_strtolower(trim((string) $p->unit));
                    $liquid = $u === 'l' || str_starts_with($u, 'litre');
                    return [mb_strtolower(trim((string) $p->name)) => $liquid ? 'L' : 'kg'];
                })
                ->all();
        }

        $key = mb_strtolower(trim((string) $name));
        if ($key === '') return null;
        if (isset($map[$key])) return $map[$key];

        foreach ($map as $productName => $unit) {
            if (str_starts_with($key, $productName) || str_starts_with($productName, $key)) {
                return $unit;
            }
        }
        return null;
    }

    /**
     * Refuse l'accès à un magasin pour un magasinier qui n'y est pas rattaché.
     * Retourne une réponse 403 ou null si l'accès est autorisé.
     */
    private function denyWarehouseAccess($user, int $warehouseId): ?\Illuminate\Http\JsonResponse
    {
        if (!$user || $user->role !== 'magasinier') {
            return null; // admin / super_admin : accès libre
        }

        $allowed = Warehouse::where('id', $warehouseId)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            })
            ->exists();

        return $allowed
            ? null
            : response()->json(['message' => 'Magasin non autorisé.'], 403);
    }

    private function movementTypeLabel($type)
    {
        $labels = [
            'entry' => 'Entrée de stock',
            'exit' => 'Sortie de stock',
            'in' => 'Entrée de stock',
            'out' => 'Sortie de stock',
            'adjustment' => 'Ajustement',
            'transfer' => 'Transfert',
            'report' => 'Report',
        ];
        return $labels[$type] ?? $type;
    }

    /**
     * Signe d'un mouvement : +1 entrée, -1 sortie, écart signé pour ajustement.
     * Convention canonique : entry/report/in = entrée, exit/out = sortie,
     * transfer = sortie (l'entrée destination est enregistrée en type 'entry').
     */
    private function signedQuantity(StockMovement $m): float
    {
        switch ($m->type) {
            case 'entry':
            case 'report':
            case 'in':
                return (float) $m->quantity;
            case 'exit':
            case 'out':
            case 'transfer':
                return -(float) $m->quantity;
            case 'adjustment':
                $diff = (float) $m->quantity_after - (float) $m->quantity_before;
                return $diff != 0.0 ? $diff : (float) $m->quantity;
            default:
                return 0.0;
        }
    }

    /**
     * Extrait le format et l'unité d'un libellé « Produit [25kg] » / « Huile [20L] ».
     * Retourne [valeur, 'kg'|'L'] ou null si non détecté.
     */
    private function parseFormatFromReason(?string $reason): ?array
    {
        if ($reason && preg_match('/\[(\d+)\s*(kg|L|litres?)\]/i', $reason, $mm)) {
            return [(int) $mm[1], strtolower($mm[2]) === 'kg' ? 'kg' : 'L'];
        }
        return null;
    }

    private function calculateBalance($warehouseId)
    {
        $movements = StockMovement::where('warehouse_id', $warehouseId)
            ->get(['type', 'quantity', 'quantity_before', 'quantity_after', 'reason']);

        $sacs = 0;
        $units = [];
        foreach ($movements as $m) {
            $signed = $this->signedQuantity($m);
            $sacs += $signed;
            // Somme par unité réelle quand le libellé porte le format
            if (($fmt = $this->parseFormatFromReason($m->reason)) !== null) {
                [$val, $unit] = $fmt;
                $units[$unit] = ($units[$unit] ?? 0) + $signed * $val;
            }
        }

        return [
            'sacs'  => max(0, $sacs),
            'kg'    => max(0, $units['kg'] ?? 0),
            'units' => $units,
        ];
    }
}
