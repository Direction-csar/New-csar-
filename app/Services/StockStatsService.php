<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Source unique des statistiques de stock (tableau de bord + page stock).
 * Lecture seule : aucune écriture.
 *
 * Sources de vérité :
 * - État courant du stock  : table `stocks` (lots actifs, quantity/min_quantity/
 *   expiry_date/unit_price par magasin).
 * - Historique des flux    : table `stock_movements` (types normalisés ci-dessous).
 *
 * Double comptage des transferts : l'API mobile enregistre une jambe
 * `transfer`/`transfert` à la source ET une jambe `entry` à destination.
 * La jambe destination est exclue des « entrées » (détectée via numero_transfert,
 * metadata.transfer_type ou le motif « (Transfert depuis: …) »).
 */
class StockStatsService
{
    public const ENTRY_TYPES = ['in', 'entry', 'report'];
    public const EXIT_TYPES = ['out', 'exit'];
    public const TRANSFER_TYPES = ['transfer', 'transfert'];
    public const ADJUSTMENT_TYPES = ['ajustement', 'adjustment'];

    /** Motif écrit par WarehouseKeeperController::storeTransfer sur la jambe entrante. */
    private const TRANSFER_IN_REASON = '%(Transfert depuis:%';

    public function __construct(private StockAlertService $alerts)
    {
    }

    /** null = tous les magasins ; [] = aucun ; sinon liste d'ids. */
    public function warehouseScope(?User $user): ?array
    {
        return $this->alerts->accessibleWarehouseIds($user);
    }

