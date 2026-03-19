@extends('layouts.public')
@section('title', __('pages.demande_success'))
@section('content')

<div class="success-container">
    <div class="success-card">
        <!-- Icône de succès animée -->
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <!-- Message principal -->
        <h1 class="success-title">Demande envoyée avec succès !</h1>
        
        <div class="success-message">
            @if(session('success'))
                <div class="alert alert-success" style="background:#dcfce7;color:#166534;padding:16px;border-radius:8px;border:1px solid #16a34a;margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('is_aide_request'))
                <!-- Message spécial pour les demandes d'aide -->
                <div class="aide-info" style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:16px;margin-bottom:20px;">
                    <h3 style="color:#92400e;margin-bottom:12px;font-size:1.1rem;">
                        <i class="fas fa-mobile-alt"></i> Suivi par SMS
                    </h3>
                    <p style="color:#92400e;margin:0;font-size:0.95rem;">
                        Un message de confirmation a été envoyé sur votre numéro de téléphone. 
                        Vous recevrez des mises à jour sur l'évolution de votre demande d'aide.
                    </p>
                    @if(session('sms_sent'))
                        <div style="margin-top:8px;color:#059669;font-size:0.9rem;">
                            <i class="fas fa-check"></i> SMS envoyé avec succès
                        </div>
                    @else
                        <div style="margin-top:8px;color:#dc2626;font-size:0.9rem;">
                            <i class="fas fa-exclamation-triangle"></i> SMS en cours d'envoi
                        </div>
                    @endif
                </div>
            @endif
            
            <p class="main-message">
                Votre demande a été reçue et enregistrée dans notre système.
            </p>
            
            @if(session('tracking_code'))
                <div class="tracking-info">
                    <h3><i class="fas fa-barcode"></i> Code de suivi</h3>
                    <div class="tracking-code">
                        <span class="code">{{ session('tracking_code') }}</span>
                        <button onclick="copyTrackingCode()" class="copy-btn" title="Copier le code">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="tracking-note">
                        <i class="fas fa-info-circle"></i>
                        Conservez ce code pour suivre l'état de votre demande
                    </p>
                </div>
            @endif
            
            <!-- Informations sur les prochaines étapes -->
            <div class="next-steps">
                <h3><i class="fas fa-list-ol"></i> Prochaines étapes</h3>
                <div class="steps-list">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <strong>Traitement de votre demande</strong>
                            <p>Nos équipes vont examiner votre demande sous 24-48h</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <strong>Vérification et validation</strong>
                            <p>Nous vérifierons les informations fournies</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <strong>Contact et coordination</strong>
                            <p>Nous vous contacterons pour organiser l'aide</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact d'urgence -->
            <div class="emergency-contact">
                <h3><i class="fas fa-phone-alt"></i> Contact d'urgence</h3>
                <p>En cas d'urgence alimentaire immédiate, contactez-nous :</p>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+221 33 123 45 67</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>urgence@csar.sn</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="success-actions">
            @if(session('tracking_code'))
                <a href="{{ route('track') }}" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Suivre ma demande
                </a>
            @endif
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                {{ __('messages.home.back_to_home') }}
            </a>
            <a href="{{ route('action', ['locale' => app()->getLocale()]) }}" class="btn btn-outline">
                <i class="fas fa-plus"></i>
                {{ __('messages.home.request_button') }}
            </a>
        </div>
    </div>
</div>

<style>
.success-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.success-card {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    max-width: 600px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.success-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #10b981, #059669);
}

.success-icon {
    font-size: 4rem;
    color: #10b981;
    margin-bottom: 20px;
    animation: successBounce 0.8s ease-out;
}

@keyframes successBounce {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.2); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

.success-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 24px;
}

.success-message {
    text-align: left;
    margin-bottom: 32px;
}

.main-message {
    font-size: 1.1rem;
    color: #4b5563;
    margin-bottom: 24px;
    text-align: center;
    line-height: 1.6;
}

.tracking-info {
    background: #f0f9ff;
    border: 1px solid #0ea5e9;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.tracking-info h3 {
    color: #0369a1;
    margin-bottom: 12px;
    font-size: 1.1rem;
}

.tracking-code {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.code {
    background: #1f2937;
    color: #fff;
    padding: 12px 16px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: 1px;
    flex: 1;
}

.copy-btn {
    background: #0ea5e9;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.copy-btn:hover {
    background: #0284c7;
}

.tracking-note {
    color: #0369a1;
    font-size: 0.9rem;
    margin: 0;
}

.next-steps, .emergency-contact {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.next-steps h3, .emergency-contact h3 {
    color: #1f2937;
    margin-bottom: 16px;
    font-size: 1.1rem;
}

.steps-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.step-number {
    width: 32px;
    height: 32px;
    background: #10b981;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-content strong {
    color: #1f2937;
    display: block;
    margin-bottom: 4px;
}

.step-content p {
    color: #6b7280;
    margin: 0;
    font-size: 0.9rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1f2937;
}

.contact-item i {
    color: #10b981;
    width: 20px;
}

.success-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-primary {
    background: #10b981;
    color: #fff;
}

.btn-primary:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6b7280;
    color: #fff;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: #10b981;
    border: 2px solid #10b981;
}

.btn-outline:hover {
    background: #10b981;
    color: #fff;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .success-container {
        padding: 20px 15px;
    }
    
    .success-card {
        padding: 30px 20px;
    }
    
    .success-title {
        font-size: 1.8rem;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .tracking-code {
        flex-direction: column;
        gap: 8px;
    }
    
    .steps-list {
        gap: 12px;
    }
    
    .step {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
}
</style>

<script>
function copyTrackingCode() {
    const code = document.querySelector('.code').textContent;
    navigator.clipboard.writeText(code).then(function() {
        const btn = document.querySelector('.copy-btn');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.style.background = '#10b981';
        
        setTimeout(function() {
            btn.innerHTML = originalIcon;
            btn.style.background = '#0ea5e9';
        }, 2000);
    }).catch(function(err) {
        console.error('Erreur lors de la copie: ', err);
    });
}
</script>

@endsection










