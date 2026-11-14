@extends('layouts.direction-portal')

@section('title', 'Nouveau mouvement - ' . $warehouse->name)
@section('page-title', 'Nouveau mouvement — ' . $warehouse->name)

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="alert alert-info py-2">
            <i class="fas fa-info-circle me-1"></i>
            Solde actuel : <strong>{{ number_format($balance['sacs'], 0, ',', ' ') }} sacs</strong>
            ({{ number_format($balance['kg'], 0, ',', ' ') }} kg)
        </div>

        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('magasinier.mouvements.store', $warehouse->id) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type de mouvement <span class="text-danger">*</span></label>
                    <select name="movement_type" class="form-select" required>
                        <option value="entry">Entrée de stock</option>
                        <option value="exit">Sortie de stock</option>
                        <option value="report">Report</option>
                        <option value="adjustment">Ajustement</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="movement_date" class="form-control" value="{{ old('movement_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Libellé / motif <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control" value="{{ old('label') }}" placeholder="Ex : Réception livraison riz, Distribution marché…" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Position / localisation</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position') }}" placeholder="Ex : Case A3, Zone nord…">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de contenants (sacs) <span class="text-danger">*</span></label>
                    <input type="number" name="sacs" class="form-control" step="0.01" min="0" value="{{ old('sacs') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Poids total (kg) <span class="text-danger">*</span></label>
                    <input type="number" name="kg" class="form-control" step="0.01" min="0" value="{{ old('kg') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observation</label>
                    <textarea name="observation" class="form-control" rows="2" placeholder="Précisions éventuelles…">{{ old('observation') }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer le mouvement</button>
                <a href="{{ route('magasinier.warehouses.show', $warehouse->id) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
