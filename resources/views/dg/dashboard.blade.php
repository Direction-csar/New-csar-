@extends('layouts.dg')

@section('title', 'Tableau de Bord Direction')
@section('page-title', 'Tableau de Bord Direction Générale')

@section('content')
<div class="page-header">
    <h1 class="page-title">Tableau de Bord Direction</h1>
    <p class="page-subtitle">Vue d'ensemble stratégique du système CSAR</p>
</div>

<!-- Statistiques principales -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="stats-number" data-stat="total_users">{{ $stats['total_users'] ?? 0 }}</h3>
            <p class="stats-label">Utilisateurs Total</p>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3 class="stats-number" data-stat="pending_requests">{{ $stats['pending_requests'] ?? 0 }}</h3>
            <p class="stats-label">Demandes en Attente</p>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                <i class="fas fa-warehouse"></i>
            </div>
            <h3 class="stats-number" data-stat="total_warehouses">{{ $stats['total_warehouses'] ?? 0 }}</h3>
            <p class="stats-label">Entrepôts Actifs</p>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="stats-number" data-stat="low_stock_items">{{ $stats['low_stock_items'] ?? 0 }}</h3>
            <p class="stats-label">Alertes Stock</p>
        </div>
    </div>
</div>

<!-- Métriques de performance -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                <i class="fas fa-percentage"></i>
            </div>
            <h3 class="stats-number">{{ $stats['approval_rate'] ?? 0 }}%</h3>
            <p class="stats-label">Taux d'Approbation</p>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="stats-number">{{ $stats['average_processing_time'] ?? 0 }}h</h3>
            <p class="stats-label">Temps Traitement Moyen</p>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);">
                <i class="fas fa-user-tie"></i>
            </div>
            <h3 class="stats-number" data-stat="total_personnel">{{ $stats['total_personnel'] ?? 0 }}</h3>
            <p class="stats-label">Personnel Total</p>
        </div>
    </div>
</div>

