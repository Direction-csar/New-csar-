<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\PublicRequest;
use App\Models\RequestMission;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Données décisionnelles du tableau de bord de la Direction générale.
 *
 * Toutes les valeurs proviennent de la base. Lorsqu'une source est
 * indisponible, la valeur vaut null afin que la vue affiche « Indisponible »
 * au lieu d'un zéro trompeur.
 */
class DgDashboardService
{
    /** Dossiers non finalisés (même définition que la liste DG des demandes). */
    public const IN_PROGRESS = ['soumise', 'en_revue', 'document_attente', 'signee', 'scannee'];

    /** Dossiers signés et scannés : la décision appartient à la DG. */
    public const AWAITING_DG = ['scannee'];

    public const APPROVED = ['validee_dg', 'approuvee', 'cloturee'];

    public const DECIDED = ['validee_dg', 'approuvee', 'rejetee', 'cloturee'];

    public const TYPES = [
        'aide_alimentaire' => 'Aide alimentaire',
        'aide_urgence' => "Aide d'urgence",
        'autre' => 'Autre',
    ];

    public const PERIODS = [
        'week' => 'Cette semaine',
        'month' => 'Ce mois',
        'quarter' => 'Ce trimestre',
        'year' => 'Cette année',
    ];

    public function __construct(private StockAlertService $stockAlerts)
    {
    }

    public function normalizeFilters(array $input): array
    {
        $period = array_key_exists($input['period'] ?? '', self::PERIODS) ? $input['period'] : 'month';
        $regions = $this->availableRegions();
        $region = in_array($input['region'] ?? null, $regions, true) ? $input['region'] : null;
        $type = array_key_exists($input['type'] ?? '', self::TYPES) ? $input['type'] : null;
        [$from, $to] = $this->periodRange($period);

        return [
            'period' => $period,
            'region' => $region,
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'regions' => $regions,
        ];
    }

    public function build(?User $user, array $filters): array
    {
        $alerts = $this->safe(fn () => $this->regionAlerts($user, $filters), null);

        return [
            'kpis' => $this->kpis($filters, $alerts),
            'evolution' => $this->safe(fn () => $this->evolution($filters), null),
            'types' => $this->safe(fn () => $this->typeBreakdown($filters), null),
            'stock' => $this->safe(fn () => $this->stockSummary($filters), null),
            'stockAlerts' => $alerts?->take(4)->values(),
            'awaiting' => $this->safe(fn () => $this->awaitingDecision($filters), collect()),
            'urgent' => $this->safe(fn () => $this->urgentRequests($filters), collect()),
            'recentRequests' => $this->safe(fn () => $this->requests($filters)->latest()->take(6)->get(), collect()),
            'activities' => $this->safe(fn () => $this->recentActivities($filters), collect()),
            'performance' => $this->safe(fn () => $this->performance($filters), null),
            'operations' => $this->safe(fn () => $this->operations($filters), null),
            'donations' => $this->safe(fn () => $this->donationSummary(), null),
        ];
    }

    /**
     * Compteurs réels affichés dans la cloche de la barre supérieure.
     */
    public function notificationsFor(?User $user): array
    {
        $items = [];

        $awaiting = $this->safe(fn () => PublicRequest::whereIn('workflow_status', self::AWAITING_DG)->count(), null);
        if ($awaiting) {
            $items[] = [
                'level' => 'warning',
                'icon' => 'fa-file-signature',
                'label' => $awaiting . ' dossier(s) en attente de votre décision',
                'url' => route('dg.demandes.index', ['workflow_status' => 'scannee']),
                'count' => $awaiting,
            ];
        }

        $urgent = $this->safe(fn () => PublicRequest::whereIn('workflow_status', self::IN_PROGRESS)->where('urgency', 'haute')->count(), null);
        if ($urgent) {
            $items[] = [
                'level' => 'danger',
                'icon' => 'fa-bolt',
                'label' => $urgent . ' demande(s) urgente(s) non finalisée(s)',
                'url' => route('dg.demandes.index', ['urgency' => 'haute']),
                'count' => $urgent,
            ];
        }

        $critical = $this->safe(fn () => $this->stockAlerts->getActiveAlerts($user, ['priority' => StockAlertService::PRIORITY_CRITICAL])->count(), null);
        if ($critical) {
            $items[] = [
                'level' => 'danger',
                'icon' => 'fa-boxes',
                'label' => $critical . ' alerte(s) de stock critique(s)',
                'url' => route('dg.stocks.index'),
                'count' => $critical,
            ];
        }

        return [
            'items' => $items,
            'total' => array_sum(array_column($items, 'count')),
        ];
    }

