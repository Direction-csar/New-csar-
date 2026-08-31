@extends('layouts.admin')

@section('title', 'Nouvel événement')
@section('page-title', 'Créer un événement de distribution')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📅 Nouvel événement</h1>
                    <a href="{{ route('admin.distribution.events.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.distribution.events.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nom de l'événement *</label>
                            <input type="text" class="form-control form-control-sm" name="name" value="{{ old('name') }}" required placeholder="Ex: Grand Magal de Touba 2026">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Lieu</label>
                            <input type="text" class="form-control form-control-sm" name="location" value="{{ old('location') }}" placeholder="Ex: Touba">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Stock initial (kg) *</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="initial_stock_kg" value="{{ old('initial_stock_kg', 0) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Date de début *</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="start_date" value="{{ old('start_date') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Date de fin</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="end_date" value="{{ old('end_date') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea class="form-control form-control-sm" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.distribution.events.index') }}" class="btn btn-outline-secondary btn-sm">Annuler</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Créer l'événement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
