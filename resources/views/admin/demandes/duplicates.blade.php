@extends('layouts.admin')

@section('title', 'Gestion des Doublons')
@section('page-title', 'Gestion des Doublons de Demandes')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold"><i class="fas fa-clone me-2 text-warning"></i>Gestion des Doublons</h1>
                        <p class="text-muted mb-0 small">Identifiez et fusionnez les demandes en double</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour aux demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-2">
        <div class="col-md-6">
            <div class="card-modern p-3 bg-danger bg-opacity-10 border-danger">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <i class="fas fa-clone" style="font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-danger">{{ $stats['total_duplicates'] }}</h3>
                        <small class="text-muted">Doublons confirmés</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-modern p-3 bg-warning bg-opacity-10 border-warning">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['potential_duplicates'] }}</h3>
                        <small class="text-muted">Doublons potentiels</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recherche -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <form method="GET" action="{{ route('admin.demandes.duplicates.index') }}">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par code, nom ou téléphone..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                        @if(request('search'))
                        <a href="{{ route('admin.demandes.duplicates.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste des doublons -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-list me-2 text-primary"></i>Demandes en double</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Demandeur</th>
                                <th>Objet</th>
                                <th>Type</th>
                                <th>Statut workflow</th>
                                <th>Doublon de</th>
                                <th>Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($duplicates as $demande)
                            <tr class="{{ $demande->is_duplicate ? 'table-danger' : 'table-warning' }}">
                                <td>
                                    <strong class="text-primary">{{ $demande->tracking_code }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $demande->full_name }}</strong>
                                    <br><small class="text-muted">{{ $demande->phone }}</small>
                                </td>
                                <td>{{ Str::limit($demande->subject, 40) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($demande->type ?? 'N/A') }}</span>
                                </td>
                                <td>
                                    {!! $demande->workflow_status_badge !!}
                                </td>
                                <td>
                                    @if($demande->is_duplicate && $demande->originalRequest)
                                    <a href="{{ route('admin.demandes.show', $demande->originalRequest->id) }}" class="badge bg-dark text-decoration-none">
                                        {{ $demande->originalRequest->tracking_code }}
                                    </a>
                                    @else
                                    <span class="badge bg-warning">Potentiel</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</small>
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($demande->created_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.demandes.show', $demande->id) }}" class="btn btn-outline-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.demandes.edit', $demande->id) }}" class="btn btn-outline-warning" title="Traiter">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                        <h5>Aucun doublon détecté</h5>
                                        <p>Toutes les demandes semblent uniques.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($duplicates->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $duplicates->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
