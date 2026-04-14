<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Collecteur | CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .nav-supervisor { background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-supervisor py-3">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="{{ route('supervisor.dashboard') }}">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
            <span class="text-white">
                <i class="fas fa-user-tie me-1"></i>{{ auth()->user()->name }}
            </span>
        </div>
    </nav>

    <div class="container py-4">

        {{-- Profil collecteur --}}
        <div class="card card-stat mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-4">
                    <div style="width:64px;height:64px;border-radius:50%;background:#0078d4;display:flex;align-items:center;justify-content:center;color:white;font-size:1.8rem;flex-shrink:0;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $collector->name }}</h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-1"></i>{{ $collector->email }}
                            @if($collector->phone)
                                <span class="ms-3"><i class="fas fa-phone me-1"></i>{{ $collector->phone }}</span>
                            @endif
                        </p>
                        <span class="badge {{ $collector->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                            {{ $collector->is_active ? '✅ Actif' : '⏸️ Inactif' }}
                        </span>
                        @if(!empty($collector->assigned_zones))
                            <div class="mt-2">
                                @foreach($collector->assigned_zones as $zone)
                                    <span class="badge bg-info me-1">{{ $zone }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="text-end text-muted small">
                        <div>Dernière sync : {{ $collector->last_sync ? $collector->last_sync->diffForHumans() : 'Jamais' }}</div>
                        <div>Inscrit le : {{ $collector->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><p class="text-muted small mb-1">Total</p><h3 class="mb-0">{{ $stats['total_collections'] }}</h3></div>
                            <div class="stat-icon" style="background:#0078d4"><i class="fas fa-database"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><p class="text-muted small mb-1">Synchronisées</p><h3 class="mb-0 text-success">{{ $stats['synced'] }}</h3></div>
                            <div class="stat-icon" style="background:#00a86b"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><p class="text-muted small mb-1">En attente</p><h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3></div>
                            <div class="stat-icon" style="background:#f59e0b"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div><p class="text-muted small mb-1">Échouées</p><h3 class="mb-0 text-danger">{{ $stats['failed'] }}</h3></div>
                            <div class="stat-icon" style="background:#ef4444"><i class="fas fa-times-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Historique des collectes --}}
        <div class="card card-stat">
            <div class="card-header bg-white border-0 pt-4 pb-2">
                <h6 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Historique des collectes</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Date</th>
                                <th>Marché</th>
                                <th>Produit</th>
                                <th>Prix prod.</th>
                                <th>Prix détail</th>
                                <th>½ Gros</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $c)
                            <tr>
                                <td class="px-4 text-nowrap">{{ $c->collection_date ? $c->collection_date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $c->market->name ?? $c->market_id }}</td>
                                <td>{{ $c->product->name ?? $c->product_id }}</td>
                                <td>{{ $c->price ? number_format($c->price, 0, ',', ' ') . ' F' : '-' }}</td>
                                <td>{{ $c->retail_price ? number_format($c->retail_price, 0, ',', ' ') . ' F' : '-' }}</td>
                                <td>{{ $c->wholesale_price ? number_format($c->wholesale_price, 0, ',', ' ') . ' F' : '-' }}</td>
                                <td>
                                    @if($c->sync_status === 'synced')
                                        <span class="badge bg-success">Synchronisé</span>
                                    @elseif($c->sync_status === 'pending')
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @else
                                        <span class="badge bg-danger">Échec</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucune collecte enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $collections->links() }}</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
