@extends('layouts.admin')

@section('title', 'Gestion des Projets')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-project-diagram me-2"></i>Gestion des Projets</h1>
            <p class="text-muted">Programmes et interventions du CSAR</p>
        </div>
        <a href="{{ route('admin.projets.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouveau projet
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-list fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Actifs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['actif'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-play-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Terminés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['termine'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Suspendus</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['suspendu'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-pause-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tous les projets</h6></div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Région</th>
                            <th>Dates</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projets as $projet)
                        <tr>
                            <td>
                                <strong>{{ $projet->titre }}</strong>
                                @if($projet->lien_sim)<span class="badge badge-info ms-1">SIM</span>@endif
                                @if($projet->is_published)<span class="badge badge-success ms-1">Publié</span>@endif
                            </td>
                            <td>
                                @if($projet->statut == 'actif')
                                    <span class="badge badge-success">Actif</span>
                                @elseif($projet->statut == 'termine')
                                    <span class="badge badge-info">Terminé</span>
                                @else
                                    <span class="badge badge-warning">Suspendu</span>
                                @endif
                            </td>
                            <td>{{ $projet->region ?? '—' }}</td>
                            <td>
                                @if($projet->date_debut)
                                    {{ $projet->date_debut->format('d/m/Y') }}
                                    @if($projet->date_fin) → {{ $projet->date_fin->format('d/m/Y') }}@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $projet->position }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.projets.edit', $projet->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.projets.destroy', $projet->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Supprimer ce projet ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun projet trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $projets->links() }}</div>
        </div>
    </div>
</div>
@endsection
