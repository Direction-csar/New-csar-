@extends('layouts.admin')

@section('title', 'Partenaires Techniques')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-handshake me-2"></i>Partenaires Techniques</h1>
            <p class="text-muted">ONG, agences, institutions et partenaires privés</p>
        </div>
        <a href="{{ route('admin.partenaires.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouveau partenaire
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ONG</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['ong'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-globe fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mis en avant</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['featured'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-star fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tous les partenaires</h6></div>
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
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Contact</th>
                            <th>Statut</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partenaires as $partenaire)
                        <tr>
                            <td>
                                <strong>{{ $partenaire->name }}</strong>
                                @if($partenaire->is_featured)<span class="badge badge-warning ms-1"><i class="fas fa-star"></i></span>@endif
                                @if($partenaire->organization)<br><small class="text-muted">{{ $partenaire->organization }}</small>@endif
                            </td>
                            <td><span class="badge badge-info">{{ $partenaire->type }}</span></td>
                            <td>
                                @if($partenaire->email)<div><i class="fas fa-envelope text-muted me-1"></i>{{ $partenaire->email }}</div>@endif
                                @if($partenaire->phone)<div><i class="fas fa-phone text-muted me-1"></i>{{ $partenaire->phone }}</div>@endif
                            </td>
                            <td>
                                @if($partenaire->status == 'active')
                                    <span class="badge badge-success">Actif</span>
                                @elseif($partenaire->status == 'inactive')
                                    <span class="badge badge-secondary">Inactif</span>
                                @else
                                    <span class="badge badge-warning">En attente</span>
                                @endif
                            </td>
                            <td>{{ $partenaire->position }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.partenaires.edit', $partenaire->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.partenaires.destroy', $partenaire->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Supprimer ce partenaire ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun partenaire trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $partenaires->links() }}</div>
        </div>
    </div>
</div>
@endsection
