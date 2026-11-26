@extends('layouts.public')

@section('title', 'Demande soumise avec succès - CSAR')

@section('content')
<!-- Hero Section -->
<section class="hero" style="min-height: 60vh; padding: 100px 20px 60px;">
    <div class="hero-content">
        <div style="font-size: 64px; margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="color: #22c55e;"></i>
        </div>
        <h2>Demande soumise avec succès !</h2>
        <p>Votre demande a été enregistrée et sera traitée dans les plus brefs délais</p>
    </div>
</section>

<!-- Success Details Section -->
<section class="section">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <div class="card">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 80px; height: 80px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-check" style="font-size: 32px; color: #059669;"></i>
                    </div>
                    <h3 style="color: #059669; margin-bottom: 10px;">Demande enregistrée</h3>
                    <p style="color: #6b7280;">Votre demande a été soumise avec succès et est maintenant en cours de traitement.</p>
                </div>
                
                <!-- Tracking Information -->
                <div style="background: #f8fafc; padding: 25px; border-radius: 8px; margin-bottom: 30px;">
                    <h4 style="color: #1f2937; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                        <i class="fas fa-barcode"></i> Informations de suivi
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong style="color: #374151;">Code de suivi :</strong>
                            <div style="background: white; padding: 12px; border-radius: 6px; margin-top: 5px; font-family: monospace; font-size: 18px; text-align: center; border: 2px solid #e5e7eb;">
                                {{ $code ?? 'CSAR000001' }}
                            </div>
                        </div>
                        
                        <div>
                            <strong style="color: #374151;">Statut :</strong>
                            <div style="background: #fef3c7; color: #92400e; padding: 8px 12px; border-radius: 6px; margin-top: 5px; text-align: center; font-weight: 600;">
                                <i class="fas fa-clock"></i> En attente
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: #dbeafe; padding: 15px; border-radius: 6px; border-left: 4px solid #3b82f6;">
                        <p style="margin: 0; color: #1e40af; font-size: 14px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Important :</strong> Conservez précieusement ce code de suivi. Il vous sera nécessaire pour suivre l'évolution de votre demande.
                        </p>
                    </div>
                </div>
                
                <!-- Next Steps -->
                <div style="margin-bottom: 30px;">
                    <h4 style="color: #1f2937; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                        <i class="fas fa-list-check"></i> Prochaines étapes
                    </h4>
                    
                    <div style="display: grid; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 30px; height: 30px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
                                1
                            </div>
                            <div>
                                <strong>Accusé de réception</strong>
                                <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Un e-mail de confirmation vous est envoyé si le service de messagerie est disponible — conservez votre code de suivi</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 30px; height: 30px; background: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
                                2
                            </div>
                            <div>
                                <strong>Examen de la demande</strong>
                                <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Nos équipes examineront votre demande sous 24-48h</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 30px; height: 30px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
                                3
                            </div>
                            <div>
                                <strong>Suivi en ligne</strong>
                                <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Utilisez votre code de suivi pour consulter l'état d'avancement</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <a href="{{ route('track') }}" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-search"></i>
                        Suivre ma demande
                    </a>
                    
                    <a href="{{ route('action') }}" class="btn btn-secondary" style="justify-content: center;">
                        <i class="fas fa-plus"></i>
                        Nouvelle demande
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="section" style="background: #f8fafc;">
    <div class="container">
        <h2 class="section-title">Besoin d'aide ?</h2>
        <p class="section-subtitle">Nous sommes là pour vous accompagner</p>
        
        <div class="cards-grid">
            <div class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Contactez-nous</h3>
                <p>Appelez notre service client pour toute question</p>
                <a href="tel:+221776459242" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-phone"></i>
                    +221 77 645 92 42
                </a>
            </div>
            
            <div class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Envoyez un message</h3>
                <p>Écrivez-nous pour obtenir de l'aide</p>
                <a href="{{ route('contact.simple') }}" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-envelope"></i>
                    Nous contacter
                </a>
            </div>
            
            <div class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h3>FAQ</h3>
                <p>Consultez nos questions fréquemment posées</p>
                <a href="{{ route('about') }}" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-book"></i>
                    Voir la FAQ
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
<style>
/* Additional styles for success page */
.success-icon {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.step-number {
    transition: all 0.3s ease;
}

.step-number:hover {
    transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-buttons {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection 