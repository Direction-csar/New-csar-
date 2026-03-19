@extends('layouts.public')

@section('title', __('pages.home'))

@section('content')
<style>
/* Actualités Grid Responsive */
.news-grid-2x2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 3rem;
}

@media (max-width: 992px) {
    .news-grid-2x2 {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* Hover effects pour les cards d'actualités */
.news-card-ultra:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
}

.news-card-ultra:hover .news-image-hover {
    transform: scale(1.05);
}

.news-card-ultra:hover a[href] {
    background: #d4a574;
    color: white;
}

/* Hero Section avec Diaporama */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
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
    transition: opacity 2s ease-in-out, transform 1.5s ease-in-out;
    background-size: cover;
    background-position: center;
}

.hero-slide.active {
    opacity: 1;
    transform: scale(1);
}

.hero-slide.prev {
    transform: scale(1.1);
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, 
        rgba(0, 0, 0, 0.6) 0%, 
        rgba(34, 197, 94, 0.3) 50%, 
        rgba(0, 0, 0, 0.7) 100%
    );
    z-index: 2;
}

.hero-content {
    text-align: center;
    z-index: 10;
    position: relative;
    color: white;
    animation: fadeInUp 1.5s ease-out;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
    animation: slideInLeft 1s ease-out 0.3s both;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
    animation: slideInRight 1s ease-out 0.6s both;
}

.hero-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease-out 0.9s both;
}

.hero-btn {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    border: none;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.4s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.8rem;
    position: relative;
    overflow: hidden;
}

.hero-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    transition: left 0.5s ease;
}

.hero-btn:hover::before {
    left: 100%;
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
    transition: all 0.4s ease;
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
    transition: all 0.4s ease;
    backdrop-filter: blur(10px);
}

.hero-slider-nav:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-80px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(80px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
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
    max-width: 900px;
    margin: 0 auto;
    padding: 0 2rem;
    color: white;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 6px rgba(0,0,0,0.6);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 2.5rem;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.6);
    line-height: 1.6;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin: 0 auto;
}

.btn-hero {
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.btn-primary-hero {
    background: #22c55e;
    color: white;
    border: 2px solid #22c55e;
}

.btn-primary-hero:hover {
    background: #16a34a;
    border-color: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
}

.btn-secondary-hero {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-secondary-hero:hover {
    background: white;
    color: #1f2937;
    transform: translateY(-2px);
}

/* Services Section */
.services-section {
    padding: 5rem 0;
    background: #f8fafc;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 3rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.service-card {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.03) 0%, rgba(16, 185, 129, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 0;
}

.service-card:hover::before {
    opacity: 1;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(34, 197, 94, 0.2);
    border: 2px solid rgba(34, 197, 94, 0.1);
}

.service-card > * {
    position: relative;
    z-index: 1;
}

.service-icon {
    width: 80px;
    height: 80px;
    background: #22c55e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
    transition: all 0.3s ease;
}

.service-card:hover .service-icon {
    transform: scale(1.1) rotate(5deg);
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
}

.service-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    transition: color 0.3s ease;
}

.service-card:hover .service-title {
    color: #22c55e;
}

.service-description {
    color: #6b7280;
    line-height: 1.6;
}

/* News Section */
.news-section {
    padding: 5rem 0;
    background: white;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.news-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
}

.news-image {
    height: 200px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #9ca3af;
}

.news-content {
    padding: 1.5rem;
}

.news-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.75rem;
}

.news-excerpt {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.news-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    color: #9ca3af;
    font-size: 0.9rem;
}

/* Gallery Section */
.gallery-section {
    padding: 5rem 0;
    background: #f8fafc;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-content {
        padding: 0 1rem;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .services-grid,
    .news-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Hero Section avec Diaporama -->
<section class="hero-section">
    <!-- Diaporama d'images -->
    <div class="hero-slider">
        <div class="hero-slide active" style="background-image: url('{{ asset('images/arriere-plan/N1.jpg') }}');"></div>
        <div class="hero-slide" style="background-image: url('{{ asset('images/arriere-plan/N2.jpg') }}');"></div>
        <div class="hero-slide" style="background-image: url('{{ asset('images/arriere-plan/N3.jpg') }}');"></div>
        <div class="hero-slide" style="background-image: url('{{ asset('images/arriere-plan/N8.jpg') }}');"></div>
    </div>
    
    <!-- Overlay pour la lisibilité -->
    <div class="hero-overlay"></div>
    
    <!-- Contenu par-dessus le diaporama -->
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">{{ __('messages.home.title') }}</h1>
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
// Diaporama automatique avec animations fluides
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-slider-dot');
    const prevBtn = document.getElementById('prev-slide');
    const nextBtn = document.getElementById('next-slide');
    
    let currentSlide = 0;
    let slideInterval;
    
    // Fonction pour afficher une slide spécifique
    function showSlide(index) {
        // Retirer les classes actives
        slides.forEach(slide => {
            slide.classList.remove('active', 'prev');
        });
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Ajouter la classe prev à la slide actuelle pour l'animation de sortie
        if (slides[currentSlide]) {
            slides[currentSlide].classList.add('prev');
        }
        
        // Attendre un peu pour l'animation de transition
        setTimeout(() => {
            slides[currentSlide].classList.remove('prev');
        }, 1500);
        
        // Activer la nouvelle slide
        currentSlide = index;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
        
        // Redémarrer l'intervalle automatique
        resetInterval();
    }
    
    // Fonction pour passer à la slide suivante
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }
    
    // Fonction pour passer à la slide précédente
    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }
    
    // Réinitialiser l'intervalle automatique
    function resetInterval() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000); // Change toutes les 5 secondes
    }
    
    // Événements pour les contrôles
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
    
    // Événements pour les points
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });
    
    // Démarrer le diaporama automatique
    resetInterval();
    
    // Effet machine à écrire pour le titre
    const titleElement = document.querySelector('.hero-title');
    if (titleElement) {
        const text = titleElement.textContent;
        titleElement.textContent = '';
        let index = 0;
        
        function typeWriter() {
            if (index < text.length) {
                titleElement.textContent += text.charAt(index);
                index++;
                setTimeout(typeWriter, 80);
            }
        }
        
        // Démarrer l'effet après 1 seconde
        setTimeout(typeWriter, 1000);
    }
    
    // Animation des boutons au survol
    const buttons = document.querySelectorAll('.hero-btn');
    buttons.forEach((btn, index) => {
        btn.style.animationDelay = `${1.5 + index * 0.2}s`;
        btn.classList.add('animate-fade-in-up');
    });
});

// Ajouter des classes d'animation CSS
const style = document.createElement('style');
style.textContent = `
    .animate-fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 1s ease-out forwards;
    }
`;
document.head.appendChild(style);
</script>

<!-- Services Section ULTRA PRO -->
<section class="services-section-ultra fade-in-up" style="padding: 100px 0; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); position: relative; overflow: hidden;">
    <!-- Animated Background Shapes -->
    <div style="position: absolute; top: -50px; left: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(34, 197, 94, 0.08), transparent); border-radius: 50%; filter: blur(50px); animation: float-shape 12s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -80px; right: -80px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59, 130, 246, 0.06), transparent); border-radius: 50%; filter: blur(60px); animation: float-shape 15s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title with Animation -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-down">
            <div style="display: inline-block; padding: 8px 24px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); border-radius: 50px; margin-bottom: 1.5rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); animation: shine-badge 3s infinite;"></div>
                <span style="color: #22c55e; font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px; position: relative; z-index: 1;">
                    <i class="fas fa-concierge-bell" style="margin-right: 8px; animation: ring-bell 3s ease-in-out infinite;"></i>
                    {{ __('home.services.badge') }}
                </span>
                </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1rem; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                {{ __('home.services.title') }}
            </h2>
        </div>
        
        <div class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem;">
            <!-- Service Card 1 -->
            <a href="{{ route('gallery') }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="100" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite;">
                <!-- Animated Border -->
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #22c55e, #3b82f6, #22c55e); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                
                <!-- Glow Effect -->
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(34, 197, 94, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);">
                        <i class="fas fa-truck" style="animation: bounce-icon 2s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #22c55e; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        {{ __('home.services.food_distribution') }}
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    {{ __('home.services.food_distribution_desc') }}
                </p>
            </div>
            </a>
            
            <!-- Service Card 2 -->
            <a href="{{ route('map') }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="250" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite; animation-delay: 1s;">
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #3b82f6, #8b5cf6, #3b82f6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(59, 130, 246, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);">
                        <i class="fas fa-warehouse" style="animation: bounce-icon 2s ease-in-out infinite; animation-delay: 0.3s;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #3b82f6; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        {{ __('home.services.warehouses') }}
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    {{ __('home.services.warehouses_desc') }}
                </p>
            </div>
            </a>
            
            <!-- Service Card 3 -->
            <a href="{{ route('track') }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="400" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite; animation-delay: 2s;">
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #8b5cf6, #ec4899, #8b5cf6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(139, 92, 246, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);">
                        <i class="fas fa-search" style="animation: pulse-search 2s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #8b5cf6; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        {{ __('home.services.track_request') }}
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    {{ __('home.services.track_request_desc') }}
                </p>
            </div>
            </a>
        </div>
    </div>
</section>

<!-- Actualités Section ULTRA PRO -->
<section class="news-section-ultra fade-in-left" style="padding: 100px 0; background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); position: relative; overflow: hidden;">
    <!-- Animated Particles -->
    <div style="position: absolute; top: 10%; left: 5%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(34, 197, 94, 0.05), transparent); border-radius: 50%; filter: blur(40px); animation: float-particle-slow 20s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: 20%; right: 10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(59, 130, 246, 0.04), transparent); border-radius: 50%; filter: blur(50px); animation: float-particle-slow 25s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1; max-width: 1400px; margin: 0 auto; padding: 0 20px;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 5rem;" data-aos="fade-up">
            <h2 style="font-size: 3.5rem; font-weight: 700; margin-bottom: 1rem; color: #5a7d70; letter-spacing: 2px; text-transform: uppercase;">
                {{ __('home.news.title') }}
            </h2>
            <!-- Ligne décorative -->
            <div style="width: 200px; height: 2px; background: #5a7d70; margin: 0 auto 3rem;"></div>
        </div>
        
        <div class="news-grid-2x2" style="margin-bottom: 3rem;">
        @php $newsCount = isset($latestNews) ? $latestNews->count() : 0; @endphp
            @if(isset($latestNews) && $latestNews->count() > 0)
                @foreach($latestNews as $index => $news)
                <div class="news-card-ultra" data-aos="fade-up" data-aos-delay="{{ $index * 150 }}" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); position: relative; cursor: pointer; animation: float-news 5s ease-in-out infinite; animation-delay: {{ $index * 1 }}s;">
                    <!-- Glow on hover -->
                    <div style="position: absolute; inset: -3px; background: linear-gradient(135deg, #3b82f6, #22c55e, #3b82f6); background-size: 200% 200%; border-radius: 24px; opacity: 0; z-index: -1; animation: gradient-news 4s ease infinite; transition: opacity 0.5s ease;"></div>
                    
                    @if($news->image)
                    <div style="position: relative; height: 220px; overflow: hidden;">
                        @php
                            $imagePath = trim((string) $news->image);
                            if (preg_match('/^https?:\/\//i', $imagePath)) {
                                $imageUrl = $imagePath;
                            } else {
                                $imageUrl = asset('storage/' . ltrim($imagePath, '/'));
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $news->title }}" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);"
                             class="news-image-hover"
                             onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                        
                        <!-- Overlay gradient -->
                        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.3) 100%); transition: all 0.5s ease;"></div>
                        
                        <!-- Date Badge -->
                        <div style="position: absolute; top: 15px; right: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 20px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <i class="fas fa-calendar-alt" style="color: #3b82f6; font-size: 0.9rem;"></i>
                            <span style="color: #1f2937; font-weight: 600; font-size: 0.85rem;">
                                {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}
                            </span>
                    </div>
                    </div>
                    @else
                    <div style="position: relative; height: 220px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <i class="far fa-newspaper" style="font-size: 4rem; color: rgba(255, 255, 255, 0.3); animation: pulse-news-icon 3s ease-in-out infinite;"></i>
                        <div style="position: absolute; top: 15px; right: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 20px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-calendar-alt" style="color: #3b82f6; font-size: 0.9rem;"></i>
                            <span style="color: #1f2937; font-weight: 600; font-size: 0.85rem;">
                                {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    @endif
                    
                    <div style="padding: 2rem; position: relative;">
                        <!-- Badge ACTUALITÉ -->
                        <div style="display: inline-block; background: #5a7d70; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                            {{ __('home.news.badge') }}
                        </div>
                        
                        <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; line-height: 1.4; transition: color 0.3s ease;">
                            {{ $news->title }}
                        </h3>
                        <p style="color: #6b7280; line-height: 1.7; margin-bottom: 1.5rem; font-size: 0.95rem;">
                            {{ $news->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($news->content), 120) }}
                        </p>
                        
                        <a href="{{ route('news.show', $news->id) }}" style="display: inline-block; padding: 10px 24px; background: transparent; color: #d4a574; border: 2px solid #d4a574; border-radius: 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">
                            {{ __('home.news.read_more') }}
                        </a>
                    </div>
                </div>
                @endforeach
            @else
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-newspaper" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280; margin-bottom: 0.5rem;">{{ __('home.news.empty_title') }}</h3>
                    <p style="color: #9ca3af;">{{ __('home.news.empty_desc') }}</p>
                </div>
            @endif
        </div>
        
        @if(isset($latestNews) && $latestNews->count() > 0)
        <div style="text-align: center; margin-top: 3rem;">
            <a href="{{ route('news') }}" class="btn btn-success" style="padding: 0.75rem 2rem; border-radius: 50px;">
                <i class="fas fa-arrow-right"></i>
                {{ __('home.news.view_all') }}
            </a>
        </div>
        @endif

        <!-- Section LinkedIn - Flux des actualités en direct (widget Elfsight) -->
        @php
            $linkedInUrl = config('services.linkedin.company_url');
            $linkedInEmbed = config('services.linkedin.feed_embed_code');
        @endphp
        <div style="margin-top: 4rem; padding: 2rem; background: linear-gradient(135deg, rgba(10, 102, 194, 0.08) 0%, rgba(10, 102, 194, 0.03) 100%); border-radius: 24px; border: 2px solid rgba(10, 102, 194, 0.15);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="width: 56px; height: 56px; background: #0a66c2; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin: 0 0 0.25rem 0;">{{ __('home.linkedin.title') }}</h3>
                    <p style="color: #6b7280; margin: 0; font-size: 0.95rem;">{{ __('home.linkedin.subtitle') }}</p>
                </div>
            </div>
            @if($linkedInEmbed)
                <div class="linkedin-embed-container" style="width: 100%; min-height: 400px;">
                    {!! $linkedInEmbed !!}
                </div>
                <div style="text-align: right; margin-top: 1rem;">
                    <a href="{{ $linkedInUrl }}" target="_blank" rel="noopener noreferrer" style="color: #0a66c2; font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                        {{ __('home.linkedin.view_more') }} →
                    </a>
                </div>
            @else
                <div style="text-align: center; padding: 2rem;">
                    <button type="button" onclick="openLinkedInModal()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 12px 24px; background: #0a66c2; color: white; border: none; border-radius: 50px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(10, 102, 194, 0.4);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        {{ __('home.linkedin.view_publications') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Modal LinkedIn - Fenêtre des actualités -->
