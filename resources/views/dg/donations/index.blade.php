@extends('layouts.dg-modern')

@section('title', 'Suivi des Dons')
@section('page-title', 'Donations CSAR - Vue DG')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-hand-holding-heart me-2"></i>Suivi des Donations</h1>
            <p class="text-muted">Vue exécutive des donations reçues</p>
        </div>
        <div>
            <a href="{{ route('dg.donations.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i>Exporter CSV
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dons</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Complétés</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['success'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Attente</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Échoués</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['failed'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Montant Total</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_amount'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Ce Mois</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['monthly_amount'], 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtres</h6></div>
        <div class="card-body">
            <form method="GET" action="{{ route('dg.donations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Nom, email, transaction..." value="{{ request('search') }}">
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
                    <a href="{{ route('dg.donations.index') }}" class="btn btn-secondary w-100"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Liste des Donations</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Donateur</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Montant</th>
                            <th>Fournisseur</th>
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
                                <strong>{{ $donation->is_anonymous ? 'Anonyme' : $donation->full_name }}</strong>
                            </td>
                            <td>{{ $donation->is_anonymous ? '—' : ($donation->email ?? '—') }}</td>
                            <td>{{ $donation->is_anonymous ? '—' : ($donation->phone ?? '—') }}</td>
                            <td><strong class="text-success">{{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency ?? 'FCFA' }}</strong></td>
                            <td>
                                @if($donation->payment_provider === 'paypal')
                                    <span class="badge bg-primary">PayPal</span>
                                @else
                                    <span class="badge bg-info">PayDunya</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $donation->payment_method ?? '—' }}</span></td>
                            <td>
                                @if($donation->payment_status === 'success')
                                    <span class="badge bg-success">Complété</span>
                                @elseif($donation->payment_status === 'pending')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                    <span class="badge bg-danger">Échoué</span>
                                @endif
                            </td>
                            <td>{{ $donation->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('dg.donations.show', $donation->id) }}" class="btn btn-info btn-sm" title="Voir détail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted">Aucun don trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $donations->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection
