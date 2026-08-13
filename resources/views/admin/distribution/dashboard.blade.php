@extends('layouts.admin')

@section('title', 'Tableau de bord Distribution')
@section('page-title', 'Tableau de bord – Distribution CSAR')

@section('content')
<div class="container-fluid py-4">
    <h2 class="h4 mb-4">Indicateurs en temps réel</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-primary">{{ $stats['campaigns'] }}</h3>
                    <small class="text-muted">Campagnes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['plannings'] }}</h3>
                    <small class="text-muted">Plannings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-success">{{ $stats['beneficiaires'] }}</h3>
                    <small class="text-muted">Bénéficiaires</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-warning">{{ $stats['bons'] }}</h3>
                    <small class="text-muted">Bons-matière</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-success">{{ number_format($stats['kg_attributed'], 0, ',', ' ') }} <small class="h6">kg</small></h3>
                    <small class="text-muted">Attribué</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-primary">{{ number_format($stats['kg_delivered'], 0, ',', ' ') }} <small class="h6">kg</small></h3>
                    <small class="text-muted">Livré</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['tickets_pending'] }}</h3>
                    <small class="text-muted">Tickets actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary text-center shadow-sm">
                <div class="card-body">
                    <h3 class="text-secondary">{{ $stats['tickets_used'] }}</h3>
                    <small class="text-muted">Tickets utilisés</small>
                </div>
            </div>
        </div>
    </div>

    <h4 class="h5 mb-3">Alertes actives</h4>
    <div class="card shadow-sm">
        <ul class="list-group list-group-flush">
            @forelse($alertes as $alerte)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    <span class="badge bg-danger me-2">{{ $alerte->type }}</span>
                    {{ $alerte->controle }}
                    @if($alerte->planning) – {{ $alerte->planning->name }}@endif
                    @if($alerte->campaign) – {{ $alerte->campaign->name }}@endif
                </span>
                <small class="text-muted">{{ $alerte->created_at?->format('d/m/Y H:i') }}</small>
            </li>
            @empty
            <li class="list-group-item text-muted">Aucune alerte active.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