<div id="linkedin-modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px; overflow: auto;">
    <div style="background: white; border-radius: 24px; max-width: 900px; width: 100%; max-height: 90vh; overflow: hidden; box-shadow: 0 25px 80px rgba(0,0,0,0.4); position: relative;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #0a66c2, #004182); border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                <span style="color: white; font-weight: 700; font-size: 1.2rem;">Dernières actualités CSAR sur LinkedIn</span>
            </div>
            <button type="button" onclick="closeLinkedInModal()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; line-height: 1; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <div style="padding: 1.5rem; overflow-y: auto; max-height: calc(90vh - 80px);">
            <div id="linkedin-fallback" style="text-align: center; padding: 3rem 2rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 16px;">
                <div style="width: 80px; height: 80px; background: #0a66c2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="white"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </div>
                <h4 style="color: #1f2937; font-weight: 700; margin-bottom: 0.75rem;">Nos publications sur LinkedIn</h4>
                <p style="color: #6b7280; margin-bottom: 1.5rem; max-width: 400px; margin-left: auto; margin-right: auto;">Consultez nos dernières actualités, communiqués et partages sur notre page LinkedIn officielle.</p>
                <a href="{{ $linkedInUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 14px 28px; background: #0a66c2; color: white; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(10, 102, 194, 0.4);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    Voir nos publications sur LinkedIn
                </a>
                <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 1.5rem;">Pour afficher le flux directement sur le site, utilisez <a href="https://elfsight.com/linkedin-feed-widget/" target="_blank" rel="noopener" style="color: #0a66c2;">Elfsight</a> (gratuit).</p>
            </div>
        </div>
    </div>
