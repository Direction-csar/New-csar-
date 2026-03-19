@extends('layouts.public')

@section('title', __('pages.home'))

@section('content')
<style>
/* Hero Section avec Diaporama Simple */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #1a1a1a;
}

.hero-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.hero-slide.active {
    opacity: 1;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, 
        rgba(0, 0, 0, 0.6) 0%, 
        rgba(0, 0, 0, 0.4) 50%, 
        rgba(0, 0, 0, 0.6) 100%
    );
    z-index: 2;
}

.hero-content {
    text-align: center;
    z-index: 10;
    position: relative;
    color: white;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    min-height: 4rem;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
}

.hero-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.hero-btn {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    border: none;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.8rem;
}

.hero-btn-primary {
    background: linear-gradient(45deg, #22c55e, #16a34a);
    color: white;
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
}

.hero-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(34, 197, 94, 0.5);
}

.hero-btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
}

.hero-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
    border-color: rgba(255, 255, 255, 0.6);
}

.hero-slider-controls {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 15;
    display: flex;
    gap: 15px;
    align-items: center;
}

.hero-slider-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.6);
}

.hero-slider-dot.active {
    background: white;
    transform: scale(1.3);
    border-color: white;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
}

.hero-slider-nav {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.4);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.hero-slider-nav:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-btn {
        width: 100%;
        max-width: 280px;
    }
    
    .hero-slider-nav {
        width: 40px;
        height: 40px;
    }
    
    .hero-slider-dot {
        width: 12px;
        height: 12px;
    }
}

/* Animation pour le texte */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.typewriter-text {
    animation: fadeIn 0.5s ease-in;
}
</style>

<!-- Hero Section avec Diaporama Simple -->
<section class="hero-section">
    <!-- Diaporama d'images -->
    <div class="hero-slider">
        <div class="hero-slide active" data-image="1"></div>
        <div class="hero-slide" data-image="2"></div>
        <div class="hero-slide" data-image="3"></div>
        <div class="hero-slide" data-image="4"></div>
    </div>
    
    <!-- Overlay pour la lisibilité -->
    <div class="hero-overlay"></div>
    
    <!-- Contenu par-dessus le diaporama -->
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title typewriter-text">Commissariat à la Sécurité Alimentaire et à la Résilience</h1>
            <p class="hero-subtitle">{{ __('messages.home.subtitle') }}</p>
            <div class="hero-buttons">
                <a href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}" class="hero-btn hero-btn-primary">
                    <i class="fas fa-file-alt"></i>
                    {{ __('messages.home.request_button') }}
                </a>
                <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="hero-btn hero-btn-secondary">
                    <i class="fas fa-info-circle"></i>
                    {{ __('messages.home.discover_button') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Contrôles du diaporama -->
    <div class="hero-slider-controls">
        <div class="hero-slider-nav" id="prev-slide">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="hero-slider-dot active" data-slide="0"></div>
        <div class="hero-slider-dot" data-slide="1"></div>
        <div class="hero-slider-dot" data-slide="2"></div>
        <div class="hero-slider-dot" data-slide="3"></div>
        <div class="hero-slider-nav" id="next-slide">
            <i class="fas fa-chevron-right"></i>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DÉMARRAGE DU DIAPORAMA SIMPLE ===');
    
    // Configuration des images
    const images = [
        '{{ asset("images/arriere%20plan/N1.jpg") }}',
        '{{ asset("images/arriere%20plan/N2.jpg") }}',
        '{{ asset("images/arriere%20plan/N3.jpg") }}',
        '{{ asset("images/arriere%20plan/N8.jpg") }}'
    ];
    
    // Éléments du DOM
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-slider-dot');
    const prevBtn = document.getElementById('prev-slide');
    const nextBtn = document.getElementById('next-slide');
    const titleElement = document.querySelector('.hero-title');
    
    let currentSlide = 0;
    let slideInterval;
    let textInterval;
    
    // Fonction simple pour changer de slide
    function showSlide(index) {
        console.log(`Changement vers slide ${index + 1}`);
        
        // Retirer active de toutes les slides et dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Ajouter active à la slide et dot actives
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        
        currentSlide = index;
    }
    
    // Fonctions de navigation
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }
    
    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }
    
    // Effet machine à écrire simple et fiable
    function typeWriter() {
        if (!titleElement) return;
        
        const text = "Commissariat à la Sécurité Alimentaire et à la Résilience";
        
        // Vider et réécrire
        titleElement.textContent = '';
        titleElement.style.opacity = '0';
        
        setTimeout(() => {
            titleElement.textContent = text;
            titleElement.style.opacity = '1';
            titleElement.style.transition = 'opacity 0.5s ease-in';
        }, 100);
    }
    
    // Démarrer l'effet machine à écrire
    function startTypewriter() {
        typeWriter();
        textInterval = setInterval(typeWriter, 6000); // Toutes les 6 secondes
    }
    
    // Initialiser les images
    function initImages() {
        console.log('Initialisation des images...');
        
        slides.forEach((slide, index) => {
            // Forcer l'image de fond immédiatement
            const imageUrl = images[index];
            slide.style.backgroundImage = `url('${imageUrl}')`;
            slide.style.backgroundColor = '#1a1a1a'; // Fond sombre par défaut
            
            // Précharger l'image
            const img = new Image();
            
            img.onload = function() {
                console.log(`✅ Image ${index + 1} chargée: ${imageUrl}`);
                slide.style.backgroundImage = `url('${imageUrl}')`;
                slide.style.backgroundColor = 'transparent';
            };
            
            img.onerror = function() {
                console.error(`❌ Erreur image ${index + 1}: ${imageUrl}`);
                // Garder le fond sombre
                slide.style.backgroundColor = '#1a1a1a';
            };
            
            img.src = imageUrl;
        });
    }
    
    // Démarrer le diaporama automatique
    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    // Démarrer l'effet machine à écrire
    function startTypewriter() {
        typeWriter();
        textInterval = setInterval(typeWriter, 8000); // Toutes les 8 secondes
    }
    
    // Événements
    prevBtn.addEventListener('click', function() {
        prevSlide();
        clearInterval(slideInterval);
        startSlideshow();
    });
    
    nextBtn.addEventListener('click', function() {
        nextSlide();
        clearInterval(slideInterval);
        startSlideshow();
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            showSlide(index);
            clearInterval(slideInterval);
            startSlideshow();
        });
    });
    
    // Initialisation
    initImages();
    
    // Afficher le texte immédiatement
    if (titleElement) {
        titleElement.textContent = "Commissariat à la Sécurité Alimentaire et à la Résilience";
        titleElement.style.opacity = '1';
    }
    
    startSlideshow();
    
    // Démarrer l'effet machine à écrire après 2 secondes
    setTimeout(startTypewriter, 2000);
    
    console.log('=== DIAPORAMA INITIALISÉ ===');
});
</script>

<!-- Le reste de votre contenu existant peut aller ici... -->
@endsection
