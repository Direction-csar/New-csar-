@php
    use App\Services\StockAlertService as SA;

    $panel = $stockAlertPanel ?? null;
    $action = $stockAlertAction ?? url()->current();
    $limit = $stockAlertLimit ?? 20;
    $showStockLink = $stockAlertShowStockLink ?? false;
    $saFmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', ' '), '0'), ',');
    $saKeep = collect(request()->query())->except(['alert_warehouse', 'alert_type', 'alert_priority', 'page'])
        ->filter(fn ($v) => is_scalar($v) && $v !== '');
    $saUrl = function (array $params) use ($action, $saKeep, $panel) {
        $base = array_filter([
            'alert_warehouse' => $panel['filters']['warehouse_id'] ?? null,
        ]);
        return $action . '?' . http_build_query(array_merge($saKeep->all(), $base, array_filter($params))) . '#stock-alerts';
    };
    $saTiles = [
        ['label' => 'Critiques', 'value' => $panel['summary']['critical'] ?? 0, 'icon' => 'fa-circle-exclamation', 'tone' => 'danger', 'params' => ['alert_priority' => SA::PRIORITY_CRITICAL]],
        ['label' => 'Avertissements', 'value' => $panel['summary']['warning'] ?? 0, 'icon' => 'fa-triangle-exclamation', 'tone' => 'warning', 'params' => ['alert_priority' => SA::PRIORITY_WARNING]],
        ['label' => SA::TYPE_LABELS[SA::TYPE_OUT_OF_STOCK], 'value' => $panel['summary']['by_type'][SA::TYPE_OUT_OF_STOCK] ?? 0, 'icon' => 'fa-box-open', 'tone' => 'slate', 'params' => ['alert_type' => SA::TYPE_OUT_OF_STOCK]],
        ['label' => SA::TYPE_LABELS[SA::TYPE_LOW_STOCK], 'value' => $panel['summary']['by_type'][SA::TYPE_LOW_STOCK] ?? 0, 'icon' => 'fa-arrow-trend-down', 'tone' => 'slate', 'params' => ['alert_type' => SA::TYPE_LOW_STOCK]],
        ['label' => SA::TYPE_LABELS[SA::TYPE_EXPIRING_SOON], 'value' => $panel['summary']['by_type'][SA::TYPE_EXPIRING_SOON] ?? 0, 'icon' => 'fa-hourglass-half', 'tone' => 'slate', 'params' => ['alert_type' => SA::TYPE_EXPIRING_SOON]],
        ['label' => SA::TYPE_LABELS[SA::TYPE_EXPIRED], 'value' => $panel['summary']['by_type'][SA::TYPE_EXPIRED] ?? 0, 'icon' => 'fa-calendar-xmark', 'tone' => 'slate', 'params' => ['alert_type' => SA::TYPE_EXPIRED]],
    ];
@endphp