</div>
<script>
function openLinkedInModal() {
    document.getElementById('linkedin-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLinkedInModal() {
    document.getElementById('linkedin-modal').style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('linkedin-modal').addEventListener('click', function(e) {
    if (e.target === this) closeLinkedInModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLinkedInModal();
});
</script>

<!-- Stats Section ULTRA PRO with Counter Animation -->
<section class="stats-section-ultra slide-in-bottom" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background Elements -->
    <div style="position: absolute; top: -100px; left: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(34, 197, 94, 0.15), transparent); border-radius: 50%; filter: blur(80px); animation: pulse-orb-stats 8s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -150px; right: -150px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent); border-radius: 50%; filter: blur(100px); animation: pulse-orb-stats 10s ease-in-out infinite reverse;"></div>
    
    <!-- Particle Stars -->
    <div class="stars-container" style="position: absolute; inset: 0; overflow: hidden; pointer-events: none;">
        @for($i = 0; $i < 30; $i++)
        <div class="star" style="position: absolute; width: {{ rand(2, 4) }}px; height: {{ rand(2, 4) }}px; background: white; border-radius: 50%; top: {{ rand(0, 100) }}%; left: {{ rand(0, 100) }}%; opacity: {{ rand(30, 80) / 100 }}; animation: twinkle-star {{ rand(2, 5) }}s ease-in-out infinite; animation-delay: {{ rand(0, 50) / 10 }}s;"></div>
        @endfor
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-down">
            <div style="display: inline-block; padding: 6px 20px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.2)); border-radius: 50px; margin-bottom: 1rem; position: relative; overflow: hidden; border: 1px solid rgba(34, 197, 94, 0.3);">
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); animation: shine-badge 3s infinite;"></div>
                <span style="color: #22c55e; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.2px; position: relative; z-index: 1;">
                    <i class="fas fa-chart-line" style="margin-right: 6px; animation: pulse-chart 2s ease-in-out infinite;"></i>
                    {{ __('home.stats.badge') }}
                </span>
            </div>
            <h2 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.8rem; color: white; text-shadow: 0 0 20px rgba(34, 197, 94, 0.2);">
                {{ __('home.stats.title') }}
            </h2>
            <p style="color: rgba(255, 255, 255, 0.75); font-size: 1rem; max-width: 650px; margin: 0 auto; line-height: 1.6;">
                {{ __('home.stats.subtitle') }}
            </p>
        </div>
        
        <!-- Stats Grid - une seule ligne -->
        <div class="stats-section-ultra" style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 2rem 3rem; max-width: 1200px; margin: 0 auto;">
            <!-- Stat 1: Agents -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="100" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(34, 197, 94, 0.2); border-radius: 24px; padding: 1.5rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-users" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div style="position: relative; z-index: 2; display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="counter" data-target="{{ $stats['agents'] }}" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">0</span>
                    <span style="color: rgba(255, 255, 255, 0.9); font-size: 1rem; font-weight: 600;">{{ __('home.stats.agents') }}</span>
                </div>
            </div>
            
            <!-- Stat 2: Magasins -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="250" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(59, 130, 246, 0.2); border-radius: 24px; padding: 1.5rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-warehouse" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div style="position: relative; z-index: 2; display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="counter" data-target="{{ $stats['warehouses'] }}" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">0</span>
                    <span style="color: rgba(255, 255, 255, 0.9); font-size: 1rem; font-weight: 600;">{{ __('home.stats.warehouses') }}</span>
                </div>
            </div>
            
            <!-- Stat 3: 51+ Communes et régions -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="400" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(219, 39, 119, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(236, 72, 153, 0.2); border-radius: 24px; padding: 1.5rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-map-marker-alt" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div style="position: relative; z-index: 2; display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="counter" data-target="{{ $stats['communes'] ?? $stats['experience'] ?? '51' }}" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">0</span>
                    <span style="font-size: 2.5rem; font-weight: 900; color: white;">+</span>
                    <span style="color: rgba(255, 255, 255, 0.9); font-size: 1rem; font-weight: 600;">{{ __('home.stats.communes') }}</span>
                </div>
            </div>
            
            <!-- Stat 4: Capacité -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="550" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(139, 92, 246, 0.2); border-radius: 24px; padding: 1.5rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-boxes" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div style="position: relative; z-index: 2; display: flex; align-items: baseline; gap: 0.25rem; flex-wrap: wrap;">
                    <span class="counter" data-target="{{ $stats['capacity'] }}" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">0</span>
                    <span style="color: white; font-size: 2.5rem; font-weight: 900;">000</span>
                    <span style="color: rgba(255, 255, 255, 0.9); font-size: 1rem; font-weight: 600;">{{ __('home.stats.capacity') }}</span>
                </div>
            </div>
            
            <!-- Stat 5: Demandes traitées -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="700" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(8, 145, 178, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(6, 182, 212, 0.2); border-radius: 24px; padding: 1.5rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #06b6d4, #0891b2); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-clipboard-check" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div style="position: relative; z-index: 2; display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="counter" data-target="{{ str_replace(' ', '', $stats['demandes_traitees'] ?? '0') }}" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">0</span>
                    <span style="color: rgba(255, 255, 255, 0.9); font-size: 1rem; font-weight: 600;">{{ __('home.stats.demandes_traitees') }}</span>
                </div>
            </div>
            
            <!-- Stat 6: Taux de satisfaction -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="850" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.1), rgba(202, 138, 4, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(234, 179, 8, 0.2); border-radius: 24px; padding: 1.5rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #eab308, #ca8a04); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-star" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div style="position: relative; z-index: 2; display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="stats-value-static" style="font-size: 2.5rem; font-weight: 900; color: white; line-height: 1;">{{ $stats['taux_satisfaction'] ?? '0' }}</span>
                    <span style="color: rgba(255, 255, 255, 0.9); font-size: 1rem; font-weight: 600;">{{ __('home.stats.taux_satisfaction') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Galerie Section PRO -->
<section class="gallery-section-pro fade-in-right" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(34, 197, 94, 0.1), transparent); border-radius: 50%; filter: blur(60px); animation: float-orb 15s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(59, 130, 246, 0.08), transparent); border-radius: 50%; filter: blur(80px); animation: float-orb 20s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-up">
            <div style="display: inline-block; padding: 8px 24px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); border-radius: 50px; margin-bottom: 1.5rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); animation: shine-effect 3s infinite;"></div>
                <span style="color: #22c55e; font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px; position: relative; z-index: 1;">
                    <i class="fas fa-camera-retro" style="margin-right: 8px; animation: pulse-icon 2s ease-in-out infinite;"></i>
                    {{ __('home.gallery.badge') }}
                </span>
            </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1.2rem; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                {{ __('home.gallery.title') }}
            </h2>
            <p style="color: #6b7280; font-size: 1.2rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                {{ __('home.gallery.subtitle') }}
            </p>
        </div>
        
        @if(isset($galleryImages) && $galleryImages->count() > 0)
        <!-- SLIDER PROFESSIONNEL AVEC DIAPORAMA AUTOMATIQUE -->
        <div class="gallery-slider-container" id="gallery-slider-container" style="position: relative; max-width: 1200px; margin: 0 auto; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 80px rgba(0, 0, 0, 0.15);">
            <!-- Slider Wrapper -->
            <div class="gallery-slider" id="gallery-slider" style="position: relative; height: 600px; overflow: hidden;">
                @foreach($galleryImages as $index => $image)
                <!-- Slide {{ $index + 1 }} -->
                <div class="slider-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" style="position: absolute; inset: 0; opacity: {{ $index === 0 ? '1' : '0' }}; transform: {{ $index === 0 ? 'scale(1)' : 'scale(1.1)' }}; transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);">
                    <!-- Glow Effect Background -->
                <div class="glow-bg" style="position: absolute; inset: -30px; background: radial-gradient(circle at center, rgba(34, 197, 94, 0.4), transparent 70%); opacity: 0; filter: blur(20px); transition: all 0.6s ease; z-index: -1;"></div>
                
                <!-- Sparkle Particles -->
                <div class="sparkles" style="position: absolute; inset: 0; pointer-events: none; z-index: 10; opacity: 0; transition: opacity 0.5s ease;">
                    @for($i = 0; $i < 5; $i++)
                    <div class="sparkle" style="position: absolute; width: 4px; height: 4px; background: white; border-radius: 50%; top: {{ rand(10, 90) }}%; left: {{ rand(10, 90) }}%; animation: twinkle {{ 1 + rand(1, 3) }}s ease-in-out infinite; animation-delay: {{ $i * 0.2 }}s; box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);"></div>
                    @endfor
                </div>
                
                <!-- Image Container -->
                <div style="position: relative; width: 100%; height: 100%; overflow: hidden;">
                    <!-- Animated Corners -->
                    <div class="corner corner-tl" style="position: absolute; top: 15px; left: 15px; width: 30px; height: 30px; border-top: 3px solid #22c55e; border-left: 3px solid #22c55e; opacity: 0; transform: translate(-10px, -10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    <div class="corner corner-tr" style="position: absolute; top: 15px; right: 15px; width: 30px; height: 30px; border-top: 3px solid #22c55e; border-right: 3px solid #22c55e; opacity: 0; transform: translate(10px, -10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    <div class="corner corner-bl" style="position: absolute; bottom: 15px; left: 15px; width: 30px; height: 30px; border-bottom: 3px solid #22c55e; border-left: 3px solid #22c55e; opacity: 0; transform: translate(-10px, 10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    <div class="corner corner-br" style="position: absolute; bottom: 15px; right: 15px; width: 30px; height: 30px; border-bottom: 3px solid #22c55e; border-right: 3px solid #22c55e; opacity: 0; transform: translate(10px, 10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    
                    <!-- Image with Multiple Layers -->
                    <div style="position: relative; width: 100%; height: 100%;">
                        <!-- Main Image -->
                        <img src="{{ asset('storage/' . $image->file_path) }}" 
                             alt="{{ $image->alt_text ?? $image->title }}" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1); filter: brightness(0.95) contrast(1.05);"
                             class="gallery-image-hover">
                        
                        <!-- Color Overlay Effect -->
                        <div class="color-overlay" style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(59, 130, 246, 0.2)); mix-blend-mode: overlay; opacity: 0; transition: opacity 0.5s ease;"></div>
                    </div>
                    
                    <!-- Gradient Overlay -->
                    <div class="gallery-overlay" style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.1) 50%, rgba(0, 0, 0, 0.8) 100%); opacity: 0.8; transition: all 0.5s ease;"></div>
                    
                    <!-- Scan Line Effect -->
                    <div class="scan-line" style="position: absolute; top: -100%; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.8), transparent); opacity: 0; transition: all 0.5s ease;"></div>
                    
                    <!-- Content Overlay -->
                    <div class="gallery-content" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem; transform: translateY(20px); opacity: 0; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); z-index: 2;">
                        <h3 style="color: white; font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.3); transform: translateX(-20px); transition: all 0.5s ease 0.1s;">
                            {{ $image->title }}
                        </h3>
                        @if($image->category)
                        <div style="display: inline-block; padding: 4px 12px; background: rgba(34, 197, 94, 0.9); border-radius: 20px; font-size: 0.75rem; color: white; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; transform: scale(0.8); transition: all 0.4s ease 0.2s; box-shadow: 0 0 20px rgba(34, 197, 94, 0.6);">
                            {{ $image->category }}
                        </div>
                        @endif
                        @if($image->description)
                        <p style="color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; margin: 0.5rem 0 0; line-height: 1.5; transform: translateY(10px); transition: all 0.5s ease 0.3s;">
                            {{ Str::limit($image->description, 80) }}
                        </p>
                        @endif
                    </div>
                    
                    <!-- Zoom Icon -->
                    <div class="zoom-icon-gallery" style="position: absolute; top: 20px; right: 20px; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.95); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transform: scale(0) rotate(-180deg); transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); z-index: 3;">
                        <i class="fas fa-search-plus" style="color: #22c55e; font-size: 1.2rem; animation: pulse-zoom 2s ease-in-out infinite;"></i>
                    </div>
                    
                    <!-- Border Animation -->
                    <div class="border-animation" style="position: absolute; inset: -3px; border-radius: 24px; background: linear-gradient(135deg, #22c55e, #3b82f6, #8b5cf6, #ec4899, #22c55e); background-size: 400% 400%; opacity: 0; z-index: -1; animation: rotate-border-wave 6s ease-in-out infinite;"></div>
                    
                    <!-- Ripple Effect -->
                    <div class="ripple-effect-gallery" style="position: absolute; inset: 0; opacity: 0; transition: all 0.8s ease;"></div>
                </div>
            </div>
            @endforeach
            </div>
            <!-- Boutons de navigation du slider -->
            <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 20;">
                @foreach($galleryImages as $index => $img)
                <button type="button" class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; background: {{ $index === 0 ? 'white' : 'transparent' }}; cursor: pointer; transition: all 0.3s ease;"></button>
                @endforeach
            </div>
        </div>
        @else
        <div style="text-align: center; padding: 4rem 0;">
            <i class="fas fa-images" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <p style="color: #6b7280; font-size: 1.1rem;">{{ __('home.gallery.empty') }}</p>
        </div>
        @endif
    </div>
