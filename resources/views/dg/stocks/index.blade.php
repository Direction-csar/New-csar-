@extends('layouts.dg-modern')

@section('title', 'Consultation des Stocks')
@section('page-title', 'Stocks CSAR - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-boxes me-2 text-primary"></i>
                            Consultation des Stocks
                        </h1>
                        <p class="text-muted mb-0 small">
                            Vue d'ensemble des stocks CSAR - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary-modern btn-modern btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="exportStocks()">
                            <i class="fas fa-file-excel me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des stocks -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 50px; height: 50px;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['total_items'] ?? 0 }}</h3>
                        <p class="stats-label">📦 Articles Total</p>
                        <small class="text-muted">Tous types</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-success); width: 50px; height: 50px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['in_stock'] ?? 0 }}</h3>
                        <p class="stats-label">✅ En Stock</p>
                        <small class="text-success">Disponibles</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-warning); width: 50px; height: 50px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['low_stock'] ?? 0 }}</h3>
                        <p class="stats-label">⚠️ Stock Faible</p>
                        <small class="text-warning">Attention requise</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-danger); width: 50px; height: 50px;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['out_of_stock'] ?? 0 }}</h3>
                        <p class="stats-label">❌ Rupture</p>
                        <small class="text-danger">Réapprovisionnement</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique des stocks -->
    <div class="row mb-3">
        <div class="col-lg-8 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        État des Stocks par Entrepôt
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm active" onclick="updateStockChart('quantity')">Quantité</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="updateStockChart('value')">Valeur</button>
                    </div>
                </div>
                <div style="height: 200px; position: relative;">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-bell me-2 text-warning"></i>
                    Alertes Stock
                </h6>

                <div class="alert alert-warning d-flex align-items-center mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong class="small">Stock faible</strong><br>
                        <small>Riz - Entrepôt Principal</small>
                    </div>
                </div>

                <div class="alert alert-danger d-flex align-items-center mb-2">
                    <i class="fas fa-times-circle me-2"></i>
                    <div>
                        <strong class="small">Rupture de stock</strong><br>
                        <small>Huile - Entrepôt Régional</small>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mb-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong class="small">Réapprovisionnement</strong><br>
                        <small>Lentilles - En cours</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des stocks -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Liste des Articles en Stock
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">{{ $stocks->count() }} article(s)</span>
                    </div>
                </div>

                @if($stocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Type</th>
                                    <th>Quantité</th>
                                    <th>Entrepôt</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                                <i class="fas fa-box" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $stock->item_name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $stock->description ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $stock->stock_type ?? 'Standard' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $stock->quantity ?? 0 }} unités</div>
                                        <small class="text-muted">Seuil: {{ $stock->min_quantity ?? 0 }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $stock->warehouse_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $stock->warehouse_location ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if(($stock->quantity ?? 0) <= ($stock->min_quantity ?? 0))
                                            <span class="badge bg-danger">Stock faible</span>
                                        @elseif(($stock->quantity ?? 0) == 0)
                                            <span class="badge bg-danger">Rupture</span>
                                        @else
                                            <span class="badge bg-success">Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('dg.stocks.show', $stock->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="icon-3d mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-secondary);">
                            <i class="fas fa-boxes" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="text-muted">Aucun stock trouvé</h5>
                        <p class="text-muted">Aucun article n'est actuellement en stock</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique des stocks
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(stockCtx, {
        type: 'bar',
        data: {
            labels: ['Entrepôt Principal', 'Entrepôt Régional', 'Entrepôt Final'],
            datasets: [{
                label: 'Quantité en Stock',
                data: [150, 200, 100],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(51, 207, 102, 0.8)',
                    'rgba(255, 107, 107, 0.8)'
                ],
                borderColor: [
                    '#667eea',
                    '#51cf66',
                    '#ff6b6b'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.08)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    function refreshData() {
        location.reload();
    }

    function exportStocks() {
        window.location.href = '{{ route('dg.reports.export', ['type' => 'stocks', 'format' => 'csv']) }}';
    }

    function updateStockChart(type) {
        if (type === 'quantity') {
            stockChart.data.datasets[0].data = [150, 200, 100];
            stockChart.data.datasets[0].label = 'Quantité en Stock';
        } else if (type === 'value') {
            stockChart.data.datasets[0].data = [25000, 35000, 18000];
            stockChart.data.datasets[0].label = 'Valeur (FCFA)';
        }
        stockChart.update();
    }
</script>
@endsection
