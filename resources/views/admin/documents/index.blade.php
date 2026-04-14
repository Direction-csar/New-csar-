@extends('layouts.admin')

@section('title', 'Documents Publics')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-alt me-2"></i>Documents Publics</h1>
            <p class="text-muted">Recrutements, rapports, communiqués, appels d'offres</p>
        </div>
        <a href="{{ route('admin.documents.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouveau document
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Publiés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['published'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Recrutements</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['recrutement'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-tie fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rapports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rapport'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-chart-bar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tous les documents</h6></div>
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
                            <th>Type</th>
                            <th>Fichier</th>
                            <th>Taille</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td><strong>{{ $doc->title }}</strong></td>
                            <td><span class="badge badge-info">{{ $doc->type }}</span></td>
                            <td>
                                @if($doc->file_path)
                                    <a href="{{ route('admin.documents.download', $doc->id) }}" class="text-primary">
                                        <i class="fas fa-download me-1"></i>{{ $doc->file_name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                            <td>
                                @if($doc->is_published)
                                    <span class="badge badge-success">Publié</span>
                                @else
                                    <span class="badge badge-warning">Brouillon</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.documents.edit', $doc->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.documents.destroy', $doc->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Supprimer ce document ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun document trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $documents->links() }}</div>
        </div>
    </div>
</div>
@endsection
