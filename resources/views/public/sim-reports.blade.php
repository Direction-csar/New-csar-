@extends('layouts.public')

@section('title', 'SIM | CSAR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sim-fluid-modern.css') }}">
@endpush

@section('content')
@php
    $loc = $locale ?? app()->getLocale();
    $base = url($loc . '/sim');
@endphp
<div class="container-fluid py-4 sim-fluid-dashboard">
    {{-- En-tête + Menu SIM --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="p-4 rounded-4 text-white mb-3" style="background: linear-gradient(135deg, #0f766e 0%, #166534 100%);">
                <h1 class="h3 mb-2">
                    <i class="fas fa-chart-line me-2"></i>Système d'Information sur les Marchés (SIM)
                </h1>
                <p class="mb-3 opacity-75 small">Statistiques, prix moyens, évolution, carte des marchés, consultation des prix et publications.</p>
                {{-- Liens rapides (sans Tableau de bord ni Présentation) --}}
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="{{ url($loc . '/sim/consultation-prix') }}" class="btn btn-outline-light btn-sm">Consultation des prix</a>
                    <a href="{{ url($loc . '/sim/carte-marches') }}" class="btn btn-outline-light btn-sm">Carte des marchés</a>
                    <a href="{{ url($loc . '/sim') }}" class="btn btn-outline-light btn-sm">Rapports & Publications</a>
                    <a href="{{ url($loc . '/sim/request-access') }}" class="btn btn-warning btn-sm">Demander accès aux données</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 1. Statistiques rapides (dashboard dynamique) --}}
    <div id="stats" class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 sim-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Marchés suivis</div>
                    <div class="fs-3 fw-bold text-primary">{{ $overview['markets_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 sim-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Produits suivis</div>
                    <div class="fs-3 fw-bold text-success">{{ $overview['products_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 sim-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Collectes ce mois</div>
                    <div class="fs-3 fw-bold text-info">{{ $overview['collections_this_month'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100 sim-stat-card">
                <div class="card-body">
                    <div class="text-muted small">Dernière mise à jour</div>
                    <div class="fs-6 fw-bold">{{ isset($overview['last_updated']) && $overview['last_updated'] ? \Carbon\Carbon::parse($overview['last_updated'])->format('d M Y') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- 2. Prix moyens des produits --}}
            <div id="prix" class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-coins me-2 text-success"></i>Prix moyens des produits</h5>
                    <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="hidden" name="locale" value="{{ $loc }}">
                        <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Appliquer</button>
                    </form>
                </div>
                <div class="card-body">
                    @if(empty($prices['cards']))
                        <p class="text-muted mb-0">Aucune donnée validée pour le moment.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-end">Prix moyen</th>
                                        <th class="text-end">Unité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prices['cards'] as $card)
                                    <tr>
                                        <td>{{ $card['label'] }}</td>
                                        <td class="text-end fw-bold">{{ number_format($card['value'], 0, ',', ' ') }}</td>
                                        <td class="text-end text-muted">FCFA/kg</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Graphique évolution des prix (graphiques clairs) --}}
            <div id="evolution" class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2 text-primary"></i>Évolution des prix dans le temps</h5>
                </div>
                <div class="card-body sim-chart-container">
                    <canvas id="simEvolutionChart" height="200"></canvas>
                </div>
            </div>

            {{-- 4. Carte des marchés --}}
            <div id="carte" class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2 text-info"></i>Carte des marchés</h5>
                    <a href="{{ url($loc . '/sim/carte-marches') }}" class="btn btn-sm btn-outline-primary">Ouvrir la carte</a>
                </div>
                <div class="card-body p-0">
                    <div id="simMap" style="height: 320px;"></div>
                </div>
            </div>

            {{-- 5. Consultation des prix (filtres + tableau) --}}
            <div id="consultation" class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Consultation des prix</h5>
                    <a href="{{ url($loc . '/sim/consultation-prix') }}" class="btn btn-sm btn-outline-success">Voir tout</a>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-2 mb-3">
                        <input type="hidden" name="locale" value="{{ $loc }}">
                        <div class="col-auto">
                            <select name="year" class="form-select form-select-sm">
                                @for($y = now()->year; $y >= now()->year - 3; $y--)
                                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select form-select-sm">
                                <option value="">Tous les mois</option>
                                @foreach([1=>'Jan',2=>'Fév',3=>'Mar',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Déc'] as $m => $l)
                                    <option value="{{ $m }}" {{ request('month') == (string)$m ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="text" name="region" class="form-control form-control-sm" placeholder="Région" value="{{ request('region') }}" style="width:140px">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Année</th>
                                    <th>Mois</th>
                                    <th>Région</th>
                                    <th>Marché</th>
                                    <th>Produit</th>
                                    <th class="text-end">Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($priceTable['data'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row->year ?? '—' }}</td>
                                    <td>{{ $row->month ? \Carbon\Carbon::createFromDate(2000, $row->month, 1)->translatedFormat('M') : '—' }}</td>
                                    <td>{{ $row->region_name ?? '—' }}</td>
                                    <td>{{ Str::limit($row->market_name ?? '—', 15) }}</td>
                                    <td>{{ Str::limit($row->product_name ?? '—', 15) }}</td>
                                    <td class="text-end">{{ number_format($row->price ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Aucune donnée. Utilisez les filtres ou consultez la page complète.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 6. Publications --}}
            <div id="publications" class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-pdf me-2 text-danger"></i>Publications SIM</h5>
                    <a href="{{ url($loc . '/sim') }}" class="btn btn-sm btn-outline-secondary">Tous les rapports</a>
                </div>
                <div class="card-body">
                    @if(isset($reports) && $reports->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($reports as $report)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong>{{ $report->title }}</strong>
                                    <br><small class="text-muted">{{ optional($report->published_at)->format('d/m/Y') }}</small>
                                </div>
                                <div>
                                    <a href="{{ route('sim-reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary me-1">Voir</a>
                                    @if($report->document_file ?? null)
                                        <a href="{{ route('sim-reports.download', $report->id) }}" class="btn btn-sm btn-success">PDF</a>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Aucun bulletin ou rapport publié pour le moment. Les publications apparaîtront ici.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne droite : menu SIM --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Menu SIM</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#stats" class="list-group-item list-group-item-action">Statistiques</a>
                    <a href="#prix" class="list-group-item list-group-item-action">Prix moyens</a>
                    <a href="#evolution" class="list-group-item list-group-item-action">Évolution des prix</a>
                    <a href="#carte" class="list-group-item list-group-item-action">Carte des marchés</a>
                    <a href="#consultation" class="list-group-item list-group-item-action">Consultation des prix</a>
                    <a href="#publications" class="list-group-item list-group-item-action">Publications</a>
                    <a href="{{ url($loc . '/sim/request-access') }}" class="list-group-item list-group-item-action text-warning">Demander accès aux données</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lineConfig = @json($prices['line'] ?? ['labels' => [], 'datasets' => []]);
    var palette = ['#2563eb', '#16a34a', '#ca8a04', '#dc2626', '#7c3aed'];
    var lineDatasets = (lineConfig.datasets || []).map(function (dataset, index) {
        return {
            label: dataset.label,
            data: dataset.data,
            borderColor: palette[index % palette.length],
            backgroundColor: palette[index % palette.length] + '33',
            tension: 0.3,
            fill: true
        };
    });
    var el = document.getElementById('simEvolutionChart');
    if (el && (lineConfig.labels || []).length) {
        new Chart(el, {
            type: 'line',
            data: { labels: lineConfig.labels || [], datasets: lineDatasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 16 } },
                    tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, titleFont: { size: 13 }, bodyFont: { size: 12 } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: { font: { size: 11 }, callback: function(v) { return v + ' F'; } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, maxRotation: 45 }
                    }
                }
            }
        });
    } else if (el) {
        el.parentNode.innerHTML = '<p class="text-muted text-center py-4 mb-0">Aucune donnée d\'évolution pour l\'année sélectionnée.</p>';
    }

    var markets = @json($marketsForMap ?? []);
    var mapEl = document.getElementById('simMap');
    if (mapEl) {
        var map = L.map('simMap').setView([14.5, -14.45], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
        if (markets.length > 0) {
            var bounds = [];
            markets.forEach(function (m) {
                L.marker([m.lat, m.lng]).bindPopup('<strong>' + (m.name || '') + '</strong><br>Région : ' + (m.region || '—')).addTo(map);
                bounds.push([m.lat, m.lng]);
            });
            map.fitBounds(bounds, { padding: [20, 20] });
        } else {
            L.marker([14.5, -14.45]).bindPopup('Aucun marché avec coordonnées.').addTo(map);
        }
    }
});
</script>
@endsection
