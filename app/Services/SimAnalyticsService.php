<?php

namespace App\Services;

use App\Models\SimCollection;
use App\Models\SimCollectionStatus;
use App\Models\SimCollectorAssignment;
use App\Models\SimDataAccessRequest;
use App\Models\SimMarket;
use App\Models\SimProduct;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimAnalyticsService
{
    public function isReady(): bool
    {
        return Schema::hasTable('sim_markets')
            && Schema::hasTable('sim_products')
            && Schema::hasTable('sim_collections')
            && Schema::hasTable('sim_collection_items');
    }

    public function adminOverview(): array
    {
        if (!$this->isReady()) {
            return $this->emptyOverview();
        }

        $today = now()->toDateString();

        $collectionsThisMonth = SimCollection::whereMonth('collected_on', now()->month)
            ->whereYear('collected_on', now()->year)
            ->whereIn('status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->count();

        $lastUpdated = SimCollection::whereIn('status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->orderByDesc('collected_on')
            ->value('collected_on');

        return [
            'markets_count' => SimMarket::where('is_active', true)->count(),
            'products_count' => SimProduct::where('is_active', true)->count(),
            'collectors_count' => User::where('role', 'agent')->count(),
            'supervisors_count' => User::where('role', 'responsable')->count(),
            'collections_today' => SimCollection::whereDate('collected_on', $today)->count(),
            'collections_this_month' => $collectionsThisMonth,
            'last_updated' => $lastUpdated,
            'pending_collections' => SimCollection::whereIn('status', [
                SimCollection::STATUS_SUBMITTED,
                SimCollection::STATUS_UNDER_REVIEW,
            ])->count(),
            'validated_collections' => SimCollection::where('status', SimCollection::STATUS_VALIDATED)->count(),
            'pending_requests' => Schema::hasTable('sim_data_access_requests')
                ? SimDataAccessRequest::where('status', SimDataAccessRequest::STATUS_PENDING)->count()
                : 0,
        ];
    }

    /** Données pour la carte des marchés (lat/lng) + derniers prix par marché (BDD) */
    public function getMarketsForMap(): array
    {
        if (!Schema::hasTable('sim_markets')) {
            return [];
        }
        $markets = SimMarket::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('department.region')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'type' => $m->market_type,
                'day' => $m->market_day,
                'lat' => (float) $m->latitude,
                'lng' => (float) $m->longitude,
                'region' => $m->department?->region?->name ?? '—',
                'prices' => [],
            ])
            ->values()
            ->all();

        $pricesByMarket = $this->getLastPricesByMarket();
        foreach ($markets as &$m) {
            $m['prices'] = $pricesByMarket[$m['id']] ?? [];
        }
        return $markets;
    }

    /** Derniers prix par marché (dernière collecte validée) — 100 % BDD */
    public function getLastPricesByMarket(): array
    {
        if (!Schema::hasTable('sim_collection_items') || !Schema::hasTable('sim_collections')) {
            return [];
        }
        $latest = DB::table('sim_collections')
            ->whereIn('status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->selectRaw('sim_market_id, MAX(collected_on) as last_on')
            ->groupBy('sim_market_id')
            ->get();

        $collectionIds = DB::table('sim_collections as c')
            ->whereIn('c.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->joinSub(
                DB::table('sim_collections')
                    ->whereIn('status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
                    ->selectRaw('sim_market_id, MAX(collected_on) as last_on')
                    ->groupBy('sim_market_id'),
                'latest',
                fn ($j) => $j->on('c.sim_market_id', '=', 'latest.sim_market_id')->on('c.collected_on', '=', 'latest.last_on')
            )
            ->pluck('c.id');

        if ($collectionIds->isEmpty()) {
            return [];
        }

        $rows = DB::table('sim_collection_items as i')
            ->join('sim_collections as c', 'c.id', '=', 'i.sim_collection_id')
            ->join('sim_products as p', 'p.id', '=', 'i.sim_product_id')
            ->whereIn('i.sim_collection_id', $collectionIds)
            ->whereNotNull('i.price_retail')
            ->select('c.sim_market_id as market_id', 'p.name as product_name', DB::raw('ROUND(AVG(i.price_retail), 0) as price'), DB::raw('MAX(c.collected_on) as date'))
            ->groupBy('c.sim_market_id', 'p.name')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $id = $r->market_id;
            if (!isset($out[$id])) {
                $out[$id] = [];
            }
            $out[$id][] = ['product' => $r->product_name, 'price' => (int) $r->price, 'date' => $r->date];
        }
        return $out;
    }

    /** Catégories de produits pour les onglets (BDD) */
    public function getProductCategoriesForTabs(): array
    {
        if (!Schema::hasTable('sim_product_categories')) {
            return [['id' => null, 'name' => 'Tous']];
        }
        $cats = DB::table('sim_product_categories')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'name']);
        $out = [['id' => null, 'name' => 'Tous']];
        foreach ($cats as $c) {
            $out[] = ['id' => $c->id, 'name' => $c->name];
        }
        return $out;
    }

    /** Tableau consultation des prix (année, mois, région, catégorie, recherche, tri, pagination) — 100 % BDD */
    public function getPriceConsultationData(?int $year = null, ?int $month = null, ?string $region = null, ?int $categoryId = null, ?string $search = null, string $sortCol = 'year', string $sortDir = 'desc', int $perPage = 10)
    {
        if (!Schema::hasTable('sim_collection_items') || !Schema::hasTable('sim_collections') || !Schema::hasTable('sim_products') || !Schema::hasTable('sim_markets') || !Schema::hasTable('sim_departments') || !Schema::hasTable('sim_regions')) {
            return ['data' => collect(), 'total' => 0, 'paginator' => null];
        }
        $year = $year ?: (int) now()->year;
        $driver = Schema::getConnection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "CAST(strftime('%Y', c.collected_on) AS INTEGER)" : 'YEAR(c.collected_on)';
        $monthExpr = $driver === 'sqlite' ? "CAST(strftime('%m', c.collected_on) AS INTEGER)" : 'MONTH(c.collected_on)';

        $query = DB::table('sim_collection_items as items')
            ->join('sim_collections as c', 'c.id', '=', 'items.sim_collection_id')
            ->join('sim_products as p', 'p.id', '=', 'items.sim_product_id')
            ->join('sim_markets as m', 'm.id', '=', 'c.sim_market_id')
            ->leftJoin('sim_departments as d', 'd.id', '=', 'm.sim_department_id')
            ->leftJoin('sim_regions as r', 'r.id', '=', 'd.sim_region_id')
            ->whereIn('c.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->whereYear('c.collected_on', $year)
            ->whereNotNull('items.price_retail')
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, r.name as region_name, m.name as market_name, p.name as product_name, ROUND(AVG(items.price_retail), 0) as price");

        if ($month) {
            $query->whereMonth('c.collected_on', $month);
        }
        if ($region) {
            $query->where('r.name', $region);
        }
        if ($categoryId) {
            $query->where('p.sim_product_category_id', $categoryId);
        }
        if ($search && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('p.name', 'like', $term)
                    ->orWhere('m.name', 'like', $term)
                    ->orWhere('r.name', 'like', $term);
            });
        }

        $query->groupByRaw("{$yearExpr}, {$monthExpr}, r.name, m.name, p.name");

        $allowedSort = ['year' => 'year', 'month' => 'month', 'region_name' => 'region_name', 'market_name' => 'market_name', 'product_name' => 'product_name', 'price' => 'price'];
        $col = $allowedSort[$sortCol] ?? 'year';
        $dir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($col, $dir);

        $paginator = $query->paginate($perPage);
        return ['data' => $paginator->getCollection(), 'total' => $paginator->total(), 'paginator' => $paginator];
    }

    public function collectorOverview(User $user): array
    {
        if (!$this->isReady()) {
            return [
                'assignments_count' => 0,
                'collections_today' => 0,
                'draft_count' => 0,
                'submitted_count' => 0,
                'latest_status' => null,
            ];
        }

        $latestCollection = SimCollection::where('collector_id', $user->id)
            ->latest('last_activity_at')
            ->first();

        return [
            'assignments_count' => SimCollectorAssignment::where('collector_id', $user->id)->where('is_active', true)->count(),
            'collections_today' => SimCollection::where('collector_id', $user->id)->whereDate('collected_on', now()->toDateString())->count(),
            'draft_count' => SimCollection::where('collector_id', $user->id)->where('status', SimCollection::STATUS_DRAFT)->count(),
            'submitted_count' => SimCollection::where('collector_id', $user->id)->where('status', SimCollection::STATUS_SUBMITTED)->count(),
            'latest_status' => $latestCollection?->status,
        ];
    }

    public function supervisorOverview(User $user): array
    {
        if (!$this->isReady()) {
            return [
                'active_collectors' => 0,
                'collections_under_review' => 0,
                'validated_today' => 0,
                'late_collections' => 0,
            ];
        }

        return [
            'active_collectors' => SimCollectorAssignment::where('supervisor_id', $user->id)->where('is_active', true)->distinct('collector_id')->count('collector_id'),
            'collections_under_review' => SimCollection::where('supervisor_id', $user->id)->whereIn('status', [
                SimCollection::STATUS_SUBMITTED,
                SimCollection::STATUS_UNDER_REVIEW,
            ])->count(),
            'validated_today' => SimCollection::where('supervisor_id', $user->id)->whereDate('validated_at', now()->toDateString())->count(),
            'late_collections' => SimCollection::where('supervisor_id', $user->id)
                ->whereDate('collected_on', '<', now()->toDateString())
                ->whereNotIn('status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
                ->count(),
        ];
    }

    public function liveCollections(?int $supervisorId = null): Collection
    {
        if (!Schema::hasTable('sim_collections')) {
            return collect();
        }

        $query = SimCollection::query()
            ->with(['collector:id,name,email', 'market:id,name,sim_department_id', 'market.department.region'])
            ->whereDate('collected_on', now()->toDateString())
            ->orderByDesc('last_activity_at');

        if ($supervisorId) {
            $query->where('supervisor_id', $supervisorId);
        }

        return $query->limit(25)->get();
    }

    public function recentStatuses(?int $supervisorId = null): Collection
    {
        if (!Schema::hasTable('sim_collection_statuses')) {
            return collect();
        }

        $query = SimCollectionStatus::query()
            ->with(['user:id,name', 'collection.market:id,name']);

        if ($supervisorId) {
            $query->whereHas('collection', fn ($q) => $q->where('supervisor_id', $supervisorId));
        }

        return $query->latest()->limit(30)->get();
    }

    public function publicPrices(int $year = null): array
    {
        if (!Schema::hasTable('sim_collection_items')) {
            return [
                'cards' => [],
                'line' => ['labels' => [], 'datasets' => []],
                'bar' => ['labels' => [], 'datasets' => []],
            ];
        }

        $year = $year ?: (int) now()->year;

        $latestByProduct = DB::table('sim_collection_items as items')
            ->join('sim_collections as collections', 'collections.id', '=', 'items.sim_collection_id')
            ->join('sim_products as products', 'products.id', '=', 'items.sim_product_id')
            ->whereIn('collections.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->whereYear('collections.collected_on', $year)
            ->whereNotNull('items.price_retail')
            ->select('products.name', DB::raw('AVG(items.price_retail) as avg_price'))
            ->groupBy('products.name')
            ->orderBy('products.name')
            ->get();

        $cards = $latestByProduct->take(5)->map(fn ($row) => [
            'label' => $row->name,
            'value' => round((float) $row->avg_price, 2),
        ])->values()->all();

        $productNames = $latestByProduct->take(5)->pluck('name')->values();
        $monthLabels = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];

        $datasets = [];
        foreach ($productNames as $name) {
            $monthly = DB::table('sim_collection_items as items')
                ->join('sim_collections as collections', 'collections.id', '=', 'items.sim_collection_id')
                ->join('sim_products as products', 'products.id', '=', 'items.sim_product_id')
                ->where('products.name', $name)
                ->whereIn('collections.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
                ->whereYear('collections.collected_on', $year)
                ->whereNotNull('items.price_retail')
                ->selectRaw('MONTH(collections.collected_on) as month_number, AVG(items.price_retail) as avg_price')
                ->groupBy('month_number')
                ->pluck('avg_price', 'month_number');

            $datasets[] = [
                'label' => $name,
                'data' => collect(range(1, 12))->map(fn ($month) => round((float) ($monthly[$month] ?? 0), 2))->all(),
            ];
        }

        $currentValues = $productNames->map(function ($name) use ($year) {
            return round((float) DB::table('sim_collection_items as items')
                ->join('sim_collections as collections', 'collections.id', '=', 'items.sim_collection_id')
                ->join('sim_products as products', 'products.id', '=', 'items.sim_product_id')
                ->where('products.name', $name)
                ->whereIn('collections.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
                ->whereYear('collections.collected_on', $year)
                ->whereNotNull('items.price_retail')
                ->avg('items.price_retail'), 2);
        })->all();

        $previousValues = $productNames->map(function ($name) use ($year) {
            return round((float) DB::table('sim_collection_items as items')
                ->join('sim_collections as collections', 'collections.id', '=', 'items.sim_collection_id')
                ->join('sim_products as products', 'products.id', '=', 'items.sim_product_id')
                ->where('products.name', $name)
                ->whereIn('collections.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
                ->whereYear('collections.collected_on', $year - 1)
                ->whereNotNull('items.price_retail')
                ->avg('items.price_retail'), 2);
        })->all();

        return [
            'cards' => $cards,
            'line' => [
                'labels' => $monthLabels,
                'datasets' => $datasets,
            ],
            'bar' => [
                'labels' => $productNames->all(),
                'datasets' => [
                    ['label' => (string) $year, 'data' => $currentValues],
                    ['label' => (string) ($year - 1), 'data' => $previousValues],
                ],
            ],
        ];
    }

    protected function emptyOverview(): array
    {
        return [
            'markets_count' => 0,
            'products_count' => 0,
            'collectors_count' => 0,
            'supervisors_count' => 0,
            'collections_today' => 0,
            'collections_this_month' => 0,
            'last_updated' => null,
            'pending_collections' => 0,
            'validated_collections' => 0,
            'pending_requests' => 0,
        ];
    }

    /** Comparaison des prix par région (vue DG) */
    public function getRegionalComparison(int $year = null, int $limitProducts = 8): array
    {
        if (!Schema::hasTable('sim_collection_items') || !Schema::hasTable('sim_regions') || !Schema::hasTable('sim_departments')) {
            return ['regions' => [], 'products' => [], 'matrix' => []];
        }
        $year = $year ?: (int) now()->year;
        $byRegion = DB::table('sim_collection_items as items')
            ->join('sim_collections as c', 'c.id', '=', 'items.sim_collection_id')
            ->join('sim_products as p', 'p.id', '=', 'items.sim_product_id')
            ->join('sim_markets as m', 'm.id', '=', 'c.sim_market_id')
            ->leftJoin('sim_departments as d', 'd.id', '=', 'm.sim_department_id')
            ->leftJoin('sim_regions as r', 'r.id', '=', 'd.sim_region_id')
            ->whereIn('c.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->whereYear('c.collected_on', $year)
            ->whereNotNull('items.price_retail')
            ->whereNotNull('r.name')
            ->selectRaw('r.name as region, p.name as product, ROUND(AVG(items.price_retail), 0) as price')
            ->groupBy('r.name', 'p.name')
            ->get();
        $regions = $byRegion->pluck('region')->unique()->sort()->values()->all();
        $products = $byRegion->pluck('product')->unique()->take($limitProducts)->values()->all();
        $matrix = [];
        foreach ($byRegion as $row) {
            $matrix[$row->region][$row->product] = (int) $row->price;
        }
        return ['regions' => $regions, 'products' => $products, 'matrix' => $matrix];
    }

    /** Alertes hausse des prix (évolution > seuil % vs mois précédent) */
    public function getPriceAlerts(float $thresholdPercent = 15): array
    {
        if (!Schema::hasTable('sim_collection_items')) {
            return [];
        }
        $year = (int) now()->year;
        $currentMonth = (int) now()->month;
        $current = DB::table('sim_collection_items as items')
            ->join('sim_collections as c', 'c.id', '=', 'items.sim_collection_id')
            ->join('sim_products as p', 'p.id', '=', 'items.sim_product_id')
            ->whereIn('c.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->whereYear('c.collected_on', $year)
            ->whereMonth('c.collected_on', $currentMonth)
            ->whereNotNull('items.price_retail')
            ->select('p.name', DB::raw('AVG(items.price_retail) as avg_price'))
            ->groupBy('p.name')
            ->pluck('avg_price', 'name');
        $prevYear = $currentMonth === 1 ? $year - 1 : $year;
        $prevMonth = $currentMonth === 1 ? 12 : $currentMonth - 1;
        $previous = DB::table('sim_collection_items as items')
            ->join('sim_collections as c', 'c.id', '=', 'items.sim_collection_id')
            ->join('sim_products as p', 'p.id', '=', 'items.sim_product_id')
            ->whereIn('c.status', [SimCollection::STATUS_VALIDATED, SimCollection::STATUS_PUBLISHED])
            ->whereYear('c.collected_on', $prevYear)
            ->whereMonth('c.collected_on', $prevMonth)
            ->whereNotNull('items.price_retail')
            ->select('p.name', DB::raw('AVG(items.price_retail) as avg_price'))
            ->groupBy('p.name')
            ->pluck('avg_price', 'name');
        $alerts = [];
        foreach ($current as $product => $avg) {
            $prev = $previous->get($product);
            if ($prev && $prev > 0) {
                $pct = (($avg - $prev) / $prev) * 100;
                if ($pct >= $thresholdPercent) {
                    $alerts[] = [
                        'product' => $product,
                        'current' => round((float) $avg, 0),
                        'previous' => round((float) $prev, 0),
                        'change_percent' => round($pct, 1),
                    ];
                }
            }
        }
        usort($alerts, fn ($a, $b) => $b['change_percent'] <=> $a['change_percent']);
        return array_slice($alerts, 0, 10);
    }
}
