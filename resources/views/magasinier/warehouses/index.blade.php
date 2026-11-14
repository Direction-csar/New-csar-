@extends('layouts.direction-portal')

@section('title', 'Mes magasins - CSAR')
@section('page-title', 'Mes magasins')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-warehouse me-2 text-primary"></i>Magasins accessibles</span>
        <div>
            <a href="{{ route('magasinier.transferts.create') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-exchange-alt"></i> Transfert</a>
            <a href="{{ route('magasinier.inventaires.create') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-clipboard-list"></i> Inventaire</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Magasin</th><th>Adresse</th><th>Région</th><th class="text-end">Stock (sacs)</th><th class="text-end">Stock (kg)</th><th class="text-end">Capacité</th><th>Niveau</th><th></th></tr></thead>
            <tbody>
            @forelse($balances as $row)
                @php
                    $w = $row['warehouse']; $b = $row['balance'];
                    $pct = $w->capacity > 0 ? round($w->current_stock / $w->capacity * 100) : 0;
                    $color = $pct <= 5 ? 'danger' : ($pct <= 10 ? 'warning' : 'success');
                @endphp
                <tr>
                    <td><a href="{{ route('magasinier.warehouses.show', $w->id) }}" class="fw-semibold text-decoration-none">{{ $w->name }}</a></td>
                    <td>{{ $w->address ?? '—' }}</td>
                    <td>{{ $w->region ?? '—' }}</td>
                    <td class="text-end fw-bold">{{ number_format($b['sacs'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($b['kg'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($w->capacity ?? 0, 0, ',', ' ') }}</td>
                    <td style="min-width:120px">
                        <div class="progress" style="height:8px"><div class="progress-bar bg-{{ $color }}" style="width:{{ min(100,$pct) }}%"></div></div>
                        <small class="text-muted">{{ $pct }}%</small>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('magasinier.warehouses.stock-status', $w->id) }}" class="btn btn-sm btn-outline-secondary" title="État par produit"><i class="fas fa-boxes"></i></a>
                        <a href="{{ route('magasinier.mouvements.create', $w->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Mouvement</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun magasin accessible.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
