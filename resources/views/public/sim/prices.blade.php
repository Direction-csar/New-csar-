@extends('layouts.public')

@section('title', 'SIM CSAR - Prix validés')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Prix validés des produits</h1>
            <p class="text-muted mb-0">Visualisation des prix moyens validés par l'administration du SIM/CSAR.</p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="number" name="year" class="form-control" value="{{ $year }}" min="2000" max="2100">
            <button class="btn btn-success">Filtrer</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        @forelse($prices['cards'] as $card)
            <div class="col-6 col-md-4 col-lg">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="text-muted small">{{ $card['label'] }}</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($card['value'], 0, ',', ' ') }}</div>
                        <div class="small">FCFA</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">Aucune donnée validée disponible pour cette année.</div>
            </div>
        @endforelse
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Évolution mensuelle des prix</h5>
                </div>
                <div class="card-body">
                    <canvas id="lineChart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Comparaison annuelle</h5>
                </div>
                <div class="card-body">
                    <canvas id="barChart" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lineConfig = @json($prices['line']);
    const barConfig = @json($prices['bar']);
    const palette = ['#2563eb', '#16a34a', '#ca8a04', '#dc2626', '#7c3aed', '#0ea5e9'];

    const lineDatasets = (lineConfig.datasets || []).map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data,
        borderColor: palette[index % palette.length],
        backgroundColor: palette[index % palette.length],
        tension: 0.3
    }));

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: lineConfig.labels || [],
            datasets: lineDatasets
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } }
        }
    });

    const barDatasets = (barConfig.datasets || []).map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data,
        backgroundColor: palette[index % palette.length]
    }));

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: barConfig.labels || [],
            datasets: barDatasets
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } }
        }
    });
});
</script>
@endsection