</section>

<!-- Lightbox Modal -->
<div id="gallery-lightbox" class="gallery-lightbox" onclick="closeLightbox()" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.95); backdrop-filter: blur(10px); animation: fadeIn 0.3s ease;">
    <span onclick="closeLightbox()" style="position: absolute; top: 30px; right: 50px; color: #fff; font-size: 50px; font-weight: 300; cursor: pointer; z-index: 10000; transition: all 0.3s ease; text-shadow: 0 0 10px rgba(0,0,0,0.5);" onmouseover="this.style.color='#22c55e'" onmouseout="this.style.color='#fff'">&times;</span>
    
    <!-- Navigation Arrows -->
    <button onclick="changeImage(-1); event.stopPropagation();" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.3); color: white; font-size: 2rem; padding: 1rem 1.5rem; cursor: pointer; border-radius: 50%; transition: all 0.3s ease; z-index: 10000; backdrop-filter: blur(10px);" onmouseover="this.style.background='rgba(34, 197, 94, 0.9)'; this.style.borderColor='#22c55e'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.3)'">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <button onclick="changeImage(1); event.stopPropagation();" style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.3); color: white; font-size: 2rem; padding: 1rem 1.5rem; cursor: pointer; border-radius: 50%; transition: all 0.3s ease; z-index: 10000; backdrop-filter: blur(10px);" onmouseover="this.style.background='rgba(34, 197, 94, 0.9)'; this.style.borderColor='#22c55e'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.3)'">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <!-- Image Container -->
    <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 80px 120px;">
        <img id="lightbox-img" src="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5); animation: zoomIn 0.3s ease;">
    </div>
    
    <!-- Image Info -->
    <div id="lightbox-info" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 2rem; background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent); color: white; text-align: center;">
        <h3 id="lightbox-title" style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.5rem;"></h3>
        <p id="lightbox-category" style="font-size: 1rem; opacity: 0.9; margin-bottom: 0.5rem;"></p>
        <p id="lightbox-description" style="font-size: 1rem; opacity: 0.8; max-width: 800px; margin: 0 auto;"></p>
    </div>
    
    <!-- Image Counter -->
    <div style="position: absolute; top: 30px; left: 50%; transform: translateX(-50%); background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 10px 25px; border-radius: 50px; color: white; font-weight: 600; border: 2px solid rgba(255, 255, 255, 0.2);">
        <span id="image-counter">1 / 1</span>
    </div>
</div>

<style>
/* ========================================
   ULTRA LUXE GALLERY PRO ANIMATIONS
   ======================================== */

/* Floating Card Animation */
@keyframes float-card {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Sparkle/Twinkle Effect */
@keyframes twinkle {
    0%, 100% {
        opacity: 0;
        transform: scale(0);
    }
    50% {
        opacity: 1;
        transform: scale(1.5);
    }
}

/* Pulse Zoom Icon */
@keyframes pulse-zoom {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.15);
    }
}

/* Rotating Border Wave */
@keyframes rotate-border-wave {
    0% {
        background-position: 0% 50%;
        transform: rotate(0deg);
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
        transform: rotate(360deg);
    }
}

/* Scan Line Effect */
@keyframes scan-line {
    0% {
        top: -100%;
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        top: 100%;
        opacity: 0;
    }
}

/* Ripple Pulse */
@keyframes ripple-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7),
                    0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    50% {
        box-shadow: 0 0 0 15px rgba(34, 197, 94, 0),
                    0 0 0 30px rgba(34, 197, 94, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0),
                    0 0 0 0 rgba(34, 197, 94, 0);
    }
}

/* Gallery Item Hover Effects */
.gallery-item-pro:hover {
    transform: translateY(-20px) scale(1.03) !important;
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.35) !important;
    animation-play-state: paused !important;
}

.gallery-item-pro:hover .gallery-image-hover {
    transform: scale(1.15) rotate(2deg);
    filter: brightness(1.1) contrast(1.1) saturate(1.2);
}

.gallery-item-pro:hover .gallery-overlay {
    opacity: 0.95 !important;
    background: linear-gradient(to bottom, rgba(34, 197, 94, 0.1) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.9) 100%) !important;
}

.gallery-item-pro:hover .gallery-content {
    transform: translateY(0) !important;
    opacity: 1 !important;
}

.gallery-item-pro:hover .gallery-content h3 {
    transform: translateX(0) !important;
}

.gallery-item-pro:hover .gallery-content div {
    transform: scale(1) !important;
}

.gallery-item-pro:hover .gallery-content p {
    transform: translateY(0) !important;
}

.gallery-item-pro:hover .zoom-icon-gallery {
    opacity: 1 !important;
    transform: scale(1) rotate(0deg) !important;
    animation: ripple-pulse 2s infinite;
}

.gallery-item-pro:hover .border-animation {
    opacity: 1 !important;
}

.gallery-item-pro:hover .glow-bg {
    opacity: 1 !important;
}

.gallery-item-pro:hover .sparkles {
    opacity: 1 !important;
}

.gallery-item-pro:hover .corner {
    opacity: 1 !important;
    transform: translate(0, 0) !important;
}

.gallery-item-pro:hover .color-overlay {
    opacity: 0.3 !important;
}

.gallery-item-pro:hover .scan-line {
    animation: scan-line 2s ease-in-out;
    opacity: 1;
}

.gallery-item-pro:hover .ripple-effect-gallery {
    opacity: 1;
    background: radial-gradient(circle at center, transparent 0%, rgba(34, 197, 94, 0.2) 50%, transparent 100%);
    animation: ripple-expand 1.5s ease-out;
}

