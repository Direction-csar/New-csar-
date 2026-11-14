@extends('layouts.direction-portal')

@section('title', 'État du stock - ' . $status['warehouse_name'])
@section('page-title', 'État du stock par produit — ' . $status['warehouse_name'])

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $status['warehouse_name'] }}</strong>
            <span class="text-muted ms-2">{{ $status['warehouse_region'] }}</span>
        </div>
        <div>
            <span class="badge bg-primary fs-6">{{ number_format($status['total_current_sacs'], 0, ',', ' ') }} sacs</span>
            <a href="{{ route('magasinier.warehouses.show', $status['warehouse_id']) }}" class="btn btn-sm btn-outline-secondary ms-2">Fiche de stock</a>
        </div>
    </div>
</div>

@if(!empty($status['expiring']))
<div class="alert alert-warning">
    <i class="fas fa-clock me-2"></i><strong>Lots proches de la péremption :</strong>
    @foreach($status['expiring'] as $e)
        {{ $e['product_name'] }} ({{ number_format($e['quantity'], 0, ',', ' ') }}, expire le {{ $e['expiry_date'] }}){{ !$loop->last ? ' · ' : '' }}
    @endforeach
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Produit</th><th>Format</th><th class="text-end">Qté (contenants)</th><th class="text-end">Total ({{ 'unité' }})</th></tr></thead>
            <tbody>
            @forelse($status['products'] as $p)
                <tr>
                    <td>
                        @if($p['image_url'])<img src="{{ $p['image_url'] }}" alt="" style="width:32px;height:32px;object-fit:contain" class="me-2">@endif
                        {{ $p['product_name'] }}
                    </td>
                    <td>{{ $p['format_kg'] }}{{ $p['unit'] }}</td>
                    <td class="text-end fw-bold">{{ number_format($p['total_sacs'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($p['total_kg'], 0, ',', ' ') }} {{ $p['unit'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Aucun produit en stock.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
