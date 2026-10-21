@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
@php
    $totalStats = ($stats['total_users'] ?? 0) + ($stats['total_requests'] ?? 0) + ($stats['total_warehouses'] ?? 0) + ($stats['total_stock'] ?? 0) + ($stats['unread_notifications'] ?? 0) + ($stats['unread_messages'] ?? 0);
    $hasData = $totalStats > 0;

    $wf = $stats['workflow'] ?? [];
    $dashRole = auth()->user()->role ?? 'admin';
    $canDgValidate = in_array($dashRole, ['admin', 'super_admin', 'dg']);

    $workflowSteps = [
        ['key' => 'soumise', 'label' => 'Soumises', 'hint' => 'Reçues du site public', 'icon' => 'fa-inbox', 'url' => null],
        ['key' => 'en_revue', 'label' => 'En revue', 'hint' => 'Analyse en cours', 'icon' => 'fa-magnifying-glass', 'url' => null],
        ['key' => 'document_attente', 'label' => 'À signer', 'hint' => 'Attente document', 'icon' => 'fa-file-signature', 'url' => route('admin.demandes.dg-dashboard')],
        ['key' => 'signee', 'label' => 'À scanner', 'hint' => 'Document signé', 'icon' => 'fa-print', 'url' => route('admin.demandes.dg-dashboard')],
        ['key' => 'scannee', 'label' => 'À valider DG', 'hint' => 'Document scanné', 'icon' => 'fa-stamp', 'url' => $canDgValidate ? route('admin.demandes.dg-pending') : route('admin.demandes.dg-dashboard')],
        ['key' => 'validee_dg', 'label' => 'Validées DG', 'hint' => 'Approuvées par la DG', 'icon' => 'fa-circle-check', 'url' => null],
        ['key' => 'approuvee', 'label' => 'Approuvées', 'hint' => 'Prêtes à servir', 'icon' => 'fa-thumbs-up', 'url' => null],
        ['key' => 'cloturee', 'label' => 'Clôturées', 'hint' => 'Traitement terminé', 'icon' => 'fa-flag-checkered', 'url' => null],
    ];
    $wfRejected = $wf['rejetee'] ?? 0;

    $statusLabels = [
        'pending' => ['En attente', 'warning'],
        'processing' => ['En cours', 'info'],
        'approved' => ['Approuvée', 'success'],
        'completed' => ['Terminée', 'success'],
        'rejected' => ['Rejetée', 'danger'],
    ];
@endphp

