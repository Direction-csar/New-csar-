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

    @if($activeEvent)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">📊 Évolution du stock — {{ $activeEvent->name }}</h5>
                <canvas id="stockChart" height="80"></canvas>
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
@if($activeEvent)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    @php
        $evolution = [];
        $remaining = (float) $activeEvent->initial_stock_kg;
        $evolution[] = ['label' => 'Stock initial', 'value' => $remaining];
        foreach ($activeEvent->plannings as $p) {
            $remaining -= (float) $p->executed_kg;
            $evolution[] = ['label' => $p->name, 'value' => $remaining];
        }
        $evolution[] = ['label' => 'Projection', 'value' => $remaining - ($activeEvent->total_planned_kg - $activeEvent->total_executed_kg)];
    @endphp
    const ctx = document.getElementById('stockChart').getContext('2d');
    const labels = @json(array_map(fn($e) => $e['label'], $evolution));
    const values = @json(array_map(fn($e) => (float) $e['value'], $evolution));
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Stock restant (kg)',
                data: values,
                borderColor: '#fd7e14',
                backgroundColor: 'rgba(253, 126, 20, 0.1)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endif
@endpush
