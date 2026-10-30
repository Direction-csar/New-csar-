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
                                <option value="{{ $id }}" {{ old('planning_id', request('planning_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Catégorie par défaut (general)</label>
                            <input type="text" name="category" class="form-control form-control-sm" value="{{ old('category') }}" placeholder="ex: famille, veuve... (utilisée si la colonne est vide)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Quantité par défaut (kg)</label>
                            <input type="number" step="0.01" name="default_quantity_kg" class="form-control form-control-sm" value="{{ old('default_quantity_kg') }}" placeholder="ex: 50 — utilisée si la ligne n'en précise pas">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fichier Excel ou CSV</label>
                        <input type="file" name="import_file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv,.txt">
                        <div class="form-text">
                            Formats : <strong>.xlsx, .xls</strong>, .csv. Maximum 5 Mo et {{ number_format(\App\Services\Distribution\BeneficiaryImportService::MAX_ROWS, 0, ',', ' ') }} lignes.
                            Première feuille uniquement, première ligne = en-têtes.
                            <a href="{{ route('admin.distribution.beneficiaries.import.template') }}"><i class="fas fa-download me-1"></i>Télécharger le modèle Excel</a>
                        </div>
                        <div class="form-text">
                            Colonnes reconnues : <code>Nom</code> (obligatoire), <code>Prénom</code>, <code>Téléphone</code>, <code>CNI</code>,
                            <code>Quantité</code>, <code>Catégorie</code>, <code>Adresse</code>.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">— ou — Coller la liste</label>
                        <textarea name="list_text" class="form-control form-control-sm font-monospace" rows="8"
                            placeholder="Nom complet;téléphone;CNI;quantité_kg&#10;Exemple Fictif Un;771234567;1234567890123;50&#10;Exemple Fictif Deux;;;50">{{ old('list_text') }}</textarea>
                        <div class="form-text">Séparateurs acceptés : <code>;</code>, tabulation ou <code>,</code>. Utilisez soit le fichier, soit la liste collée.</div>
                    </div>

                    <div class="alert alert-info small py-2 mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Rien n'est enregistré à cette étape : un <strong>aperçu</strong> vous montre les lignes valides, les doublons
                        (même nom, téléphone ou CNI, dans le fichier ou déjà dans ce planning) et les erreurs avant l'import définitif.
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-1"></i>Analyser et prévisualiser
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
