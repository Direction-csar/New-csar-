@extends('layouts.admin')

@section('title', 'Gestion des Dons')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-hand-holding-heart me-2"></i>Gestion des Dons</h1>
            <p class="text-muted">Suivi de toutes les donations reçues</p>
        </div>
        <div>
            <a href="{{ route('admin.donations.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i>Exporter CSV
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dons</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Complétés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['success'] }}</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Attente</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Montant Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-coins fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Filtres</h6></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.donations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Nom, email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Complété</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Du">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="Au">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('admin.donations.index') }}" class="btn btn-secondary w-100"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Liste des Donations</h6></div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Donateur</th>
                            <th>Email</th>
                            <th>Montant</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donations as $donation)
                        <tr>
                            <td><small class="text-muted">#{{ $donation->id }}</small></td>
                            <td>
                                <strong>{{ $donation->full_name }}</strong>
                                @if($donation->is_anonymous)
                                    <span class="badge badge-secondary ms-1">Anonyme</span>
                                @endif
                            </td>
                            <td>{{ $donation->email ?? '—' }}</td>
                            <td><strong class="text-success">{{ number_format($donation->amount, 0, ',', ' ') }} FCFA</strong></td>
                            <td><span class="badge badge-info">{{ $donation->payment_method ?? '—' }}</span></td>
                            <td>
                                @if($donation->payment_status === 'success')
                                    <span class="badge badge-success">Complété</span>
                                @elseif($donation->payment_status === 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @else
                                    <span class="badge badge-danger">Échoué</span>
                                @endif
                            </td>
                            <td>{{ $donation->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.donations.show', $donation->id) }}" class="btn btn-info btn-sm" title="Voir détail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.donations.destroy', $donation->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"
                                            onclick="return confirm('Supprimer ce don ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucun don trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $donations->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection
