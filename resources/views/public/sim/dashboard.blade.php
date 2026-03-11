@extends('layouts.public')

@section('title', 'SIM CSAR - Tableau de bord')

@section('content')
<div class="container py-4">
    <div class="p-4 rounded-4 text-white mb-4" style="background: linear-gradient(135deg, #0f766e 0%, #166534 100%);">
        <h1 class="mb-2">Système d'Information sur les Marchés</h1>
        <p class="mb-3 opacity-75">Données validées, tendances des prix, bulletins publiés et accès contrôlé aux informations du SIM/CSAR.</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('sim.prices') }}" class="btn btn-light">Voir les prix</a>
            <a href="{{ route('sim.index') }}" class="btn btn-outline-light">Voir les bulletins</a>
            <a href="{{ route('sim.request-access') }}" class="btn btn-warning">Demander accès aux données</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Marchés suivis</div>
                    <div class="fs-3 fw-bold">{{ $overview['markets_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Produits suivis</div>
                    <div class="fs-3 fw-bold">{{ $overview['products_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Collectes ce mois</div>
                    <div class="fs-3 fw-bold">{{ $overview['collections_this_month'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Dernière MAJ</div>
                    <div class="fs-6 fw-bold">{{ $overview['last_updated'] ? \Carbon\Carbon::parse($overview['last_updated'])->format('d/m/Y') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Prix moyens validés</h5>
                </div>
                <div class="card-body">
                    @if(empty($prices['cards']))
                        <p class="text-muted mb-0">Aucune donnée validée à afficher pour le moment.</p>
                    @else
                        <div class="row g-3">
                            @foreach($prices['cards'] as $card)
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100 bg-light">
                                        <div class="small text-muted">{{ $card['label'] }}</div>
                                        <div class="fs-4 fw-bold text-success">{{ number_format($card['value'], 0, ',', ' ') }} FCFA</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Évolution des prix (mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="simEvolutionChart" height="180"></canvas>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Consultation des prix</h5>
                    <a href="{{ route('sim.consultation-prix', ['locale' => app()->getLocale()]) }}" class="btn btn-sm btn-outline-success">Voir tout</a>
                </div>
                <div class="card-body p-0">
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
                                @forelse($priceTable['data'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row->year }}</td>
                                    <td>{{ $row->month ? \Carbon\Carbon::createFromDate(2000, $row->month, 1)->translatedFormat('M') : '—' }}</td>
                                    <td>{{ $row->region_name ?? '—' }}</td>
                                    <td>{{ Str::limit($row->market_name ?? '—', 12) }}</td>
                                    <td>{{ Str::limit($row->product_name ?? '—', 12) }}</td>
                                    <td class="text-end">{{ number_format($row->price ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted small py-3">Aucune donnée.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Carte des marchés</h5>
                    <a href="{{ route('sim.carte-marches', ['locale' => app()->getLocale()]) }}" class="btn btn-sm btn-outline-primary">Ouvrir la carte</a>
                </div>
                <div class="card-body p-0">
                    <div id="dashboard-mini-map" style="height: 220px;"></div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Bulletins publiés</h5>
                    <a href="{{ route('sim.index') }}" class="btn btn-sm btn-outline-success">Tout voir</a>
                </div>
                <div class="card-body">
                    @forelse($latestReports as $report)
                        <div class="border-bottom py-3">
                            <div class="fw-semibold">{{ $report->title }}</div>
                            <div class="small text-muted">{{ $report->report_type_label }} - {{ optional($report->published_at)->format('d/m/Y') }}</div>
                            <a href="{{ route('sim.show', $report) }}" class="small text-decoration-none">Consulter</a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun bulletin publié pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Liens rapides</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.presentation', ['locale' => app()->getLocale()]) }}">Présentation du SIM / Rapport atelier</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.prices') }}">Prix des produits</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.consultation-prix', ['locale' => app()->getLocale()]) }}">Consultation des prix</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.carte-marches', ['locale' => app()->getLocale()]) }}">Carte des marchés</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.index') }}">Bulletins / Publications</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.magasins') }}">Magasins / entrepôts</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('sim.request-access') }}">Demander accès aux données</a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Politique d'accès</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Les données détaillées de collecte ne sont pas publiées librement.</p>
                    <p class="small text-muted mb-0">Seules les statistiques validées et les bulletins approuvés par l'administration centrale du CSAR sont diffusés publiquement.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lineConfig = @json($prices['line'] ?? ['labels' => [], 'datasets' => []]);
    const palette = ['#2563eb', '#16a34a', '#ca8a04', '#dc2626', '#7c3aed'];
    const lineDatasets = (lineConfig.datasets || []).map(function (dataset, index) {
        return {
            label: dataset.label,
            data: dataset.data,
            borderColor: palette[index % palette.length],
            backgroundColor: palette[index % palette.length] + '33',
            tension: 0.3,
            fill: true
        };
    });
    if (document.getElementById('simEvolutionChart')) {
        new Chart(document.getElementById('simEvolutionChart'), {
            type: 'line',
            data: { labels: lineConfig.labels || [], datasets: lineDatasets },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top' } } }
        });
    }

    const markets = @json($marketsForMap ?? []);
    const miniMapEl = document.getElementById('dashboard-mini-map');
    if (miniMapEl && markets.length > 0) {
        const map = L.map('dashboard-mini-map').setView([14.5, -14.45], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '' }).addTo(map);
        const bounds = [];
        markets.forEach(function (m) {
            L.marker([m.lat, m.lng]).bindPopup('<strong>' + (m.name || '') + '</strong><br>' + (m.region || '')).addTo(map);
            bounds.push([m.lat, m.lng]);
        });
        if (bounds.length) map.fitBounds(bounds, { padding: [20, 20] });
    } else if (miniMapEl) {
        const map = L.map('dashboard-mini-map').setView([14.5, -14.45], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '' }).addTo(map);
        L.marker([14.5, -14.45]).bindPopup('Aucun marché avec coordonnées.').addTo(map);
    }
});
</script>
@endsection
