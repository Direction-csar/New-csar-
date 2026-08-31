@extends('layouts.admin')

@section('title', 'Rapports de distribution')
@section('page-title', 'Rapports consolidés')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📊 Rapports de distribution</h1>
                    <div class="d-flex gap-2">
                        @if($event)
                        <a href="{{ route('admin.distribution.reports.export', ['event_id' => $event->id]) }}" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a>
                        @endif
                        <a href="{{ route('admin.distribution.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Événement</label>
                        <select class="form-select form-select-sm" name="event_id" onchange="this.form.submit()">
                            <option value="">Sélectionner</option>
                            @foreach($events as $id => $name)
                            <option value="{{ $id }}" {{ ($event?->id ?? null) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($event)
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Stock initial (kg)</div><div class="h5 fw-bold">{{ number_format($event->initial_stock_kg, 0, ',', ' ') }}</div></div></div>
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Total planifié (kg)</div><div class="h5 fw-bold text-primary">{{ number_format($event->total_planned_kg, 0, ',', ' ') }}</div></div></div>
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Total exécuté (kg)</div><div class="h5 fw-bold text-success">{{ number_format($event->total_executed_kg, 0, ',', ' ') }}</div></div></div>
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Stock restant (kg)</div><div class="h5 fw-bold {{ $event->remaining_stock_kg < 0 ? 'text-danger' : 'text-warning' }}">{{ number_format($event->remaining_stock_kg, 0, ',', ' ') }}</div></div></div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">📈 Évolution du stock</h5>
                <canvas id="stockChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">📋 État d'avancement par planning</h5>
                <table class="table table-sm">
                    <thead><tr><th>Planning</th><th>Planifié (kg)</th><th>Exécuté (kg)</th><th>En cours (kg)</th><th>Bénéf.</th><th>Tickets</th><th>Taux</th><th>Alerte</th></tr></thead>
                    <tbody>
                        @foreach($plannings as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ number_format($p->planned_quota_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($p->executed_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($p->in_progress_kg, 0, ',', ' ') }}</td>
                            <td>{{ $p->beneficiaries_count }}</td>
                            <td>{{ $p->tickets_count }}</td>
                            <td>{{ $p->execution_rate }}%</td>
                            <td>
                                @if($p->alert_level === 'ok') <span class="badge bg-success">OK</span>
                                @elseif($p->alert_level === 'watch') <span class="badge bg-info">À surveiller</span>
                                @elseif($p->alert_level === 'delay') <span class="badge bg-warning">Retard</span>
                                @else <span class="badge bg-danger">Non démarré</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-active fw-bold">
                            <td>TOTAL</td>
                            <td>{{ number_format($event->total_planned_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($event->total_executed_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($event->total_planned_kg - $event->total_executed_kg, 0, ',', ' ') }}</td>
                            <td>{{ $event->total_beneficiaries }}</td>
                            <td>{{ $event->total_tickets_issued }}</td>
                            <td>{{ $event->initial_stock_kg > 0 ? round($event->total_executed_kg / $event->initial_stock_kg * 100, 1) : 0 }}%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">⚠️ Alertes automatiques</h5>
                @if(empty($alerts))
                <p class="text-muted small mb-0">Aucune alerte.</p>
                @else
                <ul class="list-unstyled">
                    @foreach($alerts as $alert)
                    <li class="mb-2"><span class="badge {{ $alert['level'] === 'critical' ? 'bg-danger' : 'bg-warning' }}">{{ strtoupper($alert['level']) }}</span> <span class="small">{{ $alert['message'] }}</span></li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">🔍 Doublons détectés</h5>
                @if(empty($duplicates))
                <p class="text-muted small mb-0">Aucun doublon détecté.</p>
                @else
                <ul class="list-unstyled">
                    @foreach($duplicates as $dup)
                    <li class="mb-2"><span class="badge bg-danger">DOUBLON</span> <span class="small">{{ $dup['message'] }}</span></li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3 text-center text-muted py-4">Sélectionnez un événement pour voir le rapport.</div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if($event && !empty($stockEvolution))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    const labels = @json(array_map(fn($e) => $e['label'], $stockEvolution));
    const values = @json(array_map(fn($e) => (float) $e['value'], $stockEvolution));
    new Chart(ctx, {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'Stock (kg)', data: values, borderColor: '#fd7e14', backgroundColor: 'rgba(253,126,20,0.1)', fill: true, tension: 0.3 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
</script>
@endif
@endpush
