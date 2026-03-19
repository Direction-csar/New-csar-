@extends('layouts.public')

@section('title', __('pages.conditions'))

@section('content')
<div class="legal-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- En-tête de la page -->
                <div class="legal-header text-center mb-5">
                    <h1 class="legal-title">
                        <i class="fas fa-file-contract me-3 text-primary"></i>
                        Conditions d'utilisation
                    </h1>
                    <p class="legal-subtitle text-muted">
                        Règles et conditions d'utilisation de la plateforme CSAR
                    </p>
                    <div class="legal-meta">
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Dernière mise à jour : {{ date('d/m/Y') }}
                        </span>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            À lire attentivement
                        </span>
                    </div>
                </div>

                <!-- Navigation rapide -->
                <div class="legal-nav mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-list me-2"></i>
                                Navigation rapide
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><a href="#acceptation" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Acceptation des conditions</a></li>
                                        <li><a href="#utilisation" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Utilisation autorisée</a></li>
                                        <li><a href="#interdictions" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Utilisations interdites</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><a href="#responsabilites" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Responsabilités</a></li>
                                        <li><a href="#sanctions" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Sanctions</a></li>
                                        <li><a href="#modifications" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Modifications</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="legal-content">
                    <!-- Introduction -->
                    <section class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Introduction
                                </h2>
                            </div>
                            <div class="card-body">
                                <p class="lead">
                                    Les présentes conditions d'utilisation régissent l'accès et l'utilisation de la plateforme du Comité de Suivi et d'Analyse des Risques (CSAR). En utilisant cette plateforme, vous acceptez de vous conformer à ces conditions.
                                </p>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important :</strong> Toute tentative de piratage, falsification ou accès non autorisé est strictement interdite et sera sanctionnée conformément à la législation sénégalaise.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Acceptation des conditions -->
                    <section id="acceptation" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-handshake me-2"></i>
                                    Acceptation des conditions
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Engagement de l'utilisateur</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-check-circle me-2 text-success"></i>Lecture attentive</h5>
                                            <p class="mb-0">Vous vous engagez à lire et comprendre ces conditions avant d'utiliser la plateforme.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-user-check me-2 text-primary"></i>Conformité</h5>
                                            <p class="mb-0">Vous vous engagez à respecter toutes les règles énoncées dans ce document.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-shield-alt me-2 text-warning"></i>Responsabilité</h5>
                                            <p class="mb-0">Vous assumez la responsabilité de vos actions sur la plateforme.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-sync-alt me-2 text-info"></i>Mise à jour</h5>
                                            <p class="mb-0">Vous acceptez de consulter régulièrement les mises à jour de ces conditions.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Acceptation tacite :</strong> L'utilisation de la plateforme constitue une acceptation tacite de ces conditions d'utilisation.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Utilisation autorisée -->
                    <section id="utilisation" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Utilisation autorisée
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Usages légitimes</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                            <h6>Soumettre des demandes</h6>
                                            <p class="small">Demandes d'aide alimentaire et de soutien</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-search fa-2x text-success mb-2"></i>
                                            <h6>Consulter les informations</h6>
                                            <p class="small">Actualités, rapports et données publiques</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-phone fa-2x text-warning mb-2"></i>
                                            <h6>Contacter le CSAR</h6>
                                            <p class="small">Communication et support</p>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="mt-4">Règles de bonne conduite</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-check me-3 text-success"></i>
                                        <div>
                                            <strong>Exactitude des informations</strong>
                                            <p class="mb-0 small text-muted">Fournir des informations exactes et à jour</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-check me-3 text-success"></i>
                                        <div>
                                            <strong>Respect des procédures</strong>
                                            <p class="mb-0 small text-muted">Suivre les procédures établies par le CSAR</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-check me-3 text-success"></i>
                                        <div>
                                            <strong>Usage légal</strong>
                                            <p class="mb-0 small text-muted">Utiliser la plateforme conformément à la loi</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-check me-3 text-success"></i>
                                        <div>
                                            <strong>Respect d'autrui</strong>
                                            <p class="mb-0 small text-muted">Maintenir un comportement respectueux</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Utilisations interdites -->
                    <section id="interdictions" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-ban me-2"></i>
                                    Utilisations interdites
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Activités strictement interdites</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-danger bg-opacity-10 rounded mb-3 border border-danger">
                                            <h5><i class="fas fa-hacker me-2 text-danger"></i>Piratage et intrusion</h5>
                                            <ul class="mb-0">
                                                <li>Tentative de piratage des systèmes</li>
                                                <li>Accès non autorisé aux données</li>
                                                <li>Contournement des mesures de sécurité</li>
                                                <li>Exploitation de vulnérabilités</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-danger bg-opacity-10 rounded mb-3 border border-danger">
                                            <h5><i class="fas fa-file-invoice me-2 text-danger"></i>Falsification</h5>
                                            <ul class="mb-0">
                                                <li>Falsification de documents</li>
                                                <li>Usurpation d'identité</li>
                                                <li>Modification de données</li>
                                                <li>Création de faux comptes</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-danger bg-opacity-10 rounded mb-3 border border-danger">
                                            <h5><i class="fas fa-bug me-2 text-danger"></i>Sabotage</h5>
                                            <ul class="mb-0">
                                                <li>Introduction de virus ou malwares</li>
                                                <li>Attaques par déni de service</li>
                                                <li>Corruption de données</li>
                                                <li>Perturbation des services</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-danger bg-opacity-10 rounded mb-3 border border-danger">
                                            <h5><i class="fas fa-user-secret me-2 text-danger"></i>Fraude</h5>
                                            <ul class="mb-0">
                                                <li>Demandes frauduleuses</li>
                                                <li>Détournement d'aides</li>
                                                <li>Corruption d'agents</li>
                                                <li>Blanchiment d'argent</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Avertissement :</strong> Toute violation de ces interdictions sera signalée aux autorités compétentes et fera l'objet de poursuites judiciaires.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Responsabilités -->
                    <section id="responsabilites" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-balance-scale me-2"></i>
                                    Responsabilités
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-user me-2 text-primary"></i>Responsabilités de l'utilisateur</h4>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">Fournir des informations exactes et complètes</li>
                                            <li class="list-group-item">Maintenir la confidentialité de ses identifiants</li>
                                            <li class="list-group-item">Respecter les droits d'autrui</li>
                                            <li class="list-group-item">Signaler tout dysfonctionnement</li>
                                            <li class="list-group-item">Respecter la législation en vigueur</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-building me-2 text-success"></i>Responsabilités du CSAR</h4>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">Assurer la sécurité de la plateforme</li>
                                            <li class="list-group-item">Protéger les données personnelles</li>
                                            <li class="list-group-item">Traiter les demandes dans les délais</li>
                                            <li class="list-group-item">Maintenir la disponibilité du service</li>
                                            <li class="list-group-item">Respecter la confidentialité</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Limitation de responsabilité :</strong> Le CSAR ne peut être tenu responsable des dommages indirects résultant de l'utilisation de la plateforme.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Sanctions -->
                    <section id="sanctions" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-gavel me-2"></i>
                                    Sanctions et mesures
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Mesures disciplinaires</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                            <h6>Avertissement</h6>
                                            <p class="small">Première infraction mineure</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-ban fa-2x text-danger mb-2"></i>
                                            <h6>Suspension</h6>
                                            <p class="small">Accès temporairement bloqué</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-times-circle fa-2x text-dark mb-2"></i>
                                            <h6>Exclusion définitive</h6>
                                            <p class="small">Accès définitivement révoqué</p>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="mt-4">Poursuites judiciaires</h4>
                                <div class="alert alert-danger">
                                    <h5><i class="fas fa-gavel me-2"></i>Sanctions pénales</h5>
                                    <p>Les infractions graves peuvent entraîner :</p>
                                    <ul class="mb-0">
                                        <li><strong>Piratage informatique :</strong> 1 à 5 ans d'emprisonnement et 100 000 à 1 000 000 FCFA d'amende</li>
                                        <li><strong>Falsification de documents :</strong> 6 mois à 2 ans d'emprisonnement</li>
                                        <li><strong>Usurpation d'identité :</strong> 1 à 3 ans d'emprisonnement</li>
                                        <li><strong>Fraude :</strong> 1 à 5 ans d'emprisonnement selon la gravité</li>
                                    </ul>
                                </div>

                                <h4>Signalement aux autorités</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded">
                                            <h5><i class="fas fa-shield-alt me-2 text-primary"></i>ANSD</h5>
                                            <p class="mb-0">Agence Nationale de Sécurité des Données</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded">
                                            <h5><i class="fas fa-balance-scale me-2 text-success"></i>Ministère de la Justice</h5>
                                            <p class="mb-0">Poursuites judiciaires</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Modifications -->
                    <section id="modifications" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Modifications des conditions
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Droit de modification</h4>
                                <p>Le CSAR se réserve le droit de modifier ces conditions d'utilisation à tout moment, sans préavis, pour :</p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li>Adapter aux évolutions légales</li>
                                            <li>Améliorer la sécurité</li>
                                            <li>Optimiser les services</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li>Prévenir les abus</li>
                                            <li>Respecter les réglementations</li>
                                            <li>Protéger les utilisateurs</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-bell me-2"></i>
                                    <strong>Notification :</strong> Les modifications importantes seront communiquées par email ou par un avis sur la plateforme. La poursuite de l'utilisation constitue une acceptation des nouvelles conditions.
                                </div>

                                <h4>Historique des versions</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Version</th>
                                                <th>Date</th>
                                                <th>Modifications</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1.0</td>
                                                <td>{{ date('d/m/Y') }}</td>
                                                <td>Version initiale</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Actions -->
                <div class="legal-actions text-center mt-5">
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>
                        Retour à l'accueil
                    </a>
                    <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>
                        Nous contacter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.legal-page {
    padding: 2rem 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.legal-header {
    background: white;
    padding: 3rem 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.legal-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.legal-subtitle {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.legal-nav .card {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.legal-section .card {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.legal-section .card-header {
    border-radius: 10px 10px 0 0 !important;
    border: none;
}

.legal-section .card-body {
    padding: 2rem;
}

.legal-actions {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .legal-title {
        font-size: 2rem;
    }
    
    .legal-header {
        padding: 2rem 1rem;
    }
    
    .legal-section .card-body {
        padding: 1.5rem;
    }
}
</style>
@endsection

