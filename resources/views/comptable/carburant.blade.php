@extends('layouts.direction-portal')

@section('title', 'Carburant - Comptable des matières - CSAR')
@section('page-title', 'Suivi du carburant (soldes en litres)')

@section('content')
@include('comptable.carburant._nav', ['tab' => 'dashboard'])

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

@isset($pending)
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.carburant.entries.index', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-2">
                <div class="fs-4 fw-bold text-warning">{{ $pending['entries'] }}</div>
                <small class="text-muted">Entrées à valider</small>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.carburant.exits.index', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-2">
                <div class="fs-4 fw-bold text-warning">{{ $pending['exits_draft'] }}</div>
                <small class="text-muted">Sorties à autoriser</small>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.carburant.exits.index', ['status' => 'authorized']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-2">
                <div class="fs-4 fw-bold text-info">{{ $pending['exits_authorized'] }}</div>
                <small class="text-muted">Livraisons à valider</small>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.carburant.reimbs.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-2">
                <div class="fs-4 fw-bold text-primary">{{ $pending['reimbursements'] }}</div>
                <small class="text-muted">Remboursements en cours</small>
            </div>
        </a>
    </div>
</div>
@endisset

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong>Soldes carburant par produit et magasin</strong>
        <small class="text-muted d-block">Agrégation des mouvements en litres (unite = « L »), mêmes règles que l'API mobile.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Magasin</th>
                        <th class="text-end">Entrées (L)</th>
                        <th class="text-end">Sorties (L)</th>
                        <th class="text-end">Solde (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fuel as $f)
                    <tr>
                        <td><i class="fas fa-gas-pump text-warning me-1"></i>{{ $f['produit'] }}</td>
                        <td>{{ $f['warehouse_name'] ?? '-' }}</td>
                        <td class="text-end text-success">{{ number_format($f['total_in'], 0, ',', ' ') }}</td>
                        <td class="text-end text-danger">{{ number_format($f['total_out'], 0, ',', ' ') }}</td>
                        <td class="text-end">
                            <strong class="{{ $f['balance'] <= 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($f['balance'], 0, ',', ' ') }}
                            </strong>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-gas-pump fa-2x d-block mb-2 opacity-50"></i>
                        <strong>Aucun mouvement de carburant enregistré</strong><br>
                        <small>Les entrées/sorties en litres saisies par les magasiniers apparaîtront ici.</small>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@isset($stockValue)
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white">
        <strong>Valorisation du stock</strong>
        <small class="text-muted d-block">Coût moyen pondéré des entrées validées.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th class="text-end">Solde (L)</th>
                        <th class="text-end">Coût moyen (FCFA/L)</th>
                        <th class="text-end">Valeur stock (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockValue as $v)
                    <tr>
                        <td>{{ $v['produit'] }}</td>
                        <td class="text-end">{{ number_format($v['balance'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($v['avg_unit_cost'], 0, ',', ' ') }}</td>
                        <td class="text-end"><strong>{{ number_format($v['stock_value'], 0, ',', ' ') }}</strong></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Aucune valeur — pas encore d'entrée validée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endisset
@endsection
