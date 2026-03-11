@extends('layouts.dg-modern')

@section('title', 'Consultation du Personnel')
@section('page-title', 'Personnel CSAR - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-users me-2 text-primary"></i>
                            Consultation du Personnel
                        </h1>
                        <p class="text-muted mb-0 small">
                            Vue d'ensemble du personnel CSAR - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary-modern btn-modern btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="exportPersonnel()">
                            <i class="fas fa-file-excel me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques du personnel -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 50px; height: 50px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['total'] ?? 0 }}</h3>
                        <p class="stats-label">👥 Total Personnel</p>
                        <small class="text-muted">Tous départements</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-success); width: 50px; height: 50px;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['active'] ?? 0 }}</h3>
                        <p class="stats-label">✅ Actifs</p>
                        <small class="text-success">En service</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-info); width: 50px; height: 50px;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['managers'] ?? 0 }}</h3>
                        <p class="stats-label">👔 Cadres</p>
                        <small class="text-info">Direction/Management</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-warning); width: 50px; height: 50px;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['on_leave'] ?? 0 }}</h3>
                        <p class="stats-label">⏰ En Congé</p>
                        <small class="text-warning">Absents</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique du personnel -->
    <div class="row mb-3">
        <div class="col-lg-8 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>
                        Répartition par Département
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm active" onclick="updatePersonnelChart('department')">Département</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="updatePersonnelChart('position')">Poste</button>
                    </div>
                </div>
                <div style="height: 200px; position: relative;">
                    <canvas id="personnelChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-chart-line me-2 text-success"></i>
                    Performance
                </h6>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Taux de Présence</span>
                        <span class="small fw-bold">{{ $stats['presence_rate'] ?? '—' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ is_numeric($stats['presence_rate'] ?? null) ? (float)$stats['presence_rate'] : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Formations Complétées</span>
                        <span class="small fw-bold">{{ $stats['training_rate'] ?? '—' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: {{ is_numeric($stats['training_rate'] ?? null) ? (float)$stats['training_rate'] : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Satisfaction</span>
                        <span class="small fw-bold">{{ $stats['satisfaction'] ?? '—' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: {{ is_numeric($stats['satisfaction'] ?? null) ? (float)$stats['satisfaction'] * 10 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste du personnel -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Liste du Personnel
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">{{ $personnel->count() }} employé(s)</span>
                    </div>
                </div>
                
                @if($personnel->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Poste</th>
                                    <th>Département</th>
                                    <th>Entrepôt</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($personnel as $employee)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                                <i class="fas fa-user" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $employee->prenoms_nom ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $employee->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $employee->poste_actuel ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $employee->direction_service ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $employee->statut ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $employee->localisation_region ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $employee->matricule ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($employee->statut_validation == 'Valide')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($employee->statut_validation == 'En attente')
                                            <span class="badge bg-warning">En attente</span>
                                        @elseif($employee->statut_validation == 'Rejete')
                                            <span class="badge bg-danger">Rejeté</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $employee->statut_validation ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('dg.personnel.show', $employee->id) }}" class="btn btn-outline-primary btn-sm">
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
                            <i class="fas fa-users" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="text-muted">Aucun personnel trouvé</h5>
                        <p class="text-muted">Aucun employé n'est actuellement enregistré</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique du personnel
    const personnelCtx = document.getElementById('personnelChart').getContext('2d');
    const personnelChart = new Chart(personnelCtx, {
        type: 'doughnut',
        data: {
            labels: ['Direction', 'Logistique', 'Opérations', 'Administration'],
            datasets: [{
                data: [15, 35, 25, 20],
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
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    function refreshData() {
        location.reload();
    }

    function exportPersonnel() {
        alert('Export du personnel en cours...');
    }

    function updatePersonnelChart(type) {
        if (type === 'department') {
            personnelChart.data.labels = ['Direction', 'Logistique', 'Opérations', 'Administration'];
            personnelChart.data.datasets[0].data = [15, 35, 25, 20];
        } else if (type === 'position') {
            personnelChart.data.labels = ['Cadres', 'Agents', 'Ouvriers', 'Stagiaires'];
            personnelChart.data.datasets[0].data = [20, 45, 25, 10];
        }
        personnelChart.update();
    }
</script>
@endsection