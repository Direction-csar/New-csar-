@extends('layouts.direction-portal')

@section('title', 'Nouvelle entrée carburant - Comptable - CSAR')
@section('page-title', 'Nouvelle entrée de carburant')

@section('content')
@include('comptable.carburant._nav', ['tab' => 'entries'])

@if($errors->any())
<div class="alert alert-danger py-2">
    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Approvisionnement en carburant</strong>
        <small class="text-muted d-block">Le stock n'est crédité qu'à la validation de l'entrée.</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('comptable.carburant.entries.store') }}">
            @csrf
            <div class="row g-3">
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
                    <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type de carburant <span class="text-danger">*</span></label>
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
                <div class="col-md-3">
                    <label class="form-label">Quantité (litres) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="{{ old('quantity') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix unitaire (FCFA/L) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="unit_price" class="form-control" value="{{ old('unit_price') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° facture</label>
                    <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date facture</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bon de livraison</label>
                    <input type="text" name="delivery_note_ref" class="form-control" value="{{ old('delivery_note_ref') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Source de financement</label>
                    <input type="text" name="funding_source" class="form-control" value="{{ old('funding_source') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer (brouillon)</button>
                <a href="{{ route('comptable.carburant.entries.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
