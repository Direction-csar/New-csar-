@extends('layouts.admin')

@section('title', 'SIM — Régions')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-map-marked-alt me-2"></i>Régions SIM</h1>
        <a href="{{ route('admin.sim.dashboard') }}" class="btn btn-outline-secondary btn-sm me-2">Tableau de bord</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRegion">
            <i class="fas fa-plus me-1"></i> Nouvelle région
        </button>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" style="max-width:300px" placeholder="Recherche..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary">Filtrer</button>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ordre</th>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Départements</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $r)
                        <tr>
                            <td>{{ $r->display_order }}</td>
                            <td>{{ $r->name }}</td>
                            <td>{{ $r->code ?? '-' }}</td>
                            <td>{{ $r->departments_count ?? 0 }}</td>
                            <td>@if($r->is_active)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif</td>
                            <td>
                                <a href="{{ route('admin.sim.regions.edit', $r) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                @if(!($r->departments_count ?? 0))
                                    <form action="{{ route('admin.sim.regions.destroy', $r) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer cette région ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune région. Lancez le seeder : php artisan db:seed --class=SimGeographySeeder</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($regions->hasPages())
            <div class="card-footer">{{ $regions->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalAddRegion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sim.regions.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle région</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" maxlength="20" placeholder="ex: DK">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="region_active" checked>
                        <label class="form-check-label" for="region_active">Actif</label>
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
