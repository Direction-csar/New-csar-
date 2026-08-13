@extends('layouts.admin')

@section('title', isset($planning) ? 'Modifier le planning' : 'Nouveau planning')
@section('page-title', isset($planning) ? 'Modifier le planning' : 'Nouveau planning')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ isset($planning) ? route('admin.distribution.plannings.update', $planning) : route('admin.distribution.plannings.store') }}">
                        @csrf
                        @isset($planning) @method('PUT') @endisset

                        <div class="mb-3">
                            <label class="form-label">Campagne</label>
                            <select name="campaign_id" class="form-select @error('campaign_id') is-invalid @enderror" required>
                                <option value="">— Choisir —</option>
                                @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ old('campaign_id', $planning->campaign_id ?? '') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                            @error('campaign_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nom du planning</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $planning->name ?? '') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Catégorie</label>
                                <input type="text" name="category" list="categories" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $planning->category ?? '') }}" required>
                                <datalist id="categories">
                                    <option value="Instructions DGCSAR">
                                    <option value="Vulnérables">
                                    <option value="Religieux">
                                    <option value="Spontanées">
                                    <option value="OAL">
                                </datalist>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quota planifié (kg)</label>
                                <input type="number" step="0.01" name="planned_quota_kg" class="form-control @error('planned_quota_kg') is-invalid @enderror" value="{{ old('planned_quota_kg', $planning->planned_quota_kg ?? 0) }}" required>
                                @error('planned_quota_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Seuil d'alerte (kg)</label>
                                <input type="number" step="0.01" name="alert_threshold_kg" class="form-control @error('alert_threshold_kg') is-invalid @enderror" value="{{ old('alert_threshold_kg', $planning->alert_threshold_kg ?? 0) }}" required>
                                @error('alert_threshold_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Entrepôt</label>
                                <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror">
                                    <option value="">— Aucun —</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $planning->warehouse_id ?? '') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $planning->status ?? 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="full" {{ old('status', $planning->status ?? '') === 'full' ? 'selected' : '' }}>Complet</option>
                                <option value="closed" {{ old('status', $planning->status ?? '') === 'closed' ? 'selected' : '' }}>Clôturé</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.distribution.plannings.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($planning) ? 'Mettre à jour' : 'Créer le planning' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
