@extends('layouts.admin')

@section('title', $warehouse->exists ? 'Modifier le magasin' : 'Nouveau magasin')

@section('content')
<div class="container-fluid" style="max-width: 860px;">
    <div class="card-modern">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-warehouse me-2"></i>{{ $warehouse->exists ? 'Modifier : '.$warehouse->name : 'Nouveau magasin' }}
        </div>
        <div class="card-body">
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ $warehouse->exists ? route('admin.warehouses.update', $warehouse->id) : route('admin.warehouses.store') }}">
                @csrf
                @if($warehouse->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nom du magasin *</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $warehouse->name) }}" placeholder="Ex : Magasin Central Dakar, Complexe céréalier de Thiès">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $warehouse->exists ? $warehouse->is_active : true))>
                            <label class="form-check-label" for="is_active">Magasin actif</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Région</label>
                        <input type="text" name="region" class="form-control" value="{{ old('region', $warehouse->region) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ville / commune</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $warehouse->city) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $warehouse->address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $warehouse->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $warehouse->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Capacité (contenants)</label>
                        <input type="number" name="capacity" class="form-control" step="0.01" min="0" value="{{ old('capacity', $warehouse->capacity) }}">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary-modern btn-modern"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
