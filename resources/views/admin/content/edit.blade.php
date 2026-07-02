@extends('layouts.admin')

@section('title', 'Modifier : ' . $content->title)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Modifier le contenu</h1>
            <p class="text-muted mb-0">{{ $content->title }}</p>
        </div>
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <form action="{{ route('admin.content.update', $content->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $content->title) }}" required
                                       style="border-radius: 12px;">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="slug" class="form-label fw-semibold">Slug (URL)</label>
                                <input type="text" class="form-control form-control-lg @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug', $content->slug) }}"
                                       style="border-radius: 12px;">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('type') is-invalid @enderror" id="type" name="type" required style="border-radius: 12px;">
                                    <option value="page" {{ old('type', $content->type) == 'page' ? 'selected' : '' }}>Page</option>
                                    <option value="article" {{ old('type', $content->type) == 'article' ? 'selected' : '' }}>Article</option>
                                    <option value="announcement" {{ old('type', $content->type) == 'announcement' ? 'selected' : '' }}>Annonce</option>
                                    <option value="banner" {{ old('type', $content->type) == 'banner' ? 'selected' : '' }}>Bannière</option>
                                    <option value="footer" {{ old('type', $content->type) == 'footer' ? 'selected' : '' }}>Pied de page</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="category" class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('category') is-invalid @enderror" id="category" name="category" required style="border-radius: 12px;">
                                    <option value="general" {{ old('category', $content->category) == 'general' ? 'selected' : '' }}>Général</option>
                                    <option value="news" {{ old('category', $content->category) == 'news' ? 'selected' : '' }}>Actualité</option>
                                    <option value="announcements" {{ old('category', $content->category) == 'announcements' ? 'selected' : '' }}>Annonces</option>
                                    <option value="about" {{ old('category', $content->category) == 'about' ? 'selected' : '' }}>À propos</option>
                                    <option value="home" {{ old('category', $content->category) == 'home' ? 'selected' : '' }}>Accueil</option>
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg @error('status') is-invalid @enderror" id="status" name="status" required style="border-radius: 12px;">
                                    <option value="draft" {{ old('status', $content->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                    <option value="published" {{ old('status', $content->status) == 'published' ? 'selected' : '' }}>Publié</option>
                                    <option value="scheduled" {{ old('status', $content->status) == 'scheduled' ? 'selected' : '' }}>Programmé</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="body" class="form-label fw-semibold">Contenu <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="12" required
                                          style="border-radius: 12px; resize: vertical;">{{ old('body', $content->body) }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="meta_title" class="form-label fw-semibold">Meta Titre (SEO)</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title', $content->meta_title) }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-4">
                                <label for="meta_keywords" class="form-label fw-semibold">Meta Mots-clés</label>
                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $content->meta_keywords) }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-4">
                                <label for="featured_image" class="form-label fw-semibold">Image de couverture</label>
                                <input type="text" class="form-control" id="featured_image" name="featured_image" value="{{ old('featured_image', $content->featured_image) }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-6">
                                <label for="meta_description" class="form-label fw-semibold">Meta Description (SEO)</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" rows="2" style="border-radius: 12px; resize: vertical;">{{ old('meta_description', $content->meta_description) }}</textarea>
                            </div>

                            <div class="col-md-3">
                                <label for="published_at" class="form-label fw-semibold">Date de publication</label>
                                <input type="datetime-local" class="form-control" id="published_at" name="published_at"
                                       value="{{ old('published_at', $content->published_at?->format('Y-m-d\TH:i')) }}" style="border-radius: 12px;">
                            </div>

                            <div class="col-md-3">
                                <label for="order" class="form-label fw-semibold">Ordre d'affichage</label>
                                <input type="number" class="form-control" id="order" name="order" value="{{ old('order', $content->order) }}" min="0" style="border-radius: 12px;">
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg" style="border-radius: 50px; padding: 14px 48px;">
                                    <i class="fas fa-save me-2"></i>Mettre à jour
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
