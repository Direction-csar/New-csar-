@extends('layouts.admin')

@section('title', 'SIM — Catégories de produits')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-tags me-2"></i>Catégories SIM</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
            <i class="fas fa-plus me-1"></i> Nouvelle catégorie
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
                        <th>Produits</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>{{ $cat->display_order }}</td>
                            <td>{{ $cat->name }}</td>
                            <td>{{ $cat->products_count ?? 0 }}</td>
                            <td>@if($cat->is_active)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif</td>
                            <td>
                                <a href="{{ route('admin.sim.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                @if(!($cat->products_count ?? 0))
                                    <form action="{{ route('admin.sim.categories.destroy', $cat) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucune catégorie.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="card-footer">{{ $categories->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalAddCategory" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sim.categories.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="cat_active" checked>
                        <label class="form-check-label" for="cat_active">Actif</label>
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
