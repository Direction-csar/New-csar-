<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAlert;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class StockAlertService
{
    public const TYPE_OUT_OF_STOCK = 'out_of_stock';
    public const TYPE_LOW_STOCK = 'low_stock';
    public const TYPE_EXPIRING_SOON = 'expiring_soon';
    public const TYPE_EXPIRED = 'expired';

    public const PRIORITY_CRITICAL = 'critical';
    public const PRIORITY_WARNING = 'warning';

    public const TYPE_LABELS = [
        self::TYPE_OUT_OF_STOCK => 'Stock épuisé',
        self::TYPE_LOW_STOCK => 'Stock critique',
        self::TYPE_EXPIRING_SOON => 'Péremption proche',
        self::TYPE_EXPIRED => 'Produit périmé',
    ];

    public const PRIORITY_LABELS = [
        self::PRIORITY_CRITICAL => 'Critique',
        self::PRIORITY_WARNING => 'Avertissement',
    ];

    /**
     * Magasins visibles : null = tous, [] = aucun, sinon liste d'identifiants.
     */
    public function accessibleWarehouseIds(?User $user): ?array
    {
        if (!$user) {
            return [];
        }

        if (in_array($user->role, config('stock_alerts.global_roles', []), true)) {
            return null;
        }

        return $user->warehouse_id ? [(int) $user->warehouse_id] : [];
    }

    public function accessibleWarehouses(?User $user): Collection
    {
        $allowed = $this->accessibleWarehouseIds($user);
        if ($allowed === []) {
            return collect();
        }

        return Warehouse::query()
            ->when($allowed !== null, fn ($q) => $q->whereIn('id', $allowed))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Calcule les alertes actives à partir des lots (table stocks).
     * Lecture seule : aucune écriture en base, aucune notification.
     *
     * Filtres acceptés : warehouse_id, type, priority.
     */
    public function getActiveAlerts(?User $user, array $filters = []): Collection
    {
        $allowed = $this->accessibleWarehouseIds($user);
        if ($allowed === []) {
            return collect();
        }

        $warehouseId = !empty($filters['warehouse_id']) ? (int) $filters['warehouse_id'] : null;
        if ($warehouseId !== null && $allowed !== null && !in_array($warehouseId, $allowed, true)) {
            return collect();
        }

        $stocks = Stock::query()
            ->with(['warehouse:id,name', 'stockType'])
            ->where('is_active', true)
            ->when($allowed !== null, fn ($q) => $q->whereIn('warehouse_id', $allowed))
            ->when($warehouseId !== null, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        if ($stocks->isEmpty()) {
            return collect();
        }

        $products = Product::query()->get(['id', 'name'])
            ->keyBy(fn ($p) => mb_strtolower(trim((string) $p->name)));

        $typeOrder = array_flip(array_keys(self::TYPE_LABELS));

        return $stocks
            ->flatMap(fn (Stock $stock) => $this->evaluateStock($stock, $products))
            ->unique('key')
            ->when(isset(self::TYPE_LABELS[$filters['type'] ?? '']), fn ($c) => $c->where('type', $filters['type']))
            ->when(isset(self::PRIORITY_LABELS[$filters['priority'] ?? '']), fn ($c) => $c->where('priority', $filters['priority']))
            ->sortBy([
                fn ($a, $b) => ($a['priority'] === self::PRIORITY_CRITICAL ? 0 : 1) <=> ($b['priority'] === self::PRIORITY_CRITICAL ? 0 : 1),
                fn ($a, $b) => $typeOrder[$a['type']] <=> $typeOrder[$b['type']],
                fn ($a, $b) => strcmp($a['product_name'], $b['product_name']),
            ])
            ->values();
    }

    /**
     * Données du panneau « Alertes de stock » à partir des paramètres de requête
     * alert_warehouse, alert_type, alert_priority. Les compteurs portent sur le
     * périmètre (magasin filtré), la liste applique en plus catégorie et priorité.
     */
    public function buildPanel(?User $user, array $query): array
    {
        $warehouses = $this->accessibleWarehouses($user);

        $filters = [
            'warehouse_id' => $warehouses->contains('id', (int) ($query['alert_warehouse'] ?? 0)) ? (int) $query['alert_warehouse'] : null,
            'type' => isset(self::TYPE_LABELS[$query['alert_type'] ?? '']) ? $query['alert_type'] : null,
            'priority' => isset(self::PRIORITY_LABELS[$query['alert_priority'] ?? '']) ? $query['alert_priority'] : null,
        ];

        $scoped = $this->getActiveAlerts($user, ['warehouse_id' => $filters['warehouse_id']]);
        $alerts = $scoped
            ->when($filters['type'], fn ($c) => $c->where('type', $filters['type']))
            ->when($filters['priority'], fn ($c) => $c->where('priority', $filters['priority']))
            ->values();

        return [
            'alerts' => $alerts,
            'summary' => $this->summarize($scoped),
            'warehouses' => $warehouses,
            'filters' => $filters,
            'has_filters' => (bool) array_filter($filters),
        ];
    }

    public function summarize(Collection $alerts): array
    {
        $byType = [];
        foreach (array_keys(self::TYPE_LABELS) as $type) {
            $byType[$type] = $alerts->where('type', $type)->count();
        }

        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('priority', self::PRIORITY_CRITICAL)->count(),
            'warning' => $alerts->where('priority', self::PRIORITY_WARNING)->count(),
            'by_type' => $byType,
        ];
    }

    /**
     * Au plus une alerte de niveau de stock (épuisé > critique) et une alerte
     * de péremption (périmé > proche) par lot.
     */
    private function evaluateStock(Stock $stock, Collection $products): array
    {
        $alerts = [];
        $qty = (float) $stock->quantity;
        $min = (float) $stock->min_quantity;

        if ($qty <= 0) {
            $alerts[] = $this->makeAlert($stock, self::TYPE_OUT_OF_STOCK, self::PRIORITY_CRITICAL, $products);
        } elseif ($min > 0 && $stock->isLowStock()) {
            $ratio = (float) config('stock_alerts.low_stock_critical_ratio', 0.5);
            $priority = $qty <= $min * $ratio ? self::PRIORITY_CRITICAL : self::PRIORITY_WARNING;
            $alerts[] = $this->makeAlert($stock, self::TYPE_LOW_STOCK, $priority, $products);
        }

        if ($qty > 0 && $stock->expiry_date) {
            $days = (int) now()->startOfDay()->diffInDays($stock->expiry_date->copy()->startOfDay(), false);

            if ($stock->isExpired()) {
                $alerts[] = $this->makeAlert($stock, self::TYPE_EXPIRED, self::PRIORITY_CRITICAL, $products, $days);
            } elseif ($days <= (int) config('stock_alerts.expiring_days', 30)) {
                $priority = $days <= (int) config('stock_alerts.expiring_critical_days', 7)
                    ? self::PRIORITY_CRITICAL
                    : self::PRIORITY_WARNING;
                $alerts[] = $this->makeAlert($stock, self::TYPE_EXPIRING_SOON, $priority, $products, $days);
            }
        }

        return $alerts;
    }

    private function makeAlert(Stock $stock, string $type, string $priority, Collection $products, ?int $daysLeft = null): array
    {
        $product = $products->get(mb_strtolower(trim((string) $stock->item_name)));

        if ($product && Route::has('admin.products.show')) {
            $url = route('admin.products.show', $product->id);
        } elseif (Route::has('admin.stock.index')) {
            $url = route('admin.stock.index', ['entrepot' => $stock->warehouse_id]);
        } else {
            $url = null;
        }

        return [
            'key' => $stock->id . ':' . $type,
            'type' => $type,
            'type_label' => self::TYPE_LABELS[$type],
            'priority' => $priority,
            'priority_label' => self::PRIORITY_LABELS[$priority],
            'stock_id' => $stock->id,
            'product_id' => $product->id ?? null,
            'product_name' => (string) $stock->item_name,
            'batch_number' => $stock->batch_number,
            'warehouse_id' => $stock->warehouse_id,
            'warehouse_name' => $stock->warehouse->name ?? 'Magasin inconnu',
            'quantity' => (float) $stock->quantity,
            'unit' => $stock->stockType->unit ?? null,
            'threshold' => in_array($type, [self::TYPE_OUT_OF_STOCK, self::TYPE_LOW_STOCK], true) ? (float) $stock->min_quantity : null,
            'expiry_date' => $stock->expiry_date?->format('Y-m-d'),
            'days_left' => $daysLeft,
            'url' => $url,
        ];
    }

    /**
     * Vérifier et créer des alertes pour tous les stocks
     */
    public function checkAllStocks()
    {
        $stocks = Stock::with(['warehouse', 'stockType'])
            ->where('is_active', true)
            ->get();

        $alertsCreated = 0;

        foreach ($stocks as $stock) {
            $alerts = $this->checkStock($stock);
            $alertsCreated += count($alerts);
        }

        Log::info("StockAlertService: {$alertsCreated} alertes créées lors de la vérification des stocks");

        return $alertsCreated;
    }

    /**
     * Vérifier un stock spécifique et créer les alertes nécessaires
     */
    public function checkStock(Stock $stock)
    {
        $alerts = [];

        // Vérifier le stock faible
        if ($this->isLowStock($stock)) {
            $alert = $this->createLowStockAlert($stock);
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        // Vérifier l'expiration
        if ($this->isExpired($stock)) {
            $alert = $this->createExpiredAlert($stock);
            if ($alert) {
                $alerts[] = $alert;
            }
        } elseif ($this->isExpiringSoon($stock)) {
            $alert = $this->createExpiringSoonAlert($stock);
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    /**
     * Vérifier si le stock est faible
     */
    private function isLowStock(Stock $stock): bool
    {
        return $stock->min_quantity > 0 && $stock->quantity <= $stock->min_quantity;
    }

    /**
     * Vérifier si le produit est expiré
     */
    private function isExpired(Stock $stock): bool
    {
        return $stock->expiry_date && $stock->expiry_date->isPast();
    }

    /**
     * Vérifier si le produit expire bientôt (dans les 30 jours)
     */
    private function isExpiringSoon(Stock $stock): bool
    {
        return $stock->expiry_date &&
               $stock->expiry_date->isFuture() &&
               $stock->expiry_date->diffInDays(now()) <= 30;
    }

    /**
     * Créer une alerte de stock faible
     */
    private function createLowStockAlert(Stock $stock): ?StockAlert
    {
        // Vérifier s'il n'y a pas déjà une alerte active pour ce stock
        $existingAlert = StockAlert::where('stock_id', $stock->id)
            ->where('alert_type', 'low_stock')
            ->where('is_resolved', false)
            ->first();

        if ($existingAlert) {
            return null;
        }

        $severity = $this->calculateLowStockSeverity($stock);
        $message = $this->generateLowStockMessage($stock);

        $alert = StockAlert::create([
            'stock_id' => $stock->id,
            'warehouse_id' => $stock->warehouse_id,
            'alert_type' => 'low_stock',
            'severity' => $severity,
            'message' => $message,
            'current_quantity' => $stock->quantity,
            'threshold_quantity' => $stock->min_quantity,
        ]);

        // Envoyer une notification SMS
        try {
            $smsService = app(SmsService::class);
            $smsService->sendStockAlertNotification($alert);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification SMS pour l\'alerte de stock', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage()
            ]);
        }

        return $alert;
    }

    /**
     * Créer une alerte de produit expiré
     */
    private function createExpiredAlert(Stock $stock): ?StockAlert
    {
        $existingAlert = StockAlert::where('stock_id', $stock->id)
            ->where('alert_type', 'expired')
            ->where('is_resolved', false)
            ->first();

        if ($existingAlert) {
            return null;
        }

        $message = "Le produit '{$stock->item_name}' a expiré le {$stock->expiry_date->format('d/m/Y')}";

        return StockAlert::create([
            'stock_id' => $stock->id,
            'warehouse_id' => $stock->warehouse_id,
            'alert_type' => 'expired',
            'severity' => 'critical',
            'message' => $message,
            'current_quantity' => $stock->quantity,
            'threshold_quantity' => 0,
        ]);
    }

    /**
     * Créer une alerte de produit expirant bientôt
     */
    private function createExpiringSoonAlert(Stock $stock): ?StockAlert
    {
        $existingAlert = StockAlert::where('stock_id', $stock->id)
            ->where('alert_type', 'expiring_soon')
            ->where('is_resolved', false)
            ->first();

        if ($existingAlert) {
            return null;
        }

        $daysLeft = $stock->expiry_date->diffInDays(now());
        $severity = $daysLeft <= 7 ? 'high' : ($daysLeft <= 15 ? 'medium' : 'low');

        $message = "Le produit '{$stock->item_name}' expire dans {$daysLeft} jour(s) ({$stock->expiry_date->format('d/m/Y')})";

        return StockAlert::create([
            'stock_id' => $stock->id,
            'warehouse_id' => $stock->warehouse_id,
            'alert_type' => 'expiring_soon',
            'severity' => $severity,
            'message' => $message,
            'current_quantity' => $stock->quantity,
            'threshold_quantity' => 0,
        ]);
    }

    /**
     * Calculer la sévérité d'une alerte de stock faible
     */
    private function calculateLowStockSeverity(Stock $stock): string
    {
        if ($stock->quantity <= 0) {
            return 'critical';
        }

        $percentage = ($stock->quantity / $stock->min_quantity) * 100;

        if ($percentage <= 10) {
            return 'critical';
        } elseif ($percentage <= 25) {
            return 'high';
        } elseif ($percentage <= 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Générer le message pour une alerte de stock faible
     */
    private function generateLowStockMessage(Stock $stock): string
    {
        $warehouse = $stock->warehouse->name ?? 'Entrepôt inconnu';
        $percentage = $stock->min_quantity > 0 ?
            round(($stock->quantity / $stock->min_quantity) * 100, 1) : 0;

        return "Stock faible pour '{$stock->item_name}' dans {$warehouse}. " .
               "Quantité actuelle: {$stock->quantity}, Seuil minimum: {$stock->min_quantity} " .
               "({$percentage}% du seuil minimum)";
    }

    /**
     * Résoudre une alerte
     */
    public function resolveAlert(StockAlert $alert, $userId = null)
    {
        $alert->resolve($userId);

        Log::info("Alerte de stock résolue: {$alert->id} par utilisateur: {$userId}");
    }

    /**
     * Obtenir les statistiques des alertes
     */
    public function getAlertStats()
    {
        return [
            'total' => StockAlert::count(),
            'active' => StockAlert::where('is_resolved', false)->count(),
            'critical' => StockAlert::where('is_resolved', false)->where('severity', 'critical')->count(),
            'high' => StockAlert::where('is_resolved', false)->where('severity', 'high')->count(),
            'medium' => StockAlert::where('is_resolved', false)->where('severity', 'medium')->count(),
            'low' => StockAlert::where('is_resolved', false)->where('severity', 'low')->count(),
            'low_stock' => StockAlert::where('is_resolved', false)->where('alert_type', 'low_stock')->count(),
            'expired' => StockAlert::where('is_resolved', false)->where('alert_type', 'expired')->count(),
            'expiring_soon' => StockAlert::where('is_resolved', false)->where('alert_type', 'expiring_soon')->count(),
        ];
    }
}
