@extends('layouts.direction-portal')

@section('title', 'Inventaire physique - CSAR')
@section('page-title', 'Inventaire physique')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle me-1"></i>
            L'inventaire compare le <strong>stock théorique</strong> au <strong>comptage réel</strong> et crée un ajustement automatique de la différence.
        </div>

        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('magasinier.inventaires.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Magasin <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de l'inventaire <span class="text-danger">*</span></label>
                    <input type="date" name="movement_date" class="form-control" value="{{ old('movement_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control" value="{{ old('label', 'Inventaire physique') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Stock théorique (contenants) <span class="text-danger">*</span></label>
                    <input type="number" name="theoretical_sacs" class="form-control" step="0.01" min="0" value="{{ old('theoretical_sacs') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Comptage réel (contenants) <span class="text-danger">*</span></label>
                    <input type="number" name="actual_sacs" class="form-control" step="0.01" min="0" value="{{ old('actual_sacs') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observation</label>
                    <textarea name="observation" class="form-control" rows="2">{{ old('observation') }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer l'inventaire</button>
                <a href="{{ route('magasinier.warehouses.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
