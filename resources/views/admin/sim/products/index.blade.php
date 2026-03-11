@extends('layouts.admin')

@section('title', 'SIM — Produits')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-box me-2"></i>Produits SIM</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddProduct">
            <i class="fas fa-plus me-1"></i> Nouveau produit
        </button>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Recherche..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
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
                        <th>Ordre</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Unité</th>
                        <th>Origine</th>
                        <th>Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td>{{ $p->display_order }}</td>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->category?->name }}</td>
                            <td>{{ $p->unit }}</td>
                            <td>{{ $p->origin_type }}</td>
                            <td>@if($p->is_active)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif</td>
                            <td>
                                <a href="{{ route('admin.sim.products.edit', $p) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <form action="{{ route('admin.sim.products.destroy', $p) }}" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Aucun produit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer">{{ $products->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalAddProduct" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sim.products.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Catégorie *</label>
                        <select name="sim_product_category_id" class="form-select" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Unité *</label>
                        <input type="text" name="unit" class="form-control" placeholder="kg, L..." required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Origine *</label>
                        <select name="origin_type" class="form-select" required>
                            <option value="local">Local</option>
                            <option value="imported">Importé</option>
                            <option value="both">Les deux</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="display_order" class="form-control" value="0">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="prod_active" checked>
                        <label class="form-check-label" for="prod_active">Actif</label>
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
