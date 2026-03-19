<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Collecteur SIM | CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --csar-green: #00a86b;
            --csar-blue: #0078d4;
        }
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .nav-collector {
            background: linear-gradient(135deg, #00a86b 0%, #008f5a 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-collector .navbar-brand { color: white !important; font-weight: 700; }
        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card-stat:hover { transform: translateY(-3px); }
        .card-stat .stat-icon {
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
        .table-collections { font-size: 0.9rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-collector">
        <div class="container">
            <a class="navbar-brand" href="{{ route('collector.dashboard') }}">
                <i class="fas fa-clipboard-list me-2"></i>Espace Collecteur SIM
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-shield me-1"></i>{{ session('collector_name') }}
                </span>
                <form method="POST" action="{{ route('collector.logout') }}" class="d-inline">
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
            <i class="fas fa-tachometer-alt me-2 text-success"></i>Tableau de bord
        </h1>

        {{-- Statistiques --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Total collectes</p>
                                <h3 class="mb-0">{{ $stats['total_collections'] }}</h3>
                            </div>
                            <div class="stat-icon green">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">En attente de sync</p>
                                <h3 class="mb-0">{{ $pendingCount }}</h3>
                            </div>
                            <div class="stat-icon orange">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1">Dernière sync</p>
                                <h6 class="mb-0">
                                    {{ $stats['last_sync'] ? $stats['last_sync']->format('d/m/Y H:i') : 'Jamais' }}
                                </h6>
                            </div>
                            <div class="stat-icon blue">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Zones assignées --}}
        @if(!empty($stats['assigned_zones']))
        <div class="card card-stat mb-4">
            <div class="card-body">
                <h6 class="card-title mb-2">
                    <i class="fas fa-map-marker-alt me-2 text-success"></i>Zones assignées
                </h6>
                <p class="mb-0">
                    @foreach($stats['assigned_zones'] as $zone)
                        <span class="badge bg-success me-1">{{ $zone }}</span>
                    @endforeach
                </p>
            </div>
        </div>
        @endif

        <div class="row">
            {{-- Dernières collectes --}}
            <div class="col-lg-7 mb-4">
                <div class="card card-stat">
                    <div class="card-header bg-white border-0 pt-4 pb-2">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2 text-success"></i>Dernières collectes
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($recentCollections->isEmpty())
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>Aucune collecte pour le moment.
                            </p>
                            <p class="text-muted small mt-2">
                                Utilisez l'application mobile CSAR SIM Collect pour saisir des données sur le terrain.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-collections">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Marché</th>
                                            <th>Prix</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentCollections as $c)
                                        <tr>
                                            <td>{{ $c->collection_date->format('d/m/Y') }}</td>
                                            <td>{{ $c->market_id ?? '-' }}</td>
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

            {{-- Historique sync --}}
            <div class="col-lg-5 mb-4">
                <div class="card card-stat">
                    <div class="card-header bg-white border-0 pt-4 pb-2">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2 text-success"></i>Historique synchronisation
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($syncHistory->isEmpty())
                            <p class="text-muted mb-0">Aucun historique de synchronisation.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($syncHistory as $log)
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>{{ $log->sync_started_at->format('d/m H:i') }}</span>
                                    <span class="badge {{ $log->status === 'success' ? 'bg-success' : ($log->status === 'failed' ? 'bg-danger' : 'bg-warning') }}">
                                        {{ $log->data_count }} données
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Info application mobile --}}
        <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-mobile-alt fa-2x me-3"></i>
            <div>
                <strong>Application mobile</strong><br>
                Pour collecter les données sur le terrain, utilisez l'application <strong>CSAR SIM Collect</strong> sur votre téléphone.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
