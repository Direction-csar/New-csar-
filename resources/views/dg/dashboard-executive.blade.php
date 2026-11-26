@extends('layouts.dg-modern')

@section('title', 'Tableau de Bord Exécutif')
@section('page-title', 'Direction Générale - Vue Stratégique')

@section('content')
<div class="container-fluid">
    <!-- Header compact pour DG -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            Vue Stratégique CSAR
                        </h1>
                        <p class="text-muted mb-0 small">
                            Tableau de bord exécutif - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary-modern btn-modern btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="generateExecutiveReport()">
                            <i class="fas fa-file-pdf me-1"></i>Rapport DG
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres DG -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Période</label>
                        <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach(\App\Services\DgDashboardService::PERIODS as $periodKey => $periodLabel)
                                <option value="{{ $periodKey }}" {{ ($filters['period'] ?? 'month') === $periodKey ? 'selected' : '' }}>{{ $periodLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Région</label>
                        <select name="region" class="form-select form-select-sm">
                            <option value="">Toutes les régions</option>
                            @foreach($filters['regions'] ?? [] as $region)
                                <option value="{{ $region }}" {{ ($filters['region'] ?? null) === $region ? 'selected' : '' }}>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Type de demande</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">Tous les types</option>
                            @foreach(\App\Services\DgDashboardService::TYPES as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}" {{ ($filters['type'] ?? null) === $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Direction</label>
                        <select name="direction" class="form-select form-select-sm">
                            <option value="">Toutes les directions</option>
                            @foreach($filters['directions'] ?? [] as $direction)
                                <option value="{{ $direction }}" {{ ($filters['direction'] ?? null) === $direction ? 'selected' : '' }}>{{ $direction }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-modern btn-modern btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $k = $dashboard['kpis'] ?? [];
        $don = $dashboard['donations'] ?? null;
        $fmt = fn ($v) => $v === null ? '—' : number_format($v, 0, ',', ' ');
        $statusLabels = [
            'soumise' => ['Soumise', 'secondary'],
            'en_revue' => ['En revue', 'info'],
            'document_attente' => ['Pièces attendues', 'warning'],
            'signee' => ['Signée', 'primary'],
            'scannee' => ['Scannée', 'warning'],
            'validee_dg' => ['Validée DG', 'success'],
            'approuvee' => ['Approuvée', 'success'],
            'rejetee' => ['Rejetée', 'danger'],
            'cloturee' => ['Clôturée', 'secondary'],
        ];
        $typeLabels = \App\Services\DgDashboardService::TYPES;
    @endphp

    <!-- Métriques KPI essentielles pour DG -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 50px; height: 50px;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $fmt($k['in_progress'] ?? null) }}</h3>
                        <p class="stats-label">Dossiers en cours</p>
                        <small class="text-muted">Workflow non finalisé</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-warning); width: 50px; height: 50px;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $fmt($k['awaiting_dg'] ?? null) }}</h3>
                        <p class="stats-label">À décider</p>
                        <small class="text-warning">Signés et scannés, décision DG</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-success); width: 50px; height: 50px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $fmt($k['approved'] ?? null) }}</h3>
                        <p class="stats-label">Approuvées</p>
                        <small class="text-success">Sur la période</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); width: 50px; height: 50px;">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $fmt($k['urgent'] ?? null) }}</h3>
                        <p class="stats-label">Urgentes</p>
                        <small class="text-danger">Priorité haute non finalisée</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateurs secondaires -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $fmt($k['rejected'] ?? null) }}</h4>
                    <small class="text-muted">Rejetées (période)</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $fmt($k['warehouses_active'] ?? null) }}</h4>
                    <small class="text-muted">Entrepôts actifs</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $fmt($k['stock_alerts'] ?? null) }}</h4>
                    <small class="text-muted">Alertes de stock actives</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $don === null ? '—' : number_format($don['success_count'], 0, ',', ' ') }}</h4>
                    <small class="text-muted">Dons reçus{{ $don === null ? '' : ' — ' . number_format($don['success_amount'], 0, ',', ' ') . ' F' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SECTION DRH ── -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="fw-bold text-primary mb-2"><i class="fas fa-users me-2"></i>Direction des Ressources Humaines (DRH)</h5>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $drhStats['total'] ?? 0 }}</h4>
                    <small class="text-muted">Agents</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $drhStats['fonctionnaires'] ?? 0 }}</h4>
                    <small class="text-muted">Fonctionnaires</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $drhStats['contractuels'] ?? 0 }}</h4>
                    <small class="text-muted">Contractuels</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($drhStats['masse_salariale'] ?? 0, 0, ',', ' ') }} F</h4>
                    <small class="text-muted">Masse salariale</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ $drhStats['retraites'] ?? 0 }}</h4>
                    <small class="text-muted">Retraités imminents</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-secondary mb-1">{{ $drhStats['documents'] ?? 0 }}</h4>
                    <small class="text-muted">Docs RH générés</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SECTION DSAR ── -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="fw-bold text-success mb-2"><i class="fas fa-chart-bar me-2"></i>DSAR — Suivi des marchés</h5>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $dsarStats['markets'] ?? 0 }}</h4>
                    <small class="text-muted">Marchés actifs</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $dsarStats['products'] ?? 0 }}</h4>
                    <small class="text-muted">Produits suivis</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $dsarStats['collectors'] ?? 0 }}</h4>
                    <small class="text-muted">Collecteurs actifs</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $dsarStats['collections_month'] ?? 0 }}</h4>
                    <small class="text-muted">Collectes ce mois</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SECTION STOCKS & PROJETS ── -->
    <div class="row mb-3">
        <div class="col-lg-6">
            <h5 class="fw-bold text-info mb-2"><i class="fas fa-boxes me-2"></i>Stocks & Entrepôts</h5>
            <div class="row">
                <div class="col-4 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-dark mb-1">{{ $stockStats['warehouses'] ?? 0 }}</h4>
                            <small class="text-muted">Entrepôts</small>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-dark mb-1">{{ $stockStats['active'] ?? 0 }}</h4>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-dark mb-1">{{ $stockStats['movements'] ?? 0 }}</h4>
                            <small class="text-muted">Mouvements</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <h5 class="fw-bold text-warning mb-2"><i class="fas fa-project-diagram me-2"></i>Projets</h5>
            <div class="row">
                <div class="col-3 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-dark mb-1">{{ $projetStats['total'] ?? 0 }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-info mb-1">{{ $projetStats['en_cours'] ?? 0 }}</h4>
                            <small class="text-muted">En cours</small>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-success mb-1">{{ $projetStats['termines'] ?? 0 }}</h4>
                            <small class="text-muted">Terminés</small>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-2">
                    <div class="stats-card">
                        <div class="text-center">
                            <h4 class="fw-bold text-secondary mb-1">{{ $projetStats['suspendus'] ?? 0 }}</h4>
                            <small class="text-muted">Suspendus</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SECTION COMMUNICATION ── -->
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="fw-bold text-danger mb-2"><i class="fas fa-bullhorn me-2"></i>Communication</h5>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $commStats['news'] ?? 0 }}</h4>
                    <small class="text-muted">Actualités</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $commStats['newsletters'] ?? 0 }}</h4>
                    <small class="text-muted">Newsletters</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $commStats['subscribers'] ?? 0 }}</h4>
                    <small class="text-muted">Abonnés</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
            <div class="stats-card">
                <div class="text-center">
                    <h4 class="fw-bold text-dark mb-1">{{ $commStats['messages'] ?? 0 }}</h4>
                    <small class="text-muted">Messages contact</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ── GRAPHIQUES STRATÉGIQUES (2 par ligne) ── -->
    <div class="row mb-3">
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-users text-primary me-2"></i>DRH — Types de contrat</h6>
                <div style="height: 240px; position: relative;">
                    <canvas id="chartContracts"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-building text-primary me-2"></i>DRH — Personnel par direction (Top 10)</h6>
                <div style="height: 240px; position: relative;">
                    <canvas id="chartDirections"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar text-success me-2"></i>DSAR — Marchés par région</h6>
                <div style="height: 240px; position: relative;">
                    <canvas id="chartMarketsRegion"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-project-diagram text-warning me-2"></i>Répartition des projets</h6>
                <div style="height: 240px; position: relative;">
                    <canvas id="chartProjets"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique compact et métriques -->
    <div class="row mb-3">
        <!-- Graphique compact (hauteur fixe) -->
        <div class="col-xl-8 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                    <h6 class="mb-2 mb-md-0 fw-bold">
                        <i class="fas fa-chart-area me-2 text-primary"></i>
                        Évolution des demandes (6 derniers mois)
                    </h6>
                    @if(($dashboard['evolution']['total'] ?? null) !== null)
                        <span class="badge bg-primary">{{ $dashboard['evolution']['total'] }} demande(s)</span>
                    @endif
                </div>
                @if(!empty($dashboard['evolution']))
                    <div style="height: 180px; position: relative;">
                        <canvas id="requestsChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-area text-muted mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted small mb-0">Données d'évolution indisponibles</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Métriques de performance compactes -->
        @php $perf = $dashboard['performance'] ?? null; @endphp
        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-tachometer-alt me-2 text-success"></i>
                    Performance
                </h6>

                @if($perf !== null)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Taux de traitement</span>
                            <span class="small fw-bold">{{ $perf['processing_rate'] === null ? '—' : $perf['processing_rate'] . '%' }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $perf['processing_rate'] ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $perf['decided'] }}/{{ $perf['created'] }} dossier(s) décidé(s) sur la période</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Délai moyen de traitement</span>
                            <span class="small fw-bold">{{ $perf['average_days'] === null ? '—' : $perf['average_days'] . ' j' }}</span>
                        </div>
                        <small class="text-muted">{{ $perf['processed_count'] }} dossier(s) traité(s) sur la période</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Taux de clôture</span>
                            <span class="small fw-bold">{{ $perf['closure_rate'] === null ? '—' : $perf['closure_rate'] . '%' }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $perf['closure_rate'] ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $perf['closed'] }}/{{ $perf['approved_all'] }} dossier(s) approuvé(s) clôturé(s)</small>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-tachometer-alt text-muted mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted small mb-0">Indicateurs de performance indisponibles</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section décisionnelle : dossiers à décider et alertes de stock -->
    <div class="row">
        <div class="col-xl-8 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-file-signature me-2 text-warning"></i>
                        Dossiers en attente de votre décision
                    </h6>
                    <a href="{{ route('dg.demandes.index', ['workflow_status' => 'scannee']) }}" class="btn btn-primary-modern btn-modern btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Voir tout
                    </a>
                </div>
                @php $awaitingList = $dashboard['awaiting'] ?? collect(); @endphp
                @if($awaitingList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Dossier</th>
                                    <th>Type</th>
                                    <th>Région</th>
                                    <th>En attente</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awaitingList as $request)
                                <tr>
                                    <td>
                                        <div class="fw-bold small">{{ $request->tracking_code ?? ('#' . $request->id) }}</div>
                                        <small class="text-muted">{{ $request->full_name ?? $request->name ?? 'N/A' }}</small>
                                    </td>
                                    <td><span class="badge bg-info small">{{ $typeLabels[$request->type] ?? ucfirst((string) $request->type) }}</span></td>
                                    <td><small>{{ $request->region ?? '—' }}</small></td>
                                    <td>
                                        @if($request->awaiting_days === null)
                                            <small class="text-muted">—</small>
                                        @else
                                            <span class="badge {{ $request->awaiting_days >= 3 ? 'bg-danger' : 'bg-warning text-dark' }} small">{{ $request->awaiting_days }} j</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('dg.demandes.show', $request->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-gavel me-1"></i>Décider
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="icon-3d mx-auto mb-2" style="width: 60px; height: 60px; background: var(--gradient-success);">
                            <i class="fas fa-check" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-muted">Aucun dossier en attente de décision</h6>
                        <p class="text-muted small">Les dossiers signés et scannés apparaîtront ici</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Alertes de stock réelles -->
        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-bell me-2 text-danger"></i>
                        Alertes de stock
                    </h6>
                    <a href="{{ route('dg.stocks.index') }}" class="btn btn-outline-danger btn-sm">Stocks</a>
                </div>
                @php $alerts = $dashboard['stockAlerts'] ?? null; @endphp
                @if($alerts === null)
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-circle text-muted mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted small mb-0">Alertes indisponibles</p>
                    </div>
                @elseif($alerts->count() > 0)
                    @foreach($alerts as $alert)
                        <div class="alert {{ $alert['priority'] === 'critical' ? 'alert-danger' : 'alert-warning' }} d-flex align-items-start mb-2 py-2">
                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <strong class="small">{{ $alert['type_label'] }} — {{ $alert['product_name'] }}</strong><br>
                                <small>
                                    {{ $alert['warehouse_name'] }}
                                    @if($alert['days_left'] !== null)
                                        — {{ $alert['days_left'] <= 0 ? 'péremption dépassée' : $alert['days_left'] . ' j restants' }}
                                    @elseif($alert['threshold'] !== null)
                                        — {{ $alert['quantity'] }}{{ $alert['unit'] ? ' ' . $alert['unit'] : '' }} / seuil {{ $alert['threshold'] }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    @endforeach
                    @if(($k['stock_alerts'] ?? 0) > $alerts->count())
                        <a href="{{ route('dg.stocks.index') }}" class="small">+ {{ $k['stock_alerts'] - $alerts->count() }} autre(s) alerte(s)</a>
                    @endif
                @else
                    <div class="text-center py-4">
                        <div class="icon-3d mx-auto mb-2" style="width: 60px; height: 60px; background: var(--gradient-success);">
                            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-muted">Aucune alerte de stock active</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Urgences, journal du workflow et répartition par type -->
    <div class="row">
        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-bolt me-2 text-danger"></i>
                        Demandes urgentes
                    </h6>
                    <a href="{{ route('dg.demandes.index', ['urgency' => 'haute']) }}" class="btn btn-outline-danger btn-sm">Voir</a>
                </div>
                @php $urgentList = $dashboard['urgent'] ?? collect(); @endphp
                @if($urgentList->count() > 0)
                    <ul class="list-unstyled mb-0">
                        @foreach($urgentList as $request)
                            @php $st = $statusLabels[$request->workflow_status] ?? [$request->workflow_status ?? 'N/A', 'secondary']; @endphp
                            <li class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <a href="{{ route('dg.demandes.show', $request->id) }}" class="fw-bold small text-decoration-none">{{ $request->tracking_code ?? ('#' . $request->id) }}</a>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $request->region ?? '—' }} — {{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y') }}</div>
                                </div>
                                <span class="badge bg-{{ $st[1] }} small">{{ $st[0] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-bolt text-muted mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted small mb-0">Aucune demande urgente non finalisée</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3 h-100">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-history me-2 text-info"></i>
                    Journal du workflow
                </h6>
                @php $activities = $dashboard['activities'] ?? collect(); @endphp
                @if($activities->count() > 0)
                    <ul class="list-unstyled mb-0">
                        @foreach($activities as $act)
                            <li class="d-flex justify-content-between align-items-start py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="small fw-bold">{{ $act['label'] }}</div>
                                    <small class="text-muted">
                                        <a href="{{ route('dg.demandes.show', $act['request_id']) }}" class="text-decoration-none">{{ $act['tracking_code'] ?? ('#' . $act['request_id']) }}</a>
                                        — {{ $act['user_name'] }}
                                    </small>
                                </div>
                                <small class="text-muted ms-2 text-nowrap">{{ $act['timestamp']->diffForHumans() }}</small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history text-muted mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted small mb-0">Aucune activité de workflow récente</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3 h-100">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    Demandes par type (période)
                </h6>
                @if(($dashboard['types']['total'] ?? 0) > 0)
                    <div style="height: 220px; position: relative;">
                        <canvas id="chartTypes"></canvas>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie text-muted mb-2" style="font-size: 1.5rem;"></i>
                        <p class="text-muted small mb-0">Aucune demande sur la période</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Demandes récentes -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-eye me-2 text-info"></i>
                        Demandes récentes
                    </h6>
                    <a href="{{ route('dg.demandes.index') }}" class="btn btn-primary-modern btn-modern btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Voir tout
                    </a>
                </div>
                @php $recent = $dashboard['recentRequests'] ?? collect(); @endphp
                @if($recent->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Demandeur</th>
                                    <th>Type</th>
                                    <th>Région</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent as $request)
                                @php $st = $statusLabels[$request->workflow_status] ?? [$request->status ?? 'N/A', 'secondary']; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold small">{{ $request->full_name ?? $request->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $request->tracking_code ?? $request->email ?? '—' }}</small>
                                    </td>
                                    <td><span class="badge bg-info small">{{ $typeLabels[$request->type] ?? ucfirst((string) $request->type) }}</span></td>
                                    <td><small>{{ $request->region ?? '—' }}</small></td>
                                    <td><span class="badge bg-{{ $st[1] }} small">{{ $st[0] }}</span></td>
                                    <td><small>{{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y') }}</small></td>
                                    <td>
                                        <a href="{{ route('dg.demandes.show', $request->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="icon-3d mx-auto mb-2" style="width: 60px; height: 60px; background: var(--gradient-secondary);">
                            <i class="fas fa-inbox" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-muted">Aucune demande récente</h6>
                        <p class="text-muted small">Les nouvelles demandes apparaîtront ici</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Exécution & transmission DTL -->
    @php $ops = $dashboard['operations'] ?? null; @endphp
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="fw-bold text-secondary mb-2"><i class="fas fa-cogs me-2"></i>Exécution & transmission DTL</h5>
        </div>
        @if($ops !== null)
            <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
                <div class="stats-card">
                    <div class="text-center">
                        <h4 class="fw-bold text-primary mb-1">{{ $ops['in_execution'] }}</h4>
                        <small class="text-muted">Approuvées en exécution</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
                <div class="stats-card">
                    <div class="text-center">
                        <h4 class="fw-bold text-info mb-1">{{ $ops['transmitted_period'] }}</h4>
                        <small class="text-muted">Transmises DTL (période)</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
                <div class="stats-card">
                    <div class="text-center">
                        <h4 class="fw-bold text-warning mb-1">{{ $ops['missions_active'] }}</h4>
                        <small class="text-muted">Missions actives</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-4 col-6 mb-2">
                <div class="stats-card">
                    <div class="text-center">
                        <h4 class="fw-bold text-danger mb-1">{{ $ops['reports_pending'] }}</h4>
                        <small class="text-muted">Rapports à valider</small>
                    </div>
                </div>
            </div>
        @else
            <div class="col-12 mb-2">
                <div class="stats-card">
                    <div class="text-center text-muted small py-2">Indicateurs d'exécution indisponibles</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions rapides pour DG -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="small fw-bold mb-2">Actions Rapides</h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('dg.demandes.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-clipboard-list me-1"></i>Consulter Demandes
                    </a>
                    <a href="{{ route('dg.donations.index') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-hand-holding-heart me-1"></i>Gérer les Donations
    <!-- Donations récentes DG -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-hand-holding-heart me-2 text-danger"></i>
                        Dons Récemment Reçus
                    </h6>
                    <a href="{{ route('dg.donations.index') }}" class="btn btn-danger-modern btn-modern btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Gérer les Dons
                    </a>
                </div>

                @if(isset($recentDonations) && $recentDonations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Donateur</th>
                                    <th>Montant</th>
                                    <th>Fournisseur</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDonations as $donation)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 30px; height: 30px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                                <i class="fas fa-heart" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ $donation->is_anonymous ? 'Anonyme' : $donation->full_name }}</div>
                                                <small class="text-muted">{{ $donation->is_anonymous ? '' : ($donation->email ?? '') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success small">{{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency ?? 'FCFA' }}</strong>
                                    </td>
                                    <td>
                                        @if($donation->payment_provider === 'paypal')
                                            <span class="badge bg-primary small">PayPal</span>
                                        @else
                                            <span class="badge bg-info small">PayDunya</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($donation->payment_status === 'success')
                                            <span class="badge bg-success small">Complété</span>
                                        @elseif($donation->payment_status === 'pending')
                                            <span class="badge bg-warning text-dark small">En attente</span>
                                        @else
                                            <span class="badge bg-danger small">Échoué</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $donation->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('dg.donations.show', $donation->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="icon-3d mx-auto mb-2" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-hand-holding-heart" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-muted">Aucun don reçu encore</h6>
                        <p class="text-muted small">Les donations apparaîtront ici une fois reçues</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Carte interactive compacte -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-map-marked-alt me-2 text-primary"></i>
                        Carte des Entrepôts et Demandes
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm active" onclick="toggleMapLayer('warehouses', this)">
                            <i class="fas fa-warehouse me-1"></i>Entrepôts
                        </button>
                        <button class="btn btn-outline-primary btn-sm active" onclick="toggleMapLayer('requests', this)">
                            <i class="fas fa-map-pin me-1"></i>Demandes
                        </button>
                    </div>
                </div>
                <!-- Carte avec hauteur fixe -->
                <div id="interactiveMap" style="height: 300px; border-radius: 8px; overflow: hidden;"></div>
                <small id="mapNotice" class="text-muted d-block mt-1"></small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Évolution des demandes (données réelles, 6 derniers mois)
    const evolutionEl = document.getElementById('requestsChart');
    if (evolutionEl) {
        new Chart(evolutionEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($dashboard['evolution']['labels'] ?? []),
                datasets: [{
                    label: 'Demandes CSAR',
                    data: @json($dashboard['evolution']['values'] ?? []),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.08)', drawBorder: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#6c757d', precision: 0 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#6c757d' }
                    }
                },
                elements: {
                    line: { borderJoinStyle: 'round', borderCapStyle: 'round' }
                }
            }
        });
    }

    // Répartition par type (données réelles de la période)
    const typesEl = document.getElementById('chartTypes');
    if (typesEl) {
        new Chart(typesEl, {
            type: 'doughnut',
            data: {
                labels: @json(collect($dashboard['types']['items'] ?? [])->pluck('label')->values()),
                datasets: [{
                    data: @json(collect($dashboard['types']['items'] ?? [])->pluck('total')->values()),
                    backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#0dcaf0', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
            }
        });
    }

    // ── Chart: DRH Contract Types ──
    new Chart(document.getElementById('chartContracts'), {
        type: 'doughnut',
        data: {
            labels: ['Fonctionnaires', 'Contractuels', 'Stagiaires'],
            datasets: [{
                data: [
                    {{ $drhStats['fonctionnaires'] ?? 0 }},
                    {{ $drhStats['contractuels'] ?? 0 }},
                    {{ $drhStats['stagiaires'] ?? 0 }}
                ],
                backgroundColor: ['#0d6efd', '#ffc107', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
        }
    });

    // ── Chart: DRH Personnel by Direction ──
    new Chart(document.getElementById('chartDirections'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($drhStats['par_direction'] ?? [])) !!},
            datasets: [{
                label: 'Agents',
                data: {!! json_encode(array_values($drhStats['par_direction'] ?? [])) !!},
                backgroundColor: '#0d6efd',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 30 }, grid: { display: false } }
            }
        }
    });

    // ── Chart: DSAR Markets by Region ──
    new Chart(document.getElementById('chartMarketsRegion'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($dsarStats['markets_by_region'] ?? [])) !!},
            datasets: [{
                label: 'Marchés',
                data: {!! json_encode(array_values($dsarStats['markets_by_region'] ?? [])) !!},
                backgroundColor: '#198754',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });

    // ── Chart: Répartition des projets ──
    new Chart(document.getElementById('chartProjets'), {
        type: 'doughnut',
        data: {
            labels: ['En cours', 'Terminés', 'Suspendus'],
            datasets: [{
                data: [
                    {{ $projetStats['en_cours'] ?? 0 }},
                    {{ $projetStats['termines'] ?? 0 }},
                    {{ $projetStats['suspendus'] ?? 0 }}
                ],
                backgroundColor: ['#0dcaf0', '#198754', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
        }
    });

    // ── Carte interactive : cadrage Sénégal + marqueurs réels ──
    const senegalBoundsCfg = @json(config('regions_senegal.bounds'));
    const senegalBounds = L.latLngBounds(
        [senegalBoundsCfg.south, senegalBoundsCfg.west],
        [senegalBoundsCfg.north, senegalBoundsCfg.east]
    );

    const dgMap = L.map('interactiveMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(dgMap);
    dgMap.fitBounds(senegalBounds, {padding: [8, 8]});

    const warehouseLayer = L.layerGroup().addTo(dgMap);
    const requestLayer = L.layerGroup().addTo(dgMap);

    const warehouseIcon = L.divIcon({
        className: 'warehouse-marker',
        html: '<i class="fas fa-warehouse" style="color: #667eea; font-size: 16px;"></i>',
        iconSize: [25, 25]
    });
    const requestIcon = L.divIcon({
        className: 'request-marker',
        html: '<i class="fas fa-map-pin" style="color: #f5576c; font-size: 16px;"></i>',
        iconSize: [22, 22]
    });

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function showMapNotice(message) {
        const notice = document.getElementById('mapNotice');
        if (notice) { notice.textContent = message; }
    }

    fetch('{{ route('dg.map.data') }}', {headers: {'Accept': 'application/json'}})
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data || data.success === false) {
                showMapNotice('Données cartographiques indisponibles.');
                return;
            }
            const points = [];
            (data.warehouses || []).forEach(function (w) {
                if (w.latitude === null || w.longitude === null) { return; }
                const latLng = [w.latitude, w.longitude];
                points.push(latLng);
                L.marker(latLng, {icon: warehouseIcon})
                    .bindPopup('<b>' + escapeHtml(w.name) + '</b><br>' +
                        (w.region ? escapeHtml(w.region) + '<br>' : '') +
                        (w.requests_count || 0) + ' demande(s) — ' + (w.pending_requests || 0) + ' en cours')
                    .addTo(warehouseLayer);
            });
            (data.recent_requests || []).forEach(function (req) {
                if (req.latitude === null || req.longitude === null) { return; }
                const latLng = [req.latitude, req.longitude];
                points.push(latLng);
                L.marker(latLng, {icon: requestIcon})
                    .bindPopup('<b>' + escapeHtml(req.type || 'Demande') + '</b><br>' +
                        escapeHtml(req.user_name || '') + '<br>' +
                        (req.region ? escapeHtml(req.region) + ' — ' : '') + escapeHtml(req.created_at || ''))
                    .addTo(requestLayer);
            });
            if (points.length) {
                // Le territoire national reste entièrement cadré tout en incluant les points réels.
                dgMap.fitBounds(L.latLngBounds(points).extend(senegalBounds), {padding: [12, 12]});
            } else {
                showMapNotice('Aucun point géolocalisé disponible.');
            }
        })
        .catch(function () {
            showMapNotice('Données cartographiques indisponibles.');
        });

    // Redimensionnement : recalculer la taille quand la fenêtre ou la sidebar change.
    window.addEventListener('resize', function () { dgMap.invalidateSize(); });
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function () {
            setTimeout(function () { dgMap.invalidateSize(); }, 350);
        });
    }
    setTimeout(function () { dgMap.invalidateSize(); }, 200);

    // Fonctions pour DG
    function refreshDashboard() {
        location.reload();
    }

    function generateExecutiveReport() {
        fetch('{{ route("dg.api.generate-report") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                type: 'monthly',
                format: 'pdf',
                period: 'month'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.download_url) {
                // Ouvrir le téléchargement dans un nouvel onglet
                window.open(data.download_url, '_blank');
            } else {
                alert(data.message || 'Erreur lors de la génération du rapport DG');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la génération du rapport DG');
        });
    }

    // Bascule réelle des couches de la carte
    function toggleMapLayer(layer, btn) {
        const group = layer === 'warehouses' ? warehouseLayer : requestLayer;
        if (dgMap.hasLayer(group)) {
            dgMap.removeLayer(group);
            if (btn) { btn.classList.remove('active'); }
        } else {
            dgMap.addLayer(group);
            if (btn) { btn.classList.add('active'); }
        }
    }
</script>
@endsection
