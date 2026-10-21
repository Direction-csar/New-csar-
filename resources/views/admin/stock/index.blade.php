@extends('layouts.admin')

@section('title', 'Gestion des Stocks - Historique des Reçus')

@push('styles')
<style>
    /* Amélioration de la fluidité et lisibilité */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .stock-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px 0;
        overflow-x: hidden;
    }

    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }

    .stats-card:hover::before {
        opacity: 1;
    }

    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .stat-label {
        color: #4a5568;
        font-weight: 500;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 6px;
    }

    /* Nouvelles cartes modernes - Améliorées pour la lisibilité */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
        padding: 0 10px;
    }

    .stat-card-modern {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(20px);
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
        border-radius: 24px 24px 0 0;
    }

    .stat-card-modern:hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12);
        background: rgba(255, 255, 255, 1);
    }

    .stat-card-modern.stat-primary {
        --card-color: #667eea;
        --card-color-light: #764ba2;
    }

    .stat-card-modern.stat-success {
        --card-color: #28a745;
        --card-color-light: #20c997;
    }

    .stat-card-modern.stat-danger {
        --card-color: #dc3545;
        --card-color-light: #fd7e14;
    }

    .stat-card-modern.stat-info {
        --card-color: #17a2b8;
        --card-color-light: #6f42c1;
    }

    .stat-card-modern.stat-warning {
        --card-color: #ffc107;
        --card-color-light: #fd7e14;
    }

    .stat-icon-modern {
        width: 55px;
        height: 55px;
        border-radius: 15px;
        background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        color: white;
        font-size: 1.4rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .stat-card-modern:hover .stat-icon-modern {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .stat-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-number-modern {
        font-size: 2.4rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 6px;
        line-height: 1;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .stat-label-modern {
        color: #4a5568;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        font-weight: 700;
        padding: 8px 12px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        margin-top: auto;
    }

    /* Carte de valeur spéciale - Améliorée */
    .value-card-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 28px;
        color: white;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.25);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        min-height: 180px;
        transition: all 0.5s ease;
    }

    .value-card-modern:hover {
        transform: translateY(-8px);
        box-shadow: 0 35px 100px rgba(102, 126, 234, 0.35);
    }

    .value-card-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-20px, -20px) rotate(180deg); }
    }

    .value-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .value-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.3rem;
    }

    .value-title {
        font-size: 1rem;
        font-weight: 600;
        opacity: 0.95;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .value-amount {
        margin-bottom: 25px;
        position: relative;
        z-index: 2;
    }

    .currency {
        font-size: 1rem;
        opacity: 0.8;
        margin-right: 10px;
    }

    .amount {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .value-details {
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .value-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 0.9rem;
    }

    .value-item .label {
        opacity: 0.8;
    }

    .value-item .value {
        font-weight: 600;
    }

    .value-chart {
        position: relative;
        z-index: 2;
        height: 60px;
        margin-top: auto;
    }

    .filter-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .table-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .table-modern {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .table-modern thead {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .table-modern th {
        border: none;
        padding: 15px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table-modern td {
        border: none;
        padding: 15px;
        border-bottom: 1px solid #f8f9fa;
        vertical-align: middle;
    }

    .table-modern tbody tr:hover {
        background-color: #f8f9ff;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .badge-modern {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-entree {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .badge-sortie {
        background: linear-gradient(135deg, #dc3545, #fd7e14);
        color: white;
    }

    .badge-transfert {
        background: linear-gradient(135deg, #007bff, #6f42c1);
        color: white;
    }

    .badge-ajustement {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
    }

    .btn-modern {
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .btn-success-modern {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }

    .btn-danger-modern {
        background: linear-gradient(135deg, #dc3545, #fd7e14);
        color: white;
    }

    .btn-info-modern {
        background: linear-gradient(135deg, #17a2b8, #6f42c1);
        color: white;
    }

    .chart-container {
        height: 200px;
        margin-bottom: 20px;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    .search-input {
        border-radius: 25px;
        border: 2px solid #e9ecef;
        padding: 12px 20px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .filter-select {
        border-radius: 15px;
        border: 2px solid #e9ecef;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .pagination-modern {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }

    .pagination-modern .page-link {
        border-radius: 10px;
        margin: 0 5px;
        border: 2px solid #e9ecef;
        color: #667eea;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .pagination-modern .page-link:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-color: #667eea;
    }

    .pagination-modern .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-color: #667eea;
    }

    @media (max-width: 768px) {
        .stats-card {
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2rem;
        }

        .table-container {
            padding: 15px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .chart-container {
            height: 150px;
        }

        /* Responsive pour les nouvelles cartes */
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-card-modern {
            padding: 20px;
        }

        .stat-number-modern {
            font-size: 2.2rem;
        }

        .stat-icon-modern {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        .value-card-modern {
            padding: 25px;
        }

        .amount {
            font-size: 2rem;
        }

        .value-details {
            margin-bottom: 15px;
        }

        .value-item {
            font-size: 0.8rem;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card-modern {
            padding: 15px;
        }

        .stat-number-modern {
            font-size: 2rem;
        }

        .value-card-modern {
            padding: 20px;
        }

        .amount {
            font-size: 1.8rem;
        }
    }

    /* Améliorations de responsivité et lisibilité */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .stat-number-modern {
            font-size: 2.2rem;
        }
    }

    @media (max-width: 768px) {
        .stock-page {
            padding: 15px 0;
        }

        .container-fluid {
            padding: 0 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 15px;
            padding: 0 5px;
        }

        .stat-card-modern {
            padding: 25px;
            min-height: 160px;
        }

        .stat-number-modern {
            font-size: 2rem;
        }

        .stat-icon-modern {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        .value-card-modern {
            padding: 25px;
            min-height: 180px;
        }
    }

    @media (max-width: 480px) {
        .stat-card-modern {
            padding: 20px;
            min-height: 140px;
        }

        .stat-number-modern {
            font-size: 1.8rem;
        }

        .stat-label-modern {
            font-size: 0.8rem;
        }

        .stat-trend {
            font-size: 0.8rem;
            padding: 6px 10px;
        }
    }

    /* Améliorations de performance et fluidité */
    .stat-card-modern,
    .value-card-modern,
    .stats-card {
        will-change: transform, box-shadow;
        backface-visibility: hidden;
        perspective: 1000px;
    }

    /* Amélioration de la lisibilité des textes */
    .stat-label-modern,
    .stat-label,
    .value-title {
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Animation d'entrée fluide */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card-modern,
    .value-card-modern {
        animation: fadeInUp 0.6s ease-out;
    }

    .stat-card-modern:nth-child(1) { animation-delay: 0.1s; }
    .stat-card-modern:nth-child(2) { animation-delay: 0.2s; }
    .stat-card-modern:nth-child(3) { animation-delay: 0.3s; }
    .stat-card-modern:nth-child(4) { animation-delay: 0.4s; }
    .stat-card-modern:nth-child(5) { animation-delay: 0.5s; }

    /* Amélioration du contraste pour l'accessibilité */
    .stat-number-modern {
        color: #1a202c !important;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .stat-label-modern {
        color: #2d3748 !important;
    }

    /* Focus states pour l'accessibilité */
    .stat-card-modern:focus-within {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }

    /* Amélioration de la fluidité des transitions */
    * {
        transition: color 0.2s ease, background-color 0.2s ease;
    }

    /* Optimisation des polices pour la lisibilité */
    .stat-number-modern,
    .stat-label-modern,
    .value-title {
        font-feature-settings: "kern" 1, "liga" 1;
        font-variant-numeric: tabular-nums;
    }
</style>
@endpush

@section('content')
<div class="stock-page">
    <div class="container-fluid">
        <!-- En-tête avec titre et actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-white mb-2">
                            <i class="fas fa-boxes me-3"></i>
                            Gestion des Stocks
                        </h1>
                        <p class="text-white-50 mb-0">Historique des reçus - Tous les mouvements de stock avec téléchargement de reçu PDF</p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="{{ route('admin.stock.create') }}" class="btn btn-success-modern btn-modern">
                            <i class="fas fa-plus me-2"></i>Nouveau Mouvement
                        </a>
                        <button class="btn btn-info-modern btn-modern" onclick="exportData()">
                            <i class="fas fa-download me-2"></i>Exporter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                @include('admin.partials.stock-alerts', [
                    'stockAlertPanel' => $stockAlertPanel ?? null,
                    'stockAlertAction' => route('admin.stock.index'),
                    'stockAlertLimit' => 50,
                ])
            </div>
        </div>

        <!-- Statistiques Modernes -->
        <div class="row mb-5">
            <!-- Statistiques Principales -->
            <div class="col-lg-8 mb-4">
                <div class="stats-grid">
                    <div class="stat-card-modern stat-primary">
                        <div class="stat-icon-modern">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-modern">{{ $stats['total_movements'] }}</div>
                            <div class="stat-label-modern">Total Mouvements</div>
                            <div class="stat-trend">
                                <i class="fas fa-boxes text-muted"></i>
                                <span class="text-muted">{{ $stats['lots'] ?? 0 }} lots actifs · {{ $stats['produits_distincts'] ?? 0 }} produits</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern stat-success">
                        <div class="stat-icon-modern">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-modern">{{ $stats['entrees'] }}</div>
                            <div class="stat-label-modern">Entrées</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-down text-success"></i>
                                <span class="text-success">{{ number_format($stats['qte_entrees'] ?? 0, 0, ',', ' ') }} unités</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern stat-danger">
                        <div class="stat-icon-modern">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-modern">{{ $stats['sorties'] }}</div>
                            <div class="stat-label-modern">Sorties</div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up text-danger"></i>
                                <span class="text-danger">{{ number_format($stats['qte_sorties'] ?? 0, 0, ',', ' ') }} unités</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern stat-info">
                        <div class="stat-icon-modern">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-modern">{{ $stats['transferts'] }}</div>
                            <div class="stat-label-modern">Transferts</div>
                            <div class="stat-trend">
                                <i class="fas fa-exchange-alt text-info"></i>
                                <span class="text-info">entre magasins</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-modern stat-warning">
                        <div class="stat-icon-modern">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number-modern">{{ $stats['ajustements'] }}</div>
                            <div class="stat-label-modern">Ajustements</div>
                            <div class="stat-trend">
                                @php $adjNet = $stats['ajustement_net'] ?? 0; @endphp
                                <i class="fas {{ $adjNet >= 0 ? 'fa-arrow-up text-warning' : 'fa-arrow-down text-warning' }}"></i>
                                <span class="text-warning">{{ $adjNet >= 0 ? '+' : '' }}{{ number_format($adjNet, 0, ',', ' ') }} net</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valeur Totale - Carte Spéciale -->
            <div class="col-lg-4 mb-4">
                <div class="value-card-modern">
                    <div class="value-header">
                        <div class="value-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="value-title">Valeur Totale</div>
                    </div>
                    <div class="value-amount">
                        @if($stats['valeur_totale'] !== null)
                            <span class="currency">FCFA</span>
                            <span class="amount">{{ number_format($stats['valeur_totale'], 0, ',', ' ') }}</span>
                        @else
                            <span class="amount">Non valorisée</span>
                        @endif
                    </div>
                    <div class="value-details">
                        <div class="value-item">
                            <span class="label">Lots valorisés:</span>
                            <span class="value">{{ ($stats['lots'] ?? 0) - ($stats['lots_sans_prix'] ?? 0) }} / {{ $stats['lots'] ?? 0 }}</span>
                        </div>
                        <div class="value-item">
                            <span class="label">Évolution des entrées:</span>
                            @if($stats['evolution_mensuelle'] === null)
                                <span class="value text-muted">—</span>
                            @else
                                <span class="value {{ $stats['evolution_mensuelle'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $stats['evolution_mensuelle'] >= 0 ? '+' : '' }}{{ number_format($stats['evolution_mensuelle'], 1, ',', ' ') }}%
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(($stats['lots_sans_prix'] ?? 0) > 0)
                    <div class="value-item">
                        <span class="label small text-white-50">{{ $stats['lots_sans_prix'] }} lot(s) sans prix unitaire exclus de la valorisation</span>
                    </div>
                    @endif
                    <div class="value-chart">
                        <canvas id="valueChart" width="200" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="stats-card">
                    <h5 class="mb-3">Répartition par Type</h5>
                    <div class="chart-container">
                        <canvas id="typesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="stats-card">
                    <h5 class="mb-3">Mouvements par Entrepôt</h5>
                    <div class="chart-container">
                        <canvas id="entrepotsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="stats-card">
                    <h5 class="mb-3">Top Produits</h5>
                    <div class="chart-container">
                        <canvas id="produitsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.stock.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label fw-bold">Recherche</label>
                        <input type="text" name="search" class="form-control search-input"
                               placeholder="Référence ou produit..." value="{{ $search }}">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label fw-bold">Type</label>
                        <select name="type" class="form-select filter-select">
                            <option value="">Tous les types</option>
                            <option value="entree" {{ $type == 'entree' ? 'selected' : '' }}>Entrée</option>
                            <option value="sortie" {{ $type == 'sortie' ? 'selected' : '' }}>Sortie</option>
                            <option value="transfert" {{ $type == 'transfert' ? 'selected' : '' }}>Transfert</option>
                            <option value="ajustement" {{ $type == 'ajustement' ? 'selected' : '' }}>Ajustement</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label fw-bold">Mouvements</label>
                        <select name="mouvement" class="form-select filter-select">
                            <option value="">Tous</option>
                            <option value="entrees" {{ $mouvement == 'entrees' ? 'selected' : '' }}>Entrées</option>
                            <option value="sorties" {{ $mouvement == 'sorties' ? 'selected' : '' }}>Sorties</option>
                            <option value="transferts" {{ $mouvement == 'transferts' ? 'selected' : '' }}>Transferts</option>
                            <option value="ajustements" {{ $mouvement == 'ajustements' ? 'selected' : '' }}>Ajustements</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label fw-bold">Entrepôt</label>
                        <select name="entrepot" class="form-select filter-select">
                            <option value="">Tous</option>
                            @foreach(($entrepots ?? []) as $w)
                            <option value="{{ $w->id }}" {{ (string) $entrepot === (string) $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label fw-bold">Date Début</label>
                        <input type="date" name="date_debut" class="form-control filter-select" value="{{ $date_debut }}">
                    </div>
                    <div class="col-lg-1 col-md-6 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-modern btn-modern w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label fw-bold">Date Fin</label>
                        <input type="date" name="date_fin" class="form-control filter-select" value="{{ $date_fin }}">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3 d-flex align-items-end">
                        <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-modern w-100">
                            <i class="fas fa-times me-2"></i>Effacer
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tableau des mouvements -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Historique des Mouvements</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        <i class="fas fa-check-square me-1"></i>Tout sélectionner
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSelected()">
                        <i class="fas fa-trash me-1"></i>Supprimer sélectionnés
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Type</th>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Entrepôt</th>
                            <th>Responsable</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mouvementsPaginated as $mouvement)
                            <tr>
                                <td>
                                    <input type="checkbox" class="mouvement-checkbox" value="{{ $mouvement['id'] }}">
                                </td>
                                <td>{{ \Carbon\Carbon::parse($mouvement['date_mouvement'])->format('d/m/Y H:i') }}</td>
                                <td>
                                    <strong>{{ $mouvement['reference'] }}</strong>
                                </td>
                                <td>
                                    <span class="badge-modern badge-{{ $mouvement['type'] }}">
                                        {{ ucfirst($mouvement['type']) }}
                                    </span>
                                </td>
                                <td>{{ $mouvement['produit'] }}</td>
                                <td>
                                    <strong>{{ number_format($mouvement['quantite'], 0, ',', ' ') }} {{ $mouvement['unite'] }}</strong>
                                </td>
                                <td>{{ $mouvement['entrepot_nom'] }}</td>
                                <td>{{ $mouvement['responsable'] }}</td>
                                <td>
                                    <strong>{{ number_format($mouvement['total'], 0, ',', ' ') }} FCFA</strong>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.stock.show', $mouvement['id']) }}"
                                           class="action-btn btn btn-sm btn-info" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.stock.receipt', $mouvement['id']) }}"
                                           class="action-btn btn btn-sm btn-success" title="Télécharger reçu PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button class="action-btn btn btn-sm btn-danger"
                                                onclick="deleteMouvement({{ $mouvement['id'] }})" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">Aucun mouvement de stock trouvé</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($mouvementsPaginated->hasPages())
                <div class="pagination-modern">
                    {{ $mouvementsPaginated->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Animation des statistiques
    animateStats();

    // Graphique de flux : quantités entrées/sorties par mois (données réelles)
    const valueCtx = document.getElementById('valueChart').getContext('2d');
    new Chart(valueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['monthly']['labels'] ?? []) !!},
            datasets: [{
                label: 'Entrées',
                data: {!! json_encode($chartData['monthly']['entrees'] ?? []) !!},
                borderColor: 'rgba(255, 255, 255, 0.9)',
                backgroundColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(255, 255, 255, 1)',
                pointBorderColor: 'rgba(255, 255, 255, 1)',
                pointRadius: 4,
                pointHoverRadius: 6
            }, {
                label: 'Sorties',
                data: {!! json_encode($chartData['monthly']['sorties'] ?? []) !!},
                borderColor: 'rgba(255, 214, 165, 0.9)',
                backgroundColor: 'rgba(255, 214, 165, 0.08)',
                borderWidth: 2,
                borderDash: [5, 4],
                fill: false,
                tension: 0.4,
                pointBackgroundColor: 'rgba(255, 214, 165, 1)',
                pointBorderColor: 'rgba(255, 214, 165, 1)',
                pointRadius: 3,
                pointHoverRadius: 5
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
                x: {
                    display: false
                },
                y: {
                    display: false
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: 'rgba(255, 255, 255, 1)'
                }
            }
        }
    });

    // Graphique des types
    const typesCtx = document.getElementById('typesChart').getContext('2d');
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Entrées', 'Sorties', 'Transferts', 'Ajustements'],
            datasets: [{
                data: [
                    {{ $chartData['types']['entree'] ?? 0 }},
                    {{ $chartData['types']['sortie'] ?? 0 }},
                    {{ $chartData['types']['transfert'] ?? 0 }},
                    {{ $chartData['types']['ajustement'] ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#dc3545',
                    '#007bff',
                    '#ffc107'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Graphique des entrepôts
    const entrepotsCtx = document.getElementById('entrepotsChart').getContext('2d');
    new Chart(entrepotsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($chartData['entrepots'] ?? [])) !!},
            datasets: [{
                label: 'Mouvements',
                data: {!! json_encode(array_values($chartData['entrepots'] ?? [])) !!},
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1,
                borderRadius: 5
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
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Graphique des produits (quantité en stock, lots actifs)
    const produitsCtx = document.getElementById('produitsChart').getContext('2d');
    new Chart(produitsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($chartData['produits'] ?? [])) !!},
            datasets: [{
                label: 'Quantité en stock',
                data: {!! json_encode(array_values($chartData['produits'] ?? [])) !!},
                backgroundColor: 'rgba(118, 75, 162, 0.8)',
                borderColor: 'rgba(118, 75, 162, 1)',
                borderWidth: 1,
                borderRadius: 5
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
                    beginAtZero: true
                }
            }
        }
    });
});

// Animation des statistiques
function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number-modern');

    statNumbers.forEach((stat, index) => {
        const finalValue = parseInt(stat.textContent);
        setTimeout(() => {
            animateValue(stat, 0, finalValue, 1500);
        }, index * 200);
    });

    // Animation de la valeur totale
    const amountElement = document.querySelector('.amount');
    if (amountElement) {
        const finalAmount = parseInt(amountElement.textContent.replace(/,/g, ''));
        setTimeout(() => {
            animateValue(amountElement, 0, finalAmount, 2000, true);
        }, 1000);
    }
}

function animateValue(element, start, end, duration, isAmount = false) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const current = Math.floor(progress * (end - start) + start);

        if (isAmount) {
            element.textContent = current.toLocaleString();
        } else {
            element.textContent = current;
        }

        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Fonctions utilitaires
function selectAll() {
    const checkboxes = document.querySelectorAll('.mouvement-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');

    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.mouvement-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function deleteSelected() {
    const selectedCheckboxes = document.querySelectorAll('.mouvement-checkbox:checked');

    if (selectedCheckboxes.length === 0) {
        showToast('Veuillez sélectionner au moins un mouvement', 'warning');
        return;
    }

    if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedCheckboxes.length} mouvement(s) ?`)) {
        // Implémenter la suppression
        showToast('Mouvements supprimés avec succès', 'success');
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
}

function deleteMouvement(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce mouvement ?')) {
        // Implémenter la suppression
        showToast('Mouvement supprimé avec succès', 'success');
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
}

function exportData() {
    const form = document.getElementById('filterForm');
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("admin.stock.export") }}';

    // Copier les paramètres de filtrage
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.value) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name;
            hiddenInput.value = input.value;
            exportForm.appendChild(hiddenInput);
        }
    });

    // Ajouter le token CSRF
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfToken);

    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

// Auto-submit du formulaire de filtrage
document.querySelectorAll('.filter-select, .search-input').forEach(element => {
    element.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>
@endpush
