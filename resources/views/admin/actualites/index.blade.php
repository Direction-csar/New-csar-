@extends('layouts.admin')

@section('title', 'Gestion des Actualités')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-newspaper me-2"></i>
                Gestion des Actualités
            </h1>
            <p class="text-muted">Administrer les actualités du CSAR</p>
        </div>
        <div>
            <a href="{{ route('public.actualites') }}" class="btn btn-info me-2" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>Voir la page publique
            </a>
            <a href="{{ route(route_prefix() . '.actualites.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nouvelle Actualité
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Actualités
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $actualites->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Publiées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $actualites->where('statut', 'publié')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Brouillons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $actualites->where('statut', 'brouillon')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Vues
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $actualites->sum('vues') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Actualités -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Toutes les Actualités</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Auteur</th>
                            <th>Vues</th>
                            <th>Date de Publication</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($actualites as $actualite)
                            <tr>
                                <td>
                                    <strong>{{ $actualite->titre }}</strong>
                                    @if($actualite->featured)
                                        <span class="badge badge-warning ms-1">À la une</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($actualite->categorie) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $actualite->statut == 'publié' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($actualite->statut) }}
                                    </span>
                                </td>
                                <td>{{ $actualite->auteur }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $actualite->vues }} vues</span>
                                </td>
                                <td>{{ $actualite->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route(route_prefix() . '.actualites.show', $actualite->id) }}" class="btn btn-info btn-sm" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route(route_prefix() . '.actualites.edit', $actualite->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route(route_prefix() . '.actualites.destroy', $actualite->id) }}" method="POST" style="display:inline;">
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
                                <td colspan="7" class="text-center">Aucune actualité trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
