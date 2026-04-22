<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervision Collecteurs SIM | CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --csar-green: #00a86b;
            --csar-blue: #0078d4;
        }
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .nav-supervisor {
            background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-supervisor .navbar-brand { color: white !important; font-weight: 700; }
        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card-stat:hover { transform: translateY(-3px); }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .stat-icon.green { background: #00a86b; }
        .stat-icon.blue { background: #0078d4; }
        .stat-icon.orange { background: #f59e0b; }
        .stat-icon.red { background: #ef4444; }
        .collector-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            transition: all 0.3s;
        }
        .collector-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-supervisor">
        <div class="container">
            <a class="navbar-brand" href="{{ route('supervisor.dashboard') }}">
                <i class="fas fa-users-cog me-2"></i>Supervision Collecteurs SIM
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-tie me-1"></i>{{ auth('supervisor')->user()?->name ?? auth('admin')->user()?->name ?? 'Admin' }}
                </span>
                <form method="POST" action="{{ route('supervisor.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h1 class="h4 mb-4 text-dark">
            <i class="fas fa-chart-line me-2 text-primary"></i>Tableau de bord - Supervision
        </h1>

        {{-- Bouton Suivi Temps Réel --}}
        <div class="mb-4">
            <a href="{{ route('supervisor.live.tracking') }}" class="btn btn-success btn-lg shadow-sm">
                <i class="fas fa-map-marked-alt me-2"></i>
                <i class="fas fa-broadcast-tower me-2"></i>
                Suivi des Collecteurs en Temps Réel
            </a>
            <p class="text-muted small mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Visualisez la position GPS et le statut de vos collecteurs en direct sur une carte interactive
            </p>
        </div>

        {{-- Statistiques globales --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Total collecteurs</p>
                                <h3 class="mb-0">{{ $stats['total_collectors'] }}</h3>
                            </div>
                            <div class="stat-icon blue">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Actifs</p>
                                <h3 class="mb-0 text-success">{{ $stats['active_collectors'] }}</h3>
                            </div>
                            <div class="stat-icon green">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Collectes totales</p>
                                <h3 class="mb-0">{{ number_format($stats['total_collections']) }}</h3>
                            </div>
                            <div class="stat-icon green">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">En attente sync</p>
                                <h3 class="mb-0 text-warning">{{ $stats['pending_sync'] }}</h3>
                            </div>
                            <div class="stat-icon orange">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liste des collecteurs --}}
        <div class="card card-stat mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-2">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2 text-primary"></i>Collecteurs et leurs performances
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($collectors as $collector)
                    <div class="col-md-6 col-lg-4">
                        <div class="collector-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $collector['name'] }}</h6>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-user me-1"></i>{{ $collector['username'] }}
                                    </p>
                                    @if($collector['phone'])
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-phone me-1"></i>{{ $collector['phone'] }}
                                    </p>
                                    @endif
                                </div>
                                <span class="badge-status {{ $collector['is_active'] ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $collector['is_active'] ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            
                            <div class="mt-3 pt-3 border-top">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <p class="text-muted small mb-0">Collectes</p>
                                        <h5 class="mb-0">{{ $collector['total_collections'] }}</h5>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted small mb-0">En attente</p>
                                        <h5 class="mb-0 text-warning">{{ $collector['pending_collections'] }}</h5>
                                    </div>
                                </div>
                            </div>
                            
                            @if($collector['last_collection'])
                            <p class="text-muted small mt-2 mb-0">
                                <i class="fas fa-clock me-1"></i>Dernière collecte: {{ $collector['last_collection']->format('d/m/Y') }}
                            </p>
                            @endif
                            
                            @if(!empty($collector['assigned_zones']))
                            <div class="mt-2">
                                <p class="text-muted small mb-1">Zones:</p>
                                @foreach($collector['assigned_zones'] as $zone)
                                    <span class="badge bg-info me-1">{{ $zone }}</span>
                                @endforeach
                            </div>
                            @endif
                            
                            <a href="{{ route('supervisor.collector.details', $collector['id']) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                                <i class="fas fa-eye me-1"></i>Voir détails
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-muted text-center mb-0">
                            <i class="fas fa-info-circle me-1"></i>Aucun collecteur enregistré.
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Collecteurs en temps réel --}}
        <div class="card card-stat mb-4" style="border-left: 4px solid #00a86b;">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-broadcast-tower me-2 text-success"></i>
                        Collecteurs actifs — <span class="live-badge text-success fw-bold">🟢 En direct</span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success fs-4 px-3 py-2" id="live-active-count">—</span>
                        <small class="text-muted">Actualisation dans <span id="refresh-countdown">30s</span></small>
                        <a href="{{ route('supervisor.live.tracking') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-map-marked-alt me-1"></i>Carte temps réel
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="live-collectors-list">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-success me-2"></div>
                        <span class="text-muted">Chargement...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dernières collectes --}}
        <div class="card card-stat">
            <div class="card-header bg-white border-0 pt-4 pb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>Dernières collectes
                    </h6>
                    <a href="{{ route('supervisor.collections') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($recentCollections->isEmpty())
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>Aucune collecte pour le moment.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Collecteur</th>
                                    <th>Marché</th>
                                    <th>Produit</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCollections as $c)
                                <tr>
                                    <td>{{ $c->collection_date->format('d/m/Y H:i') }}</td>
                                    <td>{{ $c->collector->name ?? '-' }}</td>
                                    <td>{{ $c->market_id ?? '-' }}</td>
                                    <td>{{ $c->product_id ?? '-' }}</td>
                                    <td>{{ number_format($c->price, 0, ',', ' ') }} F</td>
                                    <td>
                                        @if($c->sync_status === 'pending')
                                            <span class="badge bg-warning">En attente</span>
                                        @elseif($c->sync_status === 'synced')
                                            <span class="badge bg-success">Synchronisé</span>
                                        @else
                                            <span class="badge bg-danger">Échec</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Rafraîchissement automatique des stats toutes les 30 secondes
    (function() {
        var refreshTimer = null;
        var countdown = 30;

        function updateCountdown() {
            var el = document.getElementById('refresh-countdown');
            if (el) el.textContent = countdown + 's';
            countdown--;
            if (countdown < 0) {
                countdown = 30;
                loadLiveStats();
            }
        }

        function loadLiveStats() {
            fetch('/api/mobile/collectors/locations')
                .then(function(r) { return r.ok ? r.json() : null; })
                .then(function(data) {
                    if (!data || !data.success) return;
                    var collectors = data.data;
                    var active = collectors.filter(function(c) { return c.status !== 'offline'; });
                    var el = document.getElementById('live-active-count');
                    if (el) {
                        el.textContent = active.length;
                        el.parentNode.querySelector('.live-badge').textContent =
                            active.length > 0 ? '🟢 En direct' : '⚫ Aucun actif';
                    }

                    // Mettre à jour la liste des collecteurs actifs
                    var liveList = document.getElementById('live-collectors-list');
                    if (liveList && collectors.length > 0) {
                        liveList.innerHTML = collectors.map(function(c) {
                            var color = { active:'#00a86b', collecting:'#0078d4', paused:'#f59e0b', offline:'#9ca3af' }[c.status] || '#9ca3af';
                            var label = { active:'Actif', collecting:'En collecte', paused:'En pause', offline:'Hors ligne' }[c.status] || c.status;
                            return '<div class="d-flex align-items-center justify-content-between py-2 border-bottom">' +
                                '<div>' +
                                '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + color + ';margin-right:8px;"></span>' +
                                '<strong>' + c.collector_name + '</strong>' +
                                '<span class="text-muted small ms-2">' + (c.current_market || 'Aucun marché') + '</span>' +
                                '</div>' +
                                '<div class="text-end">' +
                                '<span class="badge" style="background:' + color + '">' + label + '</span>' +
                                '<div class="text-muted" style="font-size:0.72rem;">' + c.collections_today + ' collectes · ' + c.last_activity + '</div>' +
                                '</div>' +
                                '</div>';
                        }).join('');
                    } else if (liveList) {
                        liveList.innerHTML = '<p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>Aucun collecteur en ligne actuellement.</p>';
                    }
                })
                .catch(function() {});
        }

        // Lancer immédiatement puis toutes les secondes pour le compte à rebours
        loadLiveStats();
        refreshTimer = setInterval(updateCountdown, 1000);
    })();
    </script>
</body>
</html>
