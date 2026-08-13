@extends('layouts.admin')

@section('title', isset($beneficiaire) ? 'Modifier le bénéficiaire' : 'Nouveau bénéficiaire')
@section('page-title', isset($beneficiaire) ? 'Modifier le bénéficiaire' : 'Nouveau bénéficiaire')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ isset($beneficiaire) ? route('admin.distribution.beneficiaires.update', $beneficiaire) : route('admin.distribution.beneficiaires.store') }}">
                        @csrf
                        @isset($beneficiaire) @method('PUT') @endisset

                        <div class="mb-3">
                            <label class="form-label">Planning</label>
                            <select name="planning_id" class="form-select @error('planning_id') is-invalid @enderror" required>
                                <option value="">— Choisir —</option>
                                @foreach($plannings as $planning)
                                <option value="{{ $planning->id }}" {{ old('planning_id', $beneficiaire->planning_id ?? '') == $planning->id ? 'selected' : '' }}>
                                    {{ $planning->campaign?->name }} – {{ $planning->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('planning_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $beneficiaire->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $beneficiaire->phone ?? '') }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CNI</label>
                                <input type="text" name="cni" class="form-control @error('cni') is-invalid @enderror" value="{{ old('cni', $beneficiaire->cni ?? '') }}">
                                @error('cni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $beneficiaire->address ?? '') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie</label>
                                <input type="text" name="category" list="categories" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $beneficiaire->category ?? '') }}" required>
                                <datalist id="categories">
                                    <option value="Vulnérable">
                                    <option value="Religieux">
                                    <option value="Instruction">
                                    <option value="OAL">
                                    <option value="Spontané">
                                </datalist>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantité (kg)</label>
                                <input type="number" step="0.01" name="quantite_kg" class="form-control @error('quantite_kg') is-invalid @enderror" value="{{ old('quantite_kg', $beneficiaire->bonMatieres->first()?->quantite_kg ?? '') }}" {{ isset($beneficiaire) ? 'disabled' : 'required' }}>
                                @isset($beneficiaire)<input type="hidden" name="quantite_kg" value="{{ $beneficiaire->bonMatieres->first()?->quantite_kg ?? 0 }}">@endisset
                                @error('quantite_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="vulnerable" value="1" id="vulnerable" {{ old('vulnerable', $beneficiaire->vulnerable ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="vulnerable">Vulnérable</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="religious" value="1" id="religious" {{ old('religious', $beneficiaire->religious ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="religious">Religieux</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="spontaneous" value="1" id="spontaneous" {{ old('spontaneous', $beneficiaire->spontaneous ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="spontaneous">Spontané</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $beneficiaire->status ?? 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="blocked" {{ old('status', $beneficiaire->status ?? '') === 'blocked' ? 'selected' : '' }}>Bloqué</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.distribution.beneficiaires.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($beneficiaire) ? 'Mettre à jour' : 'Créer le bénéficiaire' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
