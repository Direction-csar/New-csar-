@extends('layouts.admin')

@section('title', $event->name)
@section('page-title', $event->name)

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 fw-bold">📅 {{ $event->name }}</h1>
                        <p class="text-muted mb-0 small">{{ $event->location ?? '' }} — {{ optional($event->start_date)->format('d/m/Y') }} au {{ optional($event->end_date)->format('d/m/Y') ?? '...' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.distribution.events.edit', $event->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                        <a href="{{ route('admin.distribution.events.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Stock initial (kg)</div>
                <div class="h4 fw-bold">{{ number_format($event->initial_stock_kg, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Total planifié (kg)</div>
                <div class="h4 fw-bold text-primary">{{ number_format($event->total_planned_kg, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Total exécuté (kg)</div>
                <div class="h4 fw-bold text-success">{{ number_format($event->total_executed_kg, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card-modern p-3 text-center">
                <div class="text-muted small">Stock restant (kg)</div>
                <div class="h4 fw-bold {{ $event->remaining_stock_kg < 0 ? 'text-danger' : 'text-warning' }}">{{ number_format($event->remaining_stock_kg, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">📊 Évolution du stock</h5>
                <canvas id="stockChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">📋 Plannings ({{ $event->plannings->count() }})</h5>
                    <a href="{{ route('admin.distribution.plannings.create') }}?event_id={{ $event->id }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Ajouter un planning</a>
                </div>
                <table class="table table-sm">
                    <thead>
                        <tr><th>Planning</th><th>Quota (kg)</th><th>Exécuté (kg)</th><th>En cours</th><th>Bénéf.</th><th>Tickets</th><th>Taux</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @foreach($event->plannings as $p)
                        <tr>
                            <td><a href="{{ route('admin.distribution.plannings.show', $p->id) }}">{{ $p->name }}</a></td>
                            <td>{{ number_format($p->planned_quota_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($p->executed_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($p->in_progress_kg, 0, ',', ' ') }}</td>
                            <td>{{ $p->beneficiaries_count }}</td>
                            <td>{{ $p->tickets_count }}</td>
                            <td>
                                <span class="badge {{ $p->alert_level === 'ok' ? 'bg-success' : ($p->alert_level === 'watch' ? 'bg-info' : ($p->alert_level === 'delay' ? 'bg-warning' : 'bg-danger')) }}">{{ $p->execution_rate }}%</span>
                            </td>
                            <td><span class="badge bg-secondary">{{ $p->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.distribution.events.status', $event->id) }}" method="POST" class="d-inline">
                    @csrf
                    <label class="form-label small fw-bold me-2">Changer le statut:</label>
                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="draft" {{ $event->status === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="active" {{ $event->status === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="closed" {{ $event->status === 'closed' ? 'selected' : '' }}>Clôturé</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    const labels = @json(array_map(fn($e) => $e['label'], $stockEvolution));
    const values = @json(array_map(fn($e) => (float) $e['value'], $stockEvolution));
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
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
