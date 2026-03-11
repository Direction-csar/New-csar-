@extends('layouts.admin')

@section('title', 'SIM — Départements')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-map me-2"></i>Départements SIM</h1>
        <a href="{{ route('admin.sim.dashboard') }}" class="btn btn-outline-secondary btn-sm me-2">Tableau de bord</a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddDepartment">
            <i class="fas fa-plus me-1"></i> Nouveau département
        </button>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Recherche..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="region_id" class="form-select">
                        <option value="">Toutes les régions</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
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
                        <th>Nom</th>
                        <th>Région</th>
                        <th>Code</th>
                        <th>Marchés</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $d)
                        <tr>
                            <td>{{ $d->name }}</td>
                            <td>{{ $d->region?->name }}</td>
                            <td>{{ $d->code ?? '-' }}</td>
                            <td>{{ $d->markets_count ?? 0 }}</td>
                            <td>@if($d->is_active)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif</td>
                            <td>
                                <a href="{{ route('admin.sim.departments.edit', $d) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                @if(!($d->markets_count ?? 0))
                                    <form action="{{ route('admin.sim.departments.destroy', $d) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce département ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun département. Lancez le seeder : php artisan db:seed --class=SimGeographySeeder</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
            <div class="card-footer">{{ $departments->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalAddDepartment" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sim.departments.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau département</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Région *</label>
                        <select name="sim_region_id" class="form-select" required>
                            @foreach($regions as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" maxlength="20">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="dept_active" checked>
                        <label class="form-check-label" for="dept_active">Actif</label>
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
