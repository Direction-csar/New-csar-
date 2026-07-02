@extends('layouts.admin')

@section('title', $content->title)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour à la liste
        </a>
        <div class="btn-group">
            <a href="{{ route('admin.content.edit', $content->id) }}" class="btn btn-success">
                <i class="fas fa-edit me-1"></i>Modifier
            </a>
            <form action="{{ route('admin.content.toggle-status', $content->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn {{ $content->status === 'published' ? 'btn-warning' : 'btn-primary' }}">
                    <i class="fas {{ $content->status === 'published' ? 'fa-toggle-off' : 'fa-toggle-on' }} me-1"></i>
                    {{ $content->status === 'published' ? 'Dépublier' : 'Publier' }}
                </button>
            </form>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow" style="border-radius: 24px;">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="fw-bold mb-2">{{ $content->title }}</h1>
                            <div class="d-flex gap-2">
                                <span class="badge bg-light text-dark">{{ $content->type_label }}</span>
                                <span class="badge bg-light text-dark">{{ $content->category_label }}</span>
                                @if($content->status === 'published')
                                <span class="badge bg-success">Publié</span>
                                @elseif($content->status === 'draft')
                                <span class="badge bg-secondary">Brouillon</span>
                                @else
                                <span class="badge bg-info">Programmé</span>
                                @endif
                            </div>
                        </div>
                        @if($content->featured_image)
                        <img src="{{ $content->featured_image }}" alt="" class="rounded" style="max-width: 180px; max-height: 120px; object-fit: cover;">
                        @endif
                    </div>

                    <hr>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Slug</h6>
                            <p class="fw-semibold mb-0">/{{ $content->slug }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Auteur</h6>
                            <p class="fw-semibold mb-0">{{ $content->creator?->name ?? 'Système' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Dernière modification</h6>
                            <p class="fw-semibold mb-0">{{ $content->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Date de publication</h6>
                            <p class="fw-semibold mb-0">{{ $content->published_at?->format('d/m/Y H:i') ?? 'Non publié' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Vues</h6>
                            <p class="fw-semibold mb-0">{{ $content->views_count }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Ordre</h6>
                            <p class="fw-semibold mb-0">{{ $content->order }}</p>
                        </div>
                    </div>

                    @if($content->meta_title || $content->meta_description)
                    <div class="p-3 rounded mb-4" style="background: #f8fafc;">
                        <h6 class="text-muted mb-2">SEO</h6>
                        <p class="mb-1"><strong>Titre :</strong> {{ $content->meta_title ?? 'Non défini' }}</p>
                        <p class="mb-0"><strong>Description :</strong> {{ $content->meta_description ?? 'Non définie' }}</p>
                    </div>
                    @endif

                    <hr>

                    <h6 class="text-muted mb-3">Contenu</h6>
                    <div class="p-4 rounded" style="background: #f8fafc; min-height: 200px;">
                        {!! $content->body !!}
                    </div>

                    <div class="mt-4 text-end">
                        <form action="{{ route('admin.content.destroy', $content->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce contenu ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
