@extends('layouts.admin')

@section('title', 'SIM — Modifier la catégorie')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier : {{ $simProductCategory->name }}</h1>
        <a href="{{ route('admin.sim.categories') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.sim.categories.update', $simProductCategory) }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-2">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $simProductCategory->name) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $simProductCategory->description) }}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $simProductCategory->display_order) }}">
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_cat_active" {{ $simProductCategory->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_cat_active">Actif</label>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('admin.sim.categories') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection
