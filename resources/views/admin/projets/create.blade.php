@extends('layouts.admin')

@section('title', 'Nouveau Projet')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>Nouveau projet</h1>
        <a href="{{ route('admin.projets.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Formulaire</h6></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.projets.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="titre" class="form-control" value="{{ old('titre') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Statut *</label>
                            <select name="statut" class="form-control" required>
                                <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="termine" {{ old('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                                <option value="suspendu" {{ old('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icône (classe FontAwesome)</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', 'project-diagram') }}" placeholder="ex: fa-home">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Région</label>
                            <input type="text" name="region" class="form-control" value="{{ old('region') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Budget</label>
                            <input type="text" name="budget" class="form-control" value="{{ old('budget') }}" placeholder="ex: 500M FCFA">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" value="{{ old('position', 0) }}" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-check mb-2">
                            <input type="hidden" name="lien_sim" value="0">
                            <input type="checkbox" name="lien_sim" value="1" class="form-check-input" id="lien_sim" {{ old('lien_sim') ? 'checked' : '' }}>
                            <label class="form-check-label" for="lien_sim">Lien vers SIM</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publier</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i>Enregistrer</button>
            </form>
        </div>
    </div>
</div>
@endsection
