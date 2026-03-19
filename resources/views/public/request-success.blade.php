@extends('layouts.public')

@section('title', __('pages.demande_success'))

@push('styles')
<style>
    /* Variables CSS pour la cohérence */
    :root {
        --csar-green: #10b981;
        --csar-blue: #2563eb;
        --csar-gradient: linear-gradient(135deg, #10b981 0%, #2563eb 100%);
        --glass-bg: rgba(255, 255, 255, 0.25);
        --glass-border: rgba(255, 255, 255, 0.18);
    }

    /* Animations d'entrée */
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

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    /* Styles de base */
    .modern-success-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #f5576c 75%, #4facfe 100%);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        position: relative;
        overflow-x: hidden;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Effet de particules flottantes */
    .floating-particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }

    .particle {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    .particle:nth-child(1) { width: 10px; height: 10px; top: 20%; left: 10%; animation-delay: 0s; }
    .particle:nth-child(2) { width: 15px; height: 15px; top: 60%; left: 80%; animation-delay: 2s; }
    .particle:nth-child(3) { width: 8px; height: 8px; top: 80%; left: 20%; animation-delay: 4s; }
    .particle:nth-child(4) { width: 12px; height: 12px; top: 30%; left: 70%; animation-delay: 1s; }
    .particle:nth-child(5) { width: 6px; height: 6px; top: 70%; left: 50%; animation-delay: 3s; }

    /* Contenu principal */
    .main-content {
        position: relative;
        z-index: 2;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem 1rem;
    }

    /* Carte principale avec glassmorphism */
    .success-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 2rem;
        padding: 3rem 2rem;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        animation: fadeInScale 0.8s ease-out;
        max-width: 800px;
        width: 100%;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .success-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        animation: shimmer 3s infinite;
    }

    /* Icône principale 3D */
    .main-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        background: var(--csar-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
        animation: pulse 2s ease-in-out infinite;
        position: relative;
    }

    .main-icon::before {
        content: '';
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        background: var(--csar-gradient);
        border-radius: 50%;
        z-index: -1;
        opacity: 0.3;
        animation: pulse 2s ease-in-out infinite;
    }

    .main-icon svg {
        width: 60px;
        height: 60px;
        color: white;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
    }

    /* Titre principal */
    .main-title {
        font-size: 3rem;
        font-weight: 800;
        background: var(--csar-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
        animation: fadeInUp 0.8s ease-out 0.2s both;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Sous-titre */
    .subtitle {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 3rem;
        animation: fadeInUp 0.8s ease-out 0.4s both;
        line-height: 1.6;
    }

    /* Carte de résumé */
    .summary-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1.5rem;
        padding: 2rem;
        margin: 2rem 0;
        animation: fadeInUp 0.8s ease-out 0.6s both;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-label {
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
    }

    .summary-value {
        font-weight: 700;
        color: white;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
    }

    .tracking-code {
        background: var(--csar-gradient);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        letter-spacing: 2px;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    /* Boutons d'action */
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 3rem 0;
        animation: fadeInUp 0.8s ease-out 0.8s both;
    }

    .action-btn {
        padding: 1.25rem 2rem;
        border-radius: 1.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .action-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .btn-primary {
        background: var(--csar-gradient);
        color: white;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.9);
        color: #374151;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-tertiary {
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
        color: white;
    }

    /* Section assistance */
    .assistance-section {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1.5rem;
        padding: 2rem;
        margin: 2rem 0;
        animation: fadeInUp 0.8s ease-out 1s both;
    }

    .assistance-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        text-align: center;
        margin-bottom: 2rem;
    }

    .assistance-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .assistance-card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .assistance-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.15);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .assistance-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        background: var(--csar-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
    }

    .assistance-icon svg {
        width: 30px;
        height: 30px;
        color: white;
    }

    .assistance-card h4 {
        color: white;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .assistance-card p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .assistance-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .assistance-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-title {
            font-size: 2rem;
        }
        
        .subtitle {
            font-size: 1rem;
        }
        
        .success-card {
            padding: 2rem 1rem;
            margin: 1rem;
        }
        
        .action-buttons {
            grid-template-columns: 1fr;
        }
        
        .assistance-cards {
            grid-template-columns: 1fr;
        }
        
        .summary-row {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }
    }

    /* Animation de chargement */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--csar-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }

    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top: 4px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<!-- Overlay de chargement -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<!-- Page principale -->