<div class="dash">
    {{-- En-tête --}}
    <div class="dash-header">
        <div>
            <h2 class="dash-title">Tableau de bord</h2>
            <p class="dash-subtitle">Vue d’ensemble de l’activité de la plateforme CSAR — données au {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="dash-header-actions">
            <button class="btn btn-outline-primary btn-sm" type="button" onclick="refreshDashboard()">
                <i class="fas fa-rotate me-1"></i>Actualiser
            </button>
            <button class="btn btn-primary btn-sm" type="button" onclick="generateReport()">
                <i class="fas fa-file-arrow-down me-1"></i>Générer un rapport
            </button>
        </div>
    </div>

    @if(!$hasData)
        <div class="dash-card dash-empty">
            <div class="dash-empty-icon"><i class="fas fa-database"></i></div>
            <h3 class="h6 mb-1">Aucune donnée disponible pour le moment</h3>
            <p class="text-muted small mb-3">La base de données ne contient encore aucun enregistrement exploitable pour le tableau de bord.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i>Ajouter un utilisateur</a>
                <a href="{{ route('admin.entrepots.create') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-warehouse me-1"></i>Ajouter un entrepôt</a>
            </div>
        </div>
    @endif

    {{-- Indicateurs clés --}}
    <div class="dash-kpis">
        <a href="{{ route('admin.demandes.index', ['statut' => 'pending']) }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-warning"><i class="fas fa-clipboard-list"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Demandes en attente</span>
                <span class="dash-kpi-value" id="pending-requests" data-stat="pending_requests">{{ $stats['pending_requests'] ?? 0 }}</span>
                <span class="dash-kpi-meta">sur {{ number_format($stats['total_requests'] ?? 0, 0, ',', ' ') }} demandes au total</span>
            </span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-navy"><i class="fas fa-users"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Utilisateurs</span>
                <span class="dash-kpi-value" id="total-users" data-stat="total_users">{{ $stats['total_users'] ?? 0 }}</span>
                <span class="dash-kpi-meta">{{ number_format($stats['active_users'] ?? 0, 0, ',', ' ') }} actifs</span>
            </span>
        </a>

        <a href="{{ route('admin.entrepots.index') }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-green"><i class="fas fa-warehouse"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Entrepôts</span>
                <span class="dash-kpi-value" id="total-warehouses" data-stat="total_warehouses">{{ $stats['total_warehouses'] ?? 0 }}</span>
                <span class="dash-kpi-meta">{{ number_format($stats['active_warehouses'] ?? 0, 0, ',', ' ') }} actifs</span>
            </span>
        </a>

        <a href="{{ route('admin.stock.index') }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-danger"><i class="fas fa-triangle-exclamation"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Articles en stock faible</span>
                <span class="dash-kpi-value" id="low-stock" data-stat="low_stock_items">{{ $stats['low_stock_items'] ?? 0 }}</span>
                <span class="dash-kpi-meta">à réapprovisionner</span>
            </span>
        </a>

        <a href="{{ route('admin.stock.index') }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-slate"><i class="fas fa-boxes-stacked"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Lignes de stock</span>
                <span class="dash-kpi-value" id="total-stock" data-stat="total_stock">{{ $stats['total_stock'] ?? 0 }}</span>
                <span class="dash-kpi-meta">enregistrements de stock</span>
            </span>
        </a>

        <div class="dash-kpi">
            <span class="dash-kpi-icon tone-info"><i class="fas fa-gas-pump"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Carburant disponible</span>
                <span class="dash-kpi-value"><span id="fuel-available" data-stat="fuel_available">{{ $stats['fuel_available'] ?? 0 }}</span><small class="dash-kpi-unit">L</small></span>
                <span class="dash-kpi-meta">tous entrepôts confondus</span>
            </span>
        </div>

        <a href="{{ route('admin.notifications.index') }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-navy"><i class="fas fa-bell"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Notifications non lues</span>
                <span class="dash-kpi-value" id="unread-notifications">{{ $stats['unread_notifications'] ?? 0 }}</span>
                <span class="dash-kpi-meta">centre de notifications</span>
            </span>
        </a>

        <a href="{{ route('admin.messages.index') }}" class="dash-kpi is-link">
            <span class="dash-kpi-icon tone-green"><i class="fas fa-envelope"></i></span>
            <span class="dash-kpi-body">
                <span class="dash-kpi-label">Messages non lus</span>
                <span class="dash-kpi-value" id="unread-messages">{{ $stats['unread_messages'] ?? 0 }}</span>
                <span class="dash-kpi-meta">formulaire de contact</span>
            </span>
        </a>
    </div>

    {{-- Workflow des demandes --}}
    <section class="dash-card">
        <div class="dash-card-header">
            <div>
                <h3 class="dash-card-title">Circuit de traitement des demandes</h3>
                <p class="dash-card-subtitle">Répartition des demandes par étape du workflow</p>
            </div>
            <a href="{{ route('admin.demandes.index') }}" class="btn btn-light btn-sm">Toutes les demandes <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="dash-flow">
            @foreach($workflowSteps as $step)
                @php $count = $wf[$step['key']] ?? 0; @endphp
                @if($step['url'])
                    <a href="{{ $step['url'] }}" class="dash-flow-step is-link {{ $count > 0 && in_array($step['key'], ['document_attente', 'signee', 'scannee']) ? 'is-pending' : '' }}">
                @else
                    <div class="dash-flow-step">
                @endif
                        <span class="dash-flow-icon"><i class="fas {{ $step['icon'] }}"></i></span>
                        <span class="dash-flow-count">{{ number_format($count, 0, ',', ' ') }}</span>
                        <span class="dash-flow-label">{{ $step['label'] }}</span>
                        <span class="dash-flow-hint">{{ $step['hint'] }}</span>
                @if($step['url'])
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>

        <div class="dash-flow-summary">
            <div class="dash-summary-item">
                <span class="dash-summary-label">Reçues aujourd’hui</span>
                <span class="dash-summary-value">{{ number_format($stats['today_requests'] ?? 0, 0, ',', ' ') }}</span>
            </div>
            <div class="dash-summary-item">
                <span class="dash-summary-label">Reçues ce mois</span>
                <span class="dash-summary-value">{{ number_format($stats['month_requests'] ?? 0, 0, ',', ' ') }}</span>
            </div>
            <a href="{{ route('admin.demandes.index', ['statut' => 'approved']) }}" class="dash-summary-item is-link">
                <span class="dash-summary-label"><span class="dash-dot bg-success"></span>Approuvées</span>
                <span class="dash-summary-value">{{ number_format($stats['approved_requests'] ?? 0, 0, ',', ' ') }}</span>
            </a>
            <a href="{{ route('admin.demandes.index', ['statut' => 'rejected']) }}" class="dash-summary-item is-link">
                <span class="dash-summary-label"><span class="dash-dot bg-danger"></span>Rejetées</span>
                <span class="dash-summary-value">{{ number_format($stats['rejected_requests'] ?? 0, 0, ',', ' ') }}</span>
            </a>
            <div class="dash-summary-item">
                <span class="dash-summary-label">Rejetées (workflow)</span>
                <span class="dash-summary-value">{{ number_format($wfRejected, 0, ',', ' ') }}</span>
            </div>
        </div>
    </section>

    @include('admin.partials.stock-alerts', [
        'stockAlertPanel' => $stockAlertPanel ?? null,
        'stockAlertAction' => route('admin.dashboard'),
        'stockAlertLimit' => 10,
        'stockAlertShowStockLink' => true,
    ])

    {{-- Alertes, demandes récentes, actions --}}
    <div class="dash-grid dash-grid-3">
        <section class="dash-card dash-span-2">
            <div class="dash-card-header">
                <div>
                    <h3 class="dash-card-title">Demandes récentes</h3>
                    <p class="dash-card-subtitle">Les 5 dernières demandes reçues</p>
                </div>
                <a href="{{ route('admin.demandes.index') }}" class="btn btn-light btn-sm">Voir tout</a>
            </div>
            <div class="dash-list">
                @forelse($recentActivities['recent_requests'] ?? [] as $request)
                    @php
                        $st = $statusLabels[$request->status] ?? [ucfirst($request->status ?? 'En attente'), 'secondary'];
                    @endphp
                    <a href="{{ route('admin.demandes.show', $request->id) }}" class="dash-list-item">
                        <span class="dash-list-main">
                            <span class="dash-list-title">{{ $request->tracking_code ?? $request->reference ?? 'REQ-' . $request->id }}</span>
                            <span class="dash-list-meta">
                                {{ $request->full_name ?? $request->name ?? ($request->user->name ?? 'Demandeur') }}
                                @if($request->region) · {{ $request->region }} @endif
                                @if($request->created_at) · {{ $request->created_at->diffForHumans() }} @endif
                            </span>
                        </span>
                        <span class="badge bg-{{ $st[1] }}-subtle text-{{ $st[1] }}-emphasis border border-{{ $st[1] }}-subtle">{{ $st[0] }}</span>
                    </a>
                @empty
                    <div class="dash-list-empty">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Aucune demande récente</span>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="dash-stack">
            <section class="dash-card">
                <div class="dash-card-header">
                    <div>
                        <h3 class="dash-card-title">Alertes</h3>
                        <p class="dash-card-subtitle">Points d’attention détectés</p>
                    </div>
                    @if(!empty($alerts))
                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ count($alerts) }}</span>
                    @endif
                </div>
                @if(!empty($alerts))
                    <div class="dash-alerts">
                        @foreach($alerts as $alert)
                            @php $tone = in_array($alert['type'] ?? 'info', ['danger', 'warning', 'success', 'info']) ? $alert['type'] : 'info'; @endphp
                            <div class="dash-alert tone-{{ $tone }}">
                                <i class="fas fa-{{ $alert['icon'] ?? 'circle-info' }}"></i>
                                <div>
                                    <div class="dash-alert-title">{{ $alert['title'] ?? 'Alerte' }}</div>
                                    @if(!empty($alert['message']))
                                        <div class="dash-alert-text">{{ $alert['message'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="dash-list-empty">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Aucune alerte en cours</span>
                    </div>
                @endif
            </section>

            <section class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title">Actions rapides</h3>
                </div>
                <div class="dash-actions">
                    <a href="{{ route('admin.users.create') }}" class="dash-action"><i class="fas fa-user-plus"></i>Nouvel utilisateur</a>
                    <a href="{{ route('admin.entrepots.create') }}" class="dash-action"><i class="fas fa-warehouse"></i>Nouvel entrepôt</a>
                    <a href="{{ route('admin.donations.index') }}" class="dash-action"><i class="fas fa-hand-holding-heart"></i>Gérer les dons</a>
                    <button type="button" class="dash-action" onclick="generateReport()"><i class="fas fa-file-pdf"></i>Générer un rapport</button>
                </div>
            </section>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="dash-grid dash-grid-2">
        <section class="dash-card">
            <div class="dash-card-header"><h3 class="dash-card-title">Demandes sur 7 jours</h3></div>
            <div class="chart-container">
                @if(!empty($chartsData['last7Days']))
                    <canvas id="requestsChart"></canvas>
                @else
                    <div class="dash-chart-empty"><i class="fas fa-chart-line"></i><span>Aucune donnée disponible</span></div>
                @endif
            </div>
        </section>
        <section class="dash-card">
            <div class="dash-card-header"><h3 class="dash-card-title">Entrepôts par région</h3></div>
            <div class="chart-container">
                @if(!empty($chartsData['entrepotsByRegion']))
                    <canvas id="warehousesChart"></canvas>
                @else
                    <div class="dash-chart-empty"><i class="fas fa-warehouse"></i><span>Aucune donnée disponible</span></div>
                @endif
            </div>
        </section>
        <section class="dash-card">
            <div class="dash-card-header"><h3 class="dash-card-title">Carburant par entrepôt</h3></div>
            <div class="chart-container">
                @if(!empty($chartsData['fuelByEntrepot']))
                    <canvas id="fuelChart"></canvas>
                @else
                    <div class="dash-chart-empty"><i class="fas fa-gas-pump"></i><span>Aucune donnée disponible</span></div>
                @endif
            </div>
        </section>
        <section class="dash-card">
            <div class="dash-card-header"><h3 class="dash-card-title">Stock par catégorie</h3></div>
            <div class="chart-container">
                @if(!empty($chartsData['stockByCategory']))
                    <canvas id="stockChart"></canvas>
                @else
                    <div class="dash-chart-empty"><i class="fas fa-boxes-stacked"></i><span>Aucune donnée disponible</span></div>
                @endif
            </div>
        </section>
    </div>

    {{-- Demandes par région --}}
    <section class="dash-card">
        <div class="dash-card-header">
            <div>
                <h3 class="dash-card-title">Demandes d’aide alimentaire par région</h3>
                <p class="dash-card-subtitle">Zones à forte demande</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Région</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">En attente</th>
                        <th class="text-end">Approuvées</th>
                        <th class="text-end">Rejetées</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chartsData['demandesByRegion'] ?? [] as $r)
                    <tr>
                        <td class="fw-medium">{{ $r['region'] }}</td>
                        <td class="text-end fw-semibold">{{ $r['total'] }}</td>
                        <td class="text-end">{{ $r['pending'] }}</td>
                        <td class="text-end">{{ $r['approved'] }}</td>
                        <td class="text-end">{{ $r['rejected'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune donnée par région.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Carte --}}
    <section class="dash-card">
        <div class="dash-card-header">
            <div>
                <h3 class="dash-card-title">Carte des entrepôts et des demandes d’aide</h3>
                <p class="dash-card-subtitle">Géolocalisation des entrepôts CSAR et des demandes reçues</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-light btn-sm" type="button" onclick="toggleFilters()">
                    <i class="fas fa-filter me-1"></i>Filtres
                </button>
                <button class="btn btn-light btn-sm" type="button" onclick="exportMapToPDF()">
                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                </button>
                <button class="btn btn-light btn-sm" type="button" onclick="refreshMapData()">
                    <i class="fas fa-rotate me-1"></i>Actualiser
                </button>
            </div>
        </div>

        <div id="filterPanel" class="dash-filters" style="display: none;">
            <div class="row g-2">
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="filterYear">Année</label>
                    <select id="filterYear" class="form-select form-select-sm" onchange="applyMapFilters()">
                        <option value="">Toutes</option>
                        @if(isset($chartsData['availableYears']) && count($chartsData['availableYears']) > 0)
                            @foreach($chartsData['availableYears'] as $year)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        @else
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        @endif
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="filterMonth">Mois</label>
                    <select id="filterMonth" class="form-select form-select-sm" onchange="applyMapFilters()">
                        <option value="">Tous</option>
                        <option value="1">Janvier</option>
                        <option value="2">Février</option>
                        <option value="3">Mars</option>
                        <option value="4">Avril</option>
                        <option value="5">Mai</option>
                        <option value="6">Juin</option>
                        <option value="7">Juillet</option>
                        <option value="8">Août</option>
                        <option value="9">Septembre</option>
                        <option value="10">Octobre</option>
                        <option value="11">Novembre</option>
                        <option value="12">Décembre</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="filterRegion">Région</label>
                    <select id="filterRegion" class="form-select form-select-sm" onchange="applyMapFilters()">
                        <option value="">Toutes</option>
                        @if(isset($chartsData['availableRegions']) && count($chartsData['availableRegions']) > 0)
                            @foreach($chartsData['availableRegions'] as $region)
                                <option value="{{ $region }}">{{ $region }}</option>
                            @endforeach
                        @else
                            <option value="Dakar">Dakar</option>
                            <option value="Saint-Louis">Saint-Louis</option>
                            <option value="Thiès">Thiès</option>
                            <option value="Diourbel">Diourbel</option>
                            <option value="Kaolack">Kaolack</option>
                            <option value="Ziguinchor">Ziguinchor</option>
                            <option value="Louga">Louga</option>
                            <option value="Fatick">Fatick</option>
                            <option value="Kolda">Kolda</option>
                            <option value="Matam">Matam</option>
                            <option value="Tambacounda">Tambacounda</option>
                            <option value="Kaffrine">Kaffrine</option>
                            <option value="Kédougou">Kédougou</option>
                            <option value="Sédhiou">Sédhiou</option>
                        @endif
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="filterStatus">Statut</label>
                    <select id="filterStatus" class="form-select form-select-sm" onchange="applyMapFilters()">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="traitee">Traitée</option>
                        <option value="rejetee">Rejetée</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="filterType">Type</label>
                    <select id="filterType" class="form-select form-select-sm" onchange="applyMapFilters()">
                        <option value="all">Tout afficher</option>
                        <option value="warehouses">Entrepôts uniquement</option>
                        <option value="demandes">Demandes (points)</option>
                        <option value="zones">Zones par région (demandes)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-primary btn-sm w-100" type="button" onclick="resetMapFilters()">
                        <i class="fas fa-rotate-left me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <div class="dash-map-stats">
            <div class="dash-map-stat">
                <i class="fas fa-warehouse text-primary"></i>
                <span><strong id="mapStatWarehouses">0</strong> Entrepôts</span>
            </div>
            <div class="dash-map-stat">
                <i class="fas fa-location-dot text-danger"></i>
                <span><strong id="mapStatDemandes">0</strong> Demandes</span>
            </div>
            <div class="dash-map-stat">
                <i class="fas fa-clock text-warning"></i>
                <span><strong id="mapStatPending">0</strong> En attente</span>
            </div>
            <div class="dash-map-stat">
                <i class="fas fa-circle-check text-success"></i>
                <span><strong id="mapStatApproved">0</strong> Traitées</span>
            </div>
        </div>

        <div class="position-relative">
            <div id="warehouseMap" class="dash-map"></div>
            <div id="mapLegend" class="dash-map-legend">
                <div class="fw-semibold small mb-2">Légende</div>
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-warehouse text-primary me-2"></i>
                    <span class="small">Entrepôts CSAR</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" width="16" height="16" class="me-2" alt="Logo CSAR">
                    <span class="small">Demandes d’aide</span>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-warning-subtle text-warning-emphasis">En attente</span>
                    <span class="badge bg-success-subtle text-success-emphasis">Traitée</span>
                    <span class="badge bg-danger-subtle text-danger-emphasis">Rejetée</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .dash { display: flex; flex-direction: column; gap: 1.25rem; }

    .dash-header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem; }
    .dash-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 .25rem; color: var(--adm-text); }
    .dash-subtitle { margin: 0; color: var(--adm-muted); font-size: .875rem; }
    .dash-header-actions { display: flex; gap: .5rem; flex-wrap: wrap; }

    .dash-card {
        background: var(--adm-surface);
        border: 1px solid var(--adm-border);
        border-radius: var(--adm-radius);
        box-shadow: var(--adm-shadow);
        padding: 1.25rem;
        min-width: 0;
    }
    .dash-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .dash-card-title { font-size: 1rem; font-weight: 600; margin: 0; color: var(--adm-text); }
    .dash-card-subtitle { font-size: .8125rem; color: var(--adm-muted); margin: .125rem 0 0; }

    .dash-empty { text-align: center; padding: 2rem 1.25rem; }
    .dash-empty-icon { width: 48px; height: 48px; border-radius: 50%; background: var(--adm-subtle); color: var(--adm-muted); display: inline-flex; align-items: center; justify-content: center; margin-bottom: .75rem; }

    /* KPI */
    .dash-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
    .dash-kpi {
        display: flex; align-items: flex-start; gap: .875rem;
        background: var(--adm-surface);
        border: 1px solid var(--adm-border);
        border-radius: var(--adm-radius);
        box-shadow: var(--adm-shadow);
        padding: 1rem 1.125rem;
        color: inherit;
        text-decoration: none;
        min-width: 0;
    }
    .dash-kpi.is-link { transition: border-color .15s ease, box-shadow .15s ease; }
    .dash-kpi.is-link:hover { border-color: #CBD5E1; box-shadow: var(--adm-shadow-md); color: inherit; }
    .dash-kpi.is-link:focus-visible { outline: 2px solid var(--adm-navy); outline-offset: 2px; }
    .dash-kpi-icon {
        width: 40px; height: 40px; border-radius: var(--adm-radius-sm);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .tone-navy { background: #E7ECF1; color: var(--adm-navy); }
    .tone-green { background: var(--adm-green-50); color: var(--adm-green); }
    .tone-warning { background: var(--adm-warning-50); color: var(--adm-warning); }
    .tone-danger { background: var(--adm-danger-50); color: var(--adm-danger); }
    .tone-info { background: var(--adm-info-50); color: var(--adm-info); }
    .tone-slate { background: #EEF2F6; color: #475569; }
    .dash-kpi-body { display: flex; flex-direction: column; min-width: 0; }
    .dash-kpi-label { font-size: .8125rem; color: var(--adm-muted); font-weight: 500; }
    .dash-kpi-value { font-size: 1.625rem; font-weight: 700; line-height: 1.2; color: var(--adm-text); font-variant-numeric: tabular-nums; margin: .125rem 0; }
    .dash-kpi-unit { font-size: .875rem; font-weight: 600; color: var(--adm-muted); margin-left: .25rem; }
    .dash-kpi-meta { font-size: .75rem; color: var(--adm-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Workflow */
    .dash-flow { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: .75rem; }
    .dash-flow-step {
        position: relative;
        display: flex; flex-direction: column; align-items: flex-start;
        padding: .875rem;
        border: 1px solid var(--adm-border);
        border-radius: var(--adm-radius-sm);
        background: var(--adm-subtle);
        color: inherit; text-decoration: none;
        min-width: 0;
    }
    .dash-flow-step:not(:last-child)::after {
        content: '';
        position: absolute; top: 50%; right: -.5rem;
        width: .25rem; height: 1px; background: #CBD5E1;
    }
    .dash-flow-step.is-link { background: var(--adm-surface); transition: border-color .15s ease; }
    .dash-flow-step.is-link:hover { border-color: var(--adm-navy); color: inherit; }
    .dash-flow-step.is-pending { border-color: #E9C46A; background: var(--adm-warning-50); }
    .dash-flow-icon { font-size: .875rem; color: var(--adm-muted); margin-bottom: .5rem; }
    .dash-flow-step.is-pending .dash-flow-icon { color: var(--adm-warning); }
    .dash-flow-count { font-size: 1.375rem; font-weight: 700; line-height: 1.1; color: var(--adm-text); font-variant-numeric: tabular-nums; }
    .dash-flow-label { font-size: .8125rem; font-weight: 600; color: var(--adm-text); margin-top: .125rem; }
    .dash-flow-hint { font-size: .6875rem; color: var(--adm-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }

    .dash-flow-summary {
        display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem;
        margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--adm-border);
    }
    .dash-summary-item { display: flex; flex-direction: column; color: inherit; text-decoration: none; padding: .25rem .5rem; border-radius: 6px; }
    .dash-summary-item.is-link:hover { background: var(--adm-subtle); color: inherit; }
    .dash-summary-label { font-size: .75rem; color: var(--adm-muted); display: flex; align-items: center; gap: .375rem; }
    .dash-summary-value { font-size: 1.125rem; font-weight: 700; font-variant-numeric: tabular-nums; }
    .dash-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

    /* Grilles */
    .dash-grid { display: grid; gap: 1.25rem; }
    .dash-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dash-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .dash-span-2 { grid-column: span 2; }
    .dash-stack { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }

    /* Listes */
    .dash-list { display: flex; flex-direction: column; }
    .dash-list-item {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .75rem .5rem; margin: 0 -.5rem;
        border-bottom: 1px solid var(--adm-border);
        color: inherit; text-decoration: none; border-radius: 6px;
    }
    .dash-list-item:last-child { border-bottom: 0; }
    .dash-list-item:hover { background: var(--adm-subtle); color: inherit; }
    .dash-list-main { display: flex; flex-direction: column; min-width: 0; }
    .dash-list-title { font-weight: 600; font-size: .875rem; }
    .dash-list-meta { font-size: .75rem; color: var(--adm-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dash-list-empty { display: flex; flex-direction: column; align-items: center; gap: .5rem; padding: 1.5rem 0; color: var(--adm-muted); font-size: .875rem; }
    .dash-list-empty i { font-size: 1.25rem; }

    /* Alertes */
    .dash-alerts { display: flex; flex-direction: column; gap: .5rem; }
    .dash-alert { display: flex; gap: .75rem; align-items: flex-start; padding: .75rem; border-radius: var(--adm-radius-sm); border: 1px solid transparent; }
    .dash-alert i { margin-top: .15rem; }
    .dash-alert.tone-danger { border-color: #F2C9C4; }
    .dash-alert.tone-warning { border-color: #EED9A8; }
    .dash-alert.tone-success { background: var(--adm-green-50); color: var(--adm-green); border-color: #BFE3D3; }
    .dash-alert.tone-info { border-color: #C5DBEC; }
    .dash-alert-title { font-size: .8125rem; font-weight: 600; color: var(--adm-text); }
    .dash-alert-text { font-size: .75rem; color: var(--adm-muted); }

    /* Actions */
    .dash-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
    .dash-action {
        display: flex; flex-direction: column; align-items: flex-start; gap: .375rem;
        padding: .75rem; border: 1px solid var(--adm-border); border-radius: var(--adm-radius-sm);
        background: var(--adm-surface); color: var(--adm-text); text-decoration: none;
        font-size: .8125rem; font-weight: 500; text-align: left;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .dash-action i { color: var(--adm-green); }
    .dash-action:hover { border-color: #CBD5E1; background: var(--adm-subtle); color: var(--adm-navy); }

    /* Graphiques */
    .chart-container { position: relative; height: 240px; }
    .chart-container canvas { width: 100% !important; height: 100% !important; }
    .dash-chart-empty { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem; color: var(--adm-muted); font-size: .875rem; }
    .dash-chart-empty i { font-size: 1.5rem; }

    /* Carte */
    .dash-filters { border: 1px solid var(--adm-border); border-radius: var(--adm-radius-sm); background: var(--adm-subtle); padding: .875rem; margin-bottom: 1rem; }
    .dash-map-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .dash-map-stat { display: flex; align-items: center; gap: .625rem; padding: .625rem .875rem; border: 1px solid var(--adm-border); border-radius: var(--adm-radius-sm); font-size: .8125rem; color: var(--adm-muted); }
    .dash-map-stat strong { color: var(--adm-text); font-size: 1rem; margin-right: .25rem; }
    .dash-map { height: 500px; border-radius: var(--adm-radius-sm); overflow: hidden; z-index: 1; border: 1px solid var(--adm-border); }
    .dash-map-legend {
        position: absolute; top: 10px; right: 10px; z-index: 1000;
        background: var(--adm-surface); border: 1px solid var(--adm-border);
        border-radius: var(--adm-radius-sm); box-shadow: var(--adm-shadow);
        padding: .75rem; max-width: 200px;
    }

    @media (max-width: 1399.98px) {
        .dash-flow { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .dash-flow-step::after { display: none; }
    }
    @media (max-width: 1199.98px) {
        .dash-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dash-grid-3 { grid-template-columns: 1fr; }
        .dash-span-2 { grid-column: auto; }
        .dash-flow-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .dash-grid-2 { grid-template-columns: 1fr; }
        .dash-flow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dash-flow-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dash-map-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dash-map { height: 380px; }
        .dash-map-legend { position: static; max-width: none; margin-top: .75rem; box-shadow: none; }
    }
    @media (max-width: 479.98px) {
        .dash-kpis { grid-template-columns: 1fr; }
        .dash-actions { grid-template-columns: 1fr; }
        .dash-header-actions { width: 100%; }
        .dash-header-actions .btn { flex: 1; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<!-- jsPDF et html2canvas pour l'export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<!-- Leaflet.markercluster pour grouper les marqueurs -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
// Graphique des demandes (moderne)
let requestsChart, warehousesChart, fuelChart, stockChart;
const requestsCtx = document.getElementById('requestsChart');
if (requestsCtx) {
    // Utiliser les vraies données du contrôleur
    const chartData = @json($chartsData['last7Days'] ?? []);
    const labels = chartData.map(item => item.label);
    const data = chartData.map(item => item.requests);

    requestsChart = new Chart(requestsCtx, {
        type: 'line',
        data: {
            labels: labels.length > 0 ? labels : ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Demandes',
                data: data.length > 0 ? data : [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#12304A',
                backgroundColor: 'rgba(18, 48, 74, 0.06)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#12304A',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#E2E8F0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Graphique des entrepôts (camembert)
const warehousesCtx = document.getElementById('warehousesChart');
if (warehousesCtx) {
    // Utiliser les vraies données du contrôleur
    const entrepotsData = @json($chartsData['entrepotsByRegion'] ?? []);
    const labels = Object.keys(entrepotsData);
    const data = Object.values(entrepotsData);

    warehousesChart = new Chart(warehousesCtx, {
        type: 'doughnut',
        data: {
            labels: labels.length > 0 ? labels : ['Aucune donnée'],
            datasets: [{
                data: data.length > 0 ? data : [1],
                backgroundColor: [
                    '#12304A',
                    '#16845B',
                    '#1D6FA5',
                    '#B7791F',
                    '#64748B'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        boxWidth: 12
                    }
                }
            }
        }
    });
}

// Graphique du carburant (barres)
const fuelCtx = document.getElementById('fuelChart');
if (fuelCtx) {
    // Utiliser les vraies données du contrôleur
    const fuelData = @json($chartsData['fuelByEntrepot'] ?? []);
    const labels = Object.keys(fuelData);
    const data = Object.values(fuelData);

    fuelChart = new Chart(fuelCtx, {
        type: 'bar',
        data: {
            labels: labels.length > 0 ? labels : ['Aucune donnée'],
            datasets: [{
                label: 'Carburant (L)',
                data: data.length > 0 ? data : [0],
                backgroundColor: '#1D6FA5',
                borderWidth: 0,
                borderRadius: 4,
                maxBarThickness: 36
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        maxTicksLimit: 5
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Graphique des stocks (barres horizontales)
const stockCtx = document.getElementById('stockChart');
if (stockCtx) {
    // Utiliser les vraies données du contrôleur
    const stockData = @json($chartsData['stockByCategory'] ?? []);
    const labels = Object.keys(stockData);
    const data = Object.values(stockData);

    stockChart = new Chart(stockCtx, {
        type: 'bar',
        data: {
            labels: labels.length > 0 ? labels : ['Aucune donnée'],
            datasets: [{
                label: 'Stock (tonnes)',
                data: data.length > 0 ? data : [0],
                backgroundColor: '#16845B',
                borderWidth: 0,
                borderRadius: 4,
                maxBarThickness: 28
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        maxTicksLimit: 5
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ==================== CARTE INTERACTIVE AVEC GÉOLOCALISATION ====================
let warehouseMap;
let markersLayer;
let currentMapData = [];

// Icônes personnalisées
const warehouseIcon = L.divIcon({
    className: 'custom-div-icon',
    html: '<div style="background-color: #3b82f6; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fas fa-warehouse" style="color: white; font-size: 14px;"></i></div>',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

// Icône personnalisée avec logo CSAR pour les demandes
const demandeIcon = L.divIcon({
    className: 'custom-div-icon',
    html: '<div style="background-color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #dc3545; box-shadow: 0 3px 10px rgba(220,53,69,0.5);"><img src="{{ asset("images/logos/LOGO CSAR vectoriel-01.png") }}" style="width: 22px; height: 22px; object-fit: contain;" /></div>',
    iconSize: [35, 35],
    iconAnchor: [17, 17]
});

function initWarehouseMap() {
    // Centré sur le Sénégal
    warehouseMap = L.map('warehouseMap').setView([14.4974, -14.4524], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | CSAR Admin'
    }).addTo(warehouseMap);

    // Initialiser le groupe de marqueurs avec clustering
    markersLayer = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 50
    });

    // Charger les données initiales
    const initialData = @json($chartsData['mapData'] ?? []);
    currentMapData = initialData;
    renderMapMarkers(initialData);
    updateMapStats(initialData);
}

function renderMapMarkers(data) {
    // Effacer les marqueurs existants
    if (markersLayer) {
        markersLayer.clearLayers();
    }

    // Compteurs pour les statistiques
    let warehouseCount = 0;
    let demandeCount = 0;
    let pendingCount = 0;
    let approvedCount = 0;

    if (data && data.length > 0) {
        data.forEach(item => {
            let icon, popupContent, statusBadge;

            if (item.type === 'warehouse') {
                warehouseCount++;
                icon = warehouseIcon;
                statusBadge = '<span class="badge bg-primary">Entrepôt</span>';
                popupContent = `
                    <div style="min-width: 200px;">
                        <h6 class="mb-2 fw-bold text-primary">
                            <i class="fas fa-warehouse me-1"></i>${item.name}
                        </h6>
                        ${statusBadge}
                        <hr class="my-2">
                        <p class="mb-1 small"><strong>Région:</strong> ${item.region}</p>
                        <p class="mb-1 small"><strong>Adresse:</strong> ${item.address}</p>
                        <p class="mb-2 small"><strong>Statut:</strong> ${item.status || 'Actif'}</p>
                        <a href="/admin/entrepots" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-eye me-1"></i>Voir détails
                        </a>
                </div>
                `;
            } else if (item.type === 'demande') {
                demandeCount++;
                icon = demandeIcon;

                // Déterminer le badge de statut
                let badgeClass = 'warning';
                let statusText = item.status;
                if (item.status === 'traitee' || item.status === 'approved') {
                    badgeClass = 'success';
                    statusText = 'Traitée';
                    approvedCount++;
                } else if (item.status === 'rejetee' || item.status === 'rejected') {
                    badgeClass = 'danger';
                    statusText = 'Rejetée';
                } else if (item.status === 'en_attente' || item.status === 'pending') {
                    badgeClass = 'warning';
                    statusText = 'En attente';
                    pendingCount++;
                }

                statusBadge = `<span class="badge bg-${badgeClass}">${statusText}</span>`;

                popupContent = `
                    <div style="min-width: 220px;">
                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset("images/logos/LOGO CSAR vectoriel-01.png") }}" width="24" height="24" class="me-2" />
                            <h6 class="mb-0 fw-bold text-danger">Demande d'aide</h6>
                        </div>
                        ${statusBadge}
                        <hr class="my-2">
                        <p class="mb-1 small"><strong>Demandeur:</strong> ${item.name}</p>
                        <p class="mb-1 small"><strong>Région:</strong> ${item.region}</p>
                        <p class="mb-1 small"><strong>Date:</strong> ${item.created_at}</p>
                        <p class="mb-2 small"><strong>Adresse:</strong> ${item.address}</p>
                        <a href="/admin/demandes/${item.id.replace('P','')}" class="btn btn-sm btn-danger w-100">
                            <i class="fas fa-eye me-1"></i>Voir la demande
                        </a>
                    </div>
                `;
            } else if (item.type === 'zone') {
                demandeCount += item.total || 0;
                pendingCount += item.pending || 0;
                approvedCount += item.approved || 0;
                const marker = L.circleMarker([item.lat, item.lng], {
                    color: '#2563eb',
                    fillColor: '#2563eb',
                    fillOpacity: 0.6,
                    radius: Math.min(14, 6 + (item.total || 0)),
                    weight: 2
                });
                marker.bindPopup(`
                    <div style="min-width: 200px;">
                        <h6 class="mb-2 fw-bold text-primary"><i class="fas fa-map-marker-alt me-1"></i>${item.name || item.region}</h6>
                        <span class="badge bg-info">Région</span>
                        <hr class="my-2">
                        <p class="mb-1 small"><strong>Total demandes:</strong> ${item.total}</p>
                        <p class="mb-1 small"><strong>Approuvées:</strong> ${item.approved}</p>
                        <p class="mb-1 small"><strong>En attente:</strong> ${item.pending}</p>
                        <p class="mb-2 small"><strong>Rejetées:</strong> ${item.rejected}</p>
                        <a href="/admin/demandes?region=${encodeURIComponent(item.region)}" class="btn btn-sm btn-outline-primary w-100">Voir les demandes</a>
                    </div>
                `);
                markersLayer.addLayer(marker);
                return;
            }

            if (item.type !== 'zone') {
                const marker = L.marker([item.lat, item.lng], { icon: icon });
                marker.bindPopup(popupContent);
                markersLayer.addLayer(marker);
            }
        });

        warehouseMap.addLayer(markersLayer);

        // Ajuster la vue pour englober tous les marqueurs
        if (markersLayer.getLayers().length > 0) {
            warehouseMap.fitBounds(markersLayer.getBounds(), { padding: [50, 50] });
        }
    } else {
        // Afficher un message si aucune donnée
        const centerMarker = L.marker([14.4974, -14.4524], { icon: warehouseIcon });
        centerMarker.bindPopup(`
            <div class="text-center p-2">
                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                <h6>Aucune donnée disponible</h6>
                <p class="mb-0 small">Ajustez vos filtres ou ajoutez des données</p>
            </div>
        `);
        markersLayer.addLayer(centerMarker);
        warehouseMap.addLayer(markersLayer);
    }

    // Mettre à jour les statistiques
    document.getElementById('mapStatWarehouses').textContent = warehouseCount;
    document.getElementById('mapStatDemandes').textContent = demandeCount;
    document.getElementById('mapStatPending').textContent = pendingCount;
    document.getElementById('mapStatApproved').textContent = approvedCount;
}

function updateMapStats(data) {
    if (!data) return;

    const warehouses = data.filter(item => item.type === 'warehouse').length;
    const demandesPoints = data.filter(item => item.type === 'demande').length;
    const zones = data.filter(item => item.type === 'zone');
    const demandesFromZones = zones.reduce((s, z) => s + (z.total || 0), 0);
    const demandes = demandesPoints + (zones.length ? demandesFromZones : 0);
    let pending = data.filter(item =>
        item.type === 'demande' &&
        (item.status === 'en_attente' || item.status === 'pending')
    ).length;
    let approved = data.filter(item =>
        item.type === 'demande' &&
        (item.status === 'traitee' || item.status === 'approved')
    ).length;
    if (zones.length) {
        pending += zones.reduce((s, z) => s + (z.pending || 0), 0);
        approved += zones.reduce((s, z) => s + (z.approved || 0), 0);
    }

    document.getElementById('mapStatWarehouses').textContent = warehouses;
    document.getElementById('mapStatDemandes').textContent = demandes;
    document.getElementById('mapStatPending').textContent = pending;
    document.getElementById('mapStatApproved').textContent = approved;
}

// ==================== FILTRES ====================
function toggleFilters() {
    const panel = document.getElementById('filterPanel');
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

function applyMapFilters() {
    const year = document.getElementById('filterYear').value;
    const month = document.getElementById('filterMonth').value;
    const region = document.getElementById('filterRegion').value;
    const status = document.getElementById('filterStatus').value;
    const type = document.getElementById('filterType').value;

    // Afficher un loader
    showToast('Application des filtres...', 'info');

    // Appeler l'API avec les filtres
    fetch('{{ route("admin.dashboard.filter-map") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            year: year,
            month: month,
            region: region,
            status: status,
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentMapData = data.data;
            renderMapMarkers(data.data);
            showToast(`Filtres appliqués: ${data.count} éléments trouvés`, 'success');
        } else {
            showToast('Erreur lors du filtrage', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors du filtrage des données', 'danger');
    });
}

function resetMapFilters() {
    document.getElementById('filterYear').value = '';
    document.getElementById('filterMonth').value = '';
    document.getElementById('filterRegion').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterType').value = 'all';

    // Recharger les données initiales
    const initialData = @json($chartsData['mapData'] ?? []);
    currentMapData = initialData;
    renderMapMarkers(initialData);
    showToast('Filtres réinitialisés', 'success');
}

function refreshMapData() {
    applyMapFilters();
}

// ==================== EXPORT PDF ====================
function exportMapToPDF() {
    showToast('Génération du PDF en cours...', 'info');

    // Cacher temporairement les contrôles de la carte pour un PDF propre
    const legend = document.getElementById('mapLegend');
    const legendDisplay = legend.style.display;
    legend.style.display = 'none';

    // Capturer la carte avec html2canvas
    html2canvas(document.getElementById('warehouseMap'), {
        useCORS: true,
        logging: false,
        scale: 2
    }).then(canvas => {
        // Restaurer la légende
        legend.style.display = legendDisplay;

        const imgData = canvas.toDataURL('image/png');

        // Créer le PDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4'); // landscape, millimeters, A4

        // Dimensions
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const imgWidth = pdfWidth - 20;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        // En-tête
        pdf.setFontSize(18);
        pdf.setTextColor(59, 130, 246);
        pdf.text('CSAR - Carte des Entrepots et Demandes', pdfWidth / 2, 15, { align: 'center' });

        // Date et filtres
        pdf.setFontSize(10);
        pdf.setTextColor(100, 100, 100);
        const dateStr = new Date().toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        pdf.text('Genere le: ' + dateStr, 10, 22);

        // Statistiques
        const warehouses = currentMapData.filter(item => item.type === 'warehouse').length;
        const demandes = currentMapData.filter(item => item.type === 'demande').length;
        pdf.text('Entrepots: ' + warehouses + ' | Demandes: ' + demandes, pdfWidth - 10, 22, { align: 'right' });

        // Image de la carte
        pdf.addImage(imgData, 'PNG', 10, 28, imgWidth, Math.min(imgHeight, pdfHeight - 40));

        // Légende
        let legendY = Math.min(imgHeight + 35, pdfHeight - 35);
        pdf.setFontSize(12);
        pdf.setTextColor(0, 0, 0);
        pdf.text('Legende:', 10, legendY);

        pdf.setFontSize(10);
        pdf.setTextColor(59, 130, 246);
        pdf.text('- Entrepots CSAR', 15, legendY + 6);
        pdf.setTextColor(220, 53, 69);
        pdf.text('- Demandes d\'aide alimentaire', 50, legendY + 6);

        // Statuts
        pdf.setFontSize(9);
        pdf.setTextColor(100, 100, 100);
        pdf.text('Statuts:', 10, legendY + 12);
        pdf.setTextColor(255, 193, 7);
        pdf.text('- En attente', 15, legendY + 17);
        pdf.setTextColor(40, 167, 69);
        pdf.text('- Traitee', 45, legendY + 17);
        pdf.setTextColor(220, 53, 69);
        pdf.text('- Rejetee', 70, legendY + 17);

        // Pied de page
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        pdf.text('(c) CSAR - Commissariat a la Securite Alimentaire et a la Resilience', pdfWidth / 2, pdfHeight - 5, { align: 'center' });

        // Télécharger le PDF
        const filename = `carte_csar_${new Date().getTime()}.pdf`;
        pdf.save(filename);

        showToast('PDF genere avec succes!', 'success');
    }).catch(error => {
        legend.style.display = legendDisplay;
        console.error('Erreur lors de la generation du PDF:', error);
        showToast('Erreur lors de la generation du PDF', 'danger');
    });
}

// Actualisation des statistiques dynamiques
function updateStats() {
    fetch('{{ route("admin.dashboard.realtime-stats") }}')
        .then(response => response.json())
        .then(data => {
            // Update stats cards avec animation
            document.querySelectorAll('[data-stat]').forEach(element => {
                const statName = element.getAttribute('data-stat');
                if (data[statName] !== undefined) {
                    animateValue(element, parseInt(element.textContent), data[statName], 1000);
                }
            });

            // Update notifications and messages
            const unreadNotificationsEl = document.getElementById('unread-notifications');
            const unreadMessagesEl = document.getElementById('unread-messages');

            if (unreadNotificationsEl) {
                animateValue(unreadNotificationsEl, parseInt(unreadNotificationsEl.textContent), data.unread_notifications || 0, 1000);
            }
            if (unreadMessagesEl) {
                animateValue(unreadMessagesEl, parseInt(unreadMessagesEl.textContent), data.unread_messages || 0, 1000);
            }

            // Mettre à jour les graphiques avec les nouvelles données
            updateCharts(data);

            // Mettre à jour la carte
            updateMap(data.map_data || []);

        })
        .catch(error => console.error('Erreur lors de la mise à jour des statistiques:', error));
}

// Animation des valeurs
function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Actualisation complète du dashboard
function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    const icon = refreshBtn.querySelector('i');

    // Animation de rotation
    icon.style.animation = 'spin 1s linear infinite';

    // Actualiser les données
    updateStats();

    // Actualiser les graphiques
    setTimeout(() => {
        if (typeof requestsChart !== 'undefined') requestsChart.update();
        if (typeof warehousesChart !== 'undefined') warehousesChart.update();
        if (typeof fuelChart !== 'undefined') fuelChart.update();
        if (typeof stockChart !== 'undefined') stockChart.update();

        // Arrêter l'animation
        icon.style.animation = '';

        // Afficher un message de succès
        showToast('Dashboard actualisé avec succès!', 'success');
    }, 1000);
}

// Fonction pour afficher des toasts
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Supprimer le toast après 3 secondes
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Fonction pour mettre à jour les graphiques
function updateCharts(data) {
    // Mettre à jour le graphique des demandes
    if (typeof requestsChart !== 'undefined' && data.last7Days) {
        const labels = data.last7Days.map(item => item.label);
        const chartData = data.last7Days.map(item => item.requests);
        requestsChart.data.labels = labels;
        requestsChart.data.datasets[0].data = chartData;
        requestsChart.update('none'); // Mise à jour sans animation pour éviter les clignotements
    }

    // Mettre à jour le graphique des entrepôts
    if (typeof warehousesChart !== 'undefined' && data.entrepotsByRegion) {
        const labels = Object.keys(data.entrepotsByRegion);
        const chartData = Object.values(data.entrepotsByRegion);
        warehousesChart.data.labels = labels;
        warehousesChart.data.datasets[0].data = chartData;
        warehousesChart.update('none');
    }

    // Mettre à jour le graphique du carburant
    if (typeof fuelChart !== 'undefined' && data.fuelByEntrepot) {
        const labels = Object.keys(data.fuelByEntrepot);
        const chartData = Object.values(data.fuelByEntrepot);
        fuelChart.data.labels = labels;
        fuelChart.data.datasets[0].data = chartData;
        fuelChart.update('none');
    }

    // Mettre à jour le graphique des stocks
    if (typeof stockChart !== 'undefined' && data.stockByCategory) {
        const labels = Object.keys(data.stockByCategory);
        const chartData = Object.values(data.stockByCategory);
        stockChart.data.labels = labels;
        stockChart.data.datasets[0].data = chartData;
        stockChart.update('none');
    }
}

// Fonction pour mettre à jour la carte
function updateMap(mapData) {
    if (typeof warehouseMap !== 'undefined' && mapData) {
        currentMapData = mapData;
        renderMapMarkers(mapData);
        updateMapStats(mapData);
    }
}

// Initialiser la carte et les mises à jour automatiques
document.addEventListener('DOMContentLoaded', function() {
    initWarehouseMap();

    // Mise à jour automatique toutes les 30 secondes
    setInterval(updateStats, 30000);

    // Animation CSS pour la rotation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});

// Générer un rapport
function generateReport(type = 'dashboard', format = 'pdf') {
    console.log('Début de génération du rapport...');

    // Afficher un indicateur de chargement
    showToast('Génération du rapport en cours...', 'info');

    // Vérifier que la route existe
    const url = '{{ route("admin.dashboard.generate-report") }}';
    console.log('URL de la route:', url);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: type,
            format: format,
            period: 'month'
        })
    })
    .then(response => {
        console.log('Réponse reçue:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Données reçues:', data);
        if (data.success) {
            showToast('Rapport généré avec succès!', 'success');
            // Télécharger le rapport
            if (data.download_url) {
                console.log('Téléchargement du fichier:', data.download_url);
                // Créer un lien de téléchargement temporaire
                const link = document.createElement('a');
                link.href = data.download_url;
                link.download = data.filename || 'rapport.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                showToast('Rapport généré mais URL de téléchargement manquante', 'warning');
            }
        } else {
            showToast('Erreur lors de la génération du rapport: ' + (data.message || 'Erreur inconnue'), 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur détaillée:', error);
        showToast('Erreur lors de la génération du rapport: ' + error.message, 'danger');
    });
}


// Générer un rapport avec options
function generateReportWithOptions() {
    const type = document.getElementById('report-type')?.value || 'dashboard';
    const format = document.getElementById('report-format')?.value || 'pdf';
    const period = document.getElementById('report-period')?.value || 'month';

    generateReport(type, format, period);
}
</script>
@endpush