@keyframes ripple-expand {
    0% {
        transform: scale(0);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Background Animations */
@keyframes shine-effect {
    0% { left: -100%; }
    50%, 100% { left: 200%; }
}

@keyframes float-orb {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, -30px) scale(1.1); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes zoomIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@keyframes pulse-icon {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* 3D Perspective Effect on Grid */
.gallery-grid-pro {
    perspective: 1000px;
}

/* Individual Card Entrance Animations */
.gallery-item-pro {
    will-change: transform;
}

/* Corner Animations - Staggered */
.gallery-item-pro:hover .corner-tl {
    transition-delay: 0s !important;
}

.gallery-item-pro:hover .corner-tr {
    transition-delay: 0.1s !important;
}

.gallery-item-pro:hover .corner-br {
    transition-delay: 0.2s !important;
}

.gallery-item-pro:hover .corner-bl {
    transition-delay: 0.3s !important;
}

/* Smooth transitions */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .gallery-grid-pro {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
        gap: 1.5rem !important;
    }
    
    .gallery-item-pro {
        animation: float-card 4s ease-in-out infinite !important;
    }
    
    .gallery-item-pro:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
    
    .gallery-lightbox button {
        padding: 0.5rem 1rem !important;
        font-size: 1.5rem !important;
    }
    
    .gallery-lightbox > div:first-of-type {
        padding: 60px 20px !important;
    }
    
    .corner {
        display: none !important;
    }
}

@media (max-width: 480px) {
    .gallery-grid-pro {
        grid-template-columns: 1fr !important;
    }
    
    .sparkles {
        display: none !important;
    }
}

/* Performance Optimizations */
.gallery-item-pro,
.gallery-image-hover,
.border-animation,
.glow-bg {
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* Prefers Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .gallery-item-pro,
    .sparkle,
    .scan-line,
    .border-animation {
        animation: none !important;
    }
    
    .gallery-item-pro:hover {
        transform: none !important;
    }
}

/* ========================================
   SERVICES SECTION ULTRA ANIMATIONS
   ======================================== */

/* Float Shape Background */
@keyframes float-shape {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -30px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

/* Shine Badge Effect */
@keyframes shine-badge {
    0% { left: -100%; }
    50%, 100% { left: 200%; }
}

/* Ring Bell Icon */
@keyframes ring-bell {
    0%, 100% {
        transform: rotate(0deg);
    }
    10%, 30% {
        transform: rotate(-10deg);
    }
    20%, 40% {
        transform: rotate(10deg);
    }
    50% {
        transform: rotate(0deg);
    }
}

/* Float Service Cards */
@keyframes float-service {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-15px);
    }
}

/* Border Rotate Effect */
@keyframes border-rotate {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Bounce Icon */
@keyframes bounce-icon {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

/* Rotate Dashed Circle */
@keyframes rotate-dashed {
    0% {
        transform: rotate(0deg);
        opacity: 0;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        transform: rotate(360deg);
        opacity: 0;
    }
}

/* Pulse Search Icon */
@keyframes pulse-search {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Service Card Hover Effects */
.service-card-ultra:hover {
    transform: translateY(-20px) scale(1.05) !important;
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.3) !important;
    animation-play-state: paused !important;
}

.service-card-ultra:hover .service-border {
    opacity: 1 !important;
}

.service-card-ultra:hover .service-glow {
    opacity: 1 !important;
}

.service-card-ultra:hover .service-icon-ultra {
    transform: scale(1.15) rotate(360deg);
    box-shadow: 0 15px 40px rgba(34, 197, 94, 0.5) !important;
}

.service-card-ultra:hover .service-icon-ultra div {
    opacity: 1 !important;
}

.service-card-ultra:hover h3 {
    color: #22c55e !important;
    transform: scale(1.05);
}

/* ========================================
   NEWS SECTION ULTRA ANIMATIONS
   ======================================== */

/* Float Particle Slow */
@keyframes float-particle-slow {
    0%, 100% {
        transform: translate(0, 0);
    }
    50% {
        transform: translate(50px, -50px);
    }
}

/* Pulse News Icon (badge) */
@keyframes pulse-news {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
}

/* Float News Cards */
@keyframes float-news {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-12px);
    }
}

/* Gradient News Border */
@keyframes gradient-news {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Pulse News Icon (no image) */
@keyframes pulse-news-icon {
    0%, 100% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.5;
    }
}

/* News Card Hover Effects */
.news-card-ultra:hover {
    transform: translateY(-15px) scale(1.03) !important;
    box-shadow: 0 25px 70px rgba(59, 130, 246, 0.3) !important;
    animation-play-state: paused !important;
}

.news-card-ultra:hover > div:first-child {
    opacity: 1 !important;
}

.news-card-ultra:hover .news-image-hover {
    transform: scale(1.1) rotate(2deg);
}

.news-card-ultra:hover h3 {
    color: #3b82f6 !important;
}

.news-card-ultra:hover a {
    color: #22c55e !important;
}

.news-card-ultra:hover a i {
    transform: translateX(5px) !important;
}

.news-card-ultra:hover a div {
    width: 100% !important;
}

/* ========================================
   RESPONSIVE FOR NEW SECTIONS
   ======================================== */

@media (max-width: 768px) {
    .services-section-ultra .services-grid {
        grid-template-columns: 1fr !important;
    }
    
    .service-card-ultra {
        animation-duration: 6s !important;
    }
    
    .news-section-ultra > div > div:last-child {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 480px) {
    .service-card-ultra:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
    
    .news-card-ultra:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
}

/* ========================================
   STATS SECTION ULTRA ANIMATIONS
   ======================================== */

/* Pulse Orb Stats */
@keyframes pulse-orb-stats {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
}

/* Twinkle Stars */
@keyframes twinkle-star {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.5);
    }
}

