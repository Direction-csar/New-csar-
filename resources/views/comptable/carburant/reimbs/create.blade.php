@extends('layouts.direction-portal')

@section('title', 'Nouvelle demande de remboursement - Comptable - CSAR')
@section('page-title', 'Nouvelle demande de remboursement carburant')

@section('content')
@include('comptable.carburant._nav', ['tab' => 'reimbs'])

@if($errors->any())
<div class="alert alert-danger py-2">
    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Carburant avancé par un agent</strong>
        <small class="text-muted d-block">Le carburant a été acheté directement par l'agent : cette demande ne modifie pas le stock physique.</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('comptable.carburant.reimbs.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Agent demandeur <span class="text-danger">*</span></label>
                    <input type="text" name="agent_name" class="form-control" value="{{ old('agent_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service / direction</label>
                    <input type="text" name="service" class="form-control" value="{{ old('service') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de la dépense <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motif de la dépense <span class="text-danger">*</span></label>
                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Activité</label>
                    <select name="activity_type" class="form-select">
                        <option value="">—</option>
                        <option value="mission" @selected(old('activity_type') === 'mission')>Mission</option>
                        <option value="atelier" @selected(old('activity_type') === 'atelier')>Atelier</option>
                        <option value="activite" @selected(old('activity_type') === 'activite')>Activité</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Réf. activité / mission</label>
                    <input type="text" name="activity_ref" class="form-control" value="{{ old('activity_ref') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Véhicule</label>
                    <input type="text" name="vehicle_label" class="form-control" value="{{ old('vehicle_label') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Immatriculation</label>
                    <input type="text" name="vehicle_plate" class="form-control" value="{{ old('vehicle_plate') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° reçu / facture station</label>
                    <input type="text" name="receipt_ref" class="form-control" value="{{ old('receipt_ref') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Litres (si connus)</label>
                    <input type="number" step="0.01" min="0" name="quantity_liters" class="form-control" value="{{ old('quantity_liters') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Montant avancé déclaré (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount_claimed" class="form-control" value="{{ old('amount_claimed') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Montant demandé (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount_requested" class="form-control" value="{{ old('amount_requested') }}" required>
                    <div class="form-text">≤ montant déclaré</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Avance déjà consentie (FCFA)</label>
                    <input type="number" step="0.01" min="0" name="advance_amount" class="form-control" value="{{ old('advance_amount') }}">
                    <div class="form-text">Pour régularisation d'avance</div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer la demande</button>
                <a href="{{ route('comptable.carburant.reimbs.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
