@extends('layouts.dg-modern')

@section('title', 'Rapports Exécutifs')
@section('page-title', 'Rapports CSAR - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                            Rapports Exécutifs
                        </h1>
                        <p class="text-muted mb-0 small">
                            Génération et consultation des rapports CSAR - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary-modern btn-modern btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="generateReport()">
                            <i class="fas fa-file-pdf me-1"></i>Nouveau Rapport
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Types de rapports -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-3" style="width: 60px; height: 60px; background: var(--gradient-primary);">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h6 class="fw-bold">Rapport Demandes</h6>
                <p class="text-muted small">Analyse des demandes d'aide</p>
                <button class="btn btn-primary-modern btn-modern btn-sm" onclick="generateRequestReport()">
                    <i class="fas fa-download me-1"></i>Générer
                </button>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-3" style="width: 60px; height: 60px; background: var(--gradient-success);">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h6 class="fw-bold">Rapport Entrepôts</h6>
                <p class="text-muted small">État des entrepôts</p>
                <button class="btn btn-success-modern btn-modern btn-sm" onclick="generateWarehouseReport()">
                    <i class="fas fa-download me-1"></i>Générer
                </button>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-3" style="width: 60px; height: 60px; background: var(--gradient-info);">
                    <i class="fas fa-boxes"></i>
                </div>
                <h6 class="fw-bold">Rapport Stocks</h6>
                <p class="text-muted small">Gestion des stocks</p>
                <button class="btn btn-info-modern btn-modern btn-sm" onclick="generateStockReport()">
                    <i class="fas fa-download me-1"></i>Générer
                </button>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-3" style="width: 60px; height: 60px; background: var(--gradient-warning);">
                    <i class="fas fa-users"></i>
                </div>
                <h6 class="fw-bold">Rapport Personnel</h6>
                <p class="text-muted small">Effectifs et performance</p>
                <button class="btn btn-warning-modern btn-modern btn-sm" onclick="generatePersonnelReport()">
                    <i class="fas fa-download me-1"></i>Générer
                </button>
            </div>
        </div>
    </div>

    <!-- Rapports récents -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Rapports Récents
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">{{ $reports->count() }} rapport(s)</span>
                    </div>
                </div>
                
                @if($reports->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rapport</th>
                                    <th>Type</th>
                                    <th>Période</th>
                                    <th>Généré le</th>
                                    <th>Taille</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                                <i class="fas fa-file-pdf" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $report->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $report->description ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $report->type ?? 'Standard' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $report->period ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $report->date_range ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($report->created_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $report->size ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('dg.reports.download', ['filename' => $report->filename]) }}" class="btn btn-outline-primary btn-sm me-1">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('dg.reports.show', $report->filename) }}" class="btn btn-outline-info btn-sm">
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
                            <i class="fas fa-file-pdf" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="text-muted">Aucun rapport généré</h5>
                        <p class="text-muted">Générez votre premier rapport en utilisant les options ci-dessus</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistiques des rapports -->
    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-chart-pie me-2 text-success"></i>
                    Répartition par Type
                </h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="reportTypeChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-calendar me-2 text-info"></i>
                    Génération Mensuelle
                </h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="reportMonthlyChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-info-circle me-2 text-warning"></i>
                    Informations
                </h6>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Rapports générés</span>
                        <span class="small fw-bold">{{ $reportStats['total_reports'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Taille totale</span>
                        <span class="small fw-bold">{{ $reportStats['total_size'] ?? '0 MB' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 60%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Dernière génération</span>
                        <span class="small fw-bold">{{ $reportStats['last_generated'] ?? 'N/A' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique des types de rapports
    const reportTypeCtx = document.getElementById('reportTypeChart').getContext('2d');
    const reportTypeChart = new Chart(reportTypeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Demandes', 'Entrepôts', 'Stocks', 'Personnel'],
            datasets: [{
                data: [35, 25, 20, 20],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(51, 207, 102, 0.8)',
                    'rgba(255, 107, 107, 0.8)',
                    'rgba(255, 212, 59, 0.8)'
                ],
                borderColor: [
                    '#667eea',
                    '#51cf66',
                    '#ff6b6b',
                    '#ffd43b'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Graphique mensuel
    const reportMonthlyCtx = document.getElementById('reportMonthlyChart').getContext('2d');
    const reportMonthlyChart = new Chart(reportMonthlyCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Rapports générés',
                data: [12, 19, 8, 15, 22, 18],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3,
                pointRadius: 4
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

    function generateReport() {
        alert('Génération d\'un nouveau rapport...');
    }

    function generateRequestReport() {
        alert('Génération du rapport des demandes...');
    }

    function generateWarehouseReport() {
        alert('Génération du rapport des entrepôts...');
    }

    function generateStockReport() {
        alert('Génération du rapport des stocks...');
    }

    function generatePersonnelReport() {
        alert('Génération du rapport du personnel...');
    }
</script>
@endsection