@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Gestion de la Newsletter')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-newspaper me-2"></i>Gestion de la Newsletter
                    </h1>
                    <p class="text-muted mb-0">Gérez les abonnements et envois de newsletter</p>
                </div>
                <div>
                    <button class="btn btn-info-modern btn-modern" onclick="exportSubscribers()">
                        <i class="fas fa-download me-2"></i>Exporter Abonnés
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-primary);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1" id="total-subscribers">{{ $stats['total_subscribers'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Abonnés</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-success);">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3 class="mb-1" id="newsletters-sent">{{ $stats['sent_newsletters'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Newsletters Envoyées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-warning);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1" id="pending-newsletters">{{ $stats['pending_newsletters'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">En Attente</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-info);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h3 class="mb-1" id="today-subscribers">{{ $stats['today_subscribers'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Abonnés Aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="newsletterTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="newsletters-tab" data-bs-toggle="tab" data-bs-target="#newsletters" type="button" role="tab">
                                <i class="fas fa-newspaper me-2"></i>Newsletters
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button" role="tab">
                                <i class="fas fa-users me-2"></i>Abonnés
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" type="button" role="tab">
                                <i class="fas fa-file-alt me-2"></i>Modèles
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>Analytiques
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="newsletterTabsContent">
                        <!-- Newsletters -->
                        <div class="tab-pane fade show active" id="newsletters" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="newsletter-search" placeholder="Rechercher des newsletters...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select class="form-select" id="newsletter-status-filter">
                                            <option value="">Tous les statuts</option>
                                            <option value="draft">Brouillon</option>
                                            <option value="scheduled">Programmée</option>
                                            <option value="sent">Envoyée</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary-modern btn-modern w-100" onclick="applyNewsletterFilters()">
                                        <i class="fas fa-search me-2"></i>Filtrer
                                    </button>
                                </div>
                            </div>
                            
                            <div id="newsletters-list">
                                <div class="text-center py-5">
                                    <div class="icon-3d mb-3" style="background: var(--gradient-secondary); width: 80px; height: 80px; margin: 0 auto;">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <h5 class="text-muted">Aucune newsletter</h5>
                                    <p class="text-muted">Les newsletters sont créées automatiquement depuis la plateforme publique.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Abonnés -->
                        <div class="tab-pane fade" id="subscribers" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="subscriber-search" placeholder="Rechercher des abonnés...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select class="form-select" id="subscriber-status-filter">
                                            <option value="">Tous les statuts</option>
                                            <option value="active">Actif</option>
                                            <option value="inactive">Inactif</option>
                                            <option value="unsubscribed">Désabonné</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-info-modern btn-modern w-100" onclick="exportSubscribers()">
                                        <i class="fas fa-download me-2"></i>Exporter
                                    </button>
                                </div>
                            </div>
                            
                            <div id="subscribers-list">
                                <div class="text-center py-5">
                                    <div class="icon-3d mb-3" style="background: var(--gradient-secondary); width: 80px; height: 80px; margin: 0 auto;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h5 class="text-muted">Aucun abonné</h5>
                                    <p class="text-muted">Les abonnements sont créés automatiquement depuis la plateforme publique.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modèles -->
                        <div class="tab-pane fade" id="templates" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="template-search" placeholder="Rechercher des modèles...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-info-modern btn-modern w-100" onclick="exportSubscribers()">
                                        <i class="fas fa-download me-2"></i>Exporter
                                    </button>
                                </div>
                            </div>
                            
                            <div id="templates-list">
                                <div class="text-center py-5">
                                    <div class="icon-3d mb-3" style="background: var(--gradient-secondary); width: 80px; height: 80px; margin: 0 auto;">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <h5 class="text-muted">Aucun modèle</h5>
                                    <p class="text-muted">Les modèles sont créés automatiquement depuis la plateforme publique.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Analytiques -->
                        <div class="tab-pane fade" id="analytics" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card-modern">
                                        <div class="card-header">
                                            <h6 class="mb-0">Évolution des Abonnés</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="subscribersChart" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card-modern">
                                        <div class="card-header">
                                            <h6 class="mb-0">Performance des Newsletters</h6>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="performanceChart" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
    animateCounters();
    
    // Les newsletters et abonnés sont créés automatiquement depuis la plateforme publique
    
    // Gestion de la programmation
    document.getElementById('newsletter_schedule').addEventListener('change', function() {
        const scheduleDate = document.getElementById('schedule-date');
        scheduleDate.style.display = this.value === 'schedule' ? 'block' : 'none';
    });
    
    // Initialiser les graphiques
    initCharts();
});

function animateCounters() {
    // Animation des compteurs (pour l'instant à 0)
    animateValue(document.getElementById('total-subscribers'), 0, 0, 1000);
    animateValue(document.getElementById('newsletters-sent'), 0, 0, 1000);
    animateValue(document.getElementById('open-rate'), 0, 0, 1000);
    animateValue(document.getElementById('click-rate'), 0, 0, 1000);
}

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Les newsletters, abonnés et modèles sont créés automatiquement depuis la plateforme publique

function applyNewsletterFilters() {
    // Simulation de filtrage (à remplacer par une vraie requête AJAX)
    showToast('Filtres appliqués', 'info');
}

function loadNewsletters() {
    // Simulation de chargement (à remplacer par une vraie requête AJAX)
    // Pour l'instant, la liste reste vide
}

function loadSubscribers() {
    // Simulation de chargement des abonnés
}

function loadTemplates() {
    // Simulation de chargement des modèles
}

function exportSubscribers() {
    showToast('Export des abonnés en cours...', 'info');
}

function initCharts() {
    // Graphique des abonnés
    const subscribersCtx = document.getElementById('subscribersChart').getContext('2d');
    new Chart(subscribersCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Abonnés',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Graphique de performance
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Ouvert', 'Non ouvert', 'Clics'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endpush