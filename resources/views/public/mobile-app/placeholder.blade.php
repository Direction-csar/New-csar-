@extends('layouts.public')

@section('title', 'Application Mobile CSAR - Bientôt disponible')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-mobile-alt fa-4x text-primary"></i>
                    </div>
                    <h1 class="h2 mb-3">Application Mobile CSAR</h1>
                    <p class="text-muted mb-4">Bientôt disponible sur Android</p>

                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>En cours de développement</h5>
                        <p class="mb-0">Notre application mobile est actuellement en phase de test. Elle sera disponible prochainement.</p>
                    </div>

                    <div class="row text-start mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold"><i class="fas fa-check-circle text-success me-2"></i>Fonctionnalités prévues :</h6>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-chevron-right text-primary me-1"></i> Suivi des demandes en temps réel</li>
                                <li><i class="fas fa-chevron-right text-primary me-1"></i> Notifications push</li>
                                <li><i class="fas fa-chevron-right text-primary me-1"></i> Faire un don en 1 clic</li>
                                <li><i class="fas fa-chevron-right text-primary me-1"></i> Cartographie interactive</li>
                                <li><i class="fas fa-chevron-right text-primary me-1"></i> Accès hors-ligne</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold"><i class="fas fa-cog text-primary me-2"></i>Spécifications :</h6>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-android text-success me-1"></i> Android 8.0+ minimum</li>
                                <li><i class="fas fa-microphone text-info me-1"></i> Permissions : Localisation</li>
                                <li><i class="fas fa-shield-alt text-warning me-1"></i> Sécurisée et chiffrée</li>
                                <li><i class="fas fa-language text-secondary me-1"></i> Français et Wolof</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-4">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-muted small">
                        <p class="mb-1"><strong>Contact :</strong> contact@csar.sn</p>
                        <p class="mb-0">Pour être informé de la sortie, inscrivez-vous à notre newsletter.</p>
                    </div>

                    @if(isset($content))
                    <div class="mt-4 text-start">
                        <details>
                            <summary class="text-muted small cursor-pointer">Informations techniques</summary>
                            <pre class="mt-2 p-3 bg-light rounded small text-muted">{{ $content }}</pre>
                        </details>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
