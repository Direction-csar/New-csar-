@extends('layouts.admin')

@section('title', 'SIM — Marchés')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-store me-2"></i>Marchés SIM</h1>
        <a href="{{ route('admin.sim.markets') }}?add=1" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouveau marché</a>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Recherche..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="department_id" class="form-select">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
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
                        <th>Département / Région</th>
                        <th>Type</th>
                        <th>Jour / Permanent</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($markets as $m)
                        <tr>
                            <td>{{ $m->name }}</td>
                            <td>{{ $m->department?->name }} / {{ $m->department?->region?->name }}</td>
                            <td>{{ $m->market_type }}</td>
                            <td>{{ $m->is_permanent ? 'Permanent' : ($m->market_day ?? '-') }}</td>
                            <td>@if($m->is_active)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif</td>
                            <td>
                                <a href="{{ route('admin.sim.markets.edit', $m) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <form action="{{ route('admin.sim.markets.destroy', $m) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce marché ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun marché.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($markets->hasPages())
            <div class="card-footer">{{ $markets->links() }}</div>
        @endif
    </div>

    @if(request('add'))
    <div class="card shadow mt-3">
        <div class="card-header">Nouveau marché</div>
        <div class="card-body">
            <form action="{{ route('admin.sim.markets.store') }}" method="post">
                @csrf
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Département *</label>
                        <select name="sim_department_id" class="form-select" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Commune</label>
                        <input type="text" name="commune" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type *</label>
                        <select name="market_type" class="form-select" required>
                            <option value="rural_collecte">Rural collecte</option>
                            <option value="urbain">Urbain</option>
                            <option value="frontalier">Frontalier</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jour du marché</label>
                        <input type="text" name="market_day" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_permanent" value="1" class="form-check-input" id="add_permanent">
                            <label class="form-check-label" for="add_permanent">Permanent</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="add_active" checked>
                            <label class="form-check-label" for="add_active">Actif</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Enregistrer</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
