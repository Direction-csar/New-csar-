@extends('layouts.direction-portal')

@section('title', 'Reçu ' . $receipt['reference'] . ' - CSAR')
@section('page-title', 'Reçu de mouvement')

@section('content')
@php $r = $receipt; @endphp
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>Reçu {{ $r['reference'] }}</span>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr><td class="text-muted" style="width:40%">Type</td><td class="fw-semibold">{{ $r['type_label'] }}</td></tr>
            <tr><td class="text-muted">Magasin</td><td>{{ $r['warehouse_name'] }}{{ $r['warehouse_region'] ? ' — ' . $r['warehouse_region'] : '' }}</td></tr>
            <tr><td class="text-muted">Motif</td><td>{{ $r['reason'] }}</td></tr>
            <tr><td class="text-muted">Quantité</td><td class="fw-bold fs-5">{{ number_format($r['quantity_sacs'], 0, ',', ' ') }} contenants</td></tr>
            @if(isset($r['metadata']['entry_kg']) || isset($r['metadata']['exit_kg']))
            <tr><td class="text-muted">Poids</td><td>{{ number_format(($r['metadata']['entry_kg'] ?? 0) + ($r['metadata']['exit_kg'] ?? 0), 0, ',', ' ') }} kg</td></tr>
            @endif
            <tr><td class="text-muted">Solde avant</td><td>{{ $r['quantity_before'] !== null ? number_format($r['quantity_before'], 0, ',', ' ') . ' sacs' : '—' }}</td></tr>
            <tr><td class="text-muted">Solde après</td><td class="fw-semibold">{{ $r['quantity_after'] !== null ? number_format($r['quantity_after'], 0, ',', ' ') . ' sacs' : '—' }}</td></tr>
            <tr><td class="text-muted">Date mouvement</td><td>{{ $r['movement_date'] }}</td></tr>
            <tr><td class="text-muted">Enregistré par</td><td>{{ $r['created_by'] }}</td></tr>
            <tr><td class="text-muted">Le</td><td>{{ $r['created_at'] }}</td></tr>
            @if(!empty($r['numero_transfert']))
            <tr><td class="text-muted">N° transfert</td><td class="fw-semibold">{{ $r['numero_transfert'] }}</td></tr>
            @endif
            @if(!empty($r['metadata']['observation']))
            <tr><td class="text-muted">Observation</td><td>{{ $r['metadata']['observation'] }}</td></tr>
            @endif
        </table>

        <div class="row mt-4 text-center">
            <div class="col-6"><div style="border-top:1px solid #000; padding-top:6px; margin-top:40px;">Le magasinier</div></div>
            <div class="col-6"><div style="border-top:1px solid #000; padding-top:6px; margin-top:40px;">Le réceptionnaire</div></div>
        </div>
    </div>
</div>
<p class="text-center mt-3"><a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('magasinier.warehouses.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Retour</a></p>
</div>
</div>
@endsection
