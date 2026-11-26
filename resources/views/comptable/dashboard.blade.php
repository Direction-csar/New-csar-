@extends('layouts.direction-portal')

@section('title', 'Tableau de bord - Comptable des matières - CSAR')
@section('page-title', 'Tableau de bord — Comptabilité des matières')

@section('content')
@php
    $userName = auth()->user()->name ?? 'Comptable';
@endphp

<p class="text-muted mb-4">Bonjour <strong>{{ $userName }}</strong>, voici l'état de la comptabilité des matières.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.mouvements.index', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <i class="fas fa-hourglass-half text-warning fa-lg mb-2"></i>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['draft'] ?? 0 }}</h3>
                <small class="text-muted">En attente</small>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.mouvements.index', ['status' => 'validated']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <i class="fas fa-check-circle text-success fa-lg mb-2"></i>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['validated'] ?? 0 }}</h3>
                <small class="text-muted">Validés</small>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.mouvements.index', ['status' => 'rejected']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <i class="fas fa-times-circle text-danger fa-lg mb-2"></i>
                <h3 class="mb-0 fw-bold text-danger">{{ $stats['rejected'] ?? 0 }}</h3>
                <small class="text-muted">Rejetés</small>
            </div></div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('comptable.materiel', ['status' => 'affectee']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
                <i class="fas fa-hand-holding text-primary fa-lg mb-2"></i>
                <h3 class="mb-0 fw-bold text-primary">{{ $activeAssignments }}</h3>
                <small class="text-muted">Matériel affecté</small>
            </div></div>
        </a>
    </div>
</div>

@if($lowStock->isNotEmpty())
<div class="card border-0 shadow-sm mb-4 border-start border-warning border-3">
    <div class="card-body">
        <h6 class="fw-bold"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Alertes stock faible (moins de 20 % de la capacité)</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Magasin</th><th>Région</th><th class="text-end">Stock</th><th class="text-end">Capacité</th><th class="text-end">Niveau</th></tr></thead>
                <tbody>
                @foreach($lowStock as $w)
                    <tr>
                        <td>{{ $w['name'] }}</td>
                        <td class="text-muted">{{ $w['region'] ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $w['current_stock'], 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format((float) $w['capacity'], 0, ',', ' ') }}</td>
                        <td class="text-end"><span class="badge bg-warning text-dark">{{ $w['ratio'] }} %</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Derniers mouvements</strong>
                <a href="{{ route('comptable.mouvements.index') }}" class="btn btn-sm btn-outline-primary">Tout voir</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Référence</th><th>Type</th><th>Magasin</th><th class="text-end">Quantité</th><th>Statut</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $m)
                            @php
                                $isExit = in_array($m->type, ['exit', 'out', 'transfert', 'transfer'], true);
                                $badge = match($m->status ?? 'draft') {
                                    'validated' => ['bg-success', 'Validé'],
                                    'rejected' => ['bg-danger', 'Rejeté'],
                                    default => ['bg-warning text-dark', 'En attente'],
                                };
                            @endphp
                            <tr>
                                <td><code>{{ $m->reference }}</code></td>
                                <td><i class="fas fa-arrow-{{ $isExit ? 'up' : 'down' }} text-{{ $isExit ? 'danger' : 'success' }} me-1"></i>{{ $m->type_formatted ?? ucfirst($m->type) }}</td>
                                <td>{{ $m->warehouse?->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format((float) $m->quantity, 0, ',', ' ') }}</td>
                                <td><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                                <td><a href="{{ route('comptable.mouvements.show', $m->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Aucun mouvement récent.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Soldes carburant</strong>
                <a href="{{ route('comptable.carburant') }}" class="btn btn-sm btn-outline-primary">Détail</a>
            </div>
            <div class="card-body">
                @forelse($fuel as $f)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ $f['produit'] }}@if($f['warehouse_name']) <small class="text-muted">({{ $f['warehouse_name'] }})</small>@endif</span>
                        <strong class="{{ $f['balance'] <= 0 ? 'text-danger' : 'text-success' }}">{{ number_format($f['balance'], 0, ',', ' ') }} L</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun mouvement en litres enregistré.</p>
                @endforelse
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Magasins</strong>
                <a href="{{ route('comptable.stocks') }}" class="btn btn-sm btn-outline-primary">Stocks</a>
            </div>
            <div class="card-body">
                @forelse($warehouses as $w)
                    @php $pct = $w->capacity > 0 ? round($w->current_stock / $w->capacity * 100) : 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $w->name }}</span>
                            <span class="text-muted">{{ $pct }} %</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $pct < 20 ? 'bg-warning' : 'bg-primary' }}" style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucun magasin enregistré.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
