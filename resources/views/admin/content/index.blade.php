@extends('layouts.admin')

@section('title', 'Gestion du Contenu')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Gestion du Contenu</h1>
            <p class="text-muted mb-0">Pages, articles, annonces et bannières du site</p>
        </div>
        <a href="{{ route('admin.content.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Nouveau Contenu
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #eff6ff;">
                        <i class="fas fa-file-alt text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">Total</h6>
                        <h4 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #ecfdf5;">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">Publiés</h6>
                        <h4 class="mb-0 fw-bold">{{ $stats['published'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #fffbeb;">
                        <i class="fas fa-clock text-warning fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">Brouillons</h6>
                        <h4 class="mb-0 fw-bold">{{ $stats['draft'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #eff6ff;">
                        <i class="fas fa-calendar text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">Programmés</h6>
                        <h4 class="mb-0 fw-bold">{{ $stats['scheduled'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow" style="border-radius: 20px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Titre</th>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Auteur</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contents as $content)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $content->title }}</div>
                                @if($content->slug)
                                <small class="text-muted">/{{ $content->slug }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $content->type_label }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $content->category_label }}</span>
                            </td>
                            <td>
                                @if($content->status === 'published')
                                <span class="badge bg-success">Publié</span>
                                @elseif($content->status === 'draft')
                                <span class="badge bg-secondary">Brouillon</span>
                                @else
                                <span class="badge bg-info">Programmé</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $content->creator?->name ?? 'Système' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $content->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.content.show', $content->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.content.edit', $content->id) }}" class="btn btn-sm btn-outline-success" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.content.toggle-status', $content->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $content->status === 'published' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $content->status === 'published' ? 'Dépublier' : 'Publier' }}">
                                            <i class="fas {{ $content->status === 'published' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.content.destroy', $content->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce contenu ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fs-2 mb-3 d-block"></i>
                                Aucun contenu trouvé.
                                <br><a href="{{ route('admin.content.create') }}" class="btn btn-success btn-sm mt-2">Créer un contenu</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($contents->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $contents->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
