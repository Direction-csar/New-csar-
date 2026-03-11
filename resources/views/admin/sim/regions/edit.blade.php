@extends('layouts.admin')

@section('title', 'SIM — Modifier la région')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier : {{ $simRegion->name }}</h1>
        <a href="{{ route('admin.sim.regions') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.sim.regions.update', $simRegion) }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-2">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $simRegion->name) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $simRegion->code) }}" maxlength="20">
                </div>
                <div class="mb-2">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $simRegion->display_order) }}">
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_region_active" {{ $simRegion->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_region_active">Actif</label>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('admin.sim.regions') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection
