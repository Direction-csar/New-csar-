@extends('layouts.admin')

@section('title', 'Tableau de Bord Admin')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de Bord</h1>
        <div class="text-muted">
            <i class="fas fa-calendar-alt"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Cartes de statistiques Workflow -->
    <div class="row mb-4">
        @php
            $wfCounts = [
                'soumise' => \App\Models\PublicRequest::where('workflow_status', 'soumise')->count(),
                'en_revue' => \App\Models\PublicRequest::where('workflow_status', 'en_revue')->count(),
                'document_attente' => \App\Models\PublicRequest::where('workflow_status', 'document_attente')->count(),
                'signee' => \App\Models\PublicRequest::where('workflow_status', 'signee')->count(),
                'scannee' => \App\Models\PublicRequest::where('workflow_status', 'scannee')->count(),
                'validee_dg' => \App\Models\PublicRequest::where('workflow_status', 'validee_dg')->count(),
                'approuvee' => \App\Models\PublicRequest::where('workflow_status', 'approuvee')->count(),
                'rejetee' => \App\Models\PublicRequest::where('workflow_status', 'rejetee')->count(),
            ];
            $totalPending = $wfCounts['soumise'] + $wfCounts['en_revue'] + $wfCounts['document_attente'];
            $totalApproved = $wfCounts['approuvee'] + $wfCounts['validee_dg'];
        @endphp

        <!-- Demandes en attente -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En attente de traitement
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPending }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- En revue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                En revue / Signature
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $wfCounts['en_revue'] + $wfCounts['signee'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-signature fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validées / Approuvées -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Validées / Approuvées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalApproved }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doublons -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Doublons détectés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\PublicRequest::where('is_duplicate', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clone fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow visuel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-stream me-2"></i>Workflow des Demandes</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                        @php
                            $workflowCards = [
                                ['key' => 'soumise', 'label' => 'Soumise', 'icon' => 'fa-paper-plane', 'color' => 'secondary', 'count' => $wfCounts['soumise']],
                                ['key' => 'en_revue', 'label' => 'En revue', 'icon' => 'fa-eye', 'color' => 'info', 'count' => $wfCounts['en_revue']],
                                ['key' => 'document_attente', 'label' => 'Attente doc', 'icon' => 'fa-file-alt', 'color' => 'warning', 'count' => $wfCounts['document_attente']],
                                ['key' => 'signee', 'label' => 'Signée', 'icon' => 'fa-signature', 'color' => 'primary', 'count' => $wfCounts['signee']],
                                ['key' => 'scannee', 'label' => 'Scannée', 'icon' => 'fa-file-pdf', 'color' => 'info', 'count' => $wfCounts['scannee']],
                                ['key' => 'validee_dg', 'label' => 'Validée DG', 'icon' => 'fa-stamp', 'color' => 'success', 'count' => $wfCounts['validee_dg']],
                                ['key' => 'approuvee', 'label' => 'Approuvée', 'icon' => 'fa-check-circle', 'color' => 'success', 'count' => $wfCounts['approuvee']],
                            ];
                        @endphp
                        @foreach($workflowCards as $i => $wf)
                        <div class="text-center" style="flex: 1; min-width: 90px;">
                            <a href="{{ route('admin.demandes.index') }}" class="text-decoration-none">
                                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1"
                                     style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--bs-{{ $wf['color'] }}), var(--bs-{{ $wf['color'] }}-dark, var(--bs-{{ $wf['color'] }}))); color: #fff; font-size: 1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                    <i class="fas {{ $wf['icon'] }}"></i>
                                </div>
                                <div class="small fw-bold text-dark" style="font-size: 0.75rem;">{{ $wf['label'] }}</div>
                                <div class="badge bg-{{ $wf['color'] }}" style="font-size: 0.65rem;">{{ $wf['count'] }}</div>
                            </a>
                        </div>
                        @if($i < count($workflowCards) - 1)
                        <div class="flex-fill align-self-start d-none d-md-block" style="height: 3px; background: linear-gradient(90deg, var(--bs-{{ $wf['color'] }}), var(--bs-{{ $workflowCards[$i+1]['color'] }})); margin-top: 22px; border-radius: 2px; max-width: 40px;"></div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique de répartition workflow -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Répartition par statut</h6>
                </div>
                <div class="card-body">
                    <canvas id="workflowChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Vue d'ensemble</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="workflowPieChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="row">
        <!-- Demandes récentes -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Demandes Récentes</h6>
                    <a href="{{ route('admin.demandes.index') }}" class="btn btn-sm btn-primary">
                        Voir toutes
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $recentRequests = \App\Models\PublicRequest::orderBy('created_at', 'desc')->limit(10)->get();
                    @endphp
                    @if($recentRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Demandeur</th>
                                        <th>Type</th>
                                        <th>Workflow</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequests as $demande)
                                    <tr class="{{ $demande->is_duplicate ? 'table-danger' : '' }}">
                                        <td><strong class="text-primary">{{ $demande->tracking_code }}</strong></td>
                                        <td>{{ $demande->full_name }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst($demande->type ?? 'Autre') }}
                                            </span>
                                        </td>
                                        <td>
                                            {!! $demande->workflow_status_badge !!}
                                            @if($demande->is_duplicate)
                                                <span class="badge badge-danger ml-1" style="font-size: 0.6rem;">Doublon</span>
                                            @endif
                                        </td>
                                        <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.demandes.show', $demande->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.demandes.edit', $demande->id) }}"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">Aucune demande récente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.demandes.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-primary"></i>
                            Gérer les Demandes
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users text-info"></i>
                            Gérer les Utilisateurs
                        </a>
                        <a href="{{ route('admin.warehouses.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-warehouse text-success"></i>
                            Gérer les Entrepôts
                        </a>
                        <a href="{{ route('admin.stocks.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-boxes text-warning"></i>
                            Gérer les Stocks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @php
            $chartCounts = [
                'soumise' => \App\Models\PublicRequest::where('workflow_status', 'soumise')->count(),
                'en_revue' => \App\Models\PublicRequest::where('workflow_status', 'en_revue')->count(),
                'document_attente' => \App\Models\PublicRequest::where('workflow_status', 'document_attente')->count(),
                'signee' => \App\Models\PublicRequest::where('workflow_status', 'signee')->count(),
                'scannee' => \App\Models\PublicRequest::where('workflow_status', 'scannee')->count(),
                'validee_dg' => \App\Models\PublicRequest::where('workflow_status', 'validee_dg')->count(),
                'approuvee' => \App\Models\PublicRequest::where('workflow_status', 'approuvee')->count(),
                'rejetee' => \App\Models\PublicRequest::where('workflow_status', 'rejetee')->count(),
                'cloturee' => \App\Models\PublicRequest::where('workflow_status', 'cloturee')->count(),
            ];
            $chartLabels = ['Soumise', 'En revue', 'Attente doc', 'Signée', 'Scannée', 'Validée DG', 'Approuvée', 'Rejetée', 'Clôturée'];
            $chartValues = array_values($chartCounts);
            $chartColors = ['#6c757d', '#0dcaf0', '#ffc107', '#0d6efd', '#0dcaf0', '#198754', '#198754', '#dc3545', '#212529'];
        @endphp

        const labels = @json($chartLabels);
        const data = @json($chartValues);
        const colors = @json($chartColors);

        if (document.getElementById('workflowChart')) {
            new Chart(document.getElementById('workflowChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre de demandes',
                        data: data,
                        backgroundColor: colors.map(c => c + 'cc'),
                        borderColor: colors,
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' demande(s)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        if (document.getElementById('workflowPieChart')) {
            const pieData = [
                data[0] + data[1] + data[2],
                data[3] + data[4],
                data[5] + data[6],
                data[7],
                data[8]
            ];
            const pieLabels = ['En cours', 'Documents', 'Validées', 'Rejetées', 'Clôturées'];
            const pieColors = ['#0dcaf0', '#0d6efd', '#198754', '#dc3545', '#212529'];

            new Chart(document.getElementById('workflowPieChart'), {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieColors.map(c => c + 'cc'),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 11 }, padding: 15 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    return context.label + ': ' + context.parsed + ' (' + pct + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    });
</script>
@endpush






























