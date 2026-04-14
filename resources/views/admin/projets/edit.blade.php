@extends('layouts.admin')

@section('title', 'Modifier Projet')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>Modifier projet #{{ $projet->id }}</h1>
        <a href="{{ route('admin.projets.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Formulaire</h6></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.projets.update', $projet->id) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="titre" class="form-control" value="{{ old('titre', $projet->titre) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description', $projet->description) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $projet->date_debut?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $projet->date_fin?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Statut *</label>
                            <select name="statut" class="form-control" required>
                                <option value="actif" {{ old('statut', $projet->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="termine" {{ old('statut', $projet->statut) == 'termine' ? 'selected' : '' }}>Terminé</option>
                                <option value="suspendu" {{ old('statut', $projet->statut) == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icône</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', $projet->icon) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Région</label>
                            <input type="text" name="region" class="form-control" value="{{ old('region', $projet->region) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Budget</label>
                            <input type="text" name="budget" class="form-control" value="{{ old('budget', $projet->budget) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" value="{{ old('position', $projet->position) }}" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            @if($projet->image)
                                <div class="mb-2"><img src="{{ asset('storage/' . $projet->image) }}" class="img-thumbnail" style="max-height:100px"></div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-check mb-2">
                            <input type="hidden" name="lien_sim" value="0">
                            <input type="checkbox" name="lien_sim" value="1" class="form-check-input" id="lien_sim" {{ old('lien_sim', $projet->lien_sim) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lien_sim">Lien vers SIM</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" {{ old('is_published', $projet->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publié</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i>Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
@endsection
