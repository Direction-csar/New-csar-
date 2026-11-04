@extends('layouts.direction-portal')

@section('title', 'Nouveau bon de sortie - Comptable - CSAR')
@section('page-title', 'Nouveau bon de sortie carburant')

@section('content')
@include('comptable.carburant._nav', ['tab' => 'exits'])

@if($errors->any())
<div class="alert alert-danger py-2">
    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Dotation carburant chauffeur / véhicule</strong>
        <small class="text-muted d-block">Flux : brouillon → autorisation (plafond) → livraison (débit du stock).</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('comptable.carburant.exits.store') }}">
            @csrf
            <h6 class="text-muted">Opération</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="operation_date" class="form-control" value="{{ old('operation_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Magasin / cuve <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">—</option>
                        @foreach($warehouses as $wid => $wname)
                            <option value="{{ $wid }}" @selected(old('warehouse_id') == $wid)>{{ $wname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Carburant <span class="text-danger">*</span></label>
                    <select name="fuel_type" class="form-select" required>
                        @foreach($fuelTypes as $t)
                            <option value="{{ $t }}" @selected(old('fuel_type') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Libellé (si autre)</label>
                    <input type="text" name="fuel_type_label" class="form-control" value="{{ old('fuel_type_label') }}">
                </div>
            </div>

            <h6 class="text-muted">Bénéficiaire et véhicule</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Chauffeur / bénéficiaire <span class="text-danger">*</span></label>
                    <input type="text" name="beneficiary_name" class="form-control" value="{{ old('beneficiary_name') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Véhicule</label>
                    <input type="text" name="vehicle_label" class="form-control" value="{{ old('vehicle_label') }}" placeholder="ex: Hilux, camion citerne…">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Immatriculation</label>
                    <input type="text" name="vehicle_plate" class="form-control" value="{{ old('vehicle_plate') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Destination</label>
                    <input type="text" name="destination" class="form-control" value="{{ old('destination') }}">
                </div>
            </div>

            <h6 class="text-muted">Mission et quantités</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Motif <span class="text-danger">*</span></label>
                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">N° mission</label>
                    <input type="text" name="mission_number" class="form-control" value="{{ old('mission_number') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Réf. autorisation</label>
                    <input type="text" name="authorization_ref" class="form-control" value="{{ old('authorization_ref') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Délivré par</label>
                    <input type="text" name="delivered_by_name" class="form-control" value="{{ old('delivered_by_name') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Début mission</label>
                    <input type="date" name="mission_start_date" class="form-control" value="{{ old('mission_start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fin mission</label>
                    <input type="date" name="mission_end_date" class="form-control" value="{{ old('mission_end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Km départ</label>
                    <input type="number" min="0" name="odometer_start" class="form-control" value="{{ old('odometer_start') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Km retour</label>
                    <input type="number" min="0" name="odometer_end" class="form-control" value="{{ old('odometer_end') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Litres demandés <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity_requested" class="form-control" value="{{ old('quantity_requested') }}" required>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer (brouillon)</button>
                <a href="{{ route('comptable.carburant.exits.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
