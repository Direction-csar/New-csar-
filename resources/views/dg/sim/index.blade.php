@extends('layouts.dg-modern')

@section('title', 'SIM - Vue DG')
@section('page-title', 'SIM - Indicateurs Marchés')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            Système d'Information sur les Marchés (SIM)
                        </h1>
                        <p class="text-muted mb-0 small">Prix nationaux, évolution, alertes et comparaison régionale — {{ now()->format('d/m/Y') }}</p>
                    </div>
                    <form method="get" class="d-flex align-items-center gap-2">
                        <label class="mb-0 small text-muted">Année</label>
                        <select name="year" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Indicateurs généraux --}}
    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 48px; height: 48px;">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $overview['markets_count'] ?? 0 }}</h3>
                        <p class="stats-label mb-0">Marchés suivis</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-success); width: 48px; height: 48px;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $overview['products_count'] ?? 0 }}</h3>
                        <p class="stats-label mb-0">Produits suivis</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-warning); width: 48px; height: 48px;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $overview['collections_this_month'] ?? 0 }}</h3>
                        <p class="stats-label mb-0">Collectes ce mois</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-info); width: 48px; height: 48px;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number fs-6">{{ $overview['last_updated'] ? \Carbon\Carbon::parse($overview['last_updated'])->format('d/m/Y') : '—' }}</h3>
                        <p class="stats-label mb-0">Dernière MAJ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Prix moyens nationaux --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header bg-transparent border-0 py-2">
                    <h5 class="mb-0"><i class="fas fa-coins me-2 text-success"></i>Prix moyens nationaux (détail)</h5>
                </div>
                <div class="card-body">
                    @if(empty($prices['cards']))
                        <p class="text-muted mb-0">Aucune donnée validée pour l'année {{ $year }}.</p>
                    @else
                        <div class="row g-3">
                            @foreach($prices['cards'] as $card)
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="small text-muted text-truncate" title="{{ $card['label'] }}">{{ Str::limit($card['label'], 18) }}</div>
                                        <div class="fw-bold text-success">{{ number_format($card['value'], 0, ',', ' ') }} F</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Évolution des prix (graphique) --}}
        <div class="col-lg-8 mb-3">
            <div class="card-modern h-100">
                <div class="card-header bg-transparent border-0 py-2">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2 text-primary"></i>Évolution des prix (mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="dgSimEvolutionChart" height="220"></canvas>
                </div>
            </div>
        </div>

        {{-- Alertes hausse --}}
        <div class="col-lg-4 mb-3">
            <div class="card-modern h-100">
                <div class="card-header bg-transparent border-0 py-2 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Alertes hausse</h5>
                    <span class="badge bg-warning text-dark">{{ count($alerts) }}</span>
                </div>
                <div class="card-body p-0">
                    @if(empty($alerts))
                        <p class="text-muted small px-3 py-3 mb-0">Aucune alerte (seuil 15 % vs mois précédent).</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-end">Actuel</th>
                                        <th class="text-end">Var. %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alerts as $a)
                                    <tr>
                                        <td class="text-truncate" style="max-width:120px" title="{{ $a['product'] }}">{{ Str::limit($a['product'], 16) }}</td>
                                        <td class="text-end">{{ number_format($a['current'], 0, ',', ' ') }} F</td>
                                        <td class="text-end text-danger fw-bold">+{{ $a['change_percent'] }} %</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Comparaison régions --}}
    <div class="row">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header bg-transparent border-0 py-2">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2 text-info"></i>Comparaison des prix par région ({{ $year }})</h5>
                </div>
                <div class="card-body p-0">
                    @if(empty($regional['regions']) || empty($regional['products']))
                        <p class="text-muted px-3 py-4 mb-0">Aucune donnée régionale disponible pour cette année.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Région</th>
                                        @foreach($regional['products'] as $p)
                                            <th class="text-end">{{ Str::limit($p, 12) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regional['regions'] as $reg)
                                    <tr>
                                        <td class="fw-medium">{{ $reg }}</td>
                                        @foreach($regional['products'] as $p)
                                            <td class="text-end">{{ isset($regional['matrix'][$reg][$p]) ? number_format($regional['matrix'][$reg][$p], 0, ',', ' ') : '—' }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $line = $prices['line'] ?? ['labels' => [], 'datasets' => []];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dgSimEvolutionChart');
    if (!ctx) return;
    const labels = @json($line['labels'] ?? []);
    const datasets = @json($line['datasets'] ?? []);
    if (labels.length === 0 && datasets.length === 0) {
        ctx.parentElement.innerHTML = '<p class="text-muted text-center py-4 mb-0">Aucune donnée d\'évolution pour l\'année sélectionnée.</p>';
        return;
    }
    new Chart(ctx, {
        type: 'line',
        data: { labels: labels, datasets: datasets },
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
                    ticks: { font: { size: 11 }, callback: v => v + ' F' }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } }
            }
        }
    });
});
</script>
@endsection