    private function kpis(array $f, ?Collection $alerts): array
    {
        $period = [$f['from']->toDateString(), $f['to']->toDateString()];

        return [
            'in_progress' => $this->safe(fn () => $this->requests($f)->whereIn('workflow_status', self::IN_PROGRESS)->count(), null),
            'awaiting_dg' => $this->safe(fn () => $this->requests($f)->whereIn('workflow_status', self::AWAITING_DG)->count(), null),
            'approved' => $this->safe(fn () => $this->requests($f)->whereIn('workflow_status', self::APPROVED)->whereBetween('processed_date', $period)->count(), null),
            'rejected' => $this->safe(fn () => $this->requests($f)->where('workflow_status', 'rejetee')->whereBetween('processed_date', $period)->count(), null),
            'urgent' => $this->safe(fn () => $this->requests($f)->whereIn('workflow_status', self::IN_PROGRESS)->where('urgency', 'haute')->count(), null),
            'warehouses_active' => $this->safe(fn () => Warehouse::where('is_active', true)
                ->when($f['region'], fn ($q) => $q->where('region', $f['region']))
                ->count(), null),
            'stock_alerts' => $alerts?->count(),
        ];
    }

    /**
     * Demandes créées par mois sur les six derniers mois (mois courant inclus).
     */
    private function evolution(array $f): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $counts = $this->requests($f)
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn ($date) => Carbon::parse($date)->format('Y-m'));

        $labels = [];
        $values = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = ucfirst($month->locale('fr')->translatedFormat('M Y'));
            $values[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values, 'total' => array_sum($values)];
    }

    private function typeBreakdown(array $f): array
    {
        $rows = $this->requests($f)
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $items = $rows->map(fn ($total, $type) => [
            'type' => $type,
            'label' => self::TYPES[$type] ?? ($type ? ucfirst(str_replace('_', ' ', $type)) : 'Non renseigné'),
            'total' => (int) $total,
        ])->sortByDesc('total')->values()->all();

        return ['items' => $items, 'total' => array_sum(array_column($items, 'total'))];
    }

    private function stockSummary(array $f): array
    {
        $warehouseIds = $this->regionWarehouseIds($f);

        $movements = StockMovement::query()
            ->whereBetween('created_at', [$f['from'], $f['to']])
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds));

        return [
            'in' => (clone $movements)->where('type', 'in')->count(),
            'out' => (clone $movements)->where('type', 'out')->count(),
            'total' => (clone $movements)->count(),
            'active_lots' => Stock::where('is_active', true)
                ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
                ->count(),
        ];
    }

    private function awaitingDecision(array $f): Collection
    {
        return $this->requests($f)
            ->whereIn('workflow_status', self::AWAITING_DG)
            ->orderBy('updated_at')
            ->take(5)
            ->get()
            ->map(function (PublicRequest $request) {
                $since = $this->statusSince($request, 'scannee');
                $request->setAttribute('awaiting_since', $since);
                $request->setAttribute('awaiting_days', $since ? (int) $since->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null);

                return $request;
            });
    }

    private function urgentRequests(array $f): Collection
    {
        return $this->requests($f)
            ->whereIn('workflow_status', self::IN_PROGRESS)
            ->where('urgency', 'haute')
            ->orderBy('created_at')
            ->take(5)
            ->get();
    }

    /**
     * Dernières entrées de l'historique de workflow (journal d'audit des dossiers).
     */
    private function recentActivities(array $f): Collection
    {
        $entries = $this->requests($f)
            ->whereNotNull('workflow_history')
            ->latest('updated_at')
            ->take(40)
            ->get(['id', 'tracking_code', 'workflow_history'])
            ->flatMap(fn (PublicRequest $request) => collect($request->workflow_history ?? [])
                ->filter(fn ($entry) => is_array($entry) && !empty($entry['timestamp']))
                ->map(fn ($entry) => [
                    'request_id' => $request->id,
                    'tracking_code' => $request->tracking_code,
                    'action' => (string) ($entry['action'] ?? ''),
                    'comment' => $entry['comment'] ?? null,
                    'user_id' => $entry['user_id'] ?? null,
                    'timestamp' => Carbon::parse($entry['timestamp']),
                ]))
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        $users = User::whereIn('id', $entries->pluck('user_id')->filter()->unique())
            ->get(['id', 'name', 'role'])
            ->keyBy('id');

        return $entries->map(function ($entry) use ($users) {
            $user = $entry['user_id'] ? $users->get($entry['user_id']) : null;
            $entry['user_name'] = $user->name ?? 'Système';
            $entry['user_role'] = $user->role ?? null;
            $entry['label'] = $this->actionLabel($entry['action']);

            return $entry;
        });
    }

    private function performance(array $f): array
    {
        $created = $this->requests($f)->whereBetween('created_at', [$f['from'], $f['to']]);
        $createdCount = (clone $created)->count();
        $decidedCount = (clone $created)->whereIn('workflow_status', self::DECIDED)->count();

        $processed = $this->requests($f)
            ->whereNotNull('processed_date')
            ->whereBetween('processed_date', [$f['from']->toDateString(), $f['to']->toDateString()])
            ->get(['created_at', 'processed_date']);

        $avgDays = $processed->isEmpty()
            ? null
            : round($processed->avg(fn ($r) => Carbon::parse($r->created_at)->startOfDay()->diffInDays(Carbon::parse($r->processed_date)->startOfDay())), 1);

        $approvedAll = $this->requests($f)->whereIn('workflow_status', ['approuvee', 'cloturee'])->count();
        $closed = $this->requests($f)->where('workflow_status', 'cloturee')->count();

        return [
            'created' => $createdCount,
            'decided' => $decidedCount,
            'processing_rate' => $createdCount > 0 ? (int) round($decidedCount / $createdCount * 100) : null,
            'average_days' => $avgDays,
            'processed_count' => $processed->count(),
            'closure_rate' => $approvedAll > 0 ? (int) round($closed / $approvedAll * 100) : null,
            'approved_all' => $approvedAll,
            'closed' => $closed,
        ];
    }

    private function operations(array $f): array
    {
        $missions = RequestMission::query()
            ->when($f['region'], fn ($q) => $q->whereHas('request', fn ($r) => $r->where('region', $f['region'])));

        return [
            'in_execution' => $this->requests($f)->where('workflow_status', 'approuvee')->count(),
            'transmitted_period' => $this->requests($f)->whereBetween('dtl_transmitted_at', [$f['from'], $f['to']])->count(),
            'missions_active' => (clone $missions)->whereNotIn('status', ['terminee', 'annulee'])->count(),
            'reports_pending' => (clone $missions)->where('status', 'rapport_soumis')->count(),
        ];
    }

    private function donationSummary(): array
    {
        return [
            'count' => Donation::count(),
            'success_count' => Donation::successful()->count(),
            'success_amount' => (float) Donation::successful()->sum('amount'),
        ];
    }

    private function regionAlerts(?User $user, array $f): Collection
    {
        $alerts = $this->stockAlerts->getActiveAlerts($user);
        $warehouseIds = $this->regionWarehouseIds($f);

        return $warehouseIds === null
            ? $alerts
            : $alerts->whereIn('warehouse_id', $warehouseIds)->values();
    }

    private function regionWarehouseIds(array $f): ?array
    {
        if (!$f['region']) {
            return null;
        }

        return Warehouse::where('region', $f['region'])->pluck('id')->all();
    }

    private function requests(array $f): Builder
    {
        return PublicRequest::query()
            ->when($f['region'], fn ($q) => $q->where('region', $f['region']))
            ->when($f['type'], fn ($q) => $q->where('type', $f['type']));
    }

    private function availableRegions(): array
    {
        $fromRequests = $this->safe(fn () => PublicRequest::query()->distinct()->pluck('region')->all(), []);
        $fromWarehouses = $this->safe(fn () => Warehouse::query()->distinct()->pluck('region')->all(), []);

        return collect(array_merge($fromRequests, $fromWarehouses))
            ->filter(fn ($region) => is_string($region) && trim($region) !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function periodRange(string $period): array
    {
        $now = now();
        $from = match ($period) {
            'week' => $now->copy()->startOfWeek(),
            'quarter' => $now->copy()->startOfQuarter(),
            'year' => $now->copy()->startOfYear(),
            default => $now->copy()->startOfMonth(),
        };

        return [$from, $now->copy()->endOfDay()];
    }

    /**
     * Date d'entrée dans un statut, lue dans l'historique du workflow.
     */
    private function statusSince(PublicRequest $request, string $status): ?Carbon
    {
        $entry = collect($request->workflow_history ?? [])
            ->filter(fn ($e) => is_array($e) && str_ends_with((string) ($e['action'] ?? ''), '→ ' . $status) && !empty($e['timestamp']))
            ->last();

        if ($entry) {
            return Carbon::parse($entry['timestamp']);
        }

        return $request->updated_at ? Carbon::parse($request->updated_at) : null;
    }

    private function actionLabel(string $action): string
    {
        if (preg_match('/→\s*(\w+)$/u', $action, $m)) {
            return match ($m[1]) {
                'en_revue' => 'Mise en revue',
                'document_attente' => 'Pièces demandées',
                'signee' => 'Document signé',
                'scannee' => 'Scan enregistré',
                'validee_dg' => 'Validation DG',
                'approuvee' => 'Approbation',
                'rejetee' => 'Rejet',
                'cloturee' => 'Clôture',
                default => $action,
            };
        }

        return $action;
    }

    private function safe(callable $callback, mixed $default): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::warning('DgDashboardService: donnée indisponible', ['error' => $e->getMessage()]);

            return $default;
        }
    }
}