<!-- Alertes système -->
@if(!empty($alerts))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Alertes Stratégiques
                </h5>
            </div>
            <div class="card-body">
                @foreach($alerts as $alert)
                <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                    <i class="fas fa-{{ $alert['icon'] }} me-2"></i>
                    {{ $alert['message'] }}
                    <span class="badge badge-{{ $alert['priority'] === 'high' ? 'danger' : ($alert['priority'] === 'medium' ? 'warning' : 'info') }} ms-2">
                        {{ ucfirst($alert['priority']) }}
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Graphique des tendances -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Tendances des 30 Derniers Jours
                </h5>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Répartition des utilisateurs -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Répartition par Rôle
                </h5>
            </div>
            <div class="card-body">
                <canvas id="rolesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Carte interactive -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marked-alt me-2"></i>Carte des Entrepôts
                </h5>
            </div>
            <div class="card-body">
                <div id="warehouseMap" style="height: 400px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Indicateurs clés -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Indicateurs Clés
                </h5>
            </div>
            <div class="card-body">
                <div class="kpi-item">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="kpi-content">
                        <h6>Efficacité Opérationnelle</h6>
                        <p class="kpi-value">{{ $stats['efficiency_rate'] ?? '0%' }}</p>
                    </div>
                </div>
                
                <div class="kpi-item">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="kpi-content">
                        <h6>Satisfaction Client</h6>
                        <p class="kpi-value">{{ $stats['satisfaction_rate'] ?? '0/10' }}</p>
                    </div>
                </div>
                
                <div class="kpi-item">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="kpi-content">
                        <h6>Temps de Réponse</h6>
                        <p class="kpi-value">{{ $stats['response_time'] ?? '0h' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Activités récentes -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Activités Récentes
                </h5>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="activity-content">
                            <h6>Demande approuvée</h6>
                            <p>Demande #1234 approuvée par Admin</p>
                            <small class="text-muted">Il y a 10 minutes</small>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="activity-content">
                            <h6>Nouvel entrepôt</h6>
                            <p>Entrepôt Tanger ajouté au système</p>
                            <small class="text-muted">Il y a 2 heures</small>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h6>Nouvel utilisateur</h6>
                            <p>Agent Sarah Johnson ajouté</p>
                            <small class="text-muted">Il y a 4 heures</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rapports disponibles -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>Rapports Disponibles
                </h5>
            </div>
            <div class="card-body">
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="report-content">
                            <h6>Rapport Mensuel</h6>
                            <p>Performance et statistiques du mois</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="generateReport('monthly')">
                                <i class="fas fa-download me-1"></i>Télécharger
                            </button>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="report-content">
                            <h6>Rapport Financier</h6>
                            <p>Analyse des coûts et revenus</p>
                            <button class="btn btn-sm btn-outline-success" onclick="generateReport('financial')">
                                <i class="fas fa-download me-1"></i>Télécharger
                            </button>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="report-content">
                            <h6>Rapport Personnel</h6>
                            <p>Statistiques des employés</p>
                            <button class="btn btn-sm btn-outline-info" onclick="generateReport('personnel')">
                                <i class="fas fa-download me-1"></i>Télécharger
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.kpi-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.kpi-item:last-child {
    border-bottom: none;
}

.kpi-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.kpi-content h6 {
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.kpi-content .kpi-value {
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
    color: #2c3e50;
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.activity-content h6 {
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.activity-content p {
    margin: 0 0 0.25rem 0;
    font-size: 0.8rem;
    color: #666;
}

.activity-content small {
    font-size: 0.7rem;
}

.report-list {
    max-height: 300px;
    overflow-y: auto;
}

.report-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.report-item:last-child {
    border-bottom: none;
}

.report-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.report-content h6 {
    margin: 0 0 0.25rem 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.report-content p {
    margin: 0 0 0.5rem 0;
    font-size: 0.8rem;
    color: #666;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script>
// Graphique des tendances
const trendsCtx = document.getElementById('trendsChart').getContext('2d');
const trendsChart = new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
        datasets: [{
            label: 'Demandes',
            data: [45, 52, 38, 61],
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }, {
            label: 'Approbations',
            data: [42, 48, 35, 58],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
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

// Graphique des rôles
const rolesCtx = document.getElementById('rolesChart').getContext('2d');
const rolesChart = new Chart(rolesCtx, {
    type: 'doughnut',
    data: {
        labels: ['Agents', 'Responsables', 'Admin', 'DG'],
        datasets: [{
            data: [45, 25, 5, 2],
            backgroundColor: [
                '#3498db',
                '#27ae60',
                '#f39c12',
                '#e74c3c'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Carte interactive
let warehouseMap;
let markers = [];

function initWarehouseMap() {
    warehouseMap = L.map('warehouseMap').setView([33.573110, -7.589843], 6); // Centré sur le Maroc
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(warehouseMap);

    // Ajouter des marqueurs d'exemple
    const warehouses = [
        {lat: 34.020882, lng: -6.841650, name: 'Entrepôt Rabat', status: 'active'},
        {lat: 33.573110, lng: -7.589843, name: 'Entrepôt Casablanca', status: 'active'},
        {lat: 30.420500, lng: -9.598100, name: 'Entrepôt Agadir', status: 'inactive'},
        {lat: 34.2611, lng: -6.5802, name: 'Entrepôt Salé', status: 'active'},
        {lat: 32.2999, lng: -9.2372, name: 'Entrepôt Safi', status: 'active'}
    ];

    warehouses.forEach(warehouse => {
        const markerColor = warehouse.status === 'active' ? 'green' : 'red';
        const markerHtml = `<div style="background-color:${markerColor}; width: 1.5rem; height: 1.5rem; display: block; left: -0.75rem; top: -0.75rem; position: relative; border-radius: 1.5rem 1.5rem 0; transform: rotate(45deg); border: 1px solid #FFFFFF"></div>`;
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: markerHtml,
            iconAnchor: [12, 24],
            popupAnchor: [0, -24]
        });

        const marker = L.marker([warehouse.lat, warehouse.lng], { icon: icon })
            .bindPopup(`<b>${warehouse.name}</b><br>Statut: ${warehouse.status === 'active' ? 'Actif' : 'Inactif'}`)
            .addTo(warehouseMap);
        markers.push(marker);
    });
}

// Initialiser la carte
document.addEventListener('DOMContentLoaded', function() {
    initWarehouseMap();
});

// Générer un rapport
function generateReport(type) {
    fetch('{{ route("dg.api.generate-report") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: type,
            format: 'pdf',
            period: 'month'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.download_url) {
                window.open(data.download_url, '_blank');
            } else {
                alert('Rapport ' + type + ' généré avec succès !');
            }
        } else {
            alert('Erreur lors de la génération du rapport');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la génération du rapport');
    });
}
</script>
@endpush