@extends('layouts.admin')

@section('title', 'Distribution d\'aide alimentaire')
@section('page-title', 'Distribution d\'aide alimentaire')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 fw-bold">📦 Distribution d'aide alimentaire</h1>
                        <p class="text-muted mb-0 small">Tableau de bord consolidé des opérations de distribution</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.distribution.events.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Nouvel événement
                        </a>
                        <a href="{{ route('admin.distribution.reports') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>Rapports
                        </a>
                        <a href="{{ route('admin.distribution.alerts') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-bell me-1"></i>Alertes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Stock planifié (kg)</div>
                <div class="h3 fw-bold text-primary">{{ number_format($totalPlanned, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Stock exécuté (kg)</div>
                <div class="h3 fw-bold text-success">{{ number_format($totalExecuted, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Bénéficiaires</div>
                <div class="h3 fw-bold text-info">{{ number_format($totalBeneficiaries, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Kits récupérés</div>
                <div class="h3 fw-bold text-warning">{{ number_format($totalCollected, 0, ',', ' ') }} / {{ number_format($totalTickets, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>

    @if($activeEvent && $report)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">📊 Analyse consolidée — {{ $activeEvent->name }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.distribution.events.show', $activeEvent->id) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i>Détails</a>
                        <a href="{{ route('admin.distribution.reports.print', ['event_id' => $activeEvent->id]) }}" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>Compte rendu PDF</a>
                    </div>
                </div>
                <p class="small text-muted" style="text-align:justify">{{ $report['text']['summary'] }}</p>
                @include('admin.distribution._report_widgets', ['report' => $report, 'event' => $activeEvent, 'print' => false])
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-7 mb-2">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">📋 Événements</h5>
                <table class="table table-sm">
                    <thead>
                        <tr><th>Nom</th><th>Stock initial</th><th>Planifié</th><th>Exécuté</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                        <tr>
                            <td><a href="{{ route('admin.distribution.events.show', $event->id) }}">{{ $event->name }}</a></td>
                            <td>{{ number_format($event->initial_stock_kg, 0, ',', ' ') }} kg</td>
                            <td>{{ number_format($event->total_planned_kg, 0, ',', ' ') }} kg</td>
                            <td>{{ number_format($event->total_executed_kg, 0, ',', ' ') }} kg</td>
                            <td>
                                @if($event->status === 'active') <span class="badge bg-success">Actif</span>
                                @elseif($event->status === 'draft') <span class="badge bg-secondary">Brouillon</span>
                                @else <span class="badge bg-danger">Clôturé</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-5 mb-2">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">⚠️ Alertes actives</h5>
                @if(empty($alerts))
                <p class="text-muted small mb-0">Aucune alerte active.</p>
                @else
                <ul class="list-unstyled">
                    @foreach($alerts as $alert)
                    <li class="mb-2">
                        <span class="badge {{ $alert['level'] === 'critical' ? 'bg-danger' : 'bg-warning' }}">{{ strtoupper($alert['level']) }}</span>
                        <span class="small">{{ $alert['event'] }} — {{ $alert['message'] }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($activeEvent && $report)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endif
@endpush
