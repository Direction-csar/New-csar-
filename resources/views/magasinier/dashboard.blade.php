@extends('layouts.direction-portal')

@section('title', 'Tableau de bord - Magasin - CSAR')
@section('page-title', 'Tableau de bord — Magasin & stocks')

@section('content')
@php $userName = auth()->user()->name ?? 'Magasinier'; @endphp

<p class="text-muted mb-4">Bonjour <strong>{{ $userName }}</strong>, voici l'état de vos magasins.</p>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
            <i class="fas fa-warehouse text-primary fa-lg mb-2"></i>
            <h3 class="mb-0 fw-bold text-primary">{{ $warehouses->count() }}</h3>
            <small class="text-muted">Magasins</small>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
            <i class="fas fa-arrow-down text-success fa-lg mb-2"></i>
            <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['entries_today_sacs'], 0, ',', ' ') }}</h3>
            <small class="text-muted">Entrées aujourd'hui (sacs)</small>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
            <i class="fas fa-arrow-up text-danger fa-lg mb-2"></i>
            <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['exits_today_sacs'], 0, ',', ' ') }}</h3>
            <small class="text-muted">Sorties aujourd'hui (sacs)</small>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
            <i class="fas fa-list-alt text-secondary fa-lg mb-2"></i>
            <h3 class="mb-0 fw-bold text-secondary">{{ $stats['total_movements'] }}</h3>
            <small class="text-muted">Mouvements totaux</small>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-warehouse me-2 text-primary"></i>Mes magasins</span>
                <a href="{{ route('magasinier.warehouses.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Magasin</th><th>Région</th><th class="text-end">Stock</th><th class="text-end">Capacité</th><th></th></tr></thead>
                    <tbody>
                    @forelse($balances as $row)
                        @php $w = $row['warehouse']; $b = $row['balance']; $pct = $w->capacity > 0 ? round($w->current_stock / $w->capacity * 100) : 0; @endphp
                        <tr>
                            <td><a href="{{ route('magasinier.warehouses.show', $w->id) }}" class="fw-semibold text-decoration-none">{{ $w->name }}</a></td>
                            <td>{{ $w->region ?? '—' }}</td>
                            <td class="text-end">{{ number_format($b['sacs'], 0, ',', ' ') }} sacs<br><small class="text-muted">{{ number_format($b['kg'], 0, ',', ' ') }} kg</small></td>
                            <td class="text-end">{{ number_format($w->capacity ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end">
                                <a href="{{ route('magasinier.mouvements.create', $w->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Mouvement</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun magasin assigné.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($missions->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold"><i class="fas fa-truck-loading me-2 text-warning"></i>Missions DTL en cours</div>
            <ul class="list-group list-group-flush">
                @foreach($missions->take(5) as $m)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>#{{ $m['id'] }}</strong> — {{ $m['demandeur'] ?? '—' }}
                        <br><small class="text-muted">{{ $m['tracking_code'] }} · {{ $m['region'] ?? '' }}</small>
                    </div>
                    <a href="{{ route('magasinier.missions.index') }}" class="btn btn-sm btn-outline-primary">Traiter</a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        @if($alerts)
        <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
            <div class="card-header bg-white fw-bold"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Alertes stock</div>
            <ul class="list-group list-group-flush">
                @foreach($alerts as $a)
                <li class="list-group-item">
                    <strong>{{ $a['warehouse_name'] }}</strong> — {{ number_format($a['current_stock'], 0, ',', ' ') }} / {{ number_format($a['capacity'], 0, ',', ' ') }}
                    <span class="badge bg-{{ $a['severity'] === 'critical' ? 'danger' : 'warning' }} float-end">{{ $a['percentage'] }}%</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fas fa-history me-2 text-secondary"></i>Derniers mouvements</div>
            <ul class="list-group list-group-flush">
                @forelse($recent as $mv)
                <li class="list-group-item d-flex justify-content-between">
                    <div>
                        <span class="badge bg-{{ in_array($mv->type, ['entry','in','report']) ? 'success' : (in_array($mv->type,['exit','out']) ? 'danger' : 'secondary') }}">{{ $mv->type }}</span>
                        {{ $mv->reason }}
                        <br><small class="text-muted">{{ $mv->warehouse->name ?? '' }} · {{ $mv->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <span class="fw-bold">{{ number_format($mv->quantity, 0, ',', ' ') }}</span>
                </li>
                @empty
                <li class="list-group-item text-center text-muted">Aucun mouvement.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
