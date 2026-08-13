@extends('layouts.admin')

@section('title', isset($campaign) ? 'Modifier la campagne' : 'Nouvelle campagne')
@section('page-title', isset($campaign) ? 'Modifier la campagne' : 'Nouvelle campagne')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ isset($campaign) ? route('admin.distribution.campaigns.update', $campaign) : route('admin.distribution.campaigns.store') }}">
                        @csrf
                        @isset($campaign) @method('PUT') @endisset

                        <div class="mb-3">
                            <label class="form-label">Nom de la campagne</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $campaign->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $campaign->description ?? '') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de début</label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', isset($campaign) ? $campaign->start_date?->format('Y-m-d') : '') }}" required>
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de fin</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', isset($campaign) ? $campaign->end_date?->format('Y-m-d') : '') }}">
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock initial (kg)</label>
                                <input type="number" step="0.01" name="initial_stock_kg" class="form-control @error('initial_stock_kg') is-invalid @enderror" value="{{ old('initial_stock_kg', $campaign->initial_stock_kg ?? 0) }}" required>
                                @error('initial_stock_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $campaign->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="closed" {{ old('status', $campaign->status ?? '') === 'closed' ? 'selected' : '' }}>Clôturée</option>
                                    <option value="archived" {{ old('status', $campaign->status ?? '') === 'archived' ? 'selected' : '' }}>Archivée</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.distribution.campaigns.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($campaign) ? 'Mettre à jour' : 'Créer la campagne' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
