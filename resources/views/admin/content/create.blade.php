@extends('layouts.admin')

@section('title', 'Nouveau Contenu')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Nouveau Contenu</h1>
            <p class="text-muted mb-0">Créer une page, un article ou une annonce</p>
        </div>
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <form action="{{ route('admin.content.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required
                                       style="border-radius: 12px;">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="slug" class="form-label fw-semibold">Slug (URL)</label>
                                <input type="text" class="form-control form-control-lg @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-genere"
                                       style="border-radius: 12px;">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('type') is-invalid @enderror" id="type" name="type" required style="border-radius: 12px;">
                                    <option value="page" {{ old('type') == 'page' ? 'selected' : '' }}>Page</option>
                                    <option value="article" {{ old('type') == 'article' ? 'selected' : '' }}>Article</option>
                                    <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Annonce</option>
                                    <option value="banner" {{ old('type') == 'banner' ? 'selected' : '' }}>Bannière</option>
                                    <option value="footer" {{ old('type') == 'footer' ? 'selected' : '' }}>Pied de page</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="category" class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('category') is-invalid @enderror" id="category" name="category" required style="border-radius: 12px;">
                                    <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>Général</option>
                                    <option value="news" {{ old('category') == 'news' ? 'selected' : '' }}>Actualité</option>
                                    <option value="announcements" {{ old('category') == 'announcements' ? 'selected' : '' }}>Annonces</option>
                                    <option value="about" {{ old('category') == 'about' ? 'selected' : '' }}>À propos</option>
                                    <option value="home" {{ old('category') == 'home' ? 'selected' : '' }}>Accueil</option>
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('status') is-invalid @enderror" id="status" name="status" required style="border-radius: 12px;">
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publié</option>
                                    <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Programmé</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="body" class="form-label fw-semibold">Contenu <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="12" required
                                          style="border-radius: 12px; resize: vertical;">{{ old('body') }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="meta_title" class="form-label fw-semibold">Meta Titre (SEO)</label>
                                <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                       id="meta_title" name="meta_title" value="{{ old('meta_title') }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-4">
                                <label for="meta_keywords" class="form-label fw-semibold">Meta Mots-clés</label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror"
                                       id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-4">
                                <label for="featured_image" class="form-label fw-semibold">Image de couverture</label>
                                <input type="text" class="form-control @error('featured_image') is-invalid @enderror"
                                       id="featured_image" name="featured_image" value="{{ old('featured_image') }}" placeholder="URL ou chemin"
                                       style="border-radius: 12px;">
                            </div>

                            <div class="col-md-6">
                                <label for="meta_description" class="form-label fw-semibold">Meta Description (SEO)</label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="2"
                                          style="border-radius: 12px; resize: vertical;">{{ old('meta_description') }}</textarea>
                            </div>

                            <div class="col-md-3">
                                <label for="published_at" class="form-label fw-semibold">Date de publication</label>
                                <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                       id="published_at" name="published_at" value="{{ old('published_at') }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-3">
                                <label for="order" class="form-label fw-semibold">Ordre d'affichage</label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror"
                                       id="order" name="order" value="{{ old('order', 0) }}" min="0" style="border-radius: 12px;">
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg" style="border-radius: 50px; padding: 14px 48px;">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                                <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary btn-lg ms-2" style="border-radius: 50px;">
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
