<?php

namespace App\Services\Reports;

use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\InspectorCoverageService;
use Illuminate\Support\Collection;

/**
 * Moteur de rapports stocks : construit les jeux de données réelles
 * pour les exports PDF / XLSX des portails DG et DTL.
 */
class StockReportService
{
    public function __construct(private InspectorCoverageService $coverage) {}

    /** Catalogue des rapports autorisés par portail. */
    public function catalog(string $portal): array
    {
        $common = [
            'mouvements' => 'Mouvements de stock (entrées / sorties / transferts / inventaires)',
            'operations' => 'Opérations de stock (circuit DG → DTL → Inspecteur → Magasinier)',
            'anomalies' => 'Anomalies et mouvements rejetés',
        ];

        if ($portal === 'dg') {
            return $common + [
                'stocks-national' => 'Rapport national des stocks alimentaires',
                'stocks-region' => 'Stocks par région',
                'stocks-magasin' => 'Stocks par magasin',
                'entrees' => 'Rapport des entrées',
                'sorties' => 'Rapport des sorties',
                'transferts' => 'Rapport des transferts',
                'inventaires' => 'Rapport des inventaires',
                'mouvements-attente' => 'Mouvements en attente de vérification',
                'mouvements-verifies' => 'Mouvements vérifiés',
                'couverture-regions' => 'Couverture des 14 régions',
                'performance-magasins' => 'Performance des magasins',
            ];
        }

        // DTL
        return $common + [
            'ops-reçues' => 'Opérations reçues (instructions DG)',
            'ops-affectees' => 'Opérations affectées aux inspecteurs',
            'ops-executees' => 'Opérations exécutées par les magasiniers',
            'ops-verifiees' => 'Opérations vérifiées',
            'ops-rejetees' => 'Opérations rejetées',
            'transferts' => 'Transferts et réceptions',
            'activite-magasins' => 'Activité des magasins',
            'couverture-inspecteurs' => 'Couverture des inspecteurs',
            'inventaires' => 'Inventaires et ajustements',
        ];
    }

    public function isValid(string $portal, string $type): bool
    {
        return array_key_exists($type, $this->catalog($portal));
    }

    /**
     * Construit [colonnes, lignes] pour un type de rapport.
     * Filtres : date_from, date_to, region, warehouse_id, produit, statut, inspecteur_id
     */
    public function dataset(string $type, array $f): array
    {
        return match ($type) {
            'stocks-national', 'stocks-region' => $this->stocksByRegion($f),
            'stocks-magasin' => $this->stocksByWarehouse($f),
            'mouvements', 'entrees', 'sorties', 'transferts', 'inventaires',
            'mouvements-attente', 'mouvements-verifies' => $this->movements($type, $f),
            'operations', 'ops-reçues', 'ops-affectees', 'ops-executees', 'ops-verifiees', 'ops-rejetees' => $this->operations($type, $f),
            'anomalies' => $this->anomalies($f),
            'couverture-regions' => $this->coverageRegions(),
            'couverture-inspecteurs' => $this->coverageInspectors($f),
            'performance-magasins', 'activite-magasins' => $this->warehouseActivity($f),
            default => [['Information'], ['Type de rapport inconnu']],
        };
    }

    // ---------- Stocks ----------

    private function stocksByRegion(array $f): array
    {
        $q = Warehouse::where('status', 'active');
        $this->applyWarehouseFilters($q, $f);

        $rows = $q->selectRaw('region, COUNT(*) as magasins, SUM(current_stock) as stock_total')
            ->groupBy('region')->orderBy('region')->get();

        $cols = ['Région', 'Nombre de magasins', 'Stock total (sacs)', 'Stock moyen / magasin'];
        $data = $rows->map(fn ($r) => [
            $r->region ?? 'Non renseignée',
            $r->magasins,
            round($r->stock_total, 2),
            $r->magasins ? round($r->stock_total / $r->magasins, 2) : 0,
        ])->all();

        $total = $rows->sum('stock_total');
        $data[] = ['TOTAL', $rows->sum('magasins'), round($total, 2), $rows->sum('magasins') ? round($total / $rows->sum('magasins'), 2) : 0];

        return [$cols, $data];
    }

    private function stocksByWarehouse(array $f): array
    {
        $q = Warehouse::where('status', 'active')->withCount('users as magasiniers_count');
        $this->applyWarehouseFilters($q, $f);

        $cols = ['Magasin', 'Région', 'Stock actuel (sacs)', 'Capacité', 'Taux remplissage %'];
        $data = $q->orderBy('region')->orderBy('name')->get()->map(function ($w) {
            $pct = $w->capacity > 0 ? round($w->current_stock / $w->capacity * 100, 1) : null;
            return [$w->name, $w->region, round($w->current_stock, 2), $w->capacity, $pct];
        })->all();

        return [$cols, $data];
    }

    // ---------- Mouvements ----------

    private const MOVEMENT_TYPES = [
        'entrees' => [['entry', 'in']],
        'sorties' => [['exit', 'out']],
        'transferts' => [['transfer']],
        'inventaires' => [['adjustment', 'inventory']],
        'mouvements-attente' => [],
        'mouvements-verifies' => [],
        'mouvements' => null, // tous
    ];

