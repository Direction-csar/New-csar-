@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')

@section('title', 'Tableau de Bord Ressources Humaines')
@section('page-title', 'Tableau de Bord Ressources Humaines')

@section('styles')
<style>
    .hr-dashboard { font-family: 'Segoe UI', sans-serif; }
    .hr-title-bar { background: white; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; }
    .hr-title-bar h1 { font-size: 1.8rem; font-weight: 800; color: #1e293b; margin: 0; }
    .kpi-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; border: 1px solid #e5e7eb; transition: transform 0.2s; }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
    .kpi-icon { width: 56px; height: 56px; margin: 0 auto 12px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: white; }
    .kpi-icon.effectif    { background: linear-gradient(135deg, #6b7280, #4b5563); }
    .kpi-icon.demissions  { background: linear-gradient(135deg, #f97316, #ea580c); }
    .kpi-icon.femmes      { background: linear-gradient(135deg, #ec4899, #db2777); }
    .kpi-icon.hommes      { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .kpi-icon.age         { background: linear-gradient(135deg, #10b981, #047857); }
    .kpi-label { font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .kpi-value { font-size: 2rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .kpi-suffix { font-size: 1rem; color: #64748b; font-weight: 600; }
    .chart-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; height: 100%; }
    .chart-card-title { font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f1f5f9; }
    .list-mini { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .list-mini-title { font-size: 0.85rem; font-weight: 700; color: #047857; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px; }
    .list-mini-item { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.82rem; border-bottom: 1px dashed #f1f5f9; }
    .list-mini-item:last-child { border-bottom: none; }
    .list-mini-item .name { color: #1e293b; font-weight: 500; }
    .list-mini-item .value { color: #6b7280; font-size: 0.78rem; }
    .badge-anciennete { background: #d1fae5; color: #047857; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
</style>
@endsection

@section('content')
<div class="hr-dashboard">

    {{-- Accès rapide --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.drh.personnel.index') }}" class="text-decoration-none">
                <div class="chart-card d-flex align-items-center gap-3" style="border-left: 4px solid #047857; transition: transform 0.2s;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #10b981, #047857); color: white; font-size: 1.4rem; flex-shrink: 0;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Personnel</div>
                        <div class="text-muted small">Gérer les agents</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.drh.tabaski.index') }}" class="text-decoration-none">
                <div class="chart-card d-flex align-items-center gap-3" style="border-left: 4px solid #f59e0b; transition: transform 0.2s;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; font-size: 1.4rem; flex-shrink: 0;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Avances Tabaski</div>
                        <div class="text-muted small">Gestion des avances</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.drh.health-survey.index') }}" class="text-decoration-none">
                <div class="chart-card d-flex align-items-center gap-3" style="border-left: 4px solid #0d6efd; transition: transform 0.2s;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: linear-gradient(135deg, #3b82f6, #0d6efd); color: white; font-size: 1.4rem; flex-shrink: 0;">
                        <i class="fas fa-poll-h"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Enquête Assurance Maladie</div>
                        <div class="text-muted small">Suivi des réponses</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md col-6">
            <div class="kpi-card">
                <div class="kpi-icon effectif"><i class="fas fa-users"></i></div>
                <div class="kpi-label">Effectif</div>
                <div class="kpi-value">{{ $stats['effectif'] }}</div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="kpi-card">
                <div class="kpi-icon demissions"><i class="fas fa-door-open"></i></div>
                <div class="kpi-label">Démissions</div>
                <div class="kpi-value">{{ $stats['demissions'] }}</div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="kpi-card">
                <div class="kpi-icon femmes"><i class="fas fa-female"></i></div>
                <div class="kpi-label">Femmes</div>
                <div class="kpi-value">{{ $stats['pct_femmes'] }}<span class="kpi-suffix">%</span></div>
                <div class="text-muted small mt-1">{{ $stats['femmes'] }} agents</div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="kpi-card">
                <div class="kpi-icon hommes"><i class="fas fa-male"></i></div>
                <div class="kpi-label">Hommes</div>
                <div class="kpi-value">{{ $stats['pct_hommes'] }}<span class="kpi-suffix">%</span></div>
                <div class="text-muted small mt-1">{{ $stats['hommes'] }} agents</div>
            </div>
        </div>
        <div class="col-md col-12">
            <div class="kpi-card">
                <div class="kpi-icon age"><i class="fas fa-user-clock"></i></div>
                <div class="kpi-label">Âge Moyen</div>
                <div class="kpi-value">{{ $stats['age_moyen'] }}<span class="kpi-suffix">ans</span></div>
            </div>
        </div>
    </div>

    {{-- Graphiques principaux --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-card-title"><i class="fas fa-chart-line text-success me-1"></i> Effectif — Évolution recrutements</div>
                <canvas id="chartEvolution" height="220"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-card-title"><i class="fas fa-birthday-cake text-warning me-1"></i> Tranche d'âges</div>
                <canvas id="chartTranchesAge" height="220"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-card-title"><i class="fas fa-medal text-primary me-1"></i> Ancienneté</div>
                <canvas id="chartAnciennete" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- Listes mini + graphiques bas --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3">
            <div class="list-mini">
                <div class="list-mini-title">
                    <i class="fas fa-award text-success"></i> TOP 5 PLUS ANCIENS
                </div>
                @forelse($plusAnciens as $p)
                    <div class="list-mini-item">
                        <span class="name">{{ \Illuminate\Support\Str::limit($p->prenoms_nom, 22) }}</span>
                        <span class="value badge-anciennete">{{ $p->date_recrutement_csar?->format('Y') }}</span>
                    </div>
                @empty
                    <div class="text-muted small">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-3">
            <div class="list-mini">
                <div class="list-mini-title">
                    <i class="fas fa-bolt text-warning"></i> TOP 5 PLUS RÉCENTS
                </div>
                @forelse($plusRecents as $p)
                    <div class="list-mini-item">
                        <span class="name">{{ \Illuminate\Support\Str::limit($p->prenoms_nom, 22) }}</span>
                        <span class="value">{{ $p->date_recrutement_csar?->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <div class="text-muted small">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-3">
            <div class="list-mini">
                <div class="list-mini-title">
                    <i class="fas fa-briefcase text-primary"></i> POSTES (TOP)
                </div>
                @forelse($parPoste as $poste => $count)
                    <div class="list-mini-item">
                        <span class="name">{{ \Illuminate\Support\Str::limit($poste, 22) }}</span>
                        <span class="value"><strong>{{ $count }}</strong></span>
                    </div>
                @empty
                    <div class="text-muted small">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-3">
            <div class="list-mini">
                <div class="list-mini-title">
                    <i class="fas fa-id-badge text-info"></i> STATUTS
                </div>
                @forelse($parStatut as $statut => $count)
                    <div class="list-mini-item">
                        <span class="name">{{ $statut }}</span>
                        <span class="value"><strong>{{ $count }}</strong></span>
                    </div>
                @empty
                    <div class="text-muted small">Aucune donnée</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Graphiques secondaires : direction + région --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="chart-card-title"><i class="fas fa-sitemap text-success me-1"></i> Effectif par Direction</div>
                <canvas id="chartDirections" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="chart-card">
                <div class="chart-card-title"><i class="fas fa-map-marked-alt text-danger me-1"></i> Effectif par Région</div>
                <canvas id="chartRegions" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Enquête Assurance Maladie --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="chart-card" style="border-left: 4px solid #0d6efd;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="chart-card-title" style="border-bottom: none; margin-bottom: 0;">
                            <i class="fas fa-heartbeat text-primary me-2"></i> Enquête Assurance Maladie
                        </div>
                        <p class="text-muted small mb-0 mt-1">Suivez les réponses des agents au questionnaire assurance maladie.</p>
                    </div>
                    <a href="{{ route('admin.drh.health-survey.index') }}" class="btn btn-primary">
                        <i class="fas fa-poll me-2"></i> Voir les réponses
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Action --}}
    <div class="text-center mb-4">
        <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-success btn-lg">
            <i class="fas fa-users me-2"></i> Gérer le personnel ({{ $stats['effectif'] }} agents)
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'Segoe UI', sans-serif";
    Chart.defaults.font.size = 11;

    // 1. Évolution recrutements
    new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($evolutionRecrutements)) !!},
            datasets: [{
                label: 'Recrutements',
                data: {!! json_encode(array_values($evolutionRecrutements)) !!},
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#f97316',
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 2. Tranches d'âge (bar)
    new Chart(document.getElementById('chartTranchesAge'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($tranchesAge)) !!},
            datasets: [{
                label: 'Effectif',
                data: {!! json_encode(array_values($tranchesAge)) !!},
                backgroundColor: ['#f97316','#fb923c','#fdba74','#fed7aa','#ffedd5'],
                borderRadius: 6,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 3. Ancienneté (bar horizontale)
    new Chart(document.getElementById('chartAnciennete'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($anciennete)) !!},
            datasets: [{
                label: 'Agents',
                data: {!! json_encode(array_values($anciennete)) !!},
                backgroundColor: ['#3b82f6','#60a5fa','#93c5fd','#bfdbfe','#dbeafe'],
                borderRadius: 6,
            }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 4. Directions
    new Chart(document.getElementById('chartDirections'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($parDirection)) !!},
            datasets: [{
                label: 'Agents',
                data: {!! json_encode(array_values($parDirection)) !!},
                backgroundColor: '#10b981',
                borderRadius: 6,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 5. Régions (doughnut)
    new Chart(document.getElementById('chartRegions'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($parRegion)) !!},
            datasets: [{
                data: {!! json_encode(array_values($parRegion)) !!},
                backgroundColor: ['#10b981','#3b82f6','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#ef4444','#84cc16','#f97316','#14b8a6','#a855f7','#22c55e']
            }]
        },
        options: { plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
    });
</script>
@endsection
