<?php

namespace App\Services\Magasin;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\RequestMission;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Logique métier du stock magasin — partagée entre l'API mobile
 * (WarehouseKeeperController) et le portail web /magasinier.
 * Toutes les méthodes renvoient des tableaux ; les contrôleurs
 * adaptent en JSON ou en vues.
 */
class WarehouseStockService
{
    /** Magasins accessibles par l'utilisateur (magasinier = ses affectations). */
    public function accessibleWarehouses(User $user)
    {
        $query = Warehouse::where('status', 'active');

        if ($user->role === 'magasinier') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        return $query->orderBy('name')->get();
    }

    /** Un magasinier ne voit que ses magasins ; admin/super_admin : tout. */
    public function canAccess(User $user, int $warehouseId): bool
    {
        if ($user->role !== 'magasinier') {
            return true;
        }

        return Warehouse::where('id', $warehouseId)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            })
            ->exists();
    }

    /** Mouvements qui ne comptent pas dans le stock officiel (circuit de vérification). */
    private const STATUTS_HORS_STOCK_OFFICIEL = ['en_attente', 'rejetee', 'anomalie', 'rejected'];

    /** Solde officiel du magasin : mouvements vérifiés uniquement. */
    public function balance(int $warehouseId): array
    {
        $movements = StockMovement::where('warehouse_id', $warehouseId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', self::STATUTS_HORS_STOCK_OFFICIEL);
            })
            ->get(['type', 'quantity', 'quantity_before', 'quantity_after', 'reason']);

        $sacs = 0;
        $units = [];
        foreach ($movements as $m) {
            $signed = $this->signedQuantity($m);
            $sacs += $signed;
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

    /**
     * Enregistre une entrée / sortie / ajustement / report.
     * Retourne ['ok'=>bool, 'message'=>?, 'duplicate'=>?, 'reference'=>?,
     *           'balance_sacs'=>?, 'balance_kg'=>?, 'movement'=>?]
     */
    public function recordMovement(User $user, array $data): array
    {
        $type = $data['movement_type'];
        $sacs = (float) $data['sacs'];
        $kg = (float) $data['kg'];
        $warehouseId = (int) $data['warehouse_id'];

        if (!$this->canAccess($user, $warehouseId)) {
            return ['ok' => false, 'message' => 'Magasin non autorisé.', 'status' => 403];
        }

        // Idempotence : un client_ref déjà enregistré renvoie le mouvement existant
        $clientRef = $data['client_ref'] ?? null;
        if ($clientRef) {
            $existing = StockMovement::where('reference', $clientRef)->first();
            if ($existing) {
                $meta = json_decode($existing->metadata ?? '{}', true);
                return [
                    'ok' => true, 'duplicate' => true,
                    'message' => 'Mouvement déjà enregistré.',
                    'reference' => $existing->reference,
                    'balance_sacs' => $meta['balance_sacs'] ?? null,
                    'balance_kg' => $meta['balance_kg'] ?? null,
                    'movement' => $existing,
                ];
            }
        }

        try {
            $result = DB::transaction(function () use ($data, $user, $type, $sacs, $kg, $warehouseId, $clientRef) {
                Warehouse::where('id', $warehouseId)->lockForUpdate()->first();

                if (in_array($type, ['exit', 'adjustment'], true)) {
                    $balance = $this->balance($warehouseId);
                    if ($sacs > $balance['sacs']) {
                        return ['error' => 'Solde insuffisant. Disponible : ' . $balance['sacs'] . ' contenants'];
                    }
                    if ($manque = $this->productShortage($warehouseId, (string) $data['label'], $sacs)) {
                        return ['error' => $manque];
                    }
                }

                $ref = $clientRef ?: 'STK-' . strtoupper(Str::random(8));

                $entrySacs = in_array($type, ['entry', 'report'], true) ? $sacs : 0;
                $entryKg = in_array($type, ['entry', 'report'], true) ? $kg : 0;
                $exitSacs = $type === 'exit' ? $sacs : 0;
                $exitKg = $type === 'exit' ? $kg : 0;

                $balance = $this->balance($warehouseId);
                $newBalanceSacs = $balance['sacs'] + $entrySacs - $exitSacs;
                $newBalanceKg = $balance['kg'] + $entryKg - $exitKg;

                $movement = StockMovement::create([
                    'warehouse_id' => $warehouseId,
                    'stock_id' => null,
                    'type' => $type,
                    'quantity' => $sacs,
                    'quantity_before' => $balance['sacs'],
                    'quantity_after' => $newBalanceSacs,
                    'reason' => $data['label'],
                    'reference' => $ref,
                    'created_by' => $user->id,
                    'status' => 'en_attente',
                ]);

                $movement->metadata = json_encode([
                    'entry_sacs' => $entrySacs,
                    'entry_kg' => $entryKg,
                    'exit_sacs' => $exitSacs,
                    'exit_kg' => $exitKg,
                    'balance_sacs' => $newBalanceSacs,
                    'balance_kg' => $newBalanceKg,
                    'position' => $data['position'] ?? null,
                    'observation' => $data['observation'] ?? null,
                    'movement_date' => $data['movement_date'],
                    'photo_base64' => $data['photo_base64'] ?? null,
                    'signature_base64' => $data['signature_base64'] ?? null,
                    'client_ref' => $clientRef,
                ]);
                $movement->save();

                // Le stock officiel (warehouses.current_stock) n'est PAS mis à jour :
                // le mouvement reste 'en_attente' jusqu'à vérification inspecteur/DTL/DG.

                Log::info('Mouvement de stock créé (en attente de vérification)', [
                    'user_id' => $user->id,
                    'warehouse_id' => $warehouseId,
                    'reference' => $ref,
                    'type' => $type,
                    'sacs' => $sacs,
                ]);

                return ['ref' => $ref, 'movement' => $movement, 'balance_sacs' => $balance['sacs'], 'balance_kg' => $balance['kg']];
            });
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur enregistrement : ' . $e->getMessage(), 'status' => 500];
        }

        if (isset($result['error'])) {
            return ['ok' => false, 'message' => $result['error'], 'status' => 422];
        }

        $this->auditMovement($user, 'stock_movement.create', $result['movement'],
            "Mouvement {$type} de {$sacs} contenants sur le magasin #{$warehouseId} (réf. {$result['ref']}) — en attente de vérification");

        return [
            'ok' => true,
            'message' => 'Mouvement enregistré — en attente de vérification par l\'inspecteur.',
            'reference' => $result['ref'],
            'balance_sacs' => $result['balance_sacs'],
            'balance_kg' => $result['balance_kg'],
            'movement' => $result['movement'],
        ];
    }

    /** Transfert inter-magasins (sortie source + entrée destination). */
    public function recordTransfer(User $user, array $data): array
    {
        $warehouseId = (int) $data['warehouse_id'];
        $destId = (int) $data['destination_warehouse_id'];
        $sacs = (float) $data['sacs'];
        $kg = (float) $data['kg'];

        if (!$this->canAccess($user, $warehouseId) || !$this->canAccess($user, $destId)) {
            return ['ok' => false, 'message' => 'Magasin non autorisé.', 'status' => 403];
        }

        $clientRef = $data['client_ref'] ?? null;
        if ($clientRef) {
            $existing = StockMovement::where('reference', $clientRef)->first();
            if ($existing) {
                return [
                    'ok' => true, 'duplicate' => true,
                    'message' => 'Transfert déjà enregistré.',
                    'reference' => $existing->reference,
                    'numero_transfert' => $existing->numero_transfert,
                ];
            }
        }

        $ref = $clientRef ?: 'TRF-' . strtoupper(Str::random(8));
        $numeroTransfert = 'T-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        $sourceName = Warehouse::find($warehouseId)?->name;
        $destName = Warehouse::find($destId)?->name;

        try {
            DB::beginTransaction();

            Warehouse::whereIn('id', [$warehouseId, $destId])->orderBy('id')->lockForUpdate()->get();

            $balanceSource = $this->balance($warehouseId);
            if ($sacs > $balanceSource['sacs']) {
                DB::rollBack();
                return ['ok' => false, 'message' => 'Solde insuffisant pour le transfert. Disponible : ' . $balanceSource['sacs'] . ' contenants', 'status' => 422];
            }
            if ($manque = $this->productShortage($warehouseId, (string) $data['label'], $sacs)) {
                DB::rollBack();
                return ['ok' => false, 'message' => $manque, 'status' => 422];
            }

            $newBalanceSource = $balanceSource['sacs'] - $sacs;
            $movementSource = StockMovement::create([
                'warehouse_id' => $warehouseId,
                'stock_id' => null,
                'type' => 'transfer',
                'quantity' => $sacs,
                'quantity_before' => $balanceSource['sacs'],
                'quantity_after' => $newBalanceSource,
                'reason' => $data['label'] . ' (Transfert vers: ' . $destName . ')',
                'reference' => $ref,
                'numero_transfert' => $numeroTransfert,
                'entrepot_destination_id' => $destId,
                'created_by' => $user->id,
                'status' => 'en_attente',
                'metadata' => json_encode([
                    'transfer_type' => 'out',
                    'client_ref' => $clientRef,
                    'destination_warehouse_id' => $destId,
                    'destination_warehouse_name' => $destName,
                    'entry_sacs' => 0, 'entry_kg' => 0,
                    'exit_sacs' => $sacs, 'exit_kg' => $kg,
                    'balance_sacs' => $newBalanceSource,
                    'balance_kg' => $newBalanceSource * ($kg / $sacs),
                    'observation' => $data['observation'] ?? null,
                    'movement_date' => $data['movement_date'],
                    'numero_transfert' => $numeroTransfert,
                ]),
            ]);

            $balanceDest = $this->balance($destId);
            $newBalanceDest = $balanceDest['sacs'] + $sacs;
            $movementDest = StockMovement::create([
                'warehouse_id' => $destId,
                'stock_id' => null,
                'type' => 'entry',
                'quantity' => $sacs,
                'quantity_before' => $balanceDest['sacs'],
                'quantity_after' => $newBalanceDest,
                'reason' => $data['label'] . ' (Transfert depuis: ' . $sourceName . ')',
                'reference' => $ref . '-IN',
                'numero_transfert' => $numeroTransfert,
                'entrepot_destination_id' => $warehouseId,
                'created_by' => $user->id,
                'status' => 'en_attente',
            ]);
            $movementDest->metadata = json_encode([
                'transfer_type' => 'in',
                'source_warehouse_id' => $warehouseId,
                'source_warehouse_name' => $sourceName,
                'entry_sacs' => $sacs, 'entry_kg' => $kg,
                'exit_sacs' => 0, 'exit_kg' => 0,
                'balance_sacs' => $newBalanceDest,
                'balance_kg' => $newBalanceDest * ($kg / $sacs),
                'observation' => $data['observation'] ?? null,
                'movement_date' => $data['movement_date'],
                'numero_transfert' => $numeroTransfert,
            ]);
            $movementDest->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['ok' => false, 'message' => 'Erreur transfert : ' . $e->getMessage(), 'status' => 500];
        }

        $this->auditMovement($user, 'stock_movement.transfer', $movementSource,
            "Transfert {$numeroTransfert} : {$sacs} contenants de #{$warehouseId} vers #{$destId} (réf. {$ref}) — en attente de vérification");

        return [
            'ok' => true,
            'message' => 'Transfert enregistré — en attente de vérification (sortie par l\'inspecteur, réception par le magasin destinataire).',
            'reference' => $ref,
            'numero_transfert' => $numeroTransfert,
            'source_balance_sacs' => $newBalanceSource,
            'dest_balance_sacs' => $newBalanceDest,
        ];
    }

    /** Inventaire physique : ajuste le stock au comptage réel. */
    public function recordInventory(User $user, array $data): array
    {
        $warehouseId = (int) $data['warehouse_id'];

        if (!$this->canAccess($user, $warehouseId)) {
            return ['ok' => false, 'message' => 'Magasin non autorisé.', 'status' => 403];
        }

        $theoretical = (float) $data['theoretical_sacs'];
        $actual = (float) $data['actual_sacs'];
        $ref = 'INV-' . strtoupper(Str::random(8));

        try {
            $result = DB::transaction(function () use ($data, $user, $warehouseId, $theoretical, $actual, $ref) {
                Warehouse::where('id', $warehouseId)->lockForUpdate()->first();

                // Référence = stock officiel (mouvements vérifiés) au moment du comptage.
                // L'écart appliqué à la vérification = comptage réel − stock officiel.
                $official = $this->balance($warehouseId)['sacs'];
                $diff = $actual - $official;

                $movement = StockMovement::create([
                    'warehouse_id' => $warehouseId,
                    'stock_id' => null,
                    'type' => 'adjustment',
                    'quantity' => abs($diff),
                    'quantity_before' => $official,
                    'quantity_after' => $actual,
                    'reason' => $data['label'] . ' (Inventaire)',
                    'reference' => $ref,
                    'raison_ajustement' => 'Inventaire physique',
                    'created_by' => $user->id,
                    'status' => 'en_attente',
                ]);

                $movement->metadata = json_encode([
                    'inventory_type' => true,
                    'theoretical_sacs' => $theoretical,
                    'official_sacs' => $official,
                    'actual_sacs' => $actual,
                    'difference_sacs' => $diff,
                    'observation' => $data['observation'] ?? null,
                    'movement_date' => $data['movement_date'],
                ]);
                $movement->save();

                // current_stock inchangé : correction appliquée après vérification.
                return ['movement' => $movement, 'diff' => $diff];
            });
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur inventaire : ' . $e->getMessage(), 'status' => 500];
        }

        $this->auditMovement($user, 'stock_movement.inventory', $result['movement'],
            "Inventaire physique sur le magasin #{$warehouseId} : théorique {$theoretical}, réel {$actual} (réf. {$ref}) — en attente de vérification");

        return [
            'ok' => true,
            'message' => 'Inventaire enregistré — en attente de vérification avant correction du stock officiel.',
            'reference' => $ref,
            'theoretical_sacs' => $theoretical,
            'actual_sacs' => $actual,
            'difference_sacs' => $result['diff'],
            'movement' => $result['movement'],
        ];
    }

    /** État du stock par produit/format (agrégation des mouvements). */
    public function stockStatus(int $warehouseId): array
    {
        $movements = StockMovement::where('warehouse_id', $warehouseId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', self::STATUTS_HORS_STOCK_OFFICIEL);
            })
            ->get(['type', 'quantity', 'quantity_before', 'quantity_after', 'reason', 'metadata', 'stock_id']);

        $stockByProduct = [];
        foreach ($movements as $m) {
            $meta = json_decode($m->metadata ?? '{}', true);
            $reason = $m->reason ?? '';
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
                $stock = Stock::find($m->stock_id);
                if ($stock) {
                    $productName = $stock->item_name ?: $productName;
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
            $stockByProduct[$key]['total_sacs'] += $this->signedQuantity($m);
        }

        foreach ($stockByProduct as $key => &$item) {
            $item['total_kg'] = $item['total_sacs'] * $item['format_kg'];
            $item['total_sacs'] = max(0, $item['total_sacs']);
            $item['total_kg'] = max(0, $item['total_kg']);
        }
        unset($item);

        foreach ($stockByProduct as &$item) {
            $item['image_url'] = $this->productImageUrl($item['product_name']);
            if (($item['unit'] ?? 'kg') === 'kg') {
                $unit = $this->productUnit($item['product_name']);
                if ($unit !== null) $item['unit'] = $unit;
            }
        }
        unset($item);

        $expiring = [];
        try {
            $expiring = Stock::where('warehouse_id', $warehouseId)
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
                        'quantity' => (float) $s->quantity,
                        'expiry_date' => $s->expiry_date->format('Y-m-d'),
                        'days_left' => $days,
                        'image_url' => $this->productImageUrl($s->item_name),
                    ];
                })->values()->all();
        } catch (\Throwable $e) {
            Log::warning('Péremption indisponible : ' . $e->getMessage());
        }

        $warehouse = Warehouse::find($warehouseId);

        return [
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name ?? '',
            'warehouse_region' => $warehouse->region ?? '',
            'total_current_sacs' => $warehouse->current_stock ?? 0,
            'products' => array_values($stockByProduct),
            'expiring' => $expiring,
        ];
    }

    /**
     * Situation temps réel de tous les magasins (même source que le portail magasinier et l'APK).
     *
     * @return array{generated_at: string, warehouses: list<array>, totals: list<array>, recent: list<array>}
     */
    public function situation(): array
    {
        $warehouses = [];
        $totals = [];
        foreach (Warehouse::where('is_active', true)->orderBy('region')->orderBy('name')->get() as $w) {
            $products = [];
            foreach ($this->stockStatus($w->id)['products'] as $p) {
                if ($p['total_sacs'] <= 0) {
                    continue;
                }
                $unit = $p['unit'] === 'L' ? 'L' : 'kg';
                $products[] = ['name' => $p['product_name'], 'format' => (int) $p['format_kg'], 'unit' => $unit,
                    'contenants' => (float) $p['total_sacs'], 'quantite' => (float) $p['total_kg']];
                $key = $p['product_name'] . '|' . $unit;
                $totals[$key] ??= ['name' => $p['product_name'], 'unit' => $unit, 'quantite' => 0.0, 'contenants' => 0.0];
                $totals[$key]['quantite'] += (float) $p['total_kg'];
                $totals[$key]['contenants'] += (float) $p['total_sacs'];
            }
            $last = StockMovement::where('warehouse_id', $w->id)->latest('id')->first();
            $warehouses[] = [
                'id' => $w->id, 'name' => $w->name, 'region' => $w->region,
                'products' => $products,
                'total_kg' => array_sum(array_map(fn ($x) => $x['unit'] === 'kg' ? $x['quantite'] : 0, $products)),
                'total_l' => array_sum(array_map(fn ($x) => $x['unit'] === 'L' ? $x['quantite'] : 0, $products)),
                'last_movement_at' => $last?->created_at?->format('d/m/Y H:i'),
            ];
        }

        $recent = StockMovement::with('warehouse:id,name')->latest('id')->limit(12)->get()->map(fn ($m) => [
            'date' => $m->created_at?->format('d/m/Y H:i'),
            'warehouse' => $m->warehouse?->name,
            'type' => $this->movementTypeLabel($m->type),
            'reason' => $m->reason,
            'quantity' => (float) $m->quantity,
        ])->all();

        usort($totals, fn ($a, $b) => strcmp($a['name'], $b['name']));

        // Circuit de vérification : opérations et mouvements en attente
        // (tables absentes des schémas de test minimaux → ignorées)
        $hasOps = \Illuminate\Support\Facades\Schema::hasTable('stock_operations');
        $pendingOps = $hasOps ? \App\Models\StockOperation::whereIn('status', [
            'soumise', 'affectee_inspecteur', 'transmise_magasinier', 'executee', 'anomalie',
        ])->count() : 0;
        $pendingMovements = StockMovement::whereIn('status', ['en_attente', 'anomalie'])->count();
        $pendingList = $hasOps
            ? StockMovement::with(['warehouse:id,name', 'creator:id,name', 'stockOperation:id,reference,status'])
                ->where('status', 'en_attente')->latest('id')->limit(10)->get()
                ->map(fn ($m) => [
                    'date' => $m->created_at?->format('d/m/Y H:i'),
                    'warehouse' => $m->warehouse?->name,
                    'operation' => $m->stockOperation?->reference,
                    'reason' => $m->reason,
                    'quantity' => (float) $m->quantity,
                ])->all()
            : [];

        return [
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'warehouses' => $warehouses,
            'totals' => array_values($totals),
            'recent' => $recent,
            'operations' => [
                'en_cours' => $pendingOps,
                'mouvements_en_attente' => $pendingMovements,
                'derniers_en_attente' => $pendingList,
            ],
        ];
    }

    /** Produits autorisés + formats de conditionnement. */
    public function products(): array
    {
        return Product::where('is_active', 1)
            ->orderBy('category')->orderBy('name')
            ->get(['id', 'name', 'category', 'unit', 'description', 'image'])
            ->map(function ($p) {
                $u = mb_strtolower(trim((string) $p->unit));
                $isLiquid = $u === 'l' || str_starts_with($u, 'litre');

                $decoded = json_decode($p->description ?? '', true);
                $formats = [];
                if (is_array($decoded)) {
                    $formats = $isLiquid && isset($decoded['formats_l'])
                        ? $decoded['formats_l']
                        : (!$isLiquid && isset($decoded['formats_kg']) ? $decoded['formats_kg'] : []);
                }
                if (empty($formats)) {
                    $formats = $isLiquid ? [1, 5, 10, 20, 25] : [5, 10, 25, 50, 100];
                }

                return [
                    'id' => $p->id, 'name' => $p->name, 'category' => $p->category,
                    'unit' => $isLiquid ? 'L' : 'kg', 'formats_kg' => $formats,
                    'image_url' => $p->image_url,
                ];
            })->all();
    }

    /** Statistiques du magasinier (comme l'APK). */
    public function stats(User $user): array
    {
        $today = now()->format('Y-m-d');
        $entriesToday = StockMovement::where('created_by', $user->id)
            ->whereIn('type', ['entry', 'in'])->whereDate('created_at', $today)->sum('quantity');
        $exitsToday = StockMovement::where('created_by', $user->id)
            ->whereIn('type', ['exit', 'out'])->whereDate('created_at', $today)->sum('quantity');
        $totalMovements = StockMovement::where('created_by', $user->id)->count();

        return [
            'entries_today_sacs' => $entriesToday,
            'exits_today_sacs' => $exitsToday,
            'total_movements' => $totalMovements,
        ];
    }

    /** Magasins sous le seuil (stock bas / capacité). */
    public function alerts(User $user, $threshold = 100): array
    {
        $query = Warehouse::where('status', 'active');
        if ($user->role === 'magasinier') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('id', $user->id);
            });
        }

        $alerts = [];
        foreach ($query->get(['id', 'name', 'region', 'capacity', 'current_stock']) as $wh) {
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

        return $alerts;
    }

    /** Missions de sortie de stock affectées au magasinier par le DTL. */
    public function missions(User $user, ?string $status = null)
    {
        $query = RequestMission::where('type', RequestMission::TYPE_STOCK_EXIT)
            ->with('request:id,tracking_code,full_name,region,workflow_status');

        if ($user->role === 'magasinier') {
            $query->where('assigned_to', $user->id);
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['affectee', 'en_cours']);
        }

        return $query->orderBy('created_at', 'desc')->limit(50)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'tracking_code' => $m->request?->tracking_code,
                'demandeur' => $m->request?->full_name,
                'region' => $m->request?->region,
                'instructions' => $m->instructions,
                'status' => $m->status,
                'assigned_at' => $m->created_at?->toIso8601String(),
            ];
        });
    }

    /**
     * Clôturer une mission de sortie avec justificatif (référence mouvement).
     * Retourne ['ok'=>bool, 'message'=>?, 'status'=>?]
     */
    public function reportMission(User $user, int $id, string $report, ?string $movementReference): array
    {
        $mission = RequestMission::where('type', RequestMission::TYPE_STOCK_EXIT)->findOrFail($id);

        if ($user->role === 'magasinier' && $mission->assigned_to !== $user->id) {
            return ['ok' => false, 'message' => 'Mission non affectée à cet utilisateur.', 'status' => 403];
        }
        if (in_array($mission->status, ['terminee', 'annulee'], true)) {
            return ['ok' => false, 'message' => 'Mission déjà clôturée.', 'status' => 409];
        }

        $movementId = null;
        if ($movementReference) {
            $movement = StockMovement::where('reference', $movementReference)->first();
            if (!$movement) {
                return ['ok' => false, 'message' => 'Référence de mouvement introuvable.', 'status' => 422];
            }
            if (!in_array($movement->type, ['out', 'exit'], true)) {
                return ['ok' => false, 'message' => 'La référence doit correspondre à une sortie de stock.', 'status' => 422];
            }
            if (!$this->canAccess($user, (int) $movement->warehouse_id)) {
                return ['ok' => false, 'message' => 'Magasin non autorisé.', 'status' => 403];
            }
            $movementId = $movement->id;
        }

        DB::transaction(function () use ($mission, $report, $movementId, $user) {
            $mission->update([
                'status' => 'terminee',
                'report' => $report,
                'completed_at' => now(),
            ]);

            if ($movementId) {
                StockMovement::where('id', $movementId)->update([
                    'public_request_id' => $mission->public_request_id,
                    'request_mission_id' => $mission->id,
                ]);
            }

            $mission->request?->logWorkflowAction(
                'Sortie de stock exécutée',
                "Mission #{$mission->id} clôturée par {$user->name} : {$report}",
                $user->id
            );
        });

        return ['ok' => true, 'message' => 'Mission clôturée avec justificatif.'];
    }

    /** Reçu d'un mouvement (traçabilité). */
    public function receipt(string $reference): ?array
    {
        $movement = StockMovement::with(['creator:id,name', 'warehouse:id,name,region'])
            ->where('reference', $reference)->first();

        if (!$movement) {
            return null;
        }

        $meta = json_decode($movement->metadata ?? '{}', true);

        return [
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
        ];
    }

    /** Fiche de stock : mouvements paginés + filtres + solde. */
    public function stockSheet(int $warehouseId, ?string $dateFrom, ?string $dateTo, int $perPage = 50)
    {
        $query = StockMovement::with(['creator:id,name'])
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        return [
            'movements' => $query->paginate($perPage)->withQueryString(),
            'balance' => $this->balance($warehouseId),
        ];
    }

    // ------------------------------------------------------------------
    // Helpers privés (miroir de WarehouseKeeperController)
    // ------------------------------------------------------------------

    /**
     * Vérifie la sortie contre le stock RÉEL du produit (et de son format) :
     * on ne peut pas sortir du sucre avec le stock de riz.
     * Retourne un message d'erreur, ou null si le libellé n'indique pas de produit/format ou si le stock suffit.
     */
    public function productShortage(int $warehouseId, string $label, float $sacs): ?string
    {
        if (!preg_match('/^(.+?)\s*\[(\d+)\s*(kg|L|litres?)\]/i', $label, $m)) {
            return null;
        }
        $norm = fn ($s) => mb_strtolower(\Illuminate\Support\Str::ascii(trim((string) $s)));
        $name = trim($m[1]);
        $format = (int) $m[2];
        $available = 0.0;
        foreach ($this->stockStatus($warehouseId)['products'] as $p) {
            if ($norm($p['product_name']) === $norm($name) && (int) $p['format_kg'] === $format) {
                $available = (float) $p['total_sacs'];
                break;
            }
        }

        return $sacs > $available
            ? sprintf('Stock insuffisant pour %s [%d%s]. Disponible : %s contenants', $name, $format, strtolower($m[3]) === 'kg' ? 'kg' : 'L', rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.'))
            : null;
    }

    /** Variation signée d'un mouvement (entrée +, sortie -, ajustement = écart). */
    public function signedDelta(StockMovement $m): float
    {
        return $this->signedQuantity($m);
    }

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

    private function parseFormatFromReason(?string $reason): ?array
    {
        if ($reason && preg_match('/\[(\d+)\s*(kg|L|litres?)\]/i', $reason, $mm)) {
            return [(int) $mm[1], strtolower($mm[2]) === 'kg' ? 'kg' : 'L'];
        }
        return null;
    }

    private function productImageUrl(?string $name): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = Product::whereNotNull('image')
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

    private function productUnit(?string $name): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = Product::get(['name', 'unit'])
                ->mapWithKeys(function ($p) {
                    $u = mb_strtolower(trim((string) $p->unit));
                    $liquid = $u === 'l' || str_starts_with($u, 'litre');
                    return [mb_strtolower(trim((string) $p->name)) => $liquid ? 'L' : 'kg'];
                })->all();
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

    /** Journalise un mouvement dans audit_logs (traçabilité complète). */
    private function auditMovement(User $user, string $action, StockMovement $movement, string $description): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'level' => 'info',
                'status' => 'success',
                'model_type' => StockMovement::class,
                'model_id' => $movement->id,
                'description' => $description,
                'data' => [
                    'reference' => $movement->reference,
                    'warehouse_id' => $movement->warehouse_id,
                    'type' => $movement->type,
                    'quantity' => $movement->quantity,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Audit mouvement échoué : ' . $e->getMessage());
        }
    }

    public function movementTypeLabel($type): string
    {
        return [
            'entry' => 'Entrée de stock',
            'exit' => 'Sortie de stock',
            'in' => 'Entrée de stock',
            'out' => 'Sortie de stock',
            'adjustment' => 'Ajustement',
            'transfer' => 'Transfert',
            'report' => 'Report',
        ][$type] ?? (string) $type;
    }

    // ==================================================================
    // File de vérification : mouvements saisis directement (hors
    // stock_operations) — entrées, sorties, transferts, inventaires.
    // Aucun ne touche le stock officiel tant qu'il n'est pas vérifié.
    // ==================================================================

    /** Rôles habilités à vérifier un mouvement de type entrée/sortie/transfert. */
    private const VERIFY_ROLES_OPERATION = ['inspecteur', 'dtl', 'dg', 'admin', 'super_admin'];

    /** Rôles habilités à valider un ajustement / inventaire physique. */
    private const VERIFY_ROLES_ADJUSTMENT = ['dtl', 'dg', 'admin', 'super_admin'];

    /** Un mouvement est une correction de stock (inventaire / ajustement). */
    public function isAdjustmentMovement(StockMovement $m): bool
    {
        if ($m->type === 'adjustment') {
            return true;
        }
        $meta = json_decode($m->metadata ?? '{}', true);
        return !empty($meta['inventory_type']);
    }

    /** Le mouvement est la jambe « entrée » d'un transfert. */
    public function isTransferIn(StockMovement $m): bool
    {
        if ($m->type !== 'entry') {
            return false;
        }
        $meta = json_decode($m->metadata ?? '{}', true);
        return ($meta['transfer_type'] ?? null) === 'in';
    }

    /**
     * Mouvements 'en_attente' hors circuit stock_operations.
     * - magasinier : ceux de ses magasins accessibles (y compris réceptions de transfert)
     * - inspecteur / dtl / dg / admin : tous
     */
    public function pendingMovements(User $user, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $q = StockMovement::with(['warehouse:id,name,region', 'creator:id,name'])
            ->where('status', 'en_attente')
            ->orderByDesc('id');

        $isMagasinier = $user->role === 'magasinier';
        if (!$isMagasinier) {
            // Les mouvements rattachés à une stock_operation sont gérés
            // par le circuit dédié (vérification groupée de l'opération).
            $q->whereNull('stock_operation_id');
        }

        if ($isMagasinier) {
            $ids = $this->accessibleWarehouses($user)->pluck('id')->all();
            $q->whereIn('warehouse_id', $ids ?: [0]);
        }

        if (!empty($filters['warehouse_id'])) {
            $q->where('warehouse_id', (int) $filters['warehouse_id']);
        }
        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        $rows = $q->get();

        if ($isMagasinier) {
            // Le magasinier voit ses mouvements directs + les réceptions de
            // transfert (y compris celles issues d'une stock_operation) pour
            // pouvoir confirmer la réception.
            $rows = $rows->filter(fn ($m) => !$m->stock_operation_id || $this->isTransferIn($m))->values();
        }

        return $rows;
    }

    /**
     * Vérifie un mouvement en attente → l'intègre au stock officiel.
     * Règles : ajustements/inventaires → DTL/DG/admin ; transferts « in » →
     * confirmation de réception du magasin destinataire exigée (la direction
     * peut passer outre) ; les sorties revérifient le solde officiel.
     */
    public function verifyPendingMovement(User $user, int $movementId): array
    {
        try {
            return DB::transaction(function () use ($user, $movementId) {
                $m = StockMovement::whereKey($movementId)->lockForUpdate()->first();
                if (!$m) {
                    return ['ok' => false, 'message' => 'Mouvement introuvable.', 'status' => 404];
                }
                if ($m->status !== 'en_attente' || $m->stock_operation_id) {
                    return ['ok' => false, 'message' => 'Mouvement déjà traité ou géré par une opération de stock.', 'status' => 409];
                }

                $isAdjust = $this->isAdjustmentMovement($m);
                $roles = $isAdjust ? self::VERIFY_ROLES_ADJUSTMENT : self::VERIFY_ROLES_OPERATION;
                if (!in_array($user->role, $roles, true)) {
                    return ['ok' => false, 'message' => 'Rôle non habilité à vérifier ce mouvement.', 'status' => 403];
                }

                // Jambe « in » d'un transfert : réception confirmée par le magasin destinataire
                $meta = json_decode($m->metadata ?? '{}', true) ?: [];
                if ($this->isTransferIn($m) && empty($meta['receipt_confirmed_at'])
                    && !in_array($user->role, ['dtl', 'dg', 'admin', 'super_admin'], true)) {
                    return ['ok' => false, 'message' => 'Réception non encore confirmée par le magasin destinataire.', 'status' => 409];
                }

                // Delta à appliquer : inventaire = comptage réel − stock officiel actuel
                // (auto-corrige si d'autres mouvements ont été vérifiés entre-temps).
                if (!empty($meta['inventory_type'])) {
                    $delta = (float) $m->quantity_after - $this->balance($m->warehouse_id)['sacs'];
                } else {
                    $delta = $this->signedDelta($m);
                }

                // Les sorties revérifient le solde officiel (des sorties en attente
                // concurrentes peuvent avoir été validées entre-temps).
                if ($delta < 0 && abs($delta) > $this->balance($m->warehouse_id)['sacs'] + 0.0001) {
                    return ['ok' => false, 'message' => 'Solde officiel insuffisant à la vérification. Mouvement à rejeter ou à refaire.', 'status' => 422];
                }

                // Stock produit (stocks.quantity) : même delta, avec
                // contrôle produit-par-produit pour les sorties.
                if ($m->stock_id && $delta != 0.0) {
                    $stockRow = Stock::whereKey($m->stock_id)->lockForUpdate()->first();
                    if ($stockRow) {
                        if ($delta < 0 && (float) $stockRow->quantity + $delta < -0.0001) {
                            return ['ok' => false, 'message' => 'Stock produit insuffisant à la vérification.', 'status' => 422];
                        }
                        $stockRow->increment('quantity', $delta);
                    }
                }

                $m->update([
                    'status' => 'verifie',
                    'validated_by' => $user->id,
                    'validated_at' => now(),
                ]);

                Warehouse::where('id', $m->warehouse_id)->increment('current_stock', $delta);

                $this->auditMovement($user, 'stock_movement.verify', $m,
                    "Mouvement {$m->reference} vérifié (delta {$delta}) — intégré au stock officiel");

                return ['ok' => true, 'movement' => $m->fresh(), 'delta' => $delta];
            });
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur vérification : ' . $e->getMessage(), 'status' => 500];
        }
    }

    /** Rejette un mouvement en attente — il ne comptera jamais au stock officiel. */
    public function rejectPendingMovement(User $user, int $movementId, string $reason): array
    {
        $m = StockMovement::find($movementId);
        if (!$m) {
            return ['ok' => false, 'message' => 'Mouvement introuvable.', 'status' => 404];
        }
        if ($m->status !== 'en_attente' || $m->stock_operation_id) {
            return ['ok' => false, 'message' => 'Mouvement déjà traité ou géré par une opération de stock.', 'status' => 409];
        }

        $isAdjust = $this->isAdjustmentMovement($m);
        $roles = $isAdjust ? self::VERIFY_ROLES_ADJUSTMENT : self::VERIFY_ROLES_OPERATION;
        if (!in_array($user->role, $roles, true)) {
            return ['ok' => false, 'message' => 'Rôle non habilité à rejeter ce mouvement.', 'status' => 403];
        }

        $m->update(['status' => 'rejetee', 'rejection_reason' => $reason]);
        $this->auditMovement($user, 'stock_movement.reject', $m,
            "Mouvement {$m->reference} rejeté : {$reason}");

        return ['ok' => true, 'movement' => $m->fresh()];
    }

    /**
     * Confirmation de réception d'une jambe « in » de transfert par le
     * magasinier du magasin destinataire (ou la direction).
     */
    public function confirmTransferReceipt(User $user, int $movementId): array
    {
        $m = StockMovement::find($movementId);
        if (!$m) {
            return ['ok' => false, 'message' => 'Mouvement introuvable.', 'status' => 404];
        }
        if (!$this->isTransferIn($m) || $m->status !== 'en_attente') {
            return ['ok' => false, 'message' => 'Ce mouvement n\'est pas une réception de transfert en attente.', 'status' => 409];
        }
        if (!in_array($user->role, ['magasinier', 'inspecteur', 'dtl', 'dg', 'admin', 'super_admin'], true)) {
            return ['ok' => false, 'message' => 'Rôle non autorisé.', 'status' => 403];
        }
        if ($user->role === 'magasinier' && !$this->canAccess($user, (int) $m->warehouse_id)) {
            return ['ok' => false, 'message' => 'Magasin destinataire non autorisé.', 'status' => 403];
        }

        $meta = json_decode($m->metadata ?? '{}', true) ?: [];
        if (!empty($meta['receipt_confirmed_at'])) {
            return ['ok' => true, 'message' => 'Réception déjà confirmée.', 'movement' => $m, 'duplicate' => true];
        }

        $meta['receipt_confirmed_by'] = $user->id;
        $meta['receipt_confirmed_by_name'] = $user->name;
        $meta['receipt_confirmed_at'] = now()->toIso8601String();
        $m->metadata = json_encode($meta);
        $m->save();

        $this->auditMovement($user, 'stock_movement.transfer_receipt', $m,
            "Réception confirmée pour le transfert {$m->numero_transfert} (réf. {$m->reference})");

        return ['ok' => true, 'message' => 'Réception confirmée — en attente de vérification inspecteur.', 'movement' => $m->fresh()];
    }
}
