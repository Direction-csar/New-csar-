@extends('layouts.drh-portal')

@section('title', 'Tableau de Bord Ressources Humaines')
@section('page-title', 'Tableau de Bord Ressources Humaines')

@section('styles')
<style>
    .db-header { background: linear-gradient(135deg, #5a9e3d, #7cb342); border-radius: 12px; padding: 16px 24px; color: white; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .db-header h2 { margin: 0; font-weight: 700; font-size: 1.4rem; }
    .db-tabs { display: flex; gap: 6px; }
    .db-tab { background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .db-tab:hover, .db-tab.active { background: white; color: #5a9e3d; }

    .kpi-yellow { background: #f5c518; border-radius: 12px; padding: 20px; color: #333; text-align: center; }
    .kpi-yellow .kpi-title { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
    .kpi-yellow .kpi-value { font-size: 1.8rem; font-weight: 800; color: #e53935; }

    .kpi-genre { background: #5a9e3d; border-radius: 12px; padding: 16px; color: white; text-align: center; }
    .kpi-genre h6 { font-size: 0.8rem; font-weight: 600; margin-bottom: 10px; }
    .genre-box { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; font-weight: 700; font-size: 1rem; margin: 0 4px; }
    .genre-f { background: #e91e63; }
    .genre-m { background: #0277bd; }

    .quick-btn { border-radius: 8px; padding: 10px; font-size: 0.78rem; font-weight: 600; text-align: center; color: white; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; transition: transform 0.15s; }
    .quick-btn:hover { transform: translateY(-2px); color: white; }
    .btn-green { background: #5a9e3d; }
    .btn-blue  { background: #1e88e5; }
    .btn-teal  { background: #00897b; }

    .stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; border: 1px solid #e5e7eb; }
    .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 1.3rem; color: white; }
    .stat-icon.purple { background: linear-gradient(135deg, #7e57c2, #5e35b1); }
    .stat-icon.pink { background: linear-gradient(135deg, #ec407a, #c2185b); }
    .stat-icon.orange { background: linear-gradient(135deg, #ff7043, #e64a19); }
    .stat-icon.teal { background: linear-gradient(135deg, #26a69a, #00695c); }
    .stat-number { font-size: 2.2rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .stat-label { font-size: 0.8rem; color: #64748b; font-weight: 600; margin-top: 4px; }

    .donut-wrap { position: relative; width: 90px; height: 90px; margin: 0 auto; }
    .donut-wrap .donut-text { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; color: #1e293b; }

    .doc-section { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; margin-bottom: 20px; }
    .doc-section-header { background: linear-gradient(135deg, #5a9e3d, #7cb342); color: white; padding: 12px 20px; font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 10px; }
    .doc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; padding: 16px; }
    @media (max-width: 992px) { .doc-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .doc-grid { grid-template-columns: 1fr; } }
    .doc-btn { background: linear-gradient(135deg, #ec407a, #c2185b); color: white; border: none; border-radius: 8px; padding: 12px 8px; font-size: 0.78rem; font-weight: 600; text-align: center; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; display: flex; flex-direction: column; align-items: center; gap: 6px; text-decoration: none; }
    .doc-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(236,64,114,0.35); color: white; }
    .doc-btn i { font-size: 1.2rem; }

    .info-card { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; height: 100%; }
    .info-card-header { font-size: 0.9rem; font-weight: 700; color: #1e293b; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; gap: 8px; }
    .info-card-header i { color: #f59e0b; }
    .info-list-item { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.82rem; border-bottom: 1px dashed #f1f5f9; }
    .info-list-item:last-child { border-bottom: none; }
    .no-data { color: #9ca3af; font-style: italic; font-size: 0.85rem; text-align: center; padding: 20px 0; }

    .filter-badge { display: inline-block; background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 500; margin: 2px; }
</style>
@endsection

@section('content')

@php
$currentRoute = $currentRoute ?? request()->route()?->getName() ?? '';
@endphp

{{-- Header avec tabs --}}
<div class="db-header">
    <h2><i class="fas fa-chart-line me-2"></i>Tableau de bord RH</h2>
    <div class="db-tabs">
        <a href="{{ route('admin.drh.dashboard') }}" class="db-tab {{ $currentRoute === 'admin.drh.dashboard' ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.drh.formation') }}" class="db-tab {{ $currentRoute === 'admin.drh.formation' ? 'active' : '' }}">Formation</a>
        <a href="{{ route('admin.drh.recrutement') }}" class="db-tab {{ $currentRoute === 'admin.drh.recrutement' ? 'active' : '' }}">Recrutement</a>
        <a href="{{ route('admin.drh.relations') }}" class="db-tab {{ $currentRoute === 'admin.drh.relations' ? 'active' : '' }}">Relations</a>
    </div>
</div>

{{-- Row 1 : KPIs principaux + Quick actions --}}
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="kpi-yellow h-100 d-flex flex-column justify-content-center">
            <div class="kpi-title"><i class="fas fa-coins me-1"></i> Masse salariale</div>
            <div class="kpi-value">{{ number_format($stats['masse_salariale'], 0, ',', ' ') }} FCFA</div>
        </div>
    </div>
    <div class="col-lg-2 col-md-6">
        <div class="kpi-genre h-100 d-flex flex-column justify-content-center">
            <h6><i class="fas fa-venus-mars me-1"></i> Genre</h6>
            <div>
                <span class="genre-box genre-f" title="Femmes">F<br><small style="font-size:0.65rem">{{ $stats['femmes'] }}</small></span>
                <span class="genre-box genre-m" title="Hommes">M<br><small style="font-size:0.65rem">{{ $stats['hommes'] }}</small></span>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="row g-2 h-100">
            <div class="col-4"><a href="{{ route('admin.drh.personnel.index') }}" class="quick-btn btn-green h-100"><i class="fas fa-users"></i> Base agents</a></div>
            <div class="col-4"><a href="{{ route('admin.drh.tabaski.index') }}" class="quick-btn btn-green h-100"><i class="fas fa-hand-holding-usd"></i> Pale</a></div>
            <div class="col-4"><a href="{{ route('admin.drh.tabaski.index') }}" class="quick-btn btn-green h-100"><i class="fas fa-mosque"></i> Tabaski</a></div>
            <div class="col-4"><a href="{{ route('admin.drh.health-survey.index') }}" class="quick-btn btn-blue h-100"><i class="fas fa-heartbeat"></i> Assurance</a></div>
            <div class="col-4"><a href="{{ route('admin.drh.documents') }}" class="quick-btn btn-blue h-100"><i class="fas fa-file-alt"></i> Documents</a></div>
            <div class="col-4"><a href="{{ route('admin.drh.personnel.index') }}" class="quick-btn btn-teal h-100"><i class="fas fa-user-check"></i> Agents paie</a></div>
        </div>
    </div>
</div>

{{-- Row 2 : Effectif + CDI/CDD/INTÉRIM --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="stat-icon purple"><i class="fas fa-users"></i></div>
            <div class="stat-number">{{ $stats['effectif'] }}</div>
            <div class="stat-label">Nombre d'employés</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="donut-wrap">
                <svg viewBox="0 0 36 36" width="90" height="90">
                    @php $pctCDI = $stats['effectif'] > 0 ? round($stats['cdi']/$stats['effectif']*100) : 0; @endphp
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#ec407a" stroke-width="3" stroke-dasharray="{{ $pctCDI }}, 100"/>
                </svg>
                <div class="donut-text">{{ $pctCDI }}%</div>
            </div>
            <div class="stat-label mt-2">Total <strong style="color:#ec407a">CDI</strong></div>
            <div class="text-muted small">{{ $stats['cdi'] }} agent(s)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="donut-wrap">
                <svg viewBox="0 0 36 36" width="90" height="90">
                    @php $pctCDD = $stats['effectif'] > 0 ? round($stats['cdd']/$stats['effectif']*100) : 0; @endphp
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#ff7043" stroke-width="3" stroke-dasharray="{{ $pctCDD }}, 100"/>
                </svg>
                <div class="donut-text">{{ $pctCDD }}%</div>
            </div>
            <div class="stat-label mt-2">Total <strong style="color:#ff7043">CDD</strong></div>
            <div class="text-muted small">{{ $stats['cdd'] }} agent(s)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card h-100">
            <div class="donut-wrap">
                <svg viewBox="0 0 36 36" width="90" height="90">
                    @php $pctInt = $stats['effectif'] > 0 ? round($stats['interim']/$stats['effectif']*100) : 0; @endphp
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#ffa726" stroke-width="3" stroke-dasharray="{{ $pctInt }}, 100"/>
                </svg>
                <div class="donut-text">{{ $pctInt }}%</div>
            </div>
            <div class="stat-label mt-2">Total <strong style="color:#ffa726">INTÉRIM</strong></div>
            <div class="text-muted small">{{ $stats['interim'] }} agent(s)</div>
        </div>
    </div>
</div>

{{-- Documents RH --}}
<div class="doc-section mb-4">
    <div class="doc-section-header">
        <i class="fas fa-folder-open"></i> Documents RH
    </div>
    <div class="doc-grid">
        @foreach($documentTypes as $doc)
            <a href="{{ route('admin.drh.documents.form', $doc['slug']) }}" class="doc-btn">
                <i class="fas {{ $doc['icon'] }}"></i>
                <span>{{ $doc['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- Filtres Départements & Services --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-building"></i> Départements</div>
            @forelse($departments as $dept)
                <span class="filter-badge">{{ $dept }}</span>
            @empty
                <div class="no-data">Aucun département renseigné</div>
            @endforelse
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-briefcase"></i> Services</div>
            @forelse($services as $svc)
                <span class="filter-badge">{{ $svc }}</span>
            @empty
                <div class="no-data">Aucun service renseigné</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Bottom cards : stats diverses --}}
<div class="row g-3 mb-4">
    {{-- Fins de contrats --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Fins de contrats <small class="text-muted">90 jours</small></div>
            @if($stats['fins_contrats'] > 0)
                <div class="text-center py-3">
                    <div class="stat-number" style="font-size:1.8rem">{{ $stats['fins_contrats'] }}</div>
                    <div class="text-muted small">contrat(s) se terminant bientôt</div>
                </div>
            @else
                <div class="no-data">Aucune fin de contrat proche</div>
            @endif
        </div>
    </div>

    {{-- Solde congé --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Solde congé <small class="text-muted">en jours</small></div>
            <div class="text-center py-2">
                <div class="donut-wrap" style="width:70px;height:70px">
                    <svg viewBox="0 0 36 36" width="70" height="70">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#3b82f6" stroke-width="3" stroke-dasharray="0,100"/>
                    </svg>
                    <div class="donut-text" style="font-size:0.9rem">0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Employés par genre --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Employés par genre <small class="text-muted">effectif total</small></div>
            <div style="height:120px">
                <canvas id="chartGenre"></canvas>
            </div>
        </div>
    </div>

    {{-- Tranche d'âge --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Tranche d'âge <small class="text-muted">par genre</small></div>
            <div style="height:120px">
                <canvas id="chartAge"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- AT par département --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> AT par département <small class="text-muted">en jours</small></div>
            <div class="no-data">Donnée indisponible</div>
        </div>
    </div>

    {{-- Solde récupération --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Solde récupération <small class="text-muted">en jours</small></div>
            <div class="text-center py-2">
                <div class="donut-wrap" style="width:70px;height:70px">
                    <svg viewBox="0 0 36 36" width="70" height="70">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#3b82f6" stroke-width="3" stroke-dasharray="0,100"/>
                    </svg>
                    <div class="donut-text" style="font-size:0.9rem">0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Retraités à venir --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Retraités à venir <small class="text-muted">âge légal : 60 ans</small></div>
            @if($stats['retraites'] > 0)
                <div class="text-center py-3">
                    <div class="stat-number" style="font-size:1.8rem">{{ $stats['retraites'] }}</div>
                    <div class="text-muted small">agent(s) proche(s) du départ</div>
                </div>
            @else
                <div class="no-data">Aucun départ proche</div>
            @endif
        </div>
    </div>

    {{-- Ancienneté --}}
    <div class="col-lg-3 col-md-6">
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-star"></i> Ancienneté <small class="text-muted">par genre</small></div>
            <div style="height:120px">
                <canvas id="chartAnciennete"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'Segoe UI', sans-serif";
    Chart.defaults.font.size = 10;

    // Genre
    new Chart(document.getElementById('chartGenre'), {
        type: 'bar',
        data: {
            labels: ['Femme', 'Homme'],
            datasets: [{
                label: 'Effectif',
                data: [{{ $stats['femmes'] }}, {{ $stats['hommes'] }}],
                backgroundColor: ['#ec407a', '#0d9488'],
                borderRadius: 4,
            }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }, maintainAspectRatio: false }
    });

    // Tranche d'âge
    new Chart(document.getElementById('chartAge'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($tranchesAge)) !!},
            datasets: [{
                label: 'Effectif',
                data: {!! json_encode(array_values($tranchesAge)) !!},
                backgroundColor: '#ec407a',
                borderRadius: 4,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, maintainAspectRatio: false }
    });

    // Ancienneté
    new Chart(document.getElementById('chartAnciennete'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($anciennete)) !!},
            datasets: [{
                label: 'Agents',
                data: {!! json_encode(array_values($anciennete)) !!},
                backgroundColor: '#0d9488',
                borderRadius: 4,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, maintainAspectRatio: false }
    });
</script>
@endsection
