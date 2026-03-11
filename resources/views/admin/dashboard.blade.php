@extends('layouts.admin')

@section('title', 'Tableau de Bord Admin')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de Bord</h1>
        <div class="text-muted">
            <i class="fas fa-calendar-alt"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <!-- Demandes en attente -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Demandes en Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Demande::where('statut', 'en_attente')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demandes traitées -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Demandes Traitées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Demande::where('statut', 'traite')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilisateurs actifs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Utilisateurs Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\User::where('is_active', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entrepôts -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Entrepôts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Warehouse::count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="row">
        <!-- Demandes récentes -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Demandes Récentes</h6>
                    <a href="{{ route('admin.demandes.index') }}" class="btn btn-sm btn-primary">
                        Voir toutes
                    </a>
                </div>
                <div class="card-body">
                    @if($demandesRecentes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demandesRecentes as $demande)
                                    <tr>
                                        <td>{{ $demande->nom }} {{ $demande->prenom }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $demande->type_demande ?? 'Autre' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($demande->statut == 'en_attente')
                                                <span class="badge badge-warning">En attente</span>
                                            @elseif($demande->statut == 'traite')
                                                <span class="badge badge-success">Traité</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $demande->statut }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.demandes.show', $demande->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">Aucune demande récente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.demandes.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-primary"></i>
                            Gérer les Demandes
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users text-info"></i>
                            Gérer les Utilisateurs
                        </a>
                        <a href="{{ route('admin.warehouses.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-warehouse text-success"></i>
                            Gérer les Entrepôts
                        </a>
                        <a href="{{ route('admin.stocks.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-boxes text-warning"></i>
                            Gérer les Stocks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








































