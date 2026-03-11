@extends('layouts.admin')

@section('title', 'SIM — Modifier le marché')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier : {{ $simMarket->name }}</h1>
        <a href="{{ route('admin.sim.markets') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.sim.markets.update', $simMarket) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Département *</label>
                        <select name="sim_department_id" class="form-select" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ $simMarket->sim_department_id == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $simMarket->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Commune</label>
                        <input type="text" name="commune" class="form-control" value="{{ old('commune', $simMarket->commune) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type de marché *</label>
                        <select name="market_type" class="form-select" required>
                            @foreach(['rural_collecte','rural_consommation','urbain','frontalier','regroupement','transfrontalier'] as $t)
                                <option value="{{ $t }}" {{ $simMarket->market_type === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jour du marché</label>
                        <input type="text" name="market_day" class="form-control" value="{{ old('market_day', $simMarket->market_day) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude', $simMarket->latitude) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude', $simMarket->longitude) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $simMarket->notes) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_permanent" value="1" class="form-check-input" id="edit_permanent" {{ $simMarket->is_permanent ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit_permanent">Permanent</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_active" {{ $simMarket->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit_active">Actif</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('admin.sim.markets') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
