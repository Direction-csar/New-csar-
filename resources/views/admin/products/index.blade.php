@extends('layouts.admin')

@section('title', 'Produits')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-seedling me-2"></i>Produits
            </h1>
            <p class="text-muted mb-0">Produits gérés dans les magasins (photos affichées dans l'application magasinier)</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau produit
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher un produit...">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-control">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-secondary w-100"><i class="fas fa-search me-2"></i>Filtrer</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse($products as $product)
            <div class="col-sm-6 col-md-4 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div style="height: 160px; background: #f4f6f4; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: .375rem .375rem 0 0;">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-image fa-3x mb-2"></i>
                                <div class="small">Pas de photo</div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1">{{ $product->name }}</h5>
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $product->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        <p class="text-muted small mb-0">{{ $product->category ?? $product->type }} · {{ $product->unit }}</p>
                    </div>
                    <div class="card-footer bg-white d-flex gap-2">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Supprimer ce produit ?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-seedling fa-3x mb-3"></i>
                        <p class="mb-0">Aucun produit trouvé.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
