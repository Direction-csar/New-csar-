@extends('layouts.admin')

@section('title', 'SIM — Modifier le produit')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier : {{ $simProduct->name }}</h1>
        <a href="{{ route('admin.sim.products') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.sim.products.update', $simProduct) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Catégorie *</label>
                        <select name="sim_product_category_id" class="form-select" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ $simProduct->sim_product_category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $simProduct->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unité *</label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', $simProduct->unit) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Origine *</label>
                        <select name="origin_type" class="form-select" required>
                            <option value="local" {{ $simProduct->origin_type === 'local' ? 'selected' : '' }}>Local</option>
                            <option value="imported" {{ $simProduct->origin_type === 'imported' ? 'selected' : '' }}>Importé</option>
                            <option value="both" {{ $simProduct->origin_type === 'both' ? 'selected' : '' }}>Les deux</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $simProduct->display_order) }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_prod_active" {{ $simProduct->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit_prod_active">Actif</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('admin.sim.products') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