/* Pulse Chart Icon */
@keyframes pulse-chart {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Float Icon */
@keyframes float-icon {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Border Flow */
@keyframes border-flow {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Counter Number Pulse */
@keyframes counter-pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* Stat Card Hover Effects */
.stat-card-ultra:hover {
    transform: translateY(-15px) scale(1.05) !important;
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.4) !important;
}

.stat-card-ultra:hover .stat-glow {
    opacity: 1 !important;
}

.stat-card-ultra:hover .stat-border {
    opacity: 1 !important;
}

.stat-card-ultra:hover .counter {
    animation: counter-pulse 0.6s ease !important;
}

/* Counter Animation State */
.counter.counting {
    animation: counter-pulse 0.3s ease;
}

/* Responsive Stats */
@media (max-width: 768px) {
    .stats-section-ultra > div > div:last-child {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
        gap: 2rem !important;
    }
    
    .stat-card-ultra {
        padding: 2rem 1.5rem !important;
    }
    
    .counter {
        font-size: 3rem !important;
    }
}

@media (max-width: 480px) {
    .stats-section-ultra > div > div:last-child {
        grid-template-columns: 1fr !important;
    }
    
    .stat-card-ultra:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
}
</style>

<script>
@if(isset($galleryImages) && $galleryImages->count() > 0)
const galleryData = [
    @foreach($galleryImages as $image)
    {
        src: "{{ asset('storage/' . $image->file_path) }}",
        title: "{{ $image->title }}",
        category: "{{ $image->category ?? '' }}",
        description: "{{ $image->description ?? '' }}"
    },
    @endforeach
];

let currentImageIndex = 0;

function openLightbox(index) {
    currentImageIndex = index;
    updateLightboxImage();
    document.getElementById('gallery-lightbox').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('gallery-lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function changeImage(direction) {
    currentImageIndex += direction;
    if (currentImageIndex < 0) currentImageIndex = galleryData.length - 1;
    if (currentImageIndex >= galleryData.length) currentImageIndex = 0;
    updateLightboxImage();
}

function updateLightboxImage() {
    const image = galleryData[currentImageIndex];
    document.getElementById('lightbox-img').src = image.src;
    document.getElementById('lightbox-title').textContent = image.title;
    document.getElementById('lightbox-category').textContent = image.category;
    document.getElementById('lightbox-description').textContent = image.description;
    document.getElementById('image-counter').textContent = `${currentImageIndex + 1} / ${galleryData.length}`;
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('gallery-lightbox');
    if (lightbox && lightbox.style.display === 'block') {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
    }
});

// Diaporama automatique - changement d'images toutes les 5 secondes
let currentSlide = 0;
const slides = document.querySelectorAll('.slider-slide');
const dots = document.querySelectorAll('.slider-dot');
const totalSlides = slides.length;

function goToSlide(index) {
    currentSlide = index;
    if (currentSlide >= totalSlides) currentSlide = 0;
    if (currentSlide < 0) currentSlide = totalSlides - 1;
    slides.forEach((s, i) => {
        s.style.opacity = i === currentSlide ? '1' : '0';
        s.style.transform = i === currentSlide ? 'scale(1)' : 'scale(1.1)';
    });
    dots.forEach((d, i) => {
        d.classList.toggle('active', i === currentSlide);
        d.style.background = i === currentSlide ? 'white' : 'transparent';
    });
}

if (totalSlides > 1) {
    setInterval(() => goToSlide(currentSlide + 1), 5000);
    document.querySelectorAll('.slider-dot').forEach((dot, i) => {
        dot.addEventListener('click', () => goToSlide(i));
    });
}
@endif

// ========================================
// ANIMATED COUNTERS (EFFECT CHRONO) - BOUCLE CONTINUE
// ========================================

function animateCounter(element, start, end, duration, onComplete) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = Math.floor(easeOutQuart * (end - start) + start);
        element.textContent = current.toLocaleString('fr-FR');
        
        if (progress < 1) {
            element.classList.add('counting');
            window.requestAnimationFrame(step);
        } else {
            element.classList.remove('counting');
            element.textContent = end.toLocaleString('fr-FR');
            if (onComplete) onComplete();
        }
    };
    window.requestAnimationFrame(step);
}

function runCounterLoop(counter, target, duration, pauseMs) {
    const run = () => {
        animateCounter(counter, 0, target, duration, () => {
            setTimeout(() => run(), pauseMs); // Pause 2-3 sec puis recommence
        });
    };
    run();
}

const observerOptions = { threshold: 0.3, rootMargin: '0px' };
const counters = document.querySelectorAll('.counter');
let hasAnimated = false;

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !hasAnimated) {
            hasAnimated = true;
            const pauseBetweenLoops = 2500; // 2.5 secondes de pause avant de recommencer
            counters.forEach((counter, index) => {
                const target = parseInt(counter.getAttribute('data-target')) || 0;
                setTimeout(() => {
                    runCounterLoop(counter, target, 2000, pauseBetweenLoops);
                }, index * 200);
            });
        }
    });
}, observerOptions);

const statsSection = document.querySelector('.stats-section-ultra');
if (statsSection) {
    counterObserver.observe(statsSection);
}
</script>

<!-- Partners Section with PRO Effects -->
@if(isset($partners) && $partners->count() > 0)
<section class="partners-section-pro blur-in" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background Particles -->
    <div class="particles-container">
        @for($i = 0; $i < 20; $i++)
        <div class="particle" style="
            position: absolute;
            width: {{ rand(4, 12) }}px;
            height: {{ rand(4, 12) }}px;
            background: linear-gradient(135deg, rgba(34, 197, 94, {{ rand(10, 30) / 100 }}), rgba(59, 130, 246, {{ rand(10, 30) / 100 }}));
            border-radius: 50%;
            left: {{ rand(0, 100) }}%;
            top: {{ rand(0, 100) }}%;
            animation: float-particle {{ rand(15, 30) }}s ease-in-out infinite;
            animation-delay: {{ rand(0, 10) }}s;
            filter: blur({{ rand(1, 3) }}px);
        "></div>
        @endfor
    </div>
    
    <!-- Animated Gradient Orbs -->
    <div class="gradient-orb orb-1"></div>
    <div class="gradient-orb orb-2"></div>
    <div class="gradient-orb orb-3"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title with PRO Animation -->
        <div style="text-align: center; margin-bottom: 4rem;" class="partners-title-pro">
            <div class="badge-pulse" style="display: inline-block; padding: 8px 20px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%); border-radius: 50px; margin-bottom: 1rem; position: relative; overflow: hidden;">
                <div class="badge-shine"></div>
                <span style="color: #22c55e; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; position: relative; z-index: 1;">
                    <i class="fas fa-handshake pulse-icon"></i> {{ __('messages.section.partners_title') }}
                </span>
            </div>
            <h2 class="gradient-text-animated" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; position: relative;">
                Ensemble pour la Résilience
                <div class="text-glow"></div>
            </h2>
            <p class="subtitle-fade" style="color: #6b7280; font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                {{ __('messages.section.partners_desc') }}
            </p>
        </div>
        
        <!-- Partners Grid with PRO 3D Effects -->
        <div class="partners-grid-pro" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; margin-bottom: 4rem; perspective: 1000px;">
            @foreach($partners as $index => $partner)
            <div class="partner-card-pro" data-index="{{ $index }}" style="position: relative; transform-style: preserve-3d;">
                <a href="{{ $partner->website ?: '#' }}" target="_blank" rel="noopener noreferrer" style="text-decoration: none; display: block;">
                    <div class="card-inner" style="background: #ffffff; border-radius: 28px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden; border: 2px solid transparent; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); min-height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: center; transform-style: preserve-3d;">
                        <!-- Ripple Effect on Hover -->
                        <div class="ripple-effect"></div>
                        
                        <!-- Animated Gradient Border -->
                        <div class="animated-gradient-border"></div>
                        
                        <!-- Shine Effect -->
                        <div class="shine-effect"></div>
                        
                        <!-- Background Pattern -->
                        <div class="card-pattern"></div>
                        
                        <!-- Partner Logo -->
                        <div style="position: relative; z-index: 2; margin-bottom: 1.5rem;">
                            @if($partner->logo)
                            <div style="width: 140px; height: 140px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 20px; padding: 1.5rem; transition: all 0.4s ease;">
                                <img src="{{ asset('images/' . $partner->logo) }}" 
                                     alt="{{ $partner->name }}" 
                                     title="{{ $partner->name }}"
                                     class="partner-logo-img"
                                     style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; transition: all 0.4s ease;">
                            </div>
                            @else
                            <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.4s ease;">
                                <i class="fas fa-handshake" style="font-size: 3rem; color: white;"></i>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Partner Info -->
                        <div style="position: relative; z-index: 2;">
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; transition: color 0.3s ease;">
                                {{ $partner->name }}
                            </h3>
                            <p style="font-size: 0.85rem; color: #6b7280; margin: 0; transition: color 0.3s ease;">
                                {{ $partner->organization }}
                            </p>
                            
                            <!-- Badge Type -->
                            <div style="margin-top: 1rem; display: inline-block; padding: 4px 12px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%); border-radius: 20px;">
                                <span style="font-size: 0.75rem; color: #22c55e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    @if($partner->type === 'agency') Agence
                                    @elseif($partner->type === 'institution') Institution
                                    @elseif($partner->type === 'ong') ONG
                                    @else Partenaire
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <!-- Hover Icon -->
                        <div class="hover-icon" style="position: absolute; bottom: 20px; right: 20px; width: 40px; height: 40px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transform: scale(0) rotate(-180deg); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); z-index: 3;">
                            <i class="fas fa-arrow-right" style="color: white; font-size: 1rem;"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        
        <!-- View All Partners Button with Animation -->
        <div style="text-align: center;" data-aos="fade-up" data-aos-delay="400">
            <a href="{{ route('partners.index') }}" class="btn-partners-modern" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 40px; background: linear-gradient(135deg, #22c55e 0%, #10b981 100%); color: white; font-weight: 700; font-size: 1rem; border-radius: 50px; text-decoration: none; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                <span style="position: relative; z-index: 2; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-users"></i>
                    Découvrir tous nos partenaires
                    <i class="fas fa-arrow-right" style="transition: transform 0.3s ease;"></i>
                </span>
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #10b981 0%, #059669 100%); opacity: 0; transition: opacity 0.3s ease;"></div>
            </a>
        </div>
    </div>
</section>

<style>
/* ========================================
   ULTRA PROFESSIONAL PARTNERS SECTION
   ======================================== */

/* Animated Particles */
.particles-container {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

@keyframes float-particle {
    0%, 100% {
        transform: translate(0, 0) scale(1);
        opacity: 0.3;
    }
    25% {
        transform: translate(100px, -100px) scale(1.2);
        opacity: 0.6;
    }
    50% {
        transform: translate(-50px, -150px) scale(0.8);
        opacity: 0.4;
    }
    75% {
        transform: translate(-100px, -50px) scale(1.1);
        opacity: 0.5;
    }
}

/* Animated Gradient Orbs */
.gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: pulse-orb 8s ease-in-out infinite;
}

.orb-1 {
    top: -10%;
    right: -5%;
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(16, 185, 129, 0.2));
    animation-delay: 0s;
}

