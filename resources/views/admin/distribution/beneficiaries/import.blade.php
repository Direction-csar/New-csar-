@extends('layouts.admin')

@section('title', 'Import en masse de bénéficiaires')
@section('page-title', 'Import en masse de bénéficiaires')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">Import en masse</h1>
                    <a href="{{ route('admin.distribution.beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))<div class="alert alert-danger small py-2">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger small py-2">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card-modern p-3">
                <form method="POST" action="{{ route('admin.distribution.beneficiaries.import.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Planning de destination <span class="text-danger">*</span></label>
                        <select name="planning_id" class="form-select form-select-sm" required>
                            <option value="">— Choisir un planning —</option>
                            @foreach($plannings as $id => $name)
                                <option value="{{ $id }}" {{ old('planning_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Catégorie (par défaut : general)</label>
                            <input type="text" name="category" class="form-control form-control-sm" value="{{ old('category') }}" placeholder="ex: famille, veuve...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Quantité par défaut (kg)</label>
                            <input type="number" step="0.01" name="default_quantity_kg" class="form-control form-control-sm" value="{{ old('default_quantity_kg') }}" placeholder="ex: 50 — utilisée si la ligne n'en précise pas">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Coller la liste</label>
                        <textarea name="list_text" class="form-control form-control-sm font-monospace" rows="12"
                            placeholder="Une ligne par bénéficiaire :&#10;Nom complet;téléphone;CNI;quantité_kg&#10;&#10;Exemple :&#10;Moussa Diop;771234567;1234567890123;50&#10;Fatou Ndiaye;;50">{{ old('list_text') }}</textarea>
                        <div class="form-text">
                            Séparateurs acceptés : <code>;</code>, tabulation ou <code>,</code>.
                            Téléphone, CNI et quantité sont optionnels (la quantité par défaut s'applique sinon).
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">— ou — Fichier CSV</label>
                        <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv,.txt">
                        <div class="form-text">Même format. Export Excel « CSV (séparateur point-virgule) » supporté.</div>
                    </div>

                    <div class="alert alert-info small py-2 mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Les doublons (même nom, téléphone ou CNI déjà présent dans ce planning) sont ignorés et listés dans le rapport d'import.
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload me-1"></i>Importer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
