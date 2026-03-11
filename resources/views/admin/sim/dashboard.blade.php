@extends('layouts.admin')

@section('title', 'SIM — Tableau de bord')

@section('content')
<div class="container-fluid">
    <p class="text-muted small mb-2">Module <strong>SIM</strong> (Régions, Départements, Marchés, Assignations, Collectes). Menu latéral : <strong>SIM (Régions, Marchés, Assignations)</strong>.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if((isset($regionsCount) && $regionsCount === 0) || (isset($departmentsCount) && $departmentsCount === 0))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Données géographiques manquantes.</strong>
            Pour ajouter des marchés, créez d’abord des régions et départements, ou lancez le seeder :
            <code class="d-block mt-2">php artisan db:seed --class=SimGeographySeeder</code>
            <a href="{{ route('admin.sim.regions') }}" class="alert-link">Régions</a> ·
            <a href="{{ route('admin.sim.departments') }}" class="alert-link">Départements</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h1 class="h3 mb-3 text-gray-800">
        <i class="fas fa-chart-line text-primary me-2"></i>Système d'Information sur les Marchés (SIM)
    </h1>

    {{-- Accès rapide — tout ce qu'on a ajouté (bien visible) --}}
    <div class="card shadow mb-4 border-primary">
        <div class="card-header bg-primary text-white py-2">
            <strong><i class="fas fa-th-list me-2"></i>Accès rapide SIM</strong>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <a href="{{ route('admin.sim.regions') }}" class="btn btn-outline-primary">
                        <i class="fas fa-map-marked-alt me-1"></i> Régions
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.departments') }}" class="btn btn-outline-primary">
                        <i class="fas fa-map me-1"></i> Départements
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.markets') }}" class="btn btn-primary">
                        <i class="fas fa-store me-1"></i> Marchés
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.assignments') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-tag me-1"></i> Assignations (collecteur / superviseur)
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.categories') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-tags me-1"></i> Catégories produits
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.products') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-box me-1"></i> Produits
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.collections') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-clipboard-list me-1"></i> Collectes
                    </a>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.sim.access-requests') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-key me-1"></i> Demandes d'accès
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes synthèse --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Marchés actifs</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overview['markets_count'] ?? 0 }}</div>
                    <a href="{{ route('admin.sim.markets') }}" class="small text-primary">Gérer</a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Produits suivis</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overview['products_count'] ?? 0 }}</div>
                    <a href="{{ route('admin.sim.products') }}" class="small text-success">Gérer</a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Collectes en attente</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overview['pending_collections'] ?? 0 }}</div>
                    <a href="{{ route('admin.sim.collections', ['status' => 'submitted']) }}" class="small text-warning">Voir</a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Demandes d'accès en attente</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overview['pending_requests'] ?? 0 }}</div>
                    <a href="{{ route('admin.sim.access-requests') }}" class="small text-info">Traiter</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="m-0 font-weight-bold text-primary">Collectes en cours (temps réel)</h6>
                </div>
                <div class="card-body">
                    @forelse($liveCollections as $c)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $c->market?->name ?? 'Marché' }}</strong>
                                <br><small class="text-muted">{{ $c->collector?->name ?? '-' }} · {{ $c->status ?? '-' }}</small>
                            </div>
                            @if($c->last_activity_at)
                                <small class="text-muted">{{ $c->last_activity_at->diffForHumans() }}</small>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune collecte en cours.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="m-0 font-weight-bold text-primary">Statuts récents</h6>
                </div>
                <div class="card-body">
                    @forelse($recentStatuses as $s)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span>{{ $s->label ?? $s->status ?? '-' }}</span>
                            <small class="text-muted">{{ $s->created_at?->diffForHumans() }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun statut récent.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Dernières demandes d'accès aux données</h6>
                <a href="{{ route('admin.sim.access-requests') }}" class="btn btn-sm btn-outline-primary">Toutes</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Demandeur</th>
                                <th>Organisation</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRequests as $req)
                                <tr>
                                    <td>{{ $req->requester_name }}</td>
                                    <td>{{ $req->organization }}</td>
                                    <td>{{ Str::limit($req->request_subject, 30) }}</td>
                                    <td><span class="badge bg-{{ $req->status === 'pending' ? 'warning' : 'secondary' }}">{{ $req->status }}</span></td>
                                    <td>{{ $req->created_at?->format('d/m/Y') }}</td>
                                    <td><a href="{{ route('admin.sim.access-requests.show', $req) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.sim.regions') }}" class="btn btn-outline-secondary"><i class="fas fa-map-marked-alt me-1"></i> Régions</a>
        <a href="{{ route('admin.sim.departments') }}" class="btn btn-outline-secondary"><i class="fas fa-map me-1"></i> Départements</a>
        <a href="{{ route('admin.sim.markets') }}" class="btn btn-primary"><i class="fas fa-store me-1"></i> Marchés</a>
        <a href="{{ route('admin.sim.assignments') }}" class="btn btn-outline-secondary"><i class="fas fa-user-tag me-1"></i> Assignations</a>
        <a href="{{ route('admin.sim.categories') }}" class="btn btn-outline-secondary"><i class="fas fa-tags me-1"></i> Catégories</a>
        <a href="{{ route('admin.sim.products') }}" class="btn btn-outline-secondary"><i class="fas fa-box me-1"></i> Produits</a>
        <a href="{{ route('admin.sim.collections') }}" class="btn btn-outline-secondary"><i class="fas fa-clipboard-list me-1"></i> Collectes</a>
        <a href="{{ route('admin.sim.access-requests') }}" class="btn btn-outline-secondary"><i class="fas fa-key me-1"></i> Demandes d'accès</a>
    </div>
</div>
@endsection
