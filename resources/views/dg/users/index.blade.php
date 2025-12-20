@extends('layouts.dg-modern')

@section('title', 'Gestion des Utilisateurs')
@section('page-title', 'Gestion des Utilisateurs - Vue DG')

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
                            Gestion des Utilisateurs
                        </h1>
                        <p class="text-muted mb-0 small">
                            Consultation des utilisateurs du système - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-modern btn-sm" onclick="refreshUsers()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-2" style="width: 50px; height: 50px; background: var(--gradient-primary);">
                    <i class="fas fa-users" style="font-size: 20px;"></i>
                </div>
                <h4 class="mb-1 text-dark">{{ $stats['total'] ?? 0 }}</h4>
                <small class="text-muted">Total Utilisateurs</small>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-2" style="width: 50px; height: 50px; background: var(--gradient-success);">
                    <i class="fas fa-user-check" style="font-size: 20px;"></i>
                </div>
                <h4 class="mb-1 text-dark">{{ $stats['active'] ?? 0 }}</h4>
                <small class="text-muted">Utilisateurs Actifs</small>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-2" style="width: 50px; height: 50px; background: var(--gradient-info);">
                    <i class="fas fa-user-shield" style="font-size: 20px;"></i>
                </div>
                <h4 class="mb-1 text-dark">{{ $stats['admins'] ?? 0 }}</h4>
                <small class="text-muted">Administrateurs</small>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 text-center">
                <div class="icon-3d mx-auto mb-2" style="width: 50px; height: 50px; background: var(--gradient-warning);">
                    <i class="fas fa-user-clock" style="font-size: 20px;"></i>
                </div>
                <h4 class="mb-1 text-dark">{{ $stats['inactive'] ?? 0 }}</h4>
                <small class="text-muted">Utilisateurs Inactifs</small>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold">Rechercher</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Nom, email, rôle...">
                    </div>
                    
                    <div class="col-md-3 mb-2">
                        <label class="form-label fw-bold">Rôle</label>
                        <select class="form-select" id="roleFilter">
                            <option value="">Tous les rôles</option>
                            <option value="admin">Administrateur</option>
                            <option value="dg">Directeur Général</option>
                            <option value="responsable">Responsable</option>
                            <option value="agent">Agent</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-2">
                        <label class="form-label fw-bold">Statut</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-2">
                        <label class="form-label fw-bold">&nbsp;</label>
                        <button class="btn btn-primary-modern btn-modern w-100" onclick="applyFilters()">
                            <i class="fas fa-search me-1"></i>Filtrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Liste des Utilisateurs
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">{{ $users->count() }} utilisateur(s)</span>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Rôle</th>
                                <th>Département</th>
                                <th>Dernière Connexion</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                            <i class="fas fa-user" style="font-size: 14px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $user->email ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->role == 'dg')
                                        <span class="badge bg-primary">DG</span>
                                    @elseif($user->role == 'responsable')
                                        <span class="badge bg-warning">Responsable</span>
                                    @elseif($user->role == 'agent')
                                        <span class="badge bg-info">Agent</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $user->role ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $user->department ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $user->position ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($user->last_login_at)
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Jamais</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('dg.users.show', $user->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function refreshUsers() {
    location.reload();
}

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    // Construire l'URL avec les paramètres
    let url = new URL(window.location);
    url.searchParams.set('search', search);
    url.searchParams.set('role', role);
    url.searchParams.set('status', status);
    
    // Rediriger vers la nouvelle URL
    window.location.href = url.toString();
}

// Auto-filtrer lors de la saisie
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});
</script>
@endsection



































