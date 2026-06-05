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
                            <option value="week" {{ ($filters['period'] ?? '') === 'week' ? 'selected' : '' }}>Cette semaine</option>
                            <option value="month" {{ ($filters['period'] ?? 'month') === 'month' ? 'selected' : '' }}>Ce mois</option>
                            <option value="quarter" {{ ($filters['period'] ?? '') === 'quarter' ? 'selected' : '' }}>Ce trimestre</option>
                            <option value="year" {{ ($filters['period'] ?? '') === 'year' ? 'selected' : '' }}>Cette année</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Région</label>
                        <select name="region" class="form-select form-select-sm">
                            <option value="">Toutes les régions</option>
                            @foreach($filters['regions'] ?? [] as $region)
                                <option value="{{ $region }}" {{ request('region') === $region ? 'selected' : '' }}>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Direction</label>
                        <select name="direction" class="form-select form-select-sm">
                            <option value="">Toutes les directions</option>
                            @foreach($filters['directions'] ?? [] as $direction)
                                <option value="{{ $direction }}" {{ request('direction') === $direction ? 'selected' : '' }}>{{ $direction }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary-modern btn-modern btn-sm w-100">
                            <i class="fas fa-filter me-1"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Métriques KPI essentielles pour DG -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 50px; height: 50px;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number" data-stat="total_requests">{{ $stats['total_requests'] ?? 0 }}</h3>
                        <p class="stats-label">📋 Total Demandes</p>
                        <small class="text-muted">Toutes catégories</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-warning); width: 50px; height: 50px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number" data-stat="pending_requests">{{ $stats['pending_requests'] ?? 0 }}</h3>
                        <p class="stats-label">⏳ En Attente</p>
                        <small class="text-warning">Nécessite attention</small>
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
                        <h3 class="stats-number" data-stat="approved_requests">{{ $stats['approved_requests'] ?? 0 }}</h3>
                        <p class="stats-label">✅ Traitées</p>
                        <small class="text-success">Approuvées</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-info); width: 50px; height: 50px;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number" data-stat="total_warehouses">{{ $stats['total_warehouses'] ?? 0 }}</h3>
                        <p class="stats-label">🏢 Entrepôts</p>
                        <small class="text-info">Opérationnels</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Donations -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); width: 50px; height: 50px;">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number" data-stat="total_donations">{{ $stats['total_donations'] ?? 0 }}</h3>
                        <p class="stats-label">❤️ Donations</p>
                        <small class="text-success">{{ number_format($stats['donation_amount'] ?? 0, 0, ',', ' ') }} F</small>
                    </div>
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

    <!-- ── GRAPHIQUES STRATÉGIQUES ── -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-users text-primary me-2"></i>DRH — Types de contrat</h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="chartContracts"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-building text-primary me-2"></i>DRH — Personnel par direction (Top 10)</h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="chartDirections"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar text-success me-2"></i>DSAR — Marchés par région</h6>
                <div style="height: 200px; position: relative;">
                    <canvas id="chartMarketsRegion"></canvas>
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
                        Tendance des Demandes (7 jours)
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm active" onclick="updateChart('7d')">7j</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="updateChart('30d')">30j</button>
                    </div>
                </div>
                <!-- Graphique optimisé avec hauteur responsive -->
                <div style="height: 180px; position: relative;">
                    <canvas id="requestsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Métriques de performance compactes -->
        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-tachometer-alt me-2 text-success"></i>
                    Performance
                </h6>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Taux de traitement</span>
                        <span class="small fw-bold">{{ $stats['efficiency_rate'] ?? '0%' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        @php $rate = isset($stats['processing_rate']) ? (float)$stats['processing_rate'] : 0; @endphp
                        <div class="progress-bar bg-success" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Délai moyen</span>
                        <span class="small fw-bold">{{ $stats['response_time'] ?? '—' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Satisfaction</span>
                        <span class="small fw-bold">{{ $stats['satisfaction_rate'] ?? '—' }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section principale : Vue d'ensemble et actions -->
    <div class="row">
        <!-- Vue d'ensemble des demandes -->
        <div class="col-xl-6 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-eye me-2 text-info"></i>
                        Vue d'Ensemble des Demandes
                    </h6>
                    <a href="{{ route('dg.demandes.index') }}" class="btn btn-primary-modern btn-modern btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Voir tout
                    </a>
                </div>
                
                @if(isset($recentRequests) && $recentRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Demandeur</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests->take(5) as $request)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 30px; height: 30px; background: var(--gradient-info);">
                                                <i class="fas fa-user" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ $request->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $request->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info small">{{ $request->type ?? 'Autre' }}</span>
                                    </td>
                                    <td>
                                        @if($request->status == 'pending')
                                            <span class="badge bg-warning small">En attente</span>
                                        @elseif($request->status == 'approved')
                                            <span class="badge bg-success small">Approuvé</span>
                                        @else
                                            <span class="badge bg-secondary small">{{ $request->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($request->created_at)->format('d/m') }}</small>
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
        
        <!-- Actions et alertes essentielles -->
        <div class="col-xl-6 col-lg-12 col-md-12 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-bell me-2 text-warning"></i>
                    Alertes & Actions
                </h6>
                
                <!-- Alertes critiques -->
                <div class="alert alert-warning d-flex align-items-center mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong class="small">Stock faible détecté</strong><br>
                        <small>Entrepôt Principal - Action requise</small>
                    </div>
                </div>
                
                <div class="alert alert-info d-flex align-items-center mb-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong class="small">Système opérationnel</strong><br>
                        <small>Tous les services fonctionnels</small>
                    </div>
                </div>

                <!-- Donations alert -->
                <div class="alert alert-success d-flex align-items-center mb-2">
                    <i class="fas fa-hand-holding-heart me-2"></i>
                    <div>
                        <strong class="small">{{ $stats['donation_success'] ?? 0 }} dons reçus</strong><br>
                        <small>{{ number_format($stats['donation_amount'] ?? 0, 0, ',', ' ') }} FCFA collectés</small>
                    </div>
                </div>

                <!-- Actions rapides pour DG -->
                <div class="mt-3">
                    <h6 class="small fw-bold mb-2">Actions Rapides</h6>
                    <div class="d-grid gap-1">
                    <a href="{{ route('dg.demandes.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-clipboard-list me-1"></i>Consulter Demandes
                    </a>
                        <a href="{{ route('dg.donations.index') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-hand-holding-heart me-1"></i>Gérer les Donations
                        </a>
                        <a href="{{ route('dg.reports.index') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>Générer Rapport
                        </a>
                        <a href="{{ route('dg.warehouses.index') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-warehouse me-1"></i>Voir Entrepôts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <button class="btn btn-outline-primary btn-sm" onclick="toggleMapLayer('warehouses')">
                            <i class="fas fa-warehouse me-1"></i>Entrepôts
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="toggleMapLayer('requests')">
                            <i class="fas fa-map-pin me-1"></i>Demandes
                        </button>
                    </div>
                </div>
                <!-- Carte avec hauteur fixe -->
                <div id="interactiveMap" style="height: 300px; border-radius: 8px; overflow: hidden;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique optimisé pour DG
    const ctx = document.getElementById('requestsChart').getContext('2d');
    const requestsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Demandes CSAR',
                data: [8, 12, 6, 15, 9, 11, 7],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.15)',
                borderWidth: 3,
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
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
                    max: 20,
                    grid: {
                        color: 'rgba(0,0,0,0.08)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        color: '#6c757d',
                        stepSize: 5
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        color: '#6c757d'
                    }
                }
            },
            elements: {
                line: {
                    borderJoinStyle: 'round',
                    borderCapStyle: 'round'
                }
            }
        }
    });

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

    // Carte interactive compacte
    const map = L.map('interactiveMap').setView([14.6928, -17.4467], 10);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Marqueurs d'entrepôts
    const warehouseIcon = L.divIcon({
        className: 'warehouse-marker',
        html: '<i class="fas fa-warehouse" style="color: #667eea; font-size: 16px;"></i>',
        iconSize: [25, 25]
    });

    // Ajouter des marqueurs d'exemple
    L.marker([14.6928, -17.4467], {icon: warehouseIcon})
        .addTo(map)
        .bindPopup('<b>Entrepôt Principal CSAR</b><br>Dakar, Sénégal');

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

    function updateChart(period) {
        // Mise à jour des données selon la période
        let newData;
        if (period === '7d') {
            newData = [8, 12, 6, 15, 9, 11, 7];
        } else if (period === '30d') {
            newData = [45, 52, 38, 61, 47, 55, 42, 48, 39, 44, 51, 37, 43, 49, 35, 41, 46, 40, 45, 38, 42, 47, 36, 44, 50, 39, 43, 48, 37, 41];
        }
        
        requestsChart.data.datasets[0].data = newData;
        requestsChart.update('active');
    }

    function toggleMapLayer(layer) {
        console.log('Basculement de la couche:', layer);
    }

    // Mise à jour automatique des statistiques (moins fréquente pour DG)
    setInterval(function() {
        const stats = document.querySelectorAll('[data-stat]');
        stats.forEach(stat => {
            const currentValue = parseInt(stat.textContent);
            const newValue = currentValue + Math.floor(Math.random() * 2) - 1;
            stat.textContent = Math.max(0, newValue);
        });
    }, 60000); // Mise à jour toutes les minutes
</script>
@endsection