    private function movements(string $type, array $f): array
    {
        $q = StockMovement::with(['warehouse:id,name,region', 'creator:id,name'])->latest('id');
        $this->applyDateFilters($q, $f, 'created_at');
        $this->applyWarehouseFilters($q, $f, 'warehouse_id');
        if (!empty($f['produit'])) {
            $q->where('reason', 'like', '%' . $f['produit'] . '%');
        }

        if ($type === 'mouvements-attente') {
            $q->where('status', 'en_attente');
        } elseif ($type === 'mouvements-verifies') {
            $q->whereNull('status')->orWhereNotIn('status', ['en_attente', 'rejetee', 'anomalie', 'rejected']);
        } elseif (isset(self::MOVEMENT_TYPES[$type]) && self::MOVEMENT_TYPES[$type] !== null) {
            $q->whereIn('type', self::MOVEMENT_TYPES[$type]);
        }

        $cols = ['Date', 'Magasin', 'Région', 'Type', 'Produit / Motif', 'Quantité', 'Avant', 'Après', 'Référence', 'Statut', 'Enregistré par'];
        $data = $q->limit(5000)->get()->map(fn ($m) => [
            $m->created_at?->format('d/m/Y H:i'),
            $m->warehouse->name ?? '—',
            $m->warehouse->region ?? '—',
            $m->type,
            $m->reason,
            round((float) $m->quantity, 2),
            $m->quantity_before !== null ? round((float) $m->quantity_before, 2) : null,
            $m->quantity_after !== null ? round((float) $m->quantity_after, 2) : null,
            $m->reference,
            $m->status ?? 'officiel',
            $m->creator->name ?? '—',
        ])->all();

        return [$cols, $data];
    }

    // ---------- Opérations ----------

    private function operations(string $type, array $f): array
    {
        $q = StockOperation::with([
            'warehouse:id,name,region', 'destinationWarehouse:id,name',
            'creator:id,name', 'inspector:id,name', 'magasinier:id,name',
        ])->latest('id');

        $this->applyDateFilters($q, $f, 'created_at');
        if (!empty($f['warehouse_id'])) {
            $q->where(function ($qq) use ($f) {
                $qq->where('warehouse_id', $f['warehouse_id'])->orWhere('destination_warehouse_id', $f['warehouse_id']);
            });
        }
        if (!empty($f['produit'])) {
            $q->where('produit', 'like', '%' . $f['produit'] . '%');
        }
        if (!empty($f['inspecteur_id'])) {
            $q->where('assigned_inspector_id', $f['inspecteur_id']);
        }
        if (!empty($f['statut'])) {
            $q->where('status', $f['statut']);
        } else {
            $map = [
                'ops-reçues' => [StockOperation::STATUS_SOUMISE],
                'ops-affectees' => [StockOperation::STATUS_AFFECTEE],
                'ops-executees' => [StockOperation::STATUS_EXECUTEE],
                'ops-verifiees' => [StockOperation::STATUS_VERIFIEE],
                'ops-rejetees' => [StockOperation::STATUS_REJETEE],
            ];
            if (isset($map[$type])) {
                $q->whereIn('status', $map[$type]);
            }
        }

        $cols = ['Référence', 'Type', 'Magasin source', 'Magasin destination', 'Produit', 'Qté demandée', 'Qté exécutée', 'Statut', 'Créée par', 'Inspecteur', 'Magasinier', 'Date création', 'Date vérification'];
        $data = $q->limit(5000)->get()->map(fn ($o) => [
            $o->reference ?? ('#' . $o->id),
            $o->type_label,
            $o->warehouse->name ?? '—',
            $o->destinationWarehouse->name ?? '—',
            trim(($o->produit ?? '') . ' ' . ($o->unite ?? '')),
            $o->quantity_requested,
            $o->quantity_executed,
            $o->status_label,
            $o->creator->name ?? '—',
            $o->inspector->name ?? '—',
            $o->magasinier->name ?? '—',
            $o->created_at?->format('d/m/Y H:i'),
            $o->verified_at?->format('d/m/Y H:i'),
        ])->all();

        return [$cols, $data];
    }

