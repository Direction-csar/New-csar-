@extends('layouts.admin')

@section('title', 'Modifier planning')
@section('page-title', 'Modifier: ' . $planning->name)

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📋 Modifier le planning</h1>
                    <a href="{{ route('admin.distribution.plannings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.distribution.plannings.update', $planning->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Événement *</label>
                            <select class="form-select form-select-sm" name="event_id" required>
                                @foreach($events as $id => $name)
                                <option value="{{ $id }}" {{ old('event_id', $planning->event_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nom *</label>
                            <input type="text" class="form-control form-control-sm" name="name" value="{{ old('name', $planning->name) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Quota (kg) *</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="planned_quota_kg" value="{{ old('planned_quota_kg', $planning->planned_quota_kg) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Bénéficiaires attendus</label>
                            <input type="number" class="form-control form-control-sm" name="expected_beneficiaries" value="{{ old('expected_beneficiaries', $planning->expected_beneficiaries) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Date de distribution</label>
                            <input type="date" class="form-control form-control-sm" name="distribution_date" value="{{ old('distribution_date', $planning->distribution_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Lieu</label>
                            <input type="text" class="form-control form-control-sm" name="location" value="{{ old('location', $planning->location) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Distributeur</label>
                            <select class="form-select form-select-sm" name="assigned_to">
                                <option value="">Non assigné</option>
                                @foreach($distributors as $id => $name)
                                <option value="{{ $id }}" {{ old('assigned_to', $planning->assigned_to) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Statut *</label>
                            <select class="form-select form-select-sm" name="status" required>
                                @foreach(['draft' => 'Brouillon', 'active' => 'Actif', 'completed' => 'Terminé', 'cancelled' => 'Annulé'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $planning->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea class="form-control form-control-sm" name="description" rows="2">{{ old('description', $planning->description) }}</textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.distribution.plannings.show', $planning->id) }}" class="btn btn-outline-secondary btn-sm">Annuler</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
