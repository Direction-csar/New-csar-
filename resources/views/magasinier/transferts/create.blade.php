@extends('layouts.direction-portal')

@section('title', 'Transfert inter-magasins - CSAR')
@section('page-title', 'Transfert inter-magasins')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="alert alert-warning py-2">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Le transfert crée une <strong>sortie</strong> sur le magasin source et une <strong>entrée</strong> sur le magasin destination (même référence, n° de transfert unique).
        </div>

        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('magasinier.transferts.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Magasin source <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ number_format($w->current_stock ?? 0, 0, ',', ' ') }} sacs)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Magasin destination <span class="text-danger">*</span></label>
                    <select name="destination_warehouse_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('destination_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="movement_date" class="form-control" value="{{ old('movement_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control" value="{{ old('label') }}" placeholder="Ex : Renfort stock région de Kaolack" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contenants (sacs) <span class="text-danger">*</span></label>
                    <input type="number" name="sacs" class="form-control" step="0.01" min="0.01" value="{{ old('sacs') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Poids total (kg) <span class="text-danger">*</span></label>
                    <input type="number" name="kg" class="form-control" step="0.01" min="0.01" value="{{ old('kg') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observation</label>
                    <textarea name="observation" class="form-control" rows="2">{{ old('observation') }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-exchange-alt"></i> Effectuer le transfert</button>
                <a href="{{ route('magasinier.warehouses.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