    private function anomalies(array $f): array
    {
        $ops = StockOperation::with(['warehouse:id,name,region', 'inspector:id,name'])
            ->whereIn('status', [StockOperation::STATUS_ANOMALIE, StockOperation::STATUS_REJETEE])
            ->latest('id')->limit(2000)->get()
            ->map(fn ($o) => [
                'date' => $o->created_at?->format('d/m/Y H:i'),
                'reference' => $o->reference ?? ('#' . $o->id),
                'magasin' => $o->warehouse->name ?? '—',
                'region' => $o->warehouse->region ?? '—',
                'type' => 'Opération ' . $o->status_label,
                'detail' => $o->anomaly_report ?? $o->rejection_reason ?? '',
                'auteur' => $o->inspector->name ?? '—',
            ]);

        $movs = StockMovement::with(['warehouse:id,name,region', 'creator:id,name'])
            ->whereIn('status', ['en_attente', 'anomalie', 'rejetee', 'rejected'])
            ->latest('id')->limit(2000)->get()
            ->map(fn ($m) => [
                'date' => $m->created_at?->format('d/m/Y H:i'),
                'reference' => $m->reference ?? '—',
                'magasin' => $m->warehouse->name ?? '—',
                'region' => $m->warehouse->region ?? '—',
                'type' => 'Mouvement ' . ($m->status ?? 'officiel'),
                'detail' => $m->reason ?? '',
                'auteur' => $m->creator->name ?? '—',
            ]);

        $rows = $ops->concat($movs)->sortByDesc('date')->values();
        return [
            ['Date', 'Référence', 'Magasin', 'Région', 'Type', 'Détail', 'Signalé par'],
            $rows->map(fn ($r) => [$r['date'], $r['reference'], $r['magasin'], $r['region'], $r['type'], $r['detail'], $r['auteur']])->all(),
        ];
    }

    // ---------- Couverture ----------

    private function coverageRegions(): array
    {
        $cols = ['Région', 'Magasins', 'Avec magasinier', 'Avec inspecteur', 'Statut'];
        $data = collect($this->coverage->report())->map(function ($c) {
            $w = collect($c['warehouses']);
            return [
                $c['region'],
                $w->count(),
                $w->filter(fn ($x) => count($x['magasiniers']) > 0)->count(),
                $w->filter(fn ($x) => count($x['inspecteurs']) > 0)->count(),
                $c['status'],
            ];
        })->all();
        return [$cols, $data];
    }

    private function coverageInspectors(array $f): array
    {
        $inspectors = User::where('role', 'inspecteur')->with('inspectorAssignments.warehouse:id,name,region')->get();
        $cols = ['Inspecteur', 'E-mail', 'Actif', 'Magasins affectés', 'Régions'];
        $data = $inspectors->map(function ($u) {
            $whs = $u->inspectorAssignments->where('is_active', true)->pluck('warehouse');
            return [
                $u->name, $u->email, $u->is_active ? 'Oui' : 'Non',
                $whs->count(),
                $whs->pluck('region')->filter()->unique()->implode(', '),
            ];
        })->all();
        return [$cols, $data];
    }

    // ---------- Performance ----------

    private function warehouseActivity(array $f): array
    {
        $q = Warehouse::where('status', 'active');
        $this->applyWarehouseFilters($q, $f);
        $ids = $q->pluck('id');

        [$from, $to] = $this->dateRange($f);

        $mouvements = StockMovement::whereIn('warehouse_id', $ids)
            ->when($from, fn ($qq) => $qq->where('created_at', '>=', $from))
            ->when($to, fn ($qq) => $qq->where('created_at', '<=', $to . ' 23:59:59'))
            ->selectRaw('warehouse_id, COUNT(*) as total, SUM(CASE WHEN type IN ("entry","in") THEN quantity ELSE 0 END) as entrees, SUM(CASE WHEN type IN ("exit","out") THEN quantity ELSE 0 END) as sorties')
            ->groupBy('warehouse_id')->get()->keyBy('warehouse_id');

        $ops = StockOperation::whereIn('warehouse_id', $ids)
            ->when($from, fn ($qq) => $qq->where('created_at', '>=', $from))
            ->when($to, fn ($qq) => $qq->where('created_at', '<=', $to . ' 23:59:59'))
            ->selectRaw('warehouse_id, COUNT(*) as total')
            ->groupBy('warehouse_id')->pluck('total', 'warehouse_id');

        $cols = ['Magasin', 'Région', 'Stock actuel', 'Mouvements', 'Entrées (qte)', 'Sorties (qte)', 'Opérations'];
        $data = Warehouse::whereIn('id', $ids)->orderBy('region')->orderBy('name')->get()->map(function ($w) use ($mouvements, $ops) {
            $m = $mouvements->get($w->id);
            return [
                $w->name, $w->region, round($w->current_stock, 2),
                $m->total ?? 0, round($m->entrees ?? 0, 2), round($m->sorties ?? 0, 2),
                $ops[$w->id] ?? 0,
            ];
        })->all();

        return [$cols, $data];
    }

    // ---------- Filtres ----------

    private function applyDateFilters($q, array $f, string $col): void
    {
        if (!empty($f['date_from'])) {
            $q->where($col, '>=', $f['date_from'] . ' 00:00:00');
        }
        if (!empty($f['date_to'])) {
            $q->where($col, '<=', $f['date_to'] . ' 23:59:59');
        }
    }

    private function applyWarehouseFilters($q, array $f, string $col = 'id'): void
    {
        if (!empty($f['region'])) {
            $q->where('region', $f['region']);
        }
        if (!empty($f['warehouse_id'])) {
            $q->where($col, (int) $f['warehouse_id']);
        }
    }

    private function dateRange(array $f): array
    {
        return [
            $f['date_from'] ?? null,
            $f['date_to'] ?? null,
        ];
    }
}
