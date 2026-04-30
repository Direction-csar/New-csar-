@extends('layouts.ctc')

@section('title', 'Gestion des Actualités - CTC')

@section('content')
<div class="container-fluid">
    <!-- Header CTC -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">
                <i class="fas fa-newspaper me-2"></i>
                Gestion des Actualités
            </h1>
            <p class="text-white-50 mb-0">Gérer les actualités du CTC</p>
        </div>
        <div>
            <a href="{{ route('public.actualites') }}" class="btn btn-outline-light me-2" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>Voir la page publique
            </a>
            <a href="{{ route('ctc.actualites.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nouvelle Actualité
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid var(--ctc-primary);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--ctc-primary);">
                                Total Actualités
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ $actualites->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-newspaper fa-2x" style="color: var(--ctc-primary); opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid #198754;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Publiées
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ $actualites->where('status', 'published')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Brouillons
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ $actualites->where('status', 'draft')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-warning" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid #0dcaf0;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                En attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ $actualites->where('status', 'pending')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-info" style="opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Actualités -->
    <div class="card shadow mb-4">
        <div class="card-header py-3" style="background: var(--ctc-primary); color: white;">
            <h6 class="m-0 font-weight-bold">Toutes les Actualités</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($actualites as $actualite)
                            <tr>
                                <td>
                                    <strong>{{ $actualite->title ?? $actualite->titre }}</strong>
                                    @if($actualite->is_featured ?? $actualite->featured)
                                        <span class="badge bg-warning ms-1">À la une</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($actualite->category ?? $actualite->categorie) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ ($actualite->status ?? $actualite->statut) == 'published' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($actualite->status ?? $actualite->statut) }}
                                    </span>
                                </td>
                                <td>{{ $actualite->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('ctc.actualites.show', $actualite->id) }}" class="btn btn-info btn-sm" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('ctc.actualites.edit', $actualite->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('ctc.actualites.destroy', $actualite->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune actualité trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
