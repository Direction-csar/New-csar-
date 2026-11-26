@extends('layouts.direction-portal')

@section('title', 'État des stocks - Comptable des matières - CSAR')
@section('page-title', 'État des stocks par magasin')

@section('content')
@forelse($warehouses as $w)
@php
    $pct = $w->capacity > 0 ? round($w->current_stock / $w->capacity * 100) : null;
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>{{ $w->name }}</strong>
            @if($w->region)<small class="text-muted"> — {{ $w->region }}</small>@endif
        </div>
        <div class="small">
            Stock :
            <strong>{{ number_format((float) $w->current_stock, 0, ',', ' ') }}</strong>
            / {{ number_format((float) $w->capacity, 0, ',', ' ') }}
            @if($pct !== null)
                <span class="badge {{ $pct < 20 ? 'bg-warning text-dark' : 'bg-success' }}">{{ $pct }} %</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($w->stocks->isEmpty())
            <p class="text-muted mb-0">Aucun article enregistré dans ce magasin.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Seuil min.</th>
                        <th class="text-end">Prix unitaire</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($w->stocks as $s)
                    <tr>
                        <td>{{ $s->item_name }}</td>
                        <td class="text-end">{{ number_format((float) $s->quantity, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ $s->min_quantity !== null ? number_format((float) $s->min_quantity, 0, ',', ' ') : '-' }}</td>
                        <td class="text-end">{{ $s->unit_price !== null ? number_format((float) $s->unit_price, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                        <td>
                            @if($s->isLowStock())
                                <span class="badge bg-warning text-dark">Stock bas</span>
                            @elseif($s->isExpired())
                                <span class="badge bg-danger">Expiré</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@empty
<div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
    <i class="fas fa-warehouse fa-2x d-block mb-2 opacity-50"></i>
    <strong>Aucun magasin enregistré</strong>
</div></div>
@endforelse
@endsection