    /**
     * Totaux sur les lots actifs (état courant du stock).
     * Les quantités sont séparées par unité pour ne jamais mélanger kg/L/pièces.
     */
    public function stockTotals(?array $warehouseIds = null): array
    {
        $query = Stock::query()->where('is_active', true)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds));

        $byUnit = (clone $query)
            ->leftJoin('stock_types', 'stocks.stock_type_id', '=', 'stock_types.id')
            ->selectRaw("COALESCE(NULLIF(TRIM(stock_types.unit), ''), 'unité') as unit, SUM(stocks.quantity) as total")
            ->groupBy('unit')
            ->pluck('total', 'unit')
            ->map(fn ($v) => (float) $v)
            ->all();

        $lots = (clone $query)->count();
        $produits = (clone $query)->distinct()->count('item_name');
        $magasins = (clone $query)->distinct()->count('warehouse_id');

        $valorisables = (clone $query)->whereNotNull('unit_price');
        $valorisablesCount = (clone $valorisables)->count();
        $valeur = $valorisablesCount > 0
            ? (float) $valorisables->sum(DB::raw('quantity * unit_price'))
            : null;

        return [
            'by_unit' => $byUnit,
            'lots' => $lots,
            'produits_distincts' => $produits,
            'magasins' => $magasins,
            'valeur' => $valeur === null ? null : round($valeur, 0),
            'lots_sans_prix' => $lots - $valorisablesCount,
        ];
    }

    /**
     * Lots sous leur seuil ou épuisés — même prédicat que StockAlertService.
     */
    public function lowStockLotsCount(?array $warehouseIds = null): int
    {
        return Stock::query()
            ->where('is_active', true)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->where(function ($q) {
                $q->where('quantity', '<=', 0)
                  ->orWhere(function ($q2) {
                      $q2->where('min_quantity', '>', 0)
                         ->whereRaw('quantity <= min_quantity');
                  });
            })
            ->count();
    }

    /**
     * Compteurs et quantités de mouvements sur une période et un périmètre magasin.
     * Les jambes « entrée » des transferts sont exclues des entrées.
     */
    public function movementTotals(?array $warehouseIds = null, ?string $from = null, ?string $to = null): array
    {
        $base = fn () => $this->movementQuery($warehouseIds, $from, $to);

        $entries = (clone $base())->whereIn('type', self::ENTRY_TYPES);
        $this->excludeTransferInLegs($entries);

        $exits = (clone $base())->whereIn('type', self::EXIT_TYPES);
        $transfers = (clone $base())->whereIn('type', self::TRANSFER_TYPES);
        $adjustments = (clone $base())->whereIn('type', self::ADJUSTMENT_TYPES);

        return [
            'total_movements' => (clone $base())->count(),
            'entrees' => (clone $entries)->count(),
            'qte_entrees' => (float) ((clone $entries)->sum('quantity') ?? 0),
            'sorties' => (clone $exits)->count(),
            'qte_sorties' => (float) ((clone $exits)->sum('quantity') ?? 0),
            'transferts' => (clone $transfers)->count(),
            'qte_transferts' => (float) ((clone $transfers)->sum('quantity') ?? 0),
            'ajustements' => (clone $adjustments)->count(),
            'ajustement_net' => (float) ((clone $adjustments)
                ->sum(DB::raw('quantity_after - quantity_before')) ?? 0),
        ];
    }

    /**
     * Série mensuelle des quantités entrées/sorties (source : stock_movements,
     * mêmes règles que movementTotals). Retourne labels + deux datasets.
     */
    public function monthlySeries(?array $warehouseIds = null, int $months = 6): array
    {
        $from = now()->startOfMonth()->subMonths($months - 1);

        $labels = [];
        $keys = [];
        for ($i = 0; $i < $months; $i++) {
            $d = $from->copy()->addMonths($i);
            $keys[] = $d->format('Y-m');
            $labels[] = $d->translatedFormat('M Y');
        }

        $columns = ['type', 'quantity', 'reason', 'created_at'];
        foreach (['numero_transfert', 'metadata'] as $optional) {
            if (Schema::hasColumn('stock_movements', $optional)) {
                $columns[] = $optional;
            }
        }

        $rows = $this->movementQuery($warehouseIds, $from->toDateString(), null)
            ->whereIn('type', array_merge(self::ENTRY_TYPES, self::EXIT_TYPES))
            ->get($columns);

        $entrees = array_fill_keys($keys, 0.0);
        $sorties = array_fill_keys($keys, 0.0);

        foreach ($rows as $m) {
            $key = $m->created_at->format('Y-m');
            if (!isset($entrees[$key])) {
                continue;
            }
            if (in_array($m->type, self::ENTRY_TYPES, true)) {
                // Les jambes destination d'un transfert ne sont pas des entrées réelles
                if (!$this->isTransferInLeg($m)) {
                    $entrees[$key] += (float) $m->quantity;
                }
            } else {
                $sorties[$key] += (float) $m->quantity;
            }
        }

        return [
            'labels' => $labels,
            'entrees' => array_values($entrees),
            'sorties' => array_values($sorties),
        ];
    }

    /** Répartition des lots actifs par magasin : name => quantité. */
    public function stockByWarehouse(?array $warehouseIds = null): array
    {
        return Stock::query()
            ->where('stocks.is_active', true)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('stocks.warehouse_id', $warehouseIds))
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.name as name, SUM(stocks.quantity) as total')
            ->groupBy('warehouses.name')
            ->orderByDesc('total')
            ->pluck('total', 'name')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /** Répartition des lots actifs par catégorie (type de stock). */
    public function stockByCategory(?array $warehouseIds = null): array
    {
        return Stock::query()
            ->where('stocks.is_active', true)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('stocks.warehouse_id', $warehouseIds))
            ->leftJoin('stock_types', 'stocks.stock_type_id', '=', 'stock_types.id')
            ->selectRaw("COALESCE(NULLIF(TRIM(stock_types.display_name), ''), 'Sans catégorie') as category, SUM(stocks.quantity) as total")
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /** Mouvements par magasin (nombre de mouvements), pour le graphique existant. */
    public function movementsByWarehouse(?array $warehouseIds = null, ?string $from = null, ?string $to = null): array
    {
        return $this->movementQuery($warehouseIds, $from, $to)
            ->join('warehouses', 'stock_movements.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.name as name, COUNT(*) as count')
            ->groupBy('warehouses.name')
            ->orderByDesc('count')
            ->pluck('count', 'name')
            ->all();
    }

    /** Top produits par quantité en stock (lots actifs), name => quantité. */
    public function topProducts(?array $warehouseIds = null, int $limit = 8): array
    {
        return Stock::query()
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->selectRaw('item_name, SUM(quantity) as total')
            ->groupBy('item_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('total', 'item_name')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /** Carburant disponible par magasin (lots actifs assimilés carburant). */
    public function fuelByWarehouse(?array $warehouseIds = null): array
    {
        return $this->fuelQuery($warehouseIds)
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->where('stocks.is_active', true)
            ->selectRaw('warehouses.name as name, SUM(stocks.quantity) as total')
            ->groupBy('warehouses.name')
            ->pluck('total', 'name')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /** Carburant disponible, total (même prédicat que fuelByWarehouse). */
    public function fuelTotal(?array $warehouseIds = null): float
    {
        return (float) ($this->fuelQuery($warehouseIds)->sum('stocks.quantity') ?? 0);
    }

    /** Variation mensuelle des quantités entrées (en %, null si non calculable). */
    public function monthlyEntryVariation(?array $warehouseIds = null): ?float
    {
        $now = now();
        $currentQuery = $this->movementQuery($warehouseIds, $now->copy()->startOfMonth()->toDateString(), null)
            ->whereIn('type', self::ENTRY_TYPES);
        $this->excludeTransferInLegs($currentQuery);
        $current = $currentQuery->sum('quantity');

        $prevStart = $now->copy()->subMonth()->startOfMonth();
        $prevEnd = $now->copy()->subMonth()->endOfMonth();
        $previousQuery = $this->movementQuery($warehouseIds, $prevStart->toDateString(), $prevEnd->toDateString())
            ->whereIn('type', self::ENTRY_TYPES);
        $this->excludeTransferInLegs($previousQuery);
        $previous = $previousQuery->sum('quantity');

        if ((float) $previous <= 0) {
            return null; // pas de base de comparaison : on n'invente pas de valeur
        }

        return round((((float) $current - (float) $previous) / (float) $previous) * 100, 1);
    }

    /* ------------------------------------------------------------------ */

    private function movementQuery(?array $warehouseIds, ?string $from, ?string $to)
    {
        return StockMovement::query()
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    private function fuelQuery(?array $warehouseIds)
    {
        return Stock::query()
            ->where('stocks.is_active', true)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('stocks.warehouse_id', $warehouseIds))
            ->where(function ($q) {
                $q->where('item_name', 'like', '%carburant%')
                  ->orWhere('item_name', 'like', '%fuel%')
                  ->orWhere('item_name', 'like', '%essence%')
                  ->orWhere('item_name', 'like', '%gasoil%')
                  ->orWhere('item_name', 'like', '%pétrole%');
            });
    }

    /**
     * Exclut du périmètre « entrées » les jambes destination d'un transfert.
     * La colonne numero_transfert n'est renseignée que si elle existe et a été
     * mass-assignable ; le motif et les métadonnées servent de filets de sécurité.
     */
    private function excludeTransferInLegs($query): void
    {
        $query->where(function ($q) {
            if (Schema::hasColumn('stock_movements', 'numero_transfert')) {
                $q->where(function ($q2) {
                    $q2->whereNull('numero_transfert')
                       ->orWhere('numero_transfert', '');
                });
            }

            if (Schema::hasColumn('stock_movements', 'metadata')) {
                $q->where(function ($q2) {
                    $q2->whereNull('metadata')
                       ->orWhere('metadata', 'not like', '%"transfer_type":"in"%');
                });
            }

            $q->where(function ($q2) {
                $q2->whereNull('reason')
                   ->orWhere('reason', 'not like', self::TRANSFER_IN_REASON);
            });
        });
    }

    private function isTransferInLeg(StockMovement $m): bool
    {
        if (isset($m->numero_transfert) && $m->numero_transfert) {
            return true;
        }
        $meta = $m->metadata ?? null;
        if (is_string($meta) && str_contains($meta, '"transfer_type":"in"')) {
            return true;
        }
        return is_string($m->reason) && str_contains($m->reason, '(Transfert depuis:');
    }
}
