@extends('layouts.admin')

@section('title', 'Nouveau Document')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-plus-circle me-2"></i>Nouveau document</h1>
        <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Formulaire</h6></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fichier * (PDF, DOC, XLS, max 20Mo)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-control" required>
                                <option value="recrutement" {{ old('type') == 'recrutement' ? 'selected' : '' }}>Recrutement</option>
                                <option value="rapport" {{ old('type') == 'rapport' ? 'selected' : '' }}>Rapport</option>
                                <option value="communique" {{ old('type') == 'communique' ? 'selected' : '' }}>Communiqué</option>
                                <option value="appel_offre" {{ old('type') == 'appel_offre' ? 'selected' : '' }}>Appel d'offres</option>
                                <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de publication</label>
                            <input type="date" name="published_at" class="form-control" value="{{ old('published_at') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date d'expiration</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publier immédiatement</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i>Enregistrer</button>
            </form>
        </div>
    </div>
</div>
@endsection
