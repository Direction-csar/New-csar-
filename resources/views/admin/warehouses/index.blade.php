@extends('layouts.admin')

@section('title', 'Magasins')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-warehouse me-2"></i>Magasins du CSAR</h1>
                <p class="text-muted mb-0">Seuls les magasins réels doivent figurer ici. La suppression est bloquée dès qu'un magasin a un historique.</p>
            </div>
            <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary-modern btn-modern">
                <i class="fas fa-plus me-2"></i>Nouveau magasin
            </a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card-modern">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher (nom, région, ville)…">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" @selected(request('statut')==='actif')>Actifs</option>
                        <option value="inactif" @selected(request('statut')==='inactif')>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrer</button></div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Magasin</th><th>Région / Ville</th><th>Responsables</th>
                            <th class="text-end">Mouvements</th><th class="text-end">En attente</th>
                            <th>Statut</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($warehouses as $w)
                        <tr>
                            <td class="fw-semibold">{{ $w->name }}</td>
                            <td>{{ $w->region }}{{ $w->city ? ' / '.$w->city : '' }}</td>
                            <td>
                                @foreach($w->users->take(2) as $u)
                                    <span class="badge bg-light text-dark border">{{ $u->name }} ({{ $u->role }})</span>
                                @endforeach
                                @if($w->users->count() > 2)<span class="text-muted small">+{{ $w->users->count()-2 }}</span>@endif
                                @if($w->users->isEmpty())<span class="text-muted">—</span>@endif
                            </td>
                            <td class="text-end">{{ ($movements[$w->id] ?? collect())->sum('n') }}</td>
                            <td class="text-end">{{ ($movements[$w->id] ?? collect())->where('status','en_attente')->sum('n') }}</td>
                            <td>
                                @if($w->is_active && $w->status === 'active')
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.warehouses.show', $w->id) }}" class="btn btn-sm btn-outline-primary" title="Détail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.warehouses.edit', $w->id) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('admin.warehouses.destroy', $w->id) }}" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce magasin ? (Refusé s\'il a un historique.)')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucun magasin enregistré.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($warehouses->hasPages())<div class="mt-3">{{ $warehouses->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
