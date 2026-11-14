@extends('layouts.direction-portal')

@section('title', $warehouse->name . ' - Fiche de stock - CSAR')
@section('page-title', 'Fiche de stock — ' . $warehouse->name)

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center">
        <small class="text-muted">Stock actuel</small><h4 class="mb-0 fw-bold">{{ number_format($balance['sacs'], 0, ',', ' ') }} <small>sacs</small></h4>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center">
        <small class="text-muted">Poids estimé</small><h4 class="mb-0 fw-bold">{{ number_format($balance['kg'], 0, ',', ' ') }} <small>kg</small></h4>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center">
        <small class="text-muted">Capacité</small><h4 class="mb-0 fw-bold">{{ number_format($warehouse->capacity ?? 0, 0, ',', ' ') }}</h4>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center">
        <small class="text-muted">Région</small><h4 class="mb-0 fw-bold small">{{ $warehouse->region ?? '—' }}</h4>
    </div></div></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('magasinier.mouvements.create', $warehouse->id) }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau mouvement</a>
    <a href="{{ route('magasinier.warehouses.stock-status', $warehouse->id) }}" class="btn btn-outline-secondary"><i class="fas fa-boxes"></i> État par produit</a>
    <a href="{{ route('magasinier.transferts.create') }}" class="btn btn-outline-primary"><i class="fas fa-exchange-alt"></i> Transfert</a>
    <a href="{{ route('magasinier.inventaires.create') }}" class="btn btn-outline-primary"><i class="fas fa-clipboard-list"></i> Inventaire</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><label class="form-label small mb-1">Du</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><label class="form-label small mb-1">Au</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Filtrer</button>
                <a href="{{ route('magasinier.warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-link">Réinit.</a></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold"><i class="fas fa-list me-2"></i>Mouvements</div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead><tr><th>Date</th><th>Type</th><th>Libellé</th><th>Par</th><th class="text-end">Qté</th><th class="text-end">Avant</th><th class="text-end">Après</th><th>Réf.</th></tr></thead>
            <tbody>
            @forelse($movements as $mv)
                @php
                    $meta = json_decode($mv->metadata ?? '{}', true) ?: [];
                    $label = $meta['observation'] ?? $mv->reason;
                @endphp
                <tr>
                    <td>{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-{{ in_array($mv->type, ['entry','in','report']) ? 'success' : (in_array($mv->type,['exit','out']) ? 'danger' : 'secondary') }}">{{ $mv->type }}</span></td>
                    <td>{{ \Illuminate\Support\Str::limit($label, 60) }}</td>
                    <td>{{ $mv->creator->name ?? '—' }}</td>
                    <td class="text-end fw-bold">{{ number_format($mv->quantity, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ $mv->quantity_before !== null ? number_format($mv->quantity_before, 0, ',', ' ') : '—' }}</td>
                    <td class="text-end">{{ $mv->quantity_after !== null ? number_format($mv->quantity_after, 0, ',', ' ') : '—' }}</td>
                    <td><a href="{{ route('magasinier.receipts.show', $mv->reference) }}" class="small">{{ $mv->reference }}</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun mouvement sur cette période.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="card-footer bg-white">{{ $movements->links() }}</div>
    @endif
</div>
@endsection
