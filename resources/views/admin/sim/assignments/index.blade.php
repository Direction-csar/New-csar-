@extends('layouts.admin')

@section('title', 'SIM — Assignations')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tag me-2"></i>Assignations collecteur / superviseur</h1>
        <a href="{{ route('admin.sim.dashboard') }}" class="btn btn-outline-secondary btn-sm me-2">Tableau de bord</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddAssignment">
            <i class="fas fa-plus me-1"></i> Nouvelle assignation
        </button>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-3">
                    <select name="market_id" class="form-select">
                        <option value="">Tous les marchés</option>
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ request('market_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="collector_id" class="form-select">
                        <option value="">Tous les collecteurs</option>
                        @foreach($collectors as $u)
                            <option value="{{ $u->id }}" {{ request('collector_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="active" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Marché</th>
                        <th>Collecteur (agent)</th>
                        <th>Superviseur</th>
                        <th>Début / Fin</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                        <tr>
                            <td>{{ $a->market?->name }} <small class="text-muted">({{ $a->market?->department?->region?->name }})</small></td>
                            <td>{{ $a->collector?->name ?? '-' }}</td>
                            <td>{{ $a->supervisor?->name ?? '-' }}</td>
                            <td>{{ $a->starts_at?->format('d/m/Y') ?? '-' }} — {{ $a->ends_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($a->is_active)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.sim.assignments.update', $a) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_active" value="{{ $a->is_active ? '0' : '1' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $a->is_active ? 'warning' : 'success' }}">{{ $a->is_active ? 'Désactiver' : 'Activer' }}</button>
                                </form>
                                <form action="{{ route('admin.sim.assignments.destroy', $a) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer cette assignation ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune assignation. Créez des marchés et assignez des agents (rôle agent) et optionnellement des superviseurs (rôle responsable).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
            <div class="card-footer">{{ $assignments->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalAddAssignment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sim.assignments.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle assignation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Marché *</label>
                        <select name="sim_market_id" class="form-select" required>
                            <option value="">Choisir un marché</option>
                            @foreach($markets as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->department?->region?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Collecteur (agent) *</label>
                        <select name="collector_id" class="form-select" required>
                            <option value="">Choisir un agent</option>
                            @foreach($collectors as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Superviseur (optionnel)</label>
                        <select name="supervisor_id" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($supervisors as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Début</label>
                            <input type="date" name="starts_at" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fin</label>
                            <input type="date" name="ends_at" class="form-control">
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="assign_active" checked>
                        <label class="form-check-label" for="assign_active">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
