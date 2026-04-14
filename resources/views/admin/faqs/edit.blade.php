@extends('layouts.admin')

@section('title', 'Modifier FAQ')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>Modifier FAQ #{{ $faq->id }}</h1>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Formulaire</h6></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <input type="text" name="question" class="form-control" value="{{ old('question', $faq->question) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Réponse</label>
                            <textarea name="answer" class="form-control" rows="6" required>{{ old('answer', $faq->answer) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select name="category" class="form-control" required>
                                <option value="usager" {{ old('category', $faq->category) == 'usager' ? 'selected' : '' }}>Usager</option>
                                <option value="bailleur" {{ old('category', $faq->category) == 'bailleur' ? 'selected' : '' }}>Bailleur</option>
                                <option value="general" {{ old('category', $faq->category) == 'general' ? 'selected' : '' }}>Général</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Langue</label>
                            <select name="locale" class="form-control" required>
                                <option value="fr" {{ old('locale', $faq->locale) == 'fr' ? 'selected' : '' }}>Français</option>
                                <option value="en" {{ old('locale', $faq->locale) == 'en' ? 'selected' : '' }}>English</option>
                                <option value="ar" {{ old('locale', $faq->locale) == 'ar' ? 'selected' : '' }}>العربية</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" value="{{ old('position', $faq->position) }}" min="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_published" value="0">
                                <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" {{ old('is_published', $faq->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Publiée</label>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
@endsection
