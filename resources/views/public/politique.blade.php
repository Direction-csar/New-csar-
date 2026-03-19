@extends('layouts.public')

@section('title', __('pages.privacy'))

@section('content')
<div class="legal-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- En-tête de la page -->
                <div class="legal-header text-center mb-5">
                    <h1 class="legal-title">
                        <i class="fas fa-shield-alt me-3 text-primary"></i>
                        Politique de confidentialité
                    </h1>
                    <p class="legal-subtitle text-muted">
                        Protection et sécurité de vos données personnelles
                    </p>
                    <div class="legal-meta">
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Dernière mise à jour : {{ date('d/m/Y') }}
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Conforme RGPD
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
                                        <li><a href="#collecte" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Collecte des données</a></li>
                                        <li><a href="#utilisation" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Utilisation des données</a></li>
                                        <li><a href="#protection" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Protection des données</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><a href="#droits" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Vos droits</a></li>
                                        <li><a href="#contact" class="text-decoration-none"><i class="fas fa-arrow-right me-2"></i>Contact</a></li>
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
                                    Le Comité de Suivi et d'Analyse des Risques (CSAR) s'engage à protéger la confidentialité et la sécurité de vos données personnelles. Cette politique explique comment nous collectons, utilisons et protégeons vos informations.
                                </p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Engagement du CSAR :</strong> Nous respectons la législation sénégalaise sur la protection des données personnelles et nous nous conformons aux meilleures pratiques internationales en matière de sécurité informatique.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Collecte des données -->
                    <section id="collecte" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-database me-2"></i>
                                    Collecte des données
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Types de données collectées</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-user me-2 text-primary"></i>Données d'identification</h5>
                                        <ul>
                                            <li>Nom et prénom</li>
                                            <li>Adresse email</li>
                                            <li>Numéro de téléphone</li>
                                            <li>Adresse postale</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-file-alt me-2 text-success"></i>Données de demande</h5>
                                        <ul>
                                            <li>Type de demande</li>
                                            <li>Documents fournis</li>
                                            <li>Historique des interactions</li>
                                            <li>Statut de traitement</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <h4 class="mt-4">Méthodes de collecte</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-mouse-pointer fa-2x text-primary mb-2"></i>
                                            <h6>Formulaires en ligne</h6>
                                            <p class="small text-muted">Demandes et inscriptions</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-envelope fa-2x text-success mb-2"></i>
                                            <h6>Communications</h6>
                                            <p class="small text-muted">Emails et correspondances</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-phone fa-2x text-warning mb-2"></i>
                                            <h6>Contact direct</h6>
                                            <p class="small text-muted">Appels et visites</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Utilisation des données -->
                    <section id="utilisation" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-cogs me-2"></i>
                                    Utilisation des données
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Finalités de traitement</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-tasks me-2 text-primary"></i>Traitement des demandes</h5>
                                            <p class="mb-0">Analyse et traitement de vos demandes d'aide alimentaire et de soutien.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-chart-line me-2 text-success"></i>Statistiques et rapports</h5>
                                            <p class="mb-0">Élaboration de statistiques anonymisées pour améliorer nos services.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-bell me-2 text-warning"></i>Communication</h5>
                                            <p class="mb-0">Envoi d'informations importantes concernant vos demandes.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded mb-3">
                                            <h5><i class="fas fa-shield-alt me-2 text-danger"></i>Sécurité</h5>
                                            <p class="mb-0">Prévention de la fraude et protection de l'intégrité du système.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Principe de minimisation :</strong> Nous ne collectons que les données strictement nécessaires à la réalisation de nos missions.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Protection des données -->
                    <section id="protection" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-lock me-2"></i>
                                    Protection des données
                                </h2>
                            </div>
                            <div class="card-body">
                                <h4>Mesures de sécurité techniques</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-key fa-2x text-primary mb-2"></i>
                                            <h6>Chiffrement</h6>
                                            <p class="small">SSL/TLS pour toutes les transmissions</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-server fa-2x text-success mb-2"></i>
                                            <h6>Sécurisation serveurs</h6>
                                            <p class="small">Hébergement sécurisé et surveillé</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded">
                                            <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                                            <h6>Contrôle d'accès</h6>
                                            <p class="small">Authentification et autorisation strictes</p>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="mt-4">Mesures organisationnelles</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-users me-3 text-primary"></i>
                                        <div>
                                            <strong>Formation du personnel</strong>
                                            <p class="mb-0 small text-muted">Tous nos agents sont formés à la protection des données</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-file-contract me-3 text-success"></i>
                                        <div>
                                            <strong>Engagements contractuels</strong>
                                            <p class="mb-0 small text-muted">Nos partenaires s'engagent à respecter la confidentialité</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-clipboard-check me-3 text-warning"></i>
                                        <div>
                                            <strong>Audits réguliers</strong>
                                            <p class="mb-0 small text-muted">Contrôles périodiques de nos systèmes de sécurité</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Vos droits -->
                    <section id="droits" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-balance-scale me-2"></i>
                                    Vos droits
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-eye me-2 text-primary"></i>Droit d'accès</h4>
                                        <p>Vous pouvez demander à connaître les données que nous détenons sur vous.</p>
                                        
                                        <h4><i class="fas fa-edit me-2 text-success"></i>Droit de rectification</h4>
                                        <p>Vous pouvez demander la correction de données inexactes ou incomplètes.</p>
                                        
                                        <h4><i class="fas fa-trash me-2 text-danger"></i>Droit d'effacement</h4>
                                        <p>Dans certains cas, vous pouvez demander la suppression de vos données.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-ban me-2 text-warning"></i>Droit d'opposition</h4>
                                        <p>Vous pouvez vous opposer au traitement de vos données pour certaines finalités.</p>
                                        
                                        <h4><i class="fas fa-download me-2 text-info"></i>Droit à la portabilité</h4>
                                        <p>Vous pouvez récupérer vos données dans un format structuré.</p>
                                        
                                        <h4><i class="fas fa-pause me-2 text-secondary"></i>Droit de limitation</h4>
                                        <p>Vous pouvez demander la limitation du traitement de vos données.</p>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Comment exercer vos droits :</strong> Contactez-nous par email à <a href="mailto:mohamed.sow@csar.sn">mohamed.sow@csar.sn</a> ou par courrier postal. Nous vous répondrons dans un délai de 30 jours.
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Contact -->
                    <section id="contact" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-envelope me-2"></i>
                                    Contact
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4>Délégué à la protection des données</h4>
                                        <div class="contact-info">
                                            <p><i class="fas fa-user me-2"></i><strong>Responsable :</strong> Délégué CSAR</p>
                                            <p><i class="fas fa-envelope me-2"></i><strong>Email :</strong> <a href="mailto:mohamed.sow@csar.sn">mohamed.sow@csar.sn</a></p>
                                            <p><i class="fas fa-phone me-2"></i><strong>Téléphone :</strong> <a href="tel:+221786009760">+221 78 600 97 60</a></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h4>Adresse postale</h4>
                                        <div class="contact-info">
                                            <p><i class="fas fa-map-marker-alt me-2"></i><strong>Adresse :</strong></p>
                                            <p class="ms-4">
                                                Comité de Suivi et d'Analyse des Risques<br>
                                                Avenue Léopold Sédar Senghor<br>
                                                Dakar, Sénégal
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Modifications -->
                    <section id="modifications" class="legal-section mb-5">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h2 class="card-title mb-0">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Modifications de la politique
                                </h2>
                            </div>
                            <div class="card-body">
                                <p>Cette politique de confidentialité peut être modifiée pour refléter les changements dans nos pratiques ou pour d'autres raisons opérationnelles, légales ou réglementaires.</p>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-bell me-2"></i>
                                    <strong>Notification des modifications :</strong> Nous vous informerons de tout changement significatif par email ou par un avis sur notre site web.
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

.contact-info p {
    margin-bottom: 0.5rem;
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

