@extends('layouts.admin')

@section('title', 'Modifier bénéficiaire')
@section('page-title', 'Modifier: ' . $beneficiary->full_name)

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">👤 Modifier le bénéficiaire</h1>
                    <a href="{{ route('admin.distribution.beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.distribution.beneficiaries.update', $beneficiary->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Planning *</label>
                            <select class="form-select form-select-sm" name="planning_id" required>
                                @foreach($plannings as $id => $name)
                                <option value="{{ $id }}" {{ old('planning_id', $beneficiary->planning_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nom complet *</label>
                            <input type="text" class="form-control form-control-sm" name="full_name" value="{{ old('full_name', $beneficiary->full_name) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Téléphone</label>
                            <input type="tel" class="form-control form-control-sm" name="phone" value="{{ old('phone', $beneficiary->phone) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">CNI</label>
                            <input type="text" class="form-control form-control-sm" name="cni" value="{{ old('cni', $beneficiary->cni) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Catégorie</label>
                            <input type="text" class="form-control form-control-sm" name="category" value="{{ old('category', $beneficiary->category) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Quantité (kg) *</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="quantity_kg" value="{{ old('quantity_kg', $beneficiary->quantity_kg) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Statut *</label>
                            <select class="form-select form-select-sm" name="status" required>
                                @foreach(['pending' => 'En attente', 'validated' => 'Validé', 'ticket_issued' => 'Ticket émis', 'kit_collected' => 'Kit récupéré'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $beneficiary->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Adresse</label>
                            <input type="text" class="form-control form-control-sm" name="address" value="{{ old('address', $beneficiary->address) }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_vulnerable" value="1" {{ old('is_vulnerable', $beneficiary->is_vulnerable) ? 'checked' : '' }}><label class="form-check-label small">Vulnérable</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_pregnant" value="1" {{ old('is_pregnant', $beneficiary->is_pregnant) ? 'checked' : '' }}><label class="form-check-label small">Enceinte</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_elderly" value="1" {{ old('is_elderly', $beneficiary->is_elderly) ? 'checked' : '' }}><label class="form-check-label small">Personne âgée</label></div></div>
                        <div class="col-md-3"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_disabled" value="1" {{ old('is_disabled', $beneficiary->is_disabled) ? 'checked' : '' }}><label class="form-check-label small">Handicap</label></div></div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.distribution.beneficiaries.show', $beneficiary->id) }}" class="btn btn-outline-secondary btn-sm">Annuler</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