<div class="modern-success-page">
    <!-- Particules flottantes -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="success-card">
            <!-- Icône principale 3D -->
            <div class="main-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Titre principal -->
            <h1 class="main-title">Demande soumise avec succès !</h1>
            
            <!-- Sous-titre -->
            <p class="subtitle">
                Votre demande a été enregistrée et sera traitée dans les plus brefs délais.
            </p>

            <!-- Carte de résumé -->
            <div class="summary-card">
                <div class="summary-row">
                    <span class="summary-label">Code de suivi :</span>
                    <span class="summary-value">
                        <span class="tracking-code">{{ session('tracking_code', 'CSAR-' . strtoupper(substr(md5(uniqid()), 0, 8))) }}</span>
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Statut :</span>
                    <span class="summary-value">
                        <span class="status-badge">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            En attente
                        </span>
                    </span>
                </div>
            </div>

            <!-- Message d'information -->
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 0.95rem; margin: 1.5rem 0; line-height: 1.6;">
                <strong>Conservez précieusement ce code de suivi.</strong> Il vous sera nécessaire pour suivre l'évolution de votre demande.
            </p>

            <!-- Boutons d'action -->
            <div class="action-buttons">
                <a href="{{ route('track.request') }}" class="action-btn btn-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Suivre ma demande
                </a>
                
                <a href="{{ route('demande.create') }}" class="action-btn btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nouvelle demande
                </a>
                
                <button onclick="downloadReceipt()" class="action-btn btn-tertiary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Télécharger le reçu
                </button>
            </div>
        </div>

        <!-- Section assistance -->
        <div class="assistance-section">
            <h2 class="assistance-title">Besoin d'aide ? Nous sommes là pour vous accompagner</h2>
            
            <div class="assistance-cards">
                <div class="assistance-card">
                    <div class="assistance-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <h4>Appeler le service client</h4>
                    <p>Obtenez une assistance immédiate par téléphone</p>
                    <a href="tel:+221331234567" class="assistance-btn">+221 33 123 45 67</a>
                </div>

                <div class="assistance-card">
                    <div class="assistance-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h4>Envoyer un message</h4>
                    <p>Écrivez-nous pour obtenir de l'aide</p>
                    <a href="{{ route('contact.simple') }}" class="assistance-btn">Nous contacter</a>
                </div>

                <div class="assistance-card">
                    <div class="assistance-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4>Consulter la FAQ</h4>
                    <p>Trouvez rapidement les réponses à vos questions</p>
                    <a href="#" class="assistance-btn">Voir la FAQ</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Masquer l'overlay de chargement
    window.addEventListener('load', function() {
        setTimeout(function() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.opacity = '0';
            setTimeout(function() {
                overlay.style.display = 'none';
            }, 500);
        }, 1000);
    });

    // Fonction pour télécharger le reçu
    function downloadReceipt() {
        // Créer un reçu PDF simple
        const trackingCode = '{{ session("tracking_code", "CSAR-" . strtoupper(substr(md5(uniqid()), 0, 8))) }}';
        const currentDate = new Date().toLocaleDateString('fr-FR');
        
        // Contenu du reçu
        const receiptContent = `
            RECU DE DEMANDE CSAR
            ===================
            
            Code de suivi: ${trackingCode}
            Date de soumission: ${currentDate}
            Statut: En attente de traitement
            
            Votre demande a été enregistrée avec succès.
            Conservez ce reçu pour le suivi de votre demande.
            
            Contact: contact@csar.sn
            Téléphone: +221 33 123 45 67
        `;
        
        // Créer et télécharger le fichier
        const blob = new Blob([receiptContent], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `receipt-${trackingCode}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        // Animation de confirmation
        const button = event.target.closest('.action-btn');
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    }

    // Animation des cartes au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer les éléments animés
    document.addEventListener('DOMContentLoaded', function() {
        const animatedElements = document.querySelectorAll('.assistance-card, .summary-card');
        animatedElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    });

    // Effet de parallaxe pour les particules
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const particles = document.querySelectorAll('.particle');
        
        particles.forEach((particle, index) => {
            const speed = 0.5 + (index * 0.1);
            particle.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
</script>
@endpush
@endsection