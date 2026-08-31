@extends('layouts.admin')

@section('title', 'Modifier événement')
@section('page-title', 'Modifier: ' . $event->name)

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📅 Modifier l'événement</h1>
                    <a href="{{ route('admin.distribution.events.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.distribution.events.update', $event->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nom *</label>
                            <input type="text" class="form-control form-control-sm" name="name" value="{{ old('name', $event->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Lieu</label>
                            <input type="text" class="form-control form-control-sm" name="location" value="{{ old('location', $event->location) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Stock initial (kg) *</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="initial_stock_kg" value="{{ old('initial_stock_kg', $event->initial_stock_kg) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Date de début *</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="start_date" value="{{ old('start_date', $event->start_date?->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Date de fin</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="end_date" value="{{ old('end_date', $event->end_date?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea class="form-control form-control-sm" name="description" rows="3">{{ old('description', $event->description) }}</textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.distribution.events.show', $event->id) }}" class="btn btn-outline-secondary btn-sm">Annuler</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