<section class="sa-card" id="stock-alerts" aria-labelledby="stock-alerts-title">
    <div class="sa-header">
        <div>
            <h3 class="sa-title" id="stock-alerts-title">Alertes de stock</h3>
            <p class="sa-subtitle">
                Calculées en direct sur les lots actifs
                · péremption signalée à {{ (int) config('stock_alerts.expiring_days', 30) }} jours
            </p>
        </div>
        @if($showStockLink && Route::has('admin.stock.index'))
            <a href="{{ route('admin.stock.index') }}#stock-alerts" class="btn btn-light btn-sm">Gestion des stocks <i class="fas fa-arrow-right ms-1"></i></a>
        @endif
    </div>

    @if(!$panel)
        <div class="sa-empty">
            <span class="sa-empty-icon tone-warning"><i class="fas fa-plug-circle-exclamation"></i></span>
            <p class="sa-empty-title">Alertes indisponibles</p>
            <p class="sa-empty-text">Les données de stock n’ont pas pu être chargées. Réessayez plus tard.</p>
        </div>
    @else
        <div class="sa-tiles">
            @foreach($saTiles as $tile)
                <a href="{{ $saUrl($tile['params']) }}" class="sa-tile {{ $tile['value'] > 0 ? 'is-' . $tile['tone'] : '' }}">
                    <span class="sa-tile-icon"><i class="fas {{ $tile['icon'] }}"></i></span>
                    <span class="sa-tile-value">{{ number_format($tile['value'], 0, ',', ' ') }}</span>
                    <span class="sa-tile-label">{{ $tile['label'] }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ $action }}#stock-alerts" class="sa-filters" role="search" aria-label="Filtrer les alertes de stock">
            @foreach($saKeep as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <div class="sa-field">
                <label for="sa-warehouse" class="sa-label">Magasin</label>
                <select id="sa-warehouse" name="alert_warehouse" class="form-select form-select-sm">
                    <option value="">Tous les magasins</option>
                    @foreach($panel['warehouses'] as $wh)
                        <option value="{{ $wh->id }}" @selected($panel['filters']['warehouse_id'] === $wh->id)>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sa-field">
                <label for="sa-type" class="sa-label">Catégorie</label>
                <select id="sa-type" name="alert_type" class="form-select form-select-sm">
                    <option value="">Toutes les catégories</option>
                    @foreach(SA::TYPE_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($panel['filters']['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sa-field">
                <label for="sa-priority" class="sa-label">Priorité</label>
                <select id="sa-priority" name="alert_priority" class="form-select form-select-sm">
                    <option value="">Toutes les priorités</option>
                    @foreach(SA::PRIORITY_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($panel['filters']['priority'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sa-actions">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filtrer</button>
                @if($panel['has_filters'])
                    <a href="{{ $action . ($saKeep->isNotEmpty() ? '?' . http_build_query($saKeep->all()) : '') }}#stock-alerts" class="btn btn-light btn-sm">Réinitialiser</a>
                @endif
            </div>
        </form>

        @if($panel['alerts']->isEmpty())
            <div class="sa-empty">
                @if($panel['has_filters'])
                    <span class="sa-empty-icon tone-slate"><i class="fas fa-filter-circle-xmark"></i></span>
                    <p class="sa-empty-title">Aucune alerte ne correspond aux filtres</p>
                    <p class="sa-empty-text">Modifiez ou réinitialisez les filtres pour voir les autres alertes.</p>
                @else
                    <span class="sa-empty-icon tone-green"><i class="fas fa-circle-check"></i></span>
                    <p class="sa-empty-title">Aucune alerte de stock</p>
                    <p class="sa-empty-text">Aucun lot actif n’est épuisé, sous son seuil minimal, périmé ou proche de la péremption.</p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table sa-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Priorité</th>
                            <th scope="col">Produit</th>
                            <th scope="col">Magasin</th>
                            <th scope="col" class="text-end">Disponible</th>
                            <th scope="col">Seuil / date</th>
                            <th scope="col">Alerte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($panel['alerts']->take($limit) as $alert)
                            @php $unit = $alert['unit'] ? ' ' . $alert['unit'] : ''; @endphp
                            <tr class="{{ $alert['priority'] === SA::PRIORITY_CRITICAL ? 'is-critical' : 'is-warning' }}">
                                <td>
                                    <span class="sa-badge {{ $alert['priority'] === SA::PRIORITY_CRITICAL ? 'sa-badge-critical' : 'sa-badge-warning' }}">
                                        <i class="fas {{ $alert['priority'] === SA::PRIORITY_CRITICAL ? 'fa-circle-exclamation' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                                        {{ $alert['priority_label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($alert['url'])
                                        <a href="{{ $alert['url'] }}" class="sa-product">{{ $alert['product_name'] }}</a>
                                    @else
                                        <span class="sa-product">{{ $alert['product_name'] }}</span>
                                    @endif
                                    @if($alert['batch_number'])
                                        <span class="sa-meta">Lot {{ $alert['batch_number'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $alert['warehouse_name'] }}</td>
                                <td class="text-end sa-num">{{ $saFmt($alert['quantity']) }}{{ $unit }}</td>
                                <td>
                                    @if($alert['type'] === SA::TYPE_EXPIRED)
                                        Périmé le {{ \Carbon\Carbon::parse($alert['expiry_date'])->format('d/m/Y') }}
                                        <span class="sa-meta">depuis {{ abs($alert['days_left']) }} j</span>
                                    @elseif($alert['type'] === SA::TYPE_EXPIRING_SOON)
                                        Expire le {{ \Carbon\Carbon::parse($alert['expiry_date'])->format('d/m/Y') }}
                                        <span class="sa-meta">dans {{ $alert['days_left'] }} j</span>
                                    @else
                                        Seuil : {{ $saFmt($alert['threshold']) }}{{ $unit }}
                                    @endif
                                </td>
                                <td><span class="sa-type">{{ $alert['type_label'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($panel['alerts']->count() > $limit)
                <p class="sa-more">
                    {{ $panel['alerts']->count() - $limit }} autre(s) alerte(s) non affichée(s) — affinez les filtres.
                </p>
            @endif
        @endif
    @endif
</section>

@once
@push('styles')
<style>
    .sa-card { background: var(--adm-surface); border: 1px solid var(--adm-border); border-radius: var(--adm-radius); box-shadow: var(--adm-shadow); padding: 1.25rem; min-width: 0; }
    .sa-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .sa-title { font-size: 1rem; font-weight: 600; margin: 0; color: var(--adm-text); }
    .sa-subtitle { font-size: .8125rem; color: var(--adm-muted); margin: .125rem 0 0; }

    .sa-tiles { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .sa-tile { display: flex; flex-direction: column; gap: .125rem; padding: .75rem .875rem; border: 1px solid var(--adm-border); border-radius: var(--adm-radius-sm); background: var(--adm-subtle); color: inherit; text-decoration: none; min-width: 0; transition: border-color .15s ease; }
    .sa-tile:hover { border-color: var(--adm-navy); color: inherit; }
    .sa-tile:focus-visible { outline: 2px solid var(--adm-navy); outline-offset: 2px; }
    .sa-tile-icon { font-size: .875rem; color: var(--adm-muted); }
    .sa-tile-value { font-size: 1.375rem; font-weight: 700; line-height: 1.1; color: var(--adm-text); font-variant-numeric: tabular-nums; }
    .sa-tile-label { font-size: .75rem; color: var(--adm-muted); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sa-tile.is-danger { background: var(--adm-danger-50); border-color: #F1B8B8; }
    .sa-tile.is-danger .sa-tile-icon, .sa-tile.is-danger .sa-tile-value { color: var(--adm-danger); }
    .sa-tile.is-warning { background: var(--adm-warning-50); border-color: #E9C46A; }
    .sa-tile.is-warning .sa-tile-icon, .sa-tile.is-warning .sa-tile-value { color: var(--adm-warning); }
    .sa-tile.is-slate { background: var(--adm-surface); }
    .sa-tile.is-slate .sa-tile-icon { color: var(--adm-navy); }

    .sa-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: .75rem; padding: .875rem; margin-bottom: 1rem; background: var(--adm-subtle); border: 1px solid var(--adm-border); border-radius: var(--adm-radius-sm); }
    .sa-field { display: flex; flex-direction: column; gap: .25rem; flex: 1 1 180px; min-width: 0; }
    .sa-label { font-size: .75rem; font-weight: 600; color: var(--adm-muted); margin: 0; }
    .sa-actions { display: flex; gap: .5rem; flex: 0 0 auto; }

    .sa-table { font-size: .875rem; }
    .sa-table thead th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: var(--adm-muted); background: var(--adm-subtle); border-bottom: 1px solid var(--adm-border); white-space: nowrap; }
    .sa-table td { border-color: var(--adm-border); }
    .sa-table tr.is-critical td:first-child { box-shadow: inset 3px 0 0 var(--adm-danger); }
    .sa-table tr.is-warning td:first-child { box-shadow: inset 3px 0 0 var(--adm-warning); }
    .sa-product { font-weight: 600; color: var(--adm-navy); text-decoration: none; display: block; }
    a.sa-product:hover { text-decoration: underline; }
    .sa-meta { display: block; font-size: .75rem; color: var(--adm-muted); }
    .sa-num { font-variant-numeric: tabular-nums; font-weight: 600; white-space: nowrap; }
    .sa-type { font-size: .8125rem; color: var(--adm-text); white-space: nowrap; }
    .sa-badge { display: inline-flex; align-items: center; gap: .375rem; padding: .1875rem .5rem; border-radius: 999px; font-size: .75rem; font-weight: 600; white-space: nowrap; }
    .sa-badge-critical { background: var(--adm-danger-50); color: var(--adm-danger); }
    .sa-badge-warning { background: var(--adm-warning-50); color: #8A5A00; }
    .sa-more { font-size: .8125rem; color: var(--adm-muted); margin: .75rem 0 0; }

    .sa-empty { text-align: center; padding: 1.75rem 1rem; }
    .sa-empty-icon { width: 48px; height: 48px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: .75rem; }
    .sa-empty-icon.tone-green { background: var(--adm-green-50); color: var(--adm-green); }
    .sa-empty-icon.tone-warning { background: var(--adm-warning-50); color: var(--adm-warning); }
    .sa-empty-icon.tone-slate { background: #EEF2F6; color: #475569; }
    .sa-empty-title { font-weight: 600; color: var(--adm-text); margin: 0 0 .25rem; }
    .sa-empty-text { font-size: .8125rem; color: var(--adm-muted); margin: 0; }

    @media (max-width: 1199.98px) { .sa-tiles { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) {
        .sa-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sa-actions { width: 100%; }
        .sa-actions .btn { flex: 1; }
    }
</style>
@endpush
@endonce