.orb-2 {
    bottom: -15%;
    left: -10%;
    width: 600px;
    height: 600px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(34, 197, 94, 0.2));
    animation-delay: 2s;
}

.orb-3 {
    top: 40%;
    right: 30%;
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(59, 130, 246, 0.15));
    animation-delay: 4s;
}

@keyframes pulse-orb {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(50px, 30px) scale(1.1);
    }
}

/* Title Animations */
.badge-pulse {
    animation: pulse-badge 2s ease-in-out infinite;
}

@keyframes pulse-badge {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.badge-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    animation: shine-badge 3s ease-in-out infinite;
}

@keyframes shine-badge {
    0% {
        left: -100%;
    }
    50%, 100% {
        left: 200%;
    }
}

.pulse-icon {
    animation: pulse-icon 1.5s ease-in-out infinite;
}

@keyframes pulse-icon {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Gradient Text Animation */
.gradient-text-animated {
    background: linear-gradient(135deg, #1f2937 0%, #22c55e 50%, #3b82f6 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradient-flow 3s ease infinite;
}

@keyframes gradient-flow {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.text-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #22c55e, #3b82f6);
    filter: blur(40px);
    opacity: 0.2;
    animation: glow-pulse 2s ease-in-out infinite;
    z-index: -1;
}

@keyframes glow-pulse {
    0%, 100% {
        opacity: 0.2;
    }
    50% {
        opacity: 0.4;
    }
}

.subtitle-fade {
    animation: fade-in-up 1s ease 0.5s backwards;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* PRO Partner Card Effects */
.partner-card-pro {
    animation: card-entrance 0.6s ease backwards;
    animation-delay: calc(var(--index, 0) * 0.1s);
}

@keyframes card-entrance {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.partner-card-pro:nth-child(1) { --index: 1; }
.partner-card-pro:nth-child(2) { --index: 2; }
.partner-card-pro:nth-child(3) { --index: 3; }
.partner-card-pro:nth-child(4) { --index: 4; }

/* 3D Tilt Effect on Hover */
.partner-card-pro:hover .card-inner {
    transform: translateY(-20px) scale(1.03) rotateX(5deg);
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.25), 0 0 0 1px rgba(34, 197, 94, 0.1);
}

/* Ripple Effect */
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34, 197, 94, 0.4) 0%, transparent 70%);
    width: 0;
    height: 0;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    pointer-events: none;
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.partner-card-pro:hover .ripple-effect {
    width: 500px;
    height: 500px;
    opacity: 1;
}

/* Animated Gradient Border */
.animated-gradient-border {
    position: absolute;
    inset: -2px;
    border-radius: 28px;
    background: linear-gradient(135deg, #22c55e, #3b82f6, #8b5cf6, #22c55e);
    background-size: 300% 300%;
    opacity: 0;
    animation: rotate-gradient 4s linear infinite;
    z-index: -1;
    transition: opacity 0.5s ease;
}

.partner-card-pro:hover .animated-gradient-border {
    opacity: 1;
}

@keyframes rotate-gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Shine Effect Diagonal */
.shine-effect {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
    transform: translateX(-100%) translateY(-100%);
    transition: transform 0.8s ease;
    pointer-events: none;
}

.partner-card-pro:hover .shine-effect {
    transform: translateX(50%) translateY(50%);
}

/* Background Pattern */
.card-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(34, 197, 94, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.03) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.5s ease;
    z-index: 0;
}

.partner-card-pro:hover .card-pattern {
    opacity: 1;
}

/* Logo Animation */
.partner-card-pro:hover .partner-logo-img {
    transform: scale(1.15) rotate(360deg);
    filter: drop-shadow(0 10px 20px rgba(34, 197, 94, 0.3));
}

.partner-logo-img {
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover Icon with Bounce */
.partner-card-pro:hover .hover-icon {
    opacity: 1;
    transform: scale(1) rotate(0deg);
    animation: bounce-icon 0.5s ease;
}

@keyframes bounce-icon {
    0%, 100% {
        transform: scale(1) rotate(0deg);
    }
    50% {
        transform: scale(1.2) rotate(10deg);
    }
}

.hover-icon {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #22c55e, #10b981);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0) rotate(-180deg);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 3;
    box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
}

/* Title Color Change */
.partner-card-pro:hover h3 {
    color: #22c55e !important;
    text-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

/* Badge Animation */
.partner-card-pro:hover .badge-type-animated {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(34, 197, 94, 0.3);
}

/* Button PRO Effects */
.btn-partners-modern {
    position: relative;
    overflow: hidden;
}

.btn-partners-modern::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-partners-modern:hover::before {
    width: 300px;
    height: 300px;
}

.btn-partners-modern:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 20px 60px rgba(34, 197, 94, 0.5);
}

.btn-partners-modern:hover > span > i:last-child {
    transform: translateX(8px);
    animation: arrow-bounce 0.6s ease infinite;
}

@keyframes arrow-bounce {
    0%, 100% {
        transform: translateX(8px);
    }
    50% {
        transform: translateX(12px);
    }
}

.btn-partners-modern:hover > div {
    opacity: 1;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .partner-card-pro:hover .card-inner {
        transform: translateY(-10px) scale(1.02);
    }
    
    .gradient-orb {
        width: 300px !important;
        height: 300px !important;
    }
}

/* Loading Animation */
@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

/* Smooth Entrance for All Elements */
.partners-section-pro > * {
    animation: fade-in 0.8s ease backwards;
}
</style>
@endif

@endsection