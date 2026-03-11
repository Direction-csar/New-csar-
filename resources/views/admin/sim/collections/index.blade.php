@extends('layouts.admin')

@section('title', 'SIM — Collectes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-clipboard-list me-2"></i>Collectes SIM</h1>
        <a href="{{ route('admin.sim.dashboard') }}" class="btn btn-outline-secondary">Tableau de bord SIM</a>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Soumis</option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Valide</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejete</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publie</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="market_id" class="form-select">
                        <option value="">Tous les marches</option>
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" {{ request('market_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
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
                        <th>Date</th>
                        <th>Marche</th>
                        <th>Collecteur</th>
                        <th>Superviseur</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $c)
                        <tr>
                            <td>{{ $c->collected_on?->format('d/m/Y') }}</td>
                            <td>{{ $c->market?->name }}</td>
                            <td>{{ $c->collector?->name ?? '-' }}</td>
                            <td>{{ $c->supervisor?->name ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $c->status }}</span></td>
                            <td>
                                <a href="{{ route('admin.sim.collections.show', $c) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune collecte.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($collections->hasPages())
            <div class="card-footer">{{ $collections->links() }}</div>
        @endif
    </div>
</div>
@endsection
