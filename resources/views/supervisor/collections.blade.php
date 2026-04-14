<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les collectes | CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .nav-supervisor { background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-supervisor py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand text-white fw-bold" href="{{ route('supervisor.dashboard') }}">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
            <span class="text-white small">
                Actualisation auto <span id="auto-refresh-badge" class="badge bg-success ms-1">ON</span>
            </span>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Toutes les collectes</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-primary px-3 py-2">{{ $collections->total() }} collectes</span>
                <button class="btn btn-sm btn-outline-success" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                </button>
            </div>
        </div>

        <div class="card card-stat">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Date</th>
                                <th>Collecteur</th>
                                <th>Marché</th>
                                <th>Produit</th>
                                <th>Prix prod.</th>
                                <th>Prix détail</th>
                                <th>½ Gros</th>
                                <th>Provenance</th>
                                <th>GPS</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $c)
                            <tr>
                                <td class="px-4 text-nowrap small">
                                    {{ $c->collection_date ? $c->collection_date->format('d/m/Y') : '-' }}
                                    <div class="text-muted" style="font-size:0.7rem;">{{ $c->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('supervisor.collector.details', $c->collector_id) }}" class="text-decoration-none fw-semibold">
                                        {{ $c->collector->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="small">{{ $c->market->name ?? $c->market_id }}</td>
                                <td class="small">{{ $c->product->name ?? $c->product_id }}</td>
                                <td class="text-nowrap">{{ $c->price ? number_format($c->price, 0, ',', ' ') . ' F' : '-' }}</td>
                                <td class="text-nowrap">{{ $c->retail_price ? number_format($c->retail_price, 0, ',', ' ') . ' F' : '-' }}</td>
                                <td class="text-nowrap">{{ $c->wholesale_price ? number_format($c->wholesale_price, 0, ',', ' ') . ' F' : '-' }}</td>
                                <td class="small text-muted">{{ $c->provenance ?? '-' }}</td>
                                <td>
                                    @if($c->latitude && $c->longitude)
                                        <a href="https://www.openstreetmap.org/?mlat={{ $c->latitude }}&mlon={{ $c->longitude }}&zoom=15" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:0.7rem;">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
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
                            <tr><td colspan="10" class="text-center text-muted py-5">Aucune collecte enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">Page {{ $collections->currentPage() }} / {{ $collections->lastPage() }}</div>
                    {{ $collections->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh toutes les 60 secondes
        setTimeout(function() { location.reload(); }, 60000);
    </script>
</body>
</html>
