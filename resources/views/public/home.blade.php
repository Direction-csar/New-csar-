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

.home-media-frame {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
}

.home-media-frame img {
    display: block;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    transform: translateZ(0);
}

.home-image-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.home-image-contain {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
}

.home-logo-tile {
    border-radius: 20px;
    background: rgba(255,255,255,0.98);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
}

/* Hero Section moderne avec rotation d'images */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #111827;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
    transform: scale(1);
    z-index: 0;
    will-change: opacity, transform;
}

.hero-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.45) 0%, rgba(0, 0, 0, 0.35) 50%, rgba(0, 0, 0, 0.5) 100%);
    z-index: 1;
}

/* Effet Ken Burns - Zoom progressif */
.hero-background.active {
    opacity: 1;
    animation: kenBurnsEffect 5s ease-out forwards;
}

@keyframes kenBurnsEffect {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(1.1);
    }
}

/* Animation de sortie */
.hero-background.exiting {
    opacity: 0;
    transform: scale(1.05);
    transition: opacity 0.6s ease-in-out, transform 0.6s ease-in-out;
}


.hero-content {
    text-align: center;
    z-index: 10;
    position: relative;
    max-width: 900px;
    padding: 0 2rem;
    color: white;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 2.5rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    line-height: 1.6;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
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

<!-- Préchargement prioritaire de la première image -->
<link rel="preload" as="image" href="{{ asset('images/arriere plan/N1.jpg') }}" fetchpriority="high">

<!-- Bandeau texte défilant -->
<div style="background: linear-gradient(90deg, #1a5c38 0%, #22c55e 50%, #1a5c38 100%); overflow: hidden; white-space: nowrap; padding: 10px 0; position: relative; z-index: 100; border-bottom: 2px solid rgba(255,255,255,0.15);">
    <div class="marquee-track" style="display: inline-flex; align-items: center; animation: marquee-scroll 40s linear infinite; will-change: transform;">
        @php
            $marqueeText = 'Le Commissariat à la Sécurité Alimentaire et à la Résilience œuvre pour garantir l\'accès à une alimentation suffisante et nutritive pour tous les Sénégalais, tout en renforçant leur capacité à faire face aux crises et aux défis climatiques.';
        @endphp
        @for($i = 0; $i < 4; $i++)
        <span style="display: inline-flex; align-items: center; padding: 0 3rem; font-size: 0.95rem; font-weight: 500; color: #ffffff; letter-spacing: 0.3px; font-family: 'Segoe UI', sans-serif;">
            <span style="display: inline-block; width: 6px; height: 6px; background: rgba(255,255,255,0.7); border-radius: 50%; margin-right: 1.5rem; flex-shrink: 0;"></span>
            {{ $marqueeText }}
        </span>
        @endfor
    </div>
    <style>
    @keyframes marquee-scroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .marquee-track:hover { animation-play-state: paused; }
    </style>
</div>

<!-- Hero Section -->
<section class="hero-section">
    <!-- Arrière-plans rotatifs : images chargées via JS pour éviter le chargement multiple -->
    <div class="hero-background active" data-bg="{{ asset('images/arriere plan/N1.jpg') }}" style='background-image: url("{{ asset('images/arriere plan/N1.jpg') }}");'></div>
    <div class="hero-background" data-bg="{{ asset('images/arriere plan/N2.jpg') }}"></div>
    <div class="hero-background" data-bg="{{ asset('images/arriere plan/N3.jpg') }}"></div>
    <div class="hero-background" data-bg="{{ asset('images/arriere plan/N5.jpg') }}"></div>
    <div class="hero-background" data-bg="{{ asset('images/arriere plan/N8.jpg') }}"></div>
    
    <div class="container" style="max-width: 100%; display: flex; justify-content: center; align-items: center; position: relative; z-index: 10;">
        <div class="hero-content" style="text-align: center; width: 100%; max-width: 1000px; margin: 0 auto;">
            <h1 class="hero-title" id="typewriter-title" style="min-height: 120px; text-align: center; display: block; width: 100%;"></h1>
            <div class="hero-buttons" style="display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap;">
                <a href="{{ '/demande' }}" class="btn-hero btn-primary-hero">
                    <i class="fas fa-file-alt"></i>
                    {{ __('messages.home.hero.cta_secondary') }}
                </a>
                <a href="{{ route('about') }}" class="btn-hero btn-secondary-hero">
                    <i class="fas fa-info-circle"></i>
                    {{ __('messages.home.hero.cta') }}
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var backgrounds = document.querySelectorAll('.hero-background');
    var currentBg = 0;

    // Charger la première image immédiatement
    function loadBg(el) {
        if (!el.style.backgroundImage && el.dataset.bg) {
            el.style.backgroundImage = "url('" + el.dataset.bg + "')";
        }
    }

    // Précharger les 2 premières images, le reste en différé
    loadBg(backgrounds[0]);
    if (backgrounds.length > 1) loadBg(backgrounds[1]);
    setTimeout(function() {
        for (var i = 2; i < backgrounds.length; i++) {
            loadBg(backgrounds[i]);
        }
    }, 2000);

    function rotateBackground() {
        var prev = currentBg;
        currentBg = (currentBg + 1) % backgrounds.length;

        // Nettoyer tout sauf prev et next
        for (var i = 0; i < backgrounds.length; i++) {
            if (i !== prev && i !== currentBg) {
                backgrounds[i].classList.remove('active', 'exiting');
                backgrounds[i].style.zIndex = '';
            }
        }

        // Nouvelle image par-dessus
        backgrounds[currentBg].classList.remove('exiting');
        backgrounds[currentBg].classList.add('active');
        backgrounds[currentBg].style.zIndex = '2';

        // Ancienne image sort
        backgrounds[prev].classList.remove('active');
        backgrounds[prev].classList.add('exiting');
        backgrounds[prev].style.zIndex = '1';

        // Après le fondu, nettoyer
        setTimeout(function() {
            backgrounds[prev].classList.remove('exiting');
            backgrounds[prev].style.zIndex = '';
            backgrounds[currentBg].style.zIndex = '';
        }, 900);
    }

    setInterval(rotateBackground, 5000);
});
</script>

<script>
(function() {
    'use strict';
    
    function startTypewriter() {
        var titleElement = document.getElementById('typewriter-title');
        if (!titleElement) {
            console.error('typewriter-title element not found');
            return;
        }
        
        var text = '{{ __('messages.home.hero.title') }}';
        var index = 0;
        var erasing = false;
        
        titleElement.textContent = '';
        titleElement.style.visibility = 'visible';
        titleElement.style.opacity = '1';
        
        function tick() {
            if (!erasing) {
                if (index < text.length) {
                    titleElement.textContent += text.charAt(index);
                    index++;
                    setTimeout(tick, 40);
                } else {
                    setTimeout(function() { 
                        erasing = true; 
                        tick(); 
                    }, 4000);
                }
            } else {
                if (titleElement.textContent.length > 0) {
                    titleElement.textContent = titleElement.textContent.slice(0, -1);
                    setTimeout(tick, 20);
                } else {
                    erasing = false;
                    index = 0;
                    setTimeout(tick, 300);
                }
            }
        }
        
        tick();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startTypewriter);
    } else {
        startTypewriter();
    }
})();
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
                    {{ __('messages.home.interventions.title') }}
                </span>
                </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1rem; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                {{ __('messages.home.interventions.description') }}
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
                        {{ __('messages.home.services.distribution') }}
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    {{ __('messages.home.services.distribution_desc') }}
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
                        {{ __('messages.home.services.storage') }}
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    {{ __('messages.home.services.storage_desc') }}
                </p>
            </div>
            </a>
            
            <!-- Service Card 3 -->
            <a href="{{ route('track', ['locale' => app()->getLocale()]) }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="400" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite; animation-delay: 2s;">
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #8b5cf6, #ec4899, #8b5cf6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(139, 92, 246, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);">
                        <i class="fas fa-search" style="animation: pulse-search 2s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #8b5cf6; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        {{ __('messages.home.services.tracking') }}
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    {{ __('messages.home.services.tracking_desc') }}
                </p>
            </div>
            </a>
        </div>
    </div>
</section>

<!-- ===== ACTUALITÉS SECTION - Style SONAGED ===== -->
<section style="padding: 80px 0; background: #f8f9fa; position: relative; overflow: hidden;">
    <div class="container" style="max-width: 1300px; margin: 0 auto; padding: 0 20px;">
        <!-- Titre -->
        <div style="margin-bottom: 3rem;" data-aos="fade-up">
            <h2 style="font-size: 2.8rem; font-weight: 800; color: #1f2937; text-transform: uppercase; letter-spacing: 1px; position: relative; display: inline-block;">
                {{ __('messages.home.news.title') }}
                <div style="position: absolute; bottom: -8px; left: 0; width: 80px; height: 4px; background: #22c55e; border-radius: 2px;"></div>
            </h2>
        </div>

        @if(isset($latestNews) && $latestNews->count() > 0)
        <div class="actu-sonaged-grid" data-aos="fade-up" data-aos-delay="100">
            <!-- Colonne gauche : Article principal (Featured) -->
            @php $featured = $latestNews->first(); @endphp
            <div class="actu-featured-card">
                <div class="actu-featured-img">
                    @php
                        $fImg = $featured->cover_image ?? $featured->featured_image ?? null;
                    @endphp
                    @if($fImg)
                        @php
                            $fp = trim((string) $fImg);
                            $fUrl = preg_match('/^https?:\/\//i', $fp) ? $fp : asset('storage/' . ltrim($fp, '/'));
                        @endphp
                        <img src="{{ $fUrl }}" alt="{{ $featured->title }}" class="home-image-cover" loading="eager" fetchpriority="high" decoding="async" onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                    @else
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,#22c55e,#16a34a);display:flex;align-items:center;justify-content:center;">
                            <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" class="home-image-contain" style="width:50%;max-width:200px;opacity:0.7;" loading="lazy" decoding="async">
                        </div>
                    @endif
                    <div class="actu-featured-overlay">
                        <span class="actu-date-badge"><i class="fas fa-calendar-alt"></i> {{ $featured->published_at ? $featured->published_at->format('d/m/Y') : $featured->created_at->format('d/m/Y') }}</span>
                        <h3 class="actu-featured-title">{{ $featured->title }}</h3>
                        <p class="actu-featured-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($featured->excerpt ?? $featured->content)), 160) }}</p>
                        <a href="{{ route('news.show', $featured->id) }}" class="actu-read-btn"><i class="fas fa-arrow-right"></i> {{ __('messages.home.news.link') }}</a>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Liste des articles récents -->
            <div class="actu-list-col">
                @foreach($latestNews->skip(1)->take(4) as $idx => $news)
                <a href="{{ route('news.show', $news->id) }}" class="actu-list-item" data-aos="fade-left" data-aos-delay="{{ ($idx + 1) * 100 }}">
                    <div class="actu-list-thumb">
                        @php
                            $nImg = $news->cover_image ?? $news->featured_image ?? null;
                        @endphp
                        @if($nImg)
                            @php
                                $np = trim((string) $nImg);
                                $nUrl = preg_match('/^https?:\/\//i', $np) ? $np : asset('storage/' . ltrim($np, '/'));
                            @endphp
                            <img src="{{ $nUrl }}" alt="{{ $news->title }}" class="home-image-cover" loading="lazy" decoding="async" onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                        @else
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center;">
                                <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" class="home-image-contain" style="width:60%;opacity:0.5;" loading="lazy" decoding="async">
                            </div>
                        @endif
                    </div>
                    <div class="actu-list-info">
                        <div class="actu-list-date">
                            <i class="fas fa-clock"></i>
                            {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}
                            @if($news->category)
                            <span class="actu-list-cat">{{ ucfirst($news->category) }}</span>
                            @endif
                        </div>
                        <h4 class="actu-list-title">{{ $news->title }}</h4>
                        <p class="actu-list-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($news->excerpt ?? $news->content)), 80) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Bouton voir toutes les actualités -->
        <div style="text-align: center; margin-top: 3rem;" data-aos="fade-up">
            <a href="{{ route('news') }}" style="display:inline-flex;align-items:center;gap:10px;background:#22c55e;color:white;padding:14px 36px;border-radius:50px;font-weight:700;text-decoration:none;font-size:1rem;box-shadow:0 6px 20px rgba(34,197,94,0.35);transition:all 0.3s ease;">
                <i class="fas fa-arrow-right"></i> {{ __('messages.home.news.view_all') }}
            </a>
        </div>
        @else
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-newspaper" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
            <h3 style="color: #6b7280;">{{ __('messages.home.news.none_available') }}</h3>
            <p style="color: #9ca3af;">{{ __('messages.home.news.come_back_soon') }}</p>
        </div>
        @endif
    </div>
</section>

<!-- ===== OBJECTIFS STRATÉGIQUES (jusqu'en 2028) ===== -->
<section style="padding: 80px 0; background: linear-gradient(135deg, #1f6f45 0%, #22c55e 50%, #16a34a 100%); position: relative; overflow: hidden;">
    <!-- Decorative circles -->
    <div style="position:absolute;top:-80px;right:-80px;width:300px;height:300px;border:2px solid rgba(255,255,255,0.1);border-radius:50%;"></div>
    <div style="position:absolute;bottom:-60px;left:-60px;width:200px;height:200px;border:2px solid rgba(255,255,255,0.08);border-radius:50%;"></div>
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:500px;border:1px solid rgba(255,255,255,0.05);border-radius:50%;"></div>

    <div class="container" style="max-width: 1300px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 1;">
        <!-- Header -->
        <div style="text-align:center;margin-bottom:3.5rem;">
            <div data-aos="fade-up">
                <span style="display:inline-block;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);padding:8px 20px;border-radius:50px;color:white;font-size:0.85rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-bottom:1rem;">
                    <i class="fas fa-bullseye" style="margin-right:8px;"></i> Objectifs stratégiques
                </span>
            </div>
            <h2 style="font-size: 2.5rem; font-weight: 800; color: white; margin: 0 0 1rem;" data-aos="fade-up" data-aos-delay="100">
                Nos actions concrètes pour la sécurité alimentaire de demain
            </h2>
            <div style="width:80px;height:4px;background:white;border-radius:2px;margin:0 auto;" data-aos="fade-up" data-aos-delay="150"></div>
        </div>

        <!-- 5 Axes Grid -->
        <div class="axes-grid-5" data-aos="fade-up" data-aos-delay="200">
            <!-- Axe 1 -->
            <div class="axe-card-v2" style="--axe-delay: 0s;">
                <div class="axe-num">01</div>
                <div class="axe-icon-wrap" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h4>Renforcer les capacités de stockage et distribution</h4>
                <p>Développer et moderniser l'infrastructure de stockage stratégique pour garantir une disponibilité alimentaire continue.</p>
            </div>
            <!-- Axe 2 -->
            <div class="axe-card-v2" style="--axe-delay: 0.1s;">
                <div class="axe-num">02</div>
                <div class="axe-icon-wrap" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <i class="fas fa-seedling"></i>
                </div>
                <h4>Promouvoir des innovations agricoles</h4>
                <p>Encourager l'adoption de technologies modernes et de pratiques durables pour améliorer la productivité agricole.</p>
            </div>
            <!-- Axe 3 -->
            <div class="axe-card-v2" style="--axe-delay: 0.2s;">
                <div class="axe-num">03</div>
                <div class="axe-icon-wrap" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-bell"></i>
                </div>
                <h4>Améliorer les systèmes d'alerte précoce</h4>
                <p>Développer des mécanismes de surveillance et d'alerte pour anticiper et prévenir les crises alimentaires.</p>
            </div>
            <!-- Axe 4 -->
            <div class="axe-card-v2" style="--axe-delay: 0.3s;">
                <div class="axe-num">04</div>
                <div class="axe-icon-wrap" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h4>Renforcer les capacités communautaires</h4>
                <p>Former et accompagner les communautés locales pour développer leur autonomie et leur résilience alimentaire.</p>
            </div>
            <!-- Axe 5 -->
            <div class="axe-card-v2" style="--axe-delay: 0.4s;">
                <div class="axe-num">05</div>
                <div class="axe-icon-wrap" style="background: linear-gradient(135deg, #ec4899, #db2777);">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h4>Optimiser la gouvernance et la coordination</h4>
                <p>Améliorer la coordination entre les différents acteurs pour une réponse plus efficace aux défis alimentaires.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== DOCUMENTATIONS (Dynamique) ===== -->
<section style="padding: 80px 0; background: #f1f5f9; position: relative; overflow: hidden;">
    <div class="container" style="max-width: 1300px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align:center;margin-bottom:2.5rem;" data-aos="fade-up">
            <h3 style="font-size:1.8rem;font-weight:800;color:#1f2937;text-transform:uppercase;">
                DOCUMENTATIONS
            </h3>
            <p style="color:#6b7280;font-size:1rem;margin-top:0.5rem;">Documents officiels et avis de recrutement</p>
        </div>
        <div class="doc-list" style="max-width:900px;margin:0 auto;">
            @if(isset($publicDocuments) && $publicDocuments->count() > 0)
                @foreach($publicDocuments as $idx => $doc)
                <div class="doc-item" data-aos="fade-up" data-aos-delay="{{ ($idx + 1) * 100 }}">
                    <div class="doc-item-icon">
                        <i class="{{ $doc->icon_class }}"></i>
                    </div>
                    <div class="doc-item-info">
                        <p class="doc-item-label">{{ $doc->type_label }}</p>
                        <h5 class="doc-item-title">{{ $doc->title }}</h5>
                        @if($doc->file_url)
                        <a href="{{ $doc->file_url }}" target="_blank" class="doc-download-link"><i class="fas fa-download"></i> Télécharger</a>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div style="background:white;border-radius:12px;padding:40px 20px;text-align:center;">
                    <i class="fas fa-folder-open" style="font-size:2.5rem;color:#d1d5db;margin-bottom:12px;"></i>
                    <p style="color:#9ca3af;margin:0;font-size:0.95rem;">Aucun document disponible pour le moment.</p>
                </div>
            @endif
        </div>
    </div>
</section>

<style>
/* ===== ACTUALITÉS SONAGED STYLE ===== */
.actu-sonaged-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: stretch;
}
.actu-featured-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12);
    position: relative;
    height: 100%;
    min-height: 480px;
}
.actu-featured-img {
    position: relative;
    width: 100%;
    height: 100%;
}
.actu-featured-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}
.actu-featured-card:hover .actu-featured-img img {
    transform: scale(1.05);
}
.actu-featured-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 30px;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
    color: white;
}
.actu-date-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 12px;
}
.actu-featured-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 10px;
    line-height: 1.4;
}
.actu-featured-excerpt {
    font-size: 0.9rem;
    opacity: 0.85;
    line-height: 1.6;
    margin: 0 0 15px;
}
.actu-read-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: white;
    background: #22c55e;
    padding: 10px 22px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}
.actu-read-btn:hover {
    background: #16a34a;
    transform: translateX(5px);
    color: white;
}

/* Liste articles à droite */
.actu-list-col {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.actu-list-item {
    display: flex;
    gap: 16px;
    padding: 18px;
    background: white;
    text-decoration: none;
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    position: relative;
}
.actu-list-item:first-child {
    border-radius: 12px 12px 0 0;
}
.actu-list-item:last-child {
    border-radius: 0 0 12px 12px;
    border-bottom: none;
}
.actu-list-item:hover {
    background: #f0fdf4;
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}
.actu-list-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #22c55e;
    border-radius: 0 3px 3px 0;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.actu-list-item:hover::before {
    opacity: 1;
}
.actu-list-thumb {
    width: 100px;
    min-width: 100px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}
.actu-list-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.actu-list-item:hover .actu-list-thumb img {
    transform: scale(1.08);
}
.actu-list-info {
    flex: 1;
    min-width: 0;
}
.actu-list-date {
    font-size: 0.78rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}
.actu-list-cat {
    background: #dcfce7;
    color: #166534;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 6px;
}
.actu-list-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.actu-list-excerpt {
    font-size: 0.82rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ===== OBJECTIFS STRATÉGIQUES ===== */
.axes-grid-5 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.3rem;
}
.axe-card-v2 {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 16px;
    padding: 2rem 1.3rem;
    text-align: center;
    transition: all 0.4s ease;
    animation: axeFadeIn 0.6s ease forwards;
    animation-delay: var(--axe-delay);
    opacity: 0;
    transform: translateY(20px);
    position: relative;
}
@keyframes axeFadeIn {
    to { opacity: 1; transform: translateY(0); }
}
.axe-card-v2:hover {
    background: rgba(255,255,255,0.22);
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}
.axe-num {
    position: absolute;
    top: 12px;
    right: 14px;
    font-size: 2rem;
    font-weight: 900;
    color: rgba(255,255,255,0.1);
    line-height: 1;
    transition: color 0.3s ease;
}
.axe-card-v2:hover .axe-num {
    color: rgba(255,255,255,0.25);
}
.axe-icon-wrap {
    width: 65px;
    height: 65px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.2rem;
    font-size: 1.6rem;
    color: white;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}
.axe-card-v2:hover .axe-icon-wrap {
    transform: scale(1.1) rotate(5deg);
}
.axe-card-v2 h4 {
    color: white;
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 10px;
    line-height: 1.4;
}
.axe-card-v2 p {
    color: rgba(255,255,255,0.8);
    font-size: 0.82rem;
    line-height: 1.6;
    margin: 0;
}

/* ===== DOCUMENTATION & RECRUTEMENT ===== */
.doc-recruit-grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 3rem;
    align-items: start;
}
.doc-action-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.doc-action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
}
.doc-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.doc-item {
    display: flex;
    gap: 16px;
    padding: 18px 20px;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    align-items: flex-start;
}
.doc-item:first-child {
    border-radius: 12px 12px 0 0;
}
.doc-item:last-child {
    border-radius: 0 0 12px 12px;
    border-bottom: none;
}
.doc-item:hover {
    background: #f0fdf4;
    transform: translateX(4px);
}
.doc-item-icon {
    width: 45px;
    min-width: 45px;
    height: 45px;
    background: #fef2f2;
    color: #ef4444;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.doc-item-info {
    flex: 1;
    min-width: 0;
}
.doc-item-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #22c55e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 4px;
}
.doc-item-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 8px;
    line-height: 1.4;
}
.doc-download-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #3b82f6;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}
.doc-download-link:hover {
    color: #1d4ed8;
}

/* ===== MÉTÉO WIDGET ===== */
.weather-main-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    padding: 2rem 2.5rem;
    gap: 2rem;
}
.weather-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.weather-location {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #60a5fa;
    font-size: 1rem;
    font-weight: 600;
}
.weather-temp-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.weather-temp {
    font-size: 3.5rem;
    font-weight: 800;
    color: white;
    line-height: 1;
}
.weather-right {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.weather-detail {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 12px 16px;
    min-width: 160px;
}
.weather-detail i {
    color: #60a5fa;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}
.weather-detail-label {
    display: block;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    margin-bottom: 2px;
}
.weather-detail-val {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    color: white;
}
@media (max-width: 768px) {
    .weather-main-card {
        flex-direction: column;
        padding: 1.5rem;
        text-align: center;
    }
    .weather-left {
        align-items: center;
    }
    .weather-location {
        justify-content: center;
    }
    .weather-temp-wrap {
        justify-content: center;
    }
    .weather-right {
        grid-template-columns: 1fr 1fr;
        width: 100%;
    }
}
@media (max-width: 480px) {
    .weather-right {
        grid-template-columns: 1fr;
    }
}
/* Meteo + CSAR EN ACTION grid responsive */
.meteo-action-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 992px) {
    .meteo-action-grid {
        grid-template-columns: 1fr !important;
    }
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}
/* Forecast cards */
.weather-forecast-card {
    background: rgba(255,255,255,0.07);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    padding: 1.2rem 1rem;
    text-align: center;
    transition: all 0.3s ease;
}
.weather-forecast-card:hover {
    background: rgba(255,255,255,0.12);
    transform: translateY(-4px);
}
.wf-day {
    font-size: 0.85rem;
    font-weight: 700;
    color: #60a5fa;
    margin-bottom: 4px;
}
.wf-temps {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 4px 0;
}
.wf-max {
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}
.wf-min {
    color: rgba(255,255,255,0.5);
    font-weight: 500;
    font-size: 1rem;
}
.wf-desc {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.6);
    text-transform: capitalize;
}
/* Weather alerts */
.weather-alert-card {
    background: rgba(245,158,11,0.15);
    border: 1px solid rgba(245,158,11,0.3);
    border-radius: 10px;
    padding: 12px 16px;
    color: #fbbf24;
    font-size: 0.85rem;
    line-height: 1.5;
    margin-bottom: 8px;
}
.weather-alert-card i {
    color: #f59e0b;
    margin-right: 6px;
}
@media (max-width: 576px) {
    #weather-forecast {
        grid-template-columns: 1fr !important;
    }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .axes-grid-5 {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 992px) {
    .actu-sonaged-grid {
        grid-template-columns: 1fr;
    }
    .actu-featured-card {
        min-height: 350px;
    }
    .axes-grid-5 {
        grid-template-columns: repeat(2, 1fr);
    }
    .doc-recruit-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 576px) {
    .axes-grid-5 {
        grid-template-columns: 1fr;
    }
    .actu-list-thumb {
        width: 80px;
        min-width: 80px;
        height: 65px;
    }
}
</style>

<!-- Rapports et Bulletins SIM Section -->
<section class="reports-sim-section" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background -->
    <div style="position: absolute; inset: 0; opacity: 0.1;">
        <div style="position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(34, 197, 94, 0.3), transparent); border-radius: 50%; top: -200px; left: -100px; animation: float-orb 20s ease-in-out infinite; filter: blur(80px);"></div>
        <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59, 130, 246, 0.2), transparent); border-radius: 50%; bottom: -150px; right: -50px; animation: float-orb 25s ease-in-out infinite reverse; filter: blur(100px);"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Section Header -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-up">
            <div style="display: inline-flex; align-items: center; gap: 12px; padding: 12px 28px; background: rgba(34, 197, 94, 0.15); backdrop-filter: blur(10px); border-radius: 50px; margin-bottom: 1.5rem; border: 1px solid rgba(34, 197, 94, 0.3);">
                <i class="fas fa-file-chart-line" style="font-size: 1.5rem; color: #22c55e;"></i>
                <span style="color: #22c55e; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px;">
                    Publications & Données
                </span>
            </div>
            
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1.2rem; color: white; text-shadow: 0 4px 20px rgba(34, 197, 94, 0.3); line-height: 1.2;">
                Rapports et Bulletins SIM
            </h2>
            
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                Accédez à nos rapports d'activités et bulletins du Système d'Information des Marchés pour suivre l'évolution des prix et la sécurité alimentaire
            </p>
        </div>
        
        <!-- Reports Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem; max-width: 1200px; margin: 0 auto;">
            <!-- Rapport Annuel Card -->
            <a href="{{ route('reports', ['locale' => app()->getLocale()]) }}" class="report-card" data-aos="fade-up" data-aos-delay="100" style="display:block; text-decoration:none; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(34, 197, 94, 0.2); border-radius: 24px; padding: 2.5rem; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <!-- Glow Effect -->
                <div class="report-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(34, 197, 94, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; text-align: center;">
                    <!-- Logo -->
                    <div style="width: 120px; height: 120px; border-radius: 20px; overflow: hidden; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.4); border: 2px solid rgba(34, 197, 94, 0.3); background: rgba(255,255,255,0.95);">
                        <img src="{{ asset('images/PTA/rapport.jpeg') }}" alt="Rapports Annuels" class="home-image-contain home-logo-tile" style="padding: 8px;" loading="lazy" decoding="async">
                    </div>
                    
                    <!-- Title -->
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 1rem;">
                        Rapports Annuels
                    </h3>
                    
                    <!-- Description -->
                    <p style="color: rgba(255, 255, 255, 0.7); line-height: 1.7; margin-bottom: 1.5rem;">
                        Consultez nos rapports d'activités annuels détaillant nos interventions et résultats
                    </p>
                    
                    <!-- Button -->
                    <span class="btn-report" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; background: rgba(34, 197, 94, 0.2); color: #22c55e; font-weight: 600; border-radius: 12px; border: 1px solid rgba(34, 197, 94, 0.3); transition: all 0.3s; cursor: pointer;">
                        <i class="fas fa-eye"></i>
                        Consulter
                    </span>
                </div>
            </a>
            
            <!-- Bulletin SIM Card -->
            <a href="{{ route('sim.index', ['locale' => app()->getLocale()]) }}" class="report-card" data-aos="fade-up" data-aos-delay="250" style="display:block; text-decoration:none; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(59, 130, 246, 0.2); border-radius: 24px; padding: 2.5rem; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="report-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(59, 130, 246, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; text-align: center;">
                    <div style="width: 120px; height: 120px; border-radius: 20px; overflow: hidden; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4); border: 2px solid rgba(59, 130, 246, 0.3); background: rgba(255,255,255,0.95);">
                        <img src="{{ asset('images/PTA/SIM.jpeg') }}" alt="Bulletins SIM" class="home-image-contain home-logo-tile" style="padding: 8px;" loading="lazy" decoding="async">
                    </div>
                    
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 1rem;">
                        Bulletins SIM
                    </h3>
                    
                    <p style="color: rgba(255, 255, 255, 0.7); line-height: 1.7; margin-bottom: 1.5rem;">
                        Suivez l'évolution des prix des denrées alimentaires sur les marchés du Sénégal
                    </p>
                    
                    <span class="btn-report" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; background: rgba(59, 130, 246, 0.2); color: #3b82f6; font-weight: 600; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.3); transition: all 0.3s; cursor: pointer;">
                        <i class="fas fa-eye"></i>
                        Consulter
                    </span>
                </div>
            </a>
            
            <!-- Données Statistiques Card -->
            <a href="{{ route('sim.dashboard', ['locale' => app()->getLocale()]) }}" class="report-card" data-aos="fade-up" data-aos-delay="400" style="display:block; text-decoration:none; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(139, 92, 246, 0.2); border-radius: 24px; padding: 2.5rem; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="report-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(139, 92, 246, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; text-align: center;">
                    <div style="width: 120px; height: 120px; border-radius: 20px; overflow: hidden; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4); border: 2px solid rgba(139, 92, 246, 0.3); background: rgba(255,255,255,0.95);">
                        <img src="{{ asset('images/PTA/zone itervention.jpeg') }}" alt="Données Statistiques" class="home-image-contain home-logo-tile" style="padding: 8px;" loading="lazy" decoding="async">
                    </div>
                    
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 1rem;">
                        Données Statistiques
                    </h3>
                    
                    <p style="color: rgba(255, 255, 255, 0.7); line-height: 1.7; margin-bottom: 1.5rem;">
                        Accédez aux données détaillées sur la sécurité alimentaire et les interventions
                    </p>
                    
                    <span class="btn-report" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; background: rgba(139, 92, 246, 0.2); color: #8b5cf6; font-weight: 600; border-radius: 12px; border: 1px solid rgba(139, 92, 246, 0.3); transition: all 0.3s; cursor: pointer;">
                        <i class="fas fa-eye"></i>
                        Consulter
                    </span>
                </div>
            </a>
        </div>
    </div>
</section>

<style>
/* Report Cards Hover Effects */
.report-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
}

.report-card:hover .report-glow {
    opacity: 1;
}

.btn-report:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

/* Modal Styles */
.report-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    overflow-y: auto;
}

.report-modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-radius: 24px;
    max-width: 1200px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    position: relative;
    box-shadow: 0 20px 80px rgba(0, 0, 0, 0.5);
}

.modal-header {
    padding: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 2rem;
    max-height: 60vh;
    overflow-y: auto;
}

.close-modal {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.close-modal:hover {
    background: rgba(239, 68, 68, 0.3);
    transform: rotate(90deg);
}

.slideshow-container {
    position: relative;
}

.slide {
    display: none;
    animation: slideIn 0.5s ease;
}

.slide.active {
    display: block;
}

.slide-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-btn {
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
    padding: 12px 24px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.nav-btn:hover {
    background: rgba(34, 197, 94, 0.3);
    transform: translateX(5px);
}

.nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateX(20px);
    }
    to { 
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<!-- Modal pour afficher les rapports en diaporama -->
<div id="reportModal" class="report-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 id="modalTitle" style="color: white; font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"></h2>
                <p id="modalSubtitle" style="color: rgba(255, 255, 255, 0.7); font-size: 1rem;"></p>
            </div>
            <button class="close-modal" onclick="closeReportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="slideshow-container" id="slideshowContainer">
                <!-- Les slides seront injectées ici dynamiquement -->
            </div>
            
            <div class="slide-navigation">
                <button class="nav-btn" id="prevBtn" onclick="changeSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                    Précédent
                </button>
                
                <div style="color: rgba(255, 255, 255, 0.7); font-weight: 600;">
                    <span id="currentSlide">1</span> / <span id="totalSlides">1</span>
                </div>
                
                <button class="nav-btn" id="nextBtn" onclick="changeSlide(1)">
                    Suivant
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Données des rapports et bulletins (exemple - à remplacer par vos vraies données)
const reportsData = {
    rapports: {
        title: "Rapports Annuels du CSAR",
        subtitle: "Historique des rapports d'activités",
        slides: [
            {
                year: "2024",
                title: "Rapport Annuel 2024",
                description: "Bilan des activités et interventions du CSAR pour l'année 2024",
                stats: [
                    { label: "Bénéficiaires", value: "150,000+" },
                    { label: "Tonnes distribuées", value: "25,000" },
                    { label: "Régions couvertes", value: "14" }
                ]
            },
            {
                year: "2023",
                title: "Rapport Annuel 2023",
                description: "Synthèse des actions menées en 2023 pour la sécurité alimentaire",
                stats: [
                    { label: "Bénéficiaires", value: "135,000+" },
                    { label: "Tonnes distribuées", value: "22,500" },
                    { label: "Régions couvertes", value: "14" }
                ]
            },
            {
                year: "2022",
                title: "Rapport Annuel 2022",
                description: "Résultats et impact des interventions du CSAR en 2022",
                stats: [
                    { label: "Bénéficiaires", value: "120,000+" },
                    { label: "Tonnes distribuées", value: "20,000" },
                    { label: "Régions couvertes", value: "13" }
                ]
            }
        ]
    },
    bulletins: {
        title: "Bulletins SIM",
        subtitle: "Système d'Information des Marchés",
        slides: [
            {
                year: "Janvier 2025",
                title: "Bulletin SIM - Janvier 2025",
                description: "Évolution des prix des denrées alimentaires sur les marchés du Sénégal",
                stats: [
                    { label: "Riz (kg)", value: "350 FCFA" },
                    { label: "Mil (kg)", value: "280 FCFA" },
                    { label: "Huile (L)", value: "1,200 FCFA" }
                ]
            },
            {
                year: "Décembre 2024",
                title: "Bulletin SIM - Décembre 2024",
                description: "Analyse des prix et tendances du marché alimentaire",
                stats: [
                    { label: "Riz (kg)", value: "340 FCFA" },
                    { label: "Mil (kg)", value: "275 FCFA" },
                    { label: "Huile (L)", value: "1,180 FCFA" }
                ]
            }
        ]
    },
    donnees: {
        title: "Données Statistiques",
        subtitle: "Données détaillées sur la sécurité alimentaire",
        slides: [
            {
                year: "2024",
                title: "Statistiques 2024",
                description: "Données complètes sur les interventions et la sécurité alimentaire",
                stats: [
                    { label: "Demandes traitées", value: "5,000+" },
                    { label: "Taux de satisfaction", value: "95%" },
                    { label: "Magasins actifs", value: "71" }
                ]
            },
            {
                year: "2023",
                title: "Statistiques 2023",
                description: "Recensement des données de sécurité alimentaire",
                stats: [
                    { label: "Demandes traitées", value: "4,500+" },
                    { label: "Taux de satisfaction", value: "93%" },
                    { label: "Magasins actifs", value: "68" }
                ]
            }
        ]
    }
};

let currentSlideIndex = 0;
let currentCategory = '';

function openReportModal(category) {
    currentCategory = category;
    currentSlideIndex = 0;
    
    const modal = document.getElementById('reportModal');
    const data = reportsData[category];
    
    document.getElementById('modalTitle').textContent = data.title;
    document.getElementById('modalSubtitle').textContent = data.subtitle;
    
    renderSlides(data.slides);
    showSlide(0);
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function renderSlides(slides) {
    const container = document.getElementById('slideshowContainer');
    container.innerHTML = slides.map((slide, index) => `
        <div class="slide ${index === 0 ? 'active' : ''}" data-slide="${index}">
            <div style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05)); border: 2px solid rgba(34, 197, 94, 0.2); border-radius: 20px; padding: 3rem;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span style="display: inline-block; padding: 8px 20px; background: rgba(34, 197, 94, 0.2); color: #22c55e; border-radius: 50px; font-weight: 600; margin-bottom: 1rem;">
                        ${slide.year}
                    </span>
                    <h3 style="font-size: 2rem; font-weight: 700; color: white; margin-bottom: 1rem;">
                        ${slide.title}
                    </h3>
                    <p style="color: rgba(255, 255, 255, 0.7); font-size: 1.1rem; line-height: 1.6;">
                        ${slide.description}
                    </p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 2rem;">
                    ${slide.stats.map(stat => `
                        <div style="text-align: center; padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.1);">
                            <div style="font-size: 2.5rem; font-weight: 900; color: #22c55e; margin-bottom: 0.5rem;">
                                ${stat.value}
                            </div>
                            <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem;">
                                ${stat.label}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('totalSlides').textContent = slides.length;
}

function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const data = reportsData[currentCategory];
    
    if (index >= data.slides.length) currentSlideIndex = 0;
    if (index < 0) currentSlideIndex = data.slides.length - 1;
    
    slides.forEach(slide => slide.classList.remove('active'));
    slides[currentSlideIndex].classList.add('active');
    
    document.getElementById('currentSlide').textContent = currentSlideIndex + 1;
    
    // Désactiver les boutons si nécessaire
    document.getElementById('prevBtn').disabled = currentSlideIndex === 0;
    document.getElementById('nextBtn').disabled = currentSlideIndex === data.slides.length - 1;
}

function changeSlide(direction) {
    currentSlideIndex += direction;
    showSlide(currentSlideIndex);
}

// Fermer la modal en cliquant en dehors
document.addEventListener('click', function(e) {
    const modal = document.getElementById('reportModal');
    if (e.target === modal) {
        closeReportModal();
    }
});

// Navigation avec les flèches du clavier
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('reportModal');
    if (modal.classList.contains('active')) {
        if (e.key === 'ArrowLeft') changeSlide(-1);
        if (e.key === 'ArrowRight') changeSlide(1);
        if (e.key === 'Escape') closeReportModal();
    }
});
</script>

<!-- ===== MÉTÉO EN TEMPS RÉEL (One Call 3.0) ===== -->
<section style="padding: 100px 0; background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%); position: relative; overflow: hidden;">
    <div style="position: absolute; inset: 0; opacity: 0.1;">
        <div style="position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(96, 165, 250, 0.4), transparent); border-radius: 50%; top: -200px; right: -100px; animation: float-orb 15s ease-in-out infinite; filter: blur(80px);"></div>
        <div style="position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(147, 197, 253, 0.3), transparent); border-radius: 50%; bottom: -150px; left: -50px; animation: float-orb 20s ease-in-out infinite reverse; filter: blur(100px);"></div>
    </div>

    <div class="container" style="position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 3rem;" data-aos="fade-up">
            <div style="display: inline-flex; align-items: center; gap: 12px; padding: 12px 28px; background: rgba(96, 165, 250, 0.15); backdrop-filter: blur(10px); border-radius: 50px; margin-bottom: 1.5rem; border: 1px solid rgba(96, 165, 250, 0.3);">
                <i class="fas fa-cloud-sun" style="font-size: 1.5rem; color: #60a5fa;"></i>
                <span style="color: #60a5fa; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px;">
                    Données Météorologiques
                </span>
            </div>
            
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                Conditions météorologiques actuelles et prévisions pour votre région
            </p>
        </div>

        <div class="meteo-action-grid">
            <div data-aos="fade-right">
                <div class="weather-main-card" id="weather-widget">
                    <div class="weather-left">
                        <div class="weather-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span id="weather-city">Chargement...</span>
                        </div>
                        <div class="weather-temp-wrap">
                            <div class="weather-temp" id="weather-temp">--°</div>
                            <div>
                                <div style="font-size: 1.1rem; color: rgba(255,255,255,0.9); font-weight: 600; text-transform: capitalize;" id="weather-desc">--</div>
                                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5);">Ressenti: <span id="weather-feels">--°</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="weather-right">
                        <div class="weather-detail">
                            <i class="fas fa-tint"></i>
                            <div>
                                <span class="weather-detail-label">Humidité</span>
                                <span class="weather-detail-val" id="weather-humidity">--%</span>
                            </div>
                        </div>
                        <div class="weather-detail">
                            <i class="fas fa-wind"></i>
                            <div>
                                <span class="weather-detail-label">Vent</span>
                                <span class="weather-detail-val" id="weather-wind">-- km/h</span>
                            </div>
                        </div>
                        <div class="weather-detail">
                            <i class="fas fa-compress-arrows-alt"></i>
                            <div>
                                <span class="weather-detail-label">Pression</span>
                                <span class="weather-detail-val" id="weather-pressure">-- hPa</span>
                            </div>
                        </div>
                        <div class="weather-detail">
                            <i class="fas fa-eye"></i>
                            <div>
                                <span class="weather-detail-label">Visibilité</span>
                                <span class="weather-detail-val" id="weather-visibility">-- km</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="weather-forecast" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                </div>

                <div id="weather-alerts" style="margin-top: 1.5rem;"></div>
            </div>

            <div data-aos="fade-left">
                <div style="background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; padding: 2rem; height: 100%;">
                    <h3 style="color: white; font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-hands-helping" style="color: #22c55e;"></i>
                        CSAR en Action
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 1rem; border-left: 3px solid #22c55e;">
                            <div style="color: #22c55e; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.3rem;">ASSISTANCE ALIMENTAIRE</div>
                            <div style="color: white; font-size: 0.95rem; line-height: 1.5;">Distribution de vivres aux populations vulnérables</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 1rem; border-left: 3px solid #3b82f6;">
                            <div style="color: #60a5fa; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.3rem;">SUIVI DES MARCHÉS</div>
                            <div style="color: white; font-size: 0.95rem; line-height: 1.5;">Surveillance des prix des denrées alimentaires</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 1rem; border-left: 3px solid #f59e0b;">
                            <div style="color: #fbbf24; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.3rem;">GESTION DES STOCKS</div>
                            <div style="color: white; font-size: 0.95rem; line-height: 1.5;">Optimisation des réserves stratégiques</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CARTE DES MARCHÉS SUIVIS SIM CSAR ===== -->
<section style="padding: 100px 0; background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 40%, #0f766e 100%); position: relative; overflow: hidden;">
    <!-- Animated floating particles -->
    <div style="position:absolute;inset:0;pointer-events:none;">
        <div style="position:absolute;top:10%;left:5%;width:300px;height:300px;background:radial-gradient(circle,rgba(34,211,238,0.12),transparent);border-radius:50%;filter:blur(60px);animation:float-orb 12s ease-in-out infinite;"></div>
        <div style="position:absolute;bottom:10%;right:5%;width:400px;height:400px;background:radial-gradient(circle,rgba(16,185,129,0.1),transparent);border-radius:50%;filter:blur(80px);animation:float-orb 16s ease-in-out infinite reverse;"></div>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:500px;background:radial-gradient(circle,rgba(59,130,246,0.08),transparent);border-radius:50%;filter:blur(100px);animation:pulse-orb-stats 10s ease-in-out infinite;"></div>
        <!-- Grid pattern overlay -->
        <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:0.04;" xmlns="http://www.w3.org/2000/svg">
            <defs><pattern id="mapGrid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs>
            <rect width="100%" height="100%" fill="url(#mapGrid)"/>
        </svg>
    </div>

    <div class="container" style="max-width:1300px;margin:0 auto;padding:0 20px;position:relative;z-index:2;">
        <!-- Header -->
        <div style="text-align:center;margin-bottom:3rem;" data-aos="fade-up">
            <div style="display:inline-flex;align-items:center;gap:10px;background:rgba(34,211,238,0.15);border:1px solid rgba(34,211,238,0.3);border-radius:50px;padding:8px 20px;margin-bottom:1.5rem;">
                <div style="width:8px;height:8px;background:#22d3ee;border-radius:50%;animation:pulse-dot 2s infinite;"></div>
                <span style="color:#22d3ee;font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;">Données en temps réel</span>
            </div>
            <h2 style="font-size:2.8rem;font-weight:800;color:white;margin-bottom:0.8rem;line-height:1.2;">
                <span style="background:linear-gradient(135deg,#22d3ee,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">CARTE DES MARCHÉS</span><br>
                <span style="font-size:1.8rem;font-weight:600;color:rgba(255,255,255,0.85);">Suivis SIM CSAR</span>
            </h2>
            <p style="color:rgba(255,255,255,0.6);font-size:1.1rem;max-width:650px;margin:0 auto;">
                Visualisez l'ensemble des marchés suivis par le Système d'Information sur les Marchés sur tout le territoire sénégalais
            </p>
        </div>

        <!-- Carte Leaflet directe -->
        <div class="row g-4 align-items-stretch">
            <!-- Colonne carte -->
            <div class="col-lg-8" data-aos="fade-right">
                <div style="position:relative;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.1);height:100%;min-height:420px;">
                    <div id="home-sim-map" style="width:100%;height:100%;min-height:420px;z-index:1;"></div>
                </div>
            </div>
            <!-- Colonne infos -->
            <div class="col-lg-4" data-aos="fade-left">
                <div style="display:flex;flex-direction:column;gap:1.2rem;height:100%;justify-content:center;">
                    <!-- Stat 1 -->
                    <div style="display:flex;align-items:center;gap:16px;background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.12);border-radius:18px;padding:20px 24px;transition:all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.12)';this.style.transform='translateX(5px)'" onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.transform='none'">
                        <div style="width:52px;height:52px;background:linear-gradient(135deg,#22d3ee,#06b6d4);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-map-marker-alt" style="color:white;font-size:1.3rem;"></i>
                        </div>
                        <div>
                            <div style="color:white;font-size:1.5rem;font-weight:800;">14 Régions</div>
                            <div style="color:rgba(255,255,255,0.5);font-size:0.8rem;">Couverture nationale</div>
                        </div>
                    </div>
                    <!-- Stat 2 -->
                    <div style="display:flex;align-items:center;gap:16px;background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.12);border-radius:18px;padding:20px 24px;transition:all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.12)';this.style.transform='translateX(5px)'" onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.transform='none'">
                        <div style="width:52px;height:52px;background:linear-gradient(135deg,#10b981,#059669);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-store" style="color:white;font-size:1.3rem;"></i>
                        </div>
                        <div>
                            <div style="color:white;font-size:1.5rem;font-weight:800;">68+ Marchés</div>
                            <div style="color:rgba(255,255,255,0.5);font-size:0.8rem;">Suivis en permanence</div>
                        </div>
                    </div>
                    <!-- Stat 3 -->
                    <div style="display:flex;align-items:center;gap:16px;background:rgba(255,255,255,0.07);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.12);border-radius:18px;padding:20px 24px;transition:all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.12)';this.style.transform='translateX(5px)'" onmouseout="this.style.background='rgba(255,255,255,0.07)';this.style.transform='none'">
                        <div style="width:52px;height:52px;background:linear-gradient(135deg,#3b82f6,#2563eb);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-chart-bar" style="color:white;font-size:1.3rem;"></i>
                        </div>
                        <div>
                            <div style="color:white;font-size:1.5rem;font-weight:800;">50+ Produits</div>
                            <div style="color:rgba(255,255,255,0.5);font-size:0.8rem;">Prix relevés chaque semaine</div>
                        </div>
                    </div>
                    <!-- CTA -->
                    <a href="{{ route('sim.carte-marches', ['locale' => app()->getLocale()]) }}" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:18px 28px;background:linear-gradient(135deg,#22d3ee,#10b981);color:white;font-weight:700;font-size:1.05rem;border-radius:18px;text-decoration:none;box-shadow:0 8px 30px rgba(34,211,238,0.3);transition:all 0.3s ease;margin-top:0.5rem;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 40px rgba(34,211,238,0.5)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 8px 30px rgba(34,211,238,0.3)'">
                        <i class="fas fa-expand-arrows-alt"></i> Voir la carte complète
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Leaflet carte SIM : chargement différé (lazy) -->
<script>
(function() {
    var mapLoaded = false;

    function initSimMap() {
        if (mapLoaded) return;
        mapLoaded = true;

        // Charger le CSS Leaflet
        var css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(css);

        // Charger le JS Leaflet puis initialiser
        var js = document.createElement('script');
        js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        js.onload = function() {
            var mapEl = document.getElementById('home-sim-map');
            if (!mapEl) return;

            var map = L.map('home-sim-map', {
                scrollWheelZoom: false,
                dragging: true,
                zoomControl: true
            }).setView([14.5, -14.45], 7);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var regions = [
                {name: 'Dakar', lat: 14.7167, lng: -17.4677},
                {name: 'Thiès', lat: 14.7886, lng: -16.9260},
                {name: 'Saint-Louis', lat: 16.0179, lng: -16.4896},
                {name: 'Diourbel', lat: 14.6553, lng: -16.2314},
                {name: 'Kaolack', lat: 14.1652, lng: -16.0758},
                {name: 'Fatick', lat: 14.3390, lng: -16.4041},
                {name: 'Ziguinchor', lat: 12.5681, lng: -16.2719},
                {name: 'Kolda', lat: 12.8835, lng: -14.9504},
                {name: 'Tambacounda', lat: 13.7709, lng: -13.6673},
                {name: 'Kédougou', lat: 12.5605, lng: -12.1747},
                {name: 'Matam', lat: 15.6559, lng: -13.2554},
                {name: 'Louga', lat: 15.6173, lng: -16.2240},
                {name: 'Kaffrine', lat: 14.1059, lng: -15.5506},
                {name: 'Sédhiou', lat: 12.7081, lng: -15.5569}
            ];
            var colors = ['#e74c3c','#3498db','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e','#16a085','#2980b9','#8e44ad','#27ae60','#c0392b','#d35400'];

            regions.forEach(function(r, i) {
                L.circleMarker([r.lat, r.lng], {
                    radius: 8, fillColor: colors[i % colors.length],
                    color: '#fff', weight: 2, opacity: 1, fillOpacity: 0.9
                }).bindPopup('<strong>' + r.name + '</strong><br>Région du Sénégal').addTo(map);
            });

            setTimeout(function() { map.invalidateSize(); }, 300);
        };
        document.head.appendChild(js);
    }

    // Observer : charger Leaflet seulement quand la section carte est visible
    var sentinel = document.getElementById('home-sim-map');
    if (sentinel && 'IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) {
                initSimMap();
                obs.disconnect();
            }
        }, { rootMargin: '200px' });
        obs.observe(sentinel);
    } else {
        // Fallback pour navigateurs anciens
        window.addEventListener('scroll', function handler() {
            initSimMap();
            window.removeEventListener('scroll', handler);
        }, { passive: true });
    }
})();
</script>

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
                    Nos Missions
                </span>
            </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1.2rem; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                Galerie de missions
            </h2>
            <p style="color: #6b7280; font-size: 1.2rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                Découvrez nos actions sur le terrain à travers ces moments capturés
            </p>
        </div>
        
        @if(isset($galleryImages) && $galleryImages->count() > 0)
        <!-- SLIDER PROFESSIONNEL AVEC DIAPORAMA AUTOMATIQUE -->
        <div class="gallery-slider-container" style="position: relative; max-width: 1200px; margin: 0 auto; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 80px rgba(0, 0, 0, 0.15);">
            <!-- Slider Wrapper -->
            <div class="gallery-slider" style="position: relative; height: 600px; overflow: hidden;">
                @foreach($galleryImages as $index => $image)
                <!-- Slide {{ $index + 1 }} -->
                <div class="slider-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" style="position: absolute; inset: 0; opacity: {{ $index === 0 ? '1' : '0' }}; transform: {{ $index === 0 ? 'scale(1)' : 'scale(1.1)' }}; transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);"
                
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
                             style="width: 100%; height: 100%; object-fit: cover; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1); filter: brightness(0.98) contrast(1.04);"
                             loading="lazy" decoding="async"
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
        @else
        <div style="text-align: center; padding: 4rem 0;">
            <i class="fas fa-images" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <p style="color: #6b7280; font-size: 1.1rem;">Aucune image disponible pour le moment</p>
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

/* Service Card Hover Effects - Enhanced Visibility */
.service-card-ultra:hover {
    transform: translateY(-20px) scale(1.08) !important;
    box-shadow: 0 40px 100px rgba(34, 197, 94, 0.5) !important;
    animation-play-state: paused !important;
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%) !important;
}

.service-card-ultra:hover .service-border {
    opacity: 1 !important;
    box-shadow: 0 0 30px rgba(34, 197, 94, 0.6);
}

.service-card-ultra:hover .service-glow {
    opacity: 1 !important;
    width: 300px;
    height: 300px;
}

.service-card-ultra:hover .service-icon-ultra {
    transform: scale(1.2) rotate(360deg);
    box-shadow: 0 20px 60px rgba(34, 197, 94, 0.7) !important;
}

.service-card-ultra:hover .service-icon-ultra div {
    opacity: 1 !important;
}

.service-card-ultra:hover h3 {
    color: #22c55e !important;
    transform: scale(1.08);
    text-shadow: 0 2px 10px rgba(34, 197, 94, 0.3);
}

.service-card-ultra:hover p {
    color: #1f2937 !important;
    font-weight: 500;
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
    if (lightbox.style.display === 'block') {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
    }
});
@endif

// ========================================
// ANIMATED COUNTERS (EFFECT CHRONO)
// ========================================

function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        
        const current = Math.floor(easeOutQuart * (end - start) + start);
        element.textContent = current.toLocaleString('fr-FR');
        
        // Add pulse class during counting
        if (progress < 1) {
            element.classList.add('counting');
            window.requestAnimationFrame(step);
        } else {
            element.classList.remove('counting');
            element.textContent = end.toLocaleString('fr-FR');
        }
    };
    window.requestAnimationFrame(step);
}

// Intersection Observer for triggering counter animation when visible
const observerOptions = {
    threshold: 0.3,
    rootMargin: '0px'
};

let hasAnimated = false;

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !hasAnimated) {
            hasAnimated = true;
            
            // Query counters at animation time (DOM is ready at this point)
            const counters = document.querySelectorAll('.counter');
            counters.forEach((counter, index) => {
                const target = parseInt(counter.getAttribute('data-target'));
                
                setTimeout(() => {
                    animateCounter(counter, 0, target, 2000); // 2 seconds animation
                }, index * 200); // 200ms delay between each counter
            });
        }
    });
}, observerOptions);

// Observe the stats section AFTER DOM is fully loaded
window.addEventListener('load', function() {
    const statsSection = document.querySelector('.stats-section-ultra');
    if (statsSection) {
        counterObserver.observe(statsSection);
    } else {
        // Fallback: animate immediately if section not found via observer
        const counters = document.querySelectorAll('.counter');
        counters.forEach((counter, index) => {
            const target = parseInt(counter.getAttribute('data-target'));
            setTimeout(() => { animateCounter(counter, 0, target, 2000); }, index * 200);
        });
    }
});

// ========================================
// ROTATION AUTOMATIQUE DES STATS (toutes les 5 secondes)
// ========================================
let currentStatIndex = 0;
const statCards = document.querySelectorAll('.stat-card-ultra');

function highlightStat(index) {
    // Retirer la mise en évidence de toutes les cartes
    statCards.forEach(card => {
        card.style.transform = 'scale(1)';
        card.style.boxShadow = '0 10px 40px rgba(0, 0, 0, 0.08)';
        card.querySelector('.stat-glow').style.opacity = '0';
        card.querySelector('.stat-border').style.opacity = '0';
    });
    
    // Mettre en évidence la carte actuelle
    const currentCard = statCards[index];
    currentCard.style.transform = 'scale(1.08)';
    currentCard.style.boxShadow = '0 20px 60px rgba(0, 0, 0, 0.2)';
    currentCard.querySelector('.stat-glow').style.opacity = '1';
    currentCard.querySelector('.stat-border').style.opacity = '1';
    
    // Réanimer le compteur avec effet chrono (de 0 à la valeur cible)
    const counter = currentCard.querySelector('.counter');
    const target = parseInt(counter.getAttribute('data-target'));
    animateCounter(counter, 0, target, 1500); // Animation de 1.5 secondes
    
    console.log(`📊 Statistique ${index + 1}/6 - Effet chrono activé`);
}

// Démarrer la rotation automatique après 3 secondes
setTimeout(() => {
    setInterval(() => {
        currentStatIndex = (currentStatIndex + 1) % statCards.length;
        highlightStat(currentStatIndex);
    }, 5000); // Toutes les 5 secondes
    
    console.log('🔄 Rotation automatique des statistiques activée');
}, 3000);
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
                    Chiffres Clés Dynamiques
                </span>
            </div>
            <h2 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.8rem; color: white; text-shadow: 0 0 20px rgba(34, 197, 94, 0.2);">
                L'Impact du CSAR en Chiffres
            </h2>
            <p style="color: rgba(255, 255, 255, 0.75); font-size: 1rem; max-width: 650px; margin: 0 auto; line-height: 1.6;">
                Des résultats concrets au service de la sécurité alimentaire
            </p>
        </div>
        
        <!-- Stats Grid -->
        <div id="statsGrid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; max-width: 1200px; margin: 0 auto;">
            <!-- Stat 1: Agents -->
            <div class="stat-card-ultra" data-stat="agents" data-aos="zoom-in" data-aos-delay="100" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(34, 197, 94, 0.2); border-radius: 20px; padding: 2rem 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <!-- Glow Effect -->
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(34, 197, 94, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <!-- Icon -->
                <div style="position: relative; z-index: 2; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(34, 197, 94, 0.4); position: relative;">
                        <i class="fas fa-users" style="font-size: 1.8rem; color: white; animation: float-icon 3s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(34, 197, 94, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <!-- Counter -->
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper">
                        <span class="counter" data-target="{{ $stats['agents'] }}" style="font-size: 3.5rem; font-weight: 900; color: #ffffff; display: block; line-height: 1; text-shadow: 0 0 30px rgba(34, 197, 94, 0.6), 0 0 60px rgba(34, 197, 94, 0.3);">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px;">
                        Agents recensés
                    </p>
                </div>
                
                <!-- Animated Border -->
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #22c55e, #10b981, #22c55e); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 2: Magasins -->
            <div class="stat-card-ultra" data-stat="warehouses" data-aos="zoom-in" data-aos-delay="250" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(59, 130, 246, 0.2); border-radius: 20px; padding: 2rem 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(59, 130, 246, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4); position: relative;">
                        <i class="fas fa-warehouse" style="font-size: 1.8rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 0.5s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(59, 130, 246, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper">
                        <span class="counter" data-target="{{ $stats['warehouses'] }}" style="font-size: 3.5rem; font-weight: 900; color: #ffffff; display: block; line-height: 1; text-shadow: 0 0 30px rgba(59, 130, 246, 0.6), 0 0 60px rgba(59, 130, 246, 0.3);">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px;">
                        Magasins de stockage
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #3b82f6, #2563eb, #3b82f6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 3: Demandes traitées -->
            <div class="stat-card-ultra" data-stat="demandes" data-aos="zoom-in" data-aos-delay="400" style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(234, 88, 12, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(249, 115, 22, 0.2); border-radius: 20px; padding: 2rem 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(249, 115, 22, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(249, 115, 22, 0.4); position: relative;">
                        <i class="fas fa-check-circle" style="font-size: 1.8rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 1s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(249, 115, 22, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper">
                        <span class="counter" data-target="{{ $stats['demandes_traitees'] ?? '0' }}" style="font-size: 3.5rem; font-weight: 900; color: #ffffff; display: block; line-height: 1; text-shadow: 0 0 30px rgba(249, 115, 22, 0.6), 0 0 60px rgba(249, 115, 22, 0.3);">0</span>
                        <span style="font-size: 3.5rem; font-weight: 900; color: #ffffff; text-shadow: 0 0 30px rgba(249, 115, 22, 0.6), 0 0 60px rgba(249, 115, 22, 0.3);">+</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px;">
                        Demandes traitées
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #f97316, #ea580c, #f97316); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 4: Capacité -->
            <div class="stat-card-ultra" data-stat="capacity" data-aos="zoom-in" data-aos-delay="550" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 2rem 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(139, 92, 246, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(139, 92, 246, 0.4); position: relative;">
                        <i class="fas fa-boxes" style="font-size: 1.8rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 1.5s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(139, 92, 246, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper">
                        <span class="counter" data-target="{{ $stats['capacity'] }}" style="font-size: 3.5rem; font-weight: 900; color: #ffffff; display: block; line-height: 1; text-shadow: 0 0 30px rgba(139, 92, 246, 0.6), 0 0 60px rgba(139, 92, 246, 0.3);">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px;">
                        000 tonnes de capacité
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #8b5cf6, #7c3aed, #8b5cf6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 5: Régions -->
            <div class="stat-card-ultra" data-stat="regions" data-aos="zoom-in" data-aos-delay="700" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(219, 39, 119, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(236, 72, 153, 0.2); border-radius: 20px; padding: 2rem 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(236, 72, 153, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(236, 72, 153, 0.4); position: relative;">
                        <i class="fas fa-map-marked-alt" style="font-size: 1.8rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 2s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(236, 72, 153, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper">
                        <span class="counter" data-target="14" style="font-size: 3.5rem; font-weight: 900; color: #ffffff; display: block; line-height: 1; text-shadow: 0 0 30px rgba(236, 72, 153, 0.6), 0 0 60px rgba(236, 72, 153, 0.3);">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px;">
                        Régions couvertes
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #ec4899, #db2777, #ec4899); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 6: Taux de satisfaction -->
            <div class="stat-card-ultra" data-stat="satisfaction" data-aos="zoom-in" data-aos-delay="850" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(16, 185, 129, 0.2); border-radius: 20px; padding: 2rem 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(16, 185, 129, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4); position: relative;">
                        <i class="fas fa-smile" style="font-size: 1.8rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 2.5s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(16, 185, 129, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper">
                        <span class="counter" data-target="{{ $stats['taux_satisfaction'] ?? '95' }}" style="font-size: 3.5rem; font-weight: 900; color: #ffffff; display: block; line-height: 1; text-shadow: 0 0 30px rgba(16, 185, 129, 0.6), 0 0 60px rgba(16, 185, 129, 0.3);">0</span>
                        <span style="font-size: 3.5rem; font-weight: 900; color: #ffffff; text-shadow: 0 0 30px rgba(16, 185, 129, 0.6), 0 0 60px rgba(16, 185, 129, 0.3);">%</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px;">
                        Taux de satisfaction
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #10b981, #059669, #10b981); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
        </div>
    </div>
</section>

<!-- LinkedIn Section - Suivez-nous sur LinkedIn -->
<section class="linkedin-section-pro" style="background: linear-gradient(135deg, #0077b5 0%, #00a0dc 50%, #0077b5 100%); background-size: 200% 200%; animation: gradient-shift 15s ease infinite; padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background with Enhanced Effects -->
    <div style="position: absolute; inset: 0; opacity: 0.15;">
        <div style="position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(255,255,255,0.4), transparent); border-radius: 50%; top: -250px; right: -150px; animation: float-orb 20s ease-in-out infinite; filter: blur(60px);"></div>
        <div style="position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(0,160,220,0.5), transparent); border-radius: 50%; bottom: -200px; left: -100px; animation: float-orb 25s ease-in-out infinite reverse; filter: blur(80px);"></div>
        <div style="position: absolute; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.3), transparent); border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%); animation: pulse-orb-stats 10s ease-in-out infinite; filter: blur(100px);"></div>
    </div>
    
    <!-- Particle Effects -->
    <div class="linkedin-particles" style="position: absolute; inset: 0; overflow: hidden; pointer-events: none;">
        @for($i = 0; $i < 15; $i++)
        <div style="position: absolute; width: {{ rand(3, 8) }}px; height: {{ rand(3, 8) }}px; background: rgba(255,255,255,{{ rand(30, 70) / 100 }}); border-radius: 50%; top: {{ rand(0, 100) }}%; left: {{ rand(0, 100) }}%; animation: float-particle {{ rand(15, 30) }}s ease-in-out infinite; animation-delay: {{ rand(0, 10) }}s;"></div>
        @endfor
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Section Header -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-up">
            <div style="display: inline-flex; align-items: center; gap: 12px; padding: 12px 28px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 50px; margin-bottom: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.2);">
                <i class="fab fa-linkedin" style="font-size: 1.5rem; color: white;"></i>
                <span style="color: white; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px;">
                    Suivez-nous sur LinkedIn
                </span>
            </div>
            
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1.2rem; color: white; text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); line-height: 1.2;">
                Restez Connecté à Notre Impact
            </h2>
            
            <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                Découvrez nos dernières actualités, projets et initiatives pour la sécurité alimentaire et la résilience au Sénégal
            </p>
        </div>
        
        <!-- LinkedIn Feed Widget -->
        <div style="max-width: 1200px; margin: 0 auto;" data-aos="fade-up" data-aos-delay="200">
            <div style="background: white; border-radius: 24px; padding: 3rem; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); position: relative; overflow: hidden;">
                <!-- Decorative Corner -->
                <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: linear-gradient(135deg, rgba(10, 102, 194, 0.1), transparent); border-radius: 0 24px 0 100%;"></div>
                
                <!-- LinkedIn Widget -->
                <div style="position: relative; z-index: 1;">
                    <script src="https://elfsightcdn.com/platform.js" async></script>
                    <div class="elfsight-app-b8e60e2e-9795-4930-974e-fc3bb6e9c79b" data-elfsight-app-lazy></div>
                </div>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div style="text-align: center; margin-top: 3rem;" data-aos="fade-up" data-aos-delay="400">
            <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener noreferrer" class="btn-linkedin-follow" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 45px; background: white; color: #0077b5; font-weight: 700; font-size: 1.05rem; border-radius: 50px; text-decoration: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; border: 2px solid transparent;">
                <span class="btn-text" style="position: relative; z-index: 2; display: flex; align-items: center; gap: 12px; transition: color 0.3s;">
                    <i class="fab fa-linkedin" style="font-size: 1.3rem;"></i>
                    Suivre le CSAR sur LinkedIn
                    <i class="fas fa-arrow-right" style="transition: transform 0.3s;"></i>
                </span>
                <div class="btn-bg-hover" style="position: absolute; inset: 0; background: linear-gradient(135deg, #0077b5, #005582); opacity: 0; transition: opacity 0.4s; z-index: 1;"></div>
            </a>
            
            <!-- Hashtags -->
            <div style="margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
                <span style="padding: 8px 16px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 20px; color: white; font-size: 0.9rem; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.2);">#CSAR</span>
                <span style="padding: 8px 16px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 20px; color: white; font-size: 0.9rem; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.2);">#SécuritéAlimentaire</span>
                <span style="padding: 8px 16px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 20px; color: white; font-size: 0.9rem; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.2);">#Résilience</span>
                <span style="padding: 8px 16px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 20px; color: white; font-size: 0.9rem; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.2);">#SIM</span>
                <span style="padding: 8px 16px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 20px; color: white; font-size: 0.9rem; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.2);">#PartenariatInternational</span>
            </div>
        </div>
    </div>
</section>

<!-- Nos Partenaires Section -->
<style>
.logo-hero-home {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.logo-hero-home::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(5,150,105,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
    opacity: 0.6;
}

.logo-hero-home h2 {
    font-weight: 800;
    text-align: center;
    margin: 0 0 50px 0;
    font-size: 2.8rem;
    color: #059669;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 2;
}

.logo-hero-home h2::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #059669, #3b82f6, #f59e0b);
    border-radius: 2px;
}

.marquee-home {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 25px;
    padding: 40px 0;
    box-shadow: 0 20px 40px rgba(5, 150, 105, 0.1);
    border: 2px solid rgba(5, 150, 105, 0.1);
}

.marquee-home::before,
.marquee-home::after {
    content: "";
    position: absolute;
    top: 0;
    width: 100px;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}

.marquee-home::before {
    left: 0;
    background: linear-gradient(to right, #ffffff, rgba(255, 255, 255, 0));
}

.marquee-home::after {
    right: 0;
    background: linear-gradient(to left, #ffffff, rgba(255, 255, 255, 0));
}

.marquee-home .track {
    display: flex;
    gap: 100px;
    align-items: center;
    animation: logos-scroll-home 60s linear infinite;
    will-change: transform;
}

.marquee-home:hover .track {
    animation-play-state: paused;
}

.marquee-home img {
    height: 120px;
    width: auto;
    filter: grayscale(0%) contrast(1.4) brightness(1.1) saturate(1.2);
    opacity: 1;
    transition: all 0.4s ease;
    border-radius: 12px;
    padding: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border: 2px solid rgba(5, 150, 105, 0.1);
    object-fit: contain;
    max-width: 200px;
}

.marquee-home .track a { flex: 0 0 auto; }

.marquee-home a:hover img {
    filter: none;
    opacity: 1;
    transform: scale(1.15) translateY(-5px);
    box-shadow: 0 15px 35px rgba(5, 150, 105, 0.2);
    border-color: #059669;
}

@keyframes logos-scroll-home {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

@media (max-width: 768px) {
    .logo-hero-home { padding: 60px 0; }
    .marquee-home .track { gap: 50px; animation: logos-scroll-home 100s linear infinite !important; }
    .marquee-home img { height: 56px; padding: 12px; max-width: 140px; }
    .marquee-home a:hover img { transform: none; box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
}
</style>

<div class="logo-hero-home">
  <div class="container">
    <h2>Nos Partenaires</h2>
    <div class="marquee-home">
      <div class="track">
        @for($dup=0;$dup<2;$dup++)
          <a href="https://fsrp.araa.org/fr" target="_blank" rel="noopener nofollow" title="FSRP">
            <img src="{{ asset('images/partners/fsrp.png') }}" alt="FSRP">
          </a>
          <a href="https://www.jica.go.jp/french/" target="_blank" rel="noopener nofollow" title="JICA">
            <img src="{{ asset('images/partners/jica.jpg') }}" alt="JICA">
          </a>
          <a href="https://www.araa.org/fr" target="_blank" rel="noopener nofollow" title="ARAA">
            <img src="{{ asset('images/logos/logo arra.png') }}" alt="ARAA">
          </a>
          <a href="https://fr.wfp.org/" target="_blank" rel="noopener nofollow" title="PAM">
            <img src="{{ asset('images/logos/logo pam.jpeg') }}" alt="PAM">
          </a>
          <a href="https://recrute.ansd.sn/" target="_blank" rel="noopener nofollow" title="ANSD">
            <img src="{{ asset('images/partners/ansd.png') }}" alt="ANSD">
          </a>
          <a href="https://www.fongip.sn/" target="_blank" rel="noopener nofollow" title="FONGIP">
            <img src="{{ asset('images/partners/fongip.jpeg') }}" alt="FONGIP">
          </a>
          <a href="https://www.saudia.com/" target="_blank" rel="noopener nofollow" title="Saudia">
            <img src="{{ asset('images/logos/logo arabie saudia.png') }}" alt="Saudia">
          </a>
        @endfor
      </div>
    </div>
    <div style="text-align: center; margin-top: 3rem;">
      <a href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; background: linear-gradient(135deg, #059669, #10b981); color: white; font-weight: 600; font-size: 1rem; border-radius: 50px; text-decoration: none; box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 28px rgba(5, 150, 105, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(5, 150, 105, 0.3)';">
        Voir tous nos partenaires
        <i class="fas fa-arrow-right" style="transition: transform 0.3s;"></i>
      </a>
    </div>
  </div>
</div>

<style>
/* LinkedIn Section Styles */
.btn-linkedin-follow {
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-linkedin-follow:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 20px 60px rgba(255, 255, 255, 0.8);
    border-color: rgba(255, 255, 255, 0.5);
}

.btn-linkedin-follow:hover .btn-text > i:last-child {
    transform: translateX(8px);
}

/* Gradient Animation for LinkedIn Background */
@keyframes gradient-shift {
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

@keyframes float-orb {
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

/* LinkedIn Particles Animation */
@keyframes float-particle {
    0%, 100% {
        transform: translate(0, 0);
        opacity: 0.3;
    }
    25% {
        transform: translate(20px, -30px);
        opacity: 0.6;
    }
    50% {
        transform: translate(-15px, -60px);
        opacity: 0.8;
    }
    75% {
        transform: translate(30px, -90px);
        opacity: 0.4;
    }
}
</style>

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
                    <i class="fas fa-handshake pulse-icon"></i> Nos Partenaires
                </span>
            </div>
            <h2 class="gradient-text-animated" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; position: relative;">
                Ensemble pour la Résilience
                <div class="text-glow"></div>
            </h2>
            <p class="subtitle-fade" style="color: #6b7280; font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                Avec nos partenaires stratégiques, nous œuvrons pour la sécurité alimentaire et le développement durable au Sénégal
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
                                     style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; transition: all 0.4s ease;"
                                     loading="lazy" decoding="async">
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

<!-- Script Météo — proxy Laravel (clé sécurisée, cache 10 min, refresh auto) -->
<script>
(function() {
    var DAKAR_LAT = 14.6928;
    var DAKAR_LON = -17.4467;
    var refreshInterval = null;

    function setEl(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function showLoading() {
        var w = document.getElementById('weather-widget');
        if (w) w.style.opacity = '0.6';
        setEl('weather-city', 'Chargement...');
        setEl('weather-temp', '--°');
        setEl('weather-desc', '');
        setEl('weather-feels', '--°');
        setEl('weather-humidity', '--%');
        setEl('weather-wind', '-- km/h');
        setEl('weather-pressure', '-- hPa');
        setEl('weather-visibility', '-- km');
    }

    function showError(msg) {
        var w = document.getElementById('weather-widget');
        if (w) w.style.opacity = '1';
        setEl('weather-city', 'Dakar, SN');
        setEl('weather-temp', '--°');
        setEl('weather-desc', msg || 'Service indisponible');
        setEl('weather-feels', '--°');
        setEl('weather-humidity', '--%');
        setEl('weather-wind', '-- km/h');
        setEl('weather-pressure', '-- hPa');
        setEl('weather-visibility', '-- km');
        var fc = document.getElementById('weather-forecast');
        if (fc) fc.innerHTML = '';
    }

    function renderWeather(d) {
        var w = document.getElementById('weather-widget');
        if (w) w.style.opacity = '1';

        setEl('weather-city',       d.city + ', ' + d.country);
        setEl('weather-temp',       d.temp + '°C');
        setEl('weather-desc',       d.description);
        setEl('weather-feels',      d.feels_like + '°C');
        setEl('weather-humidity',   d.humidity + '%');
        setEl('weather-wind',       d.wind + ' km/h');
        setEl('weather-pressure',   d.pressure + ' hPa');
        setEl('weather-visibility', d.visibility !== null ? d.visibility + ' km' : '-- km');

        // Icône
        var iconEl = document.getElementById('weather-icon');
        if (!iconEl) {
            iconEl = document.createElement('img');
            iconEl.id = 'weather-icon';
            iconEl.style.cssText = 'width:60px;height:60px;';
            var wrap = document.getElementById('weather-temp');
            if (wrap && wrap.parentNode) wrap.parentNode.insertBefore(iconEl, wrap);
        }
        if (d.icon) {
            iconEl.src = 'https://openweathermap.org/img/wn/' + d.icon + '@2x.png';
            iconEl.alt = d.description;
        }

        // Heure de mise à jour
        var updEl = document.getElementById('weather-updated');
        if (!updEl) {
            updEl = document.createElement('div');
            updEl.id = 'weather-updated';
            updEl.style.cssText = 'color:rgba(255,255,255,0.4);font-size:0.72rem;margin-top:8px;';
            var widget = document.getElementById('weather-widget');
            if (widget) widget.appendChild(updEl);
        }
        updEl.textContent = 'Mis à jour à ' + d.updated_at;

        var fc = document.getElementById('weather-forecast');
        if (fc) fc.innerHTML = '<div style="color:rgba(255,255,255,0.4);font-size:0.8rem;text-align:center;padding:8px;">Chargement des prévisions...</div>';
        var al = document.getElementById('weather-alerts');
        if (al) al.innerHTML = '';
    }

    function renderForecast(forecast) {
        var fc = document.getElementById('weather-forecast');
        if (!fc || !forecast || !forecast.length) return;

        fc.innerHTML = forecast.map(function(day) {
            return '<div class="weather-forecast-card">' +
                '<div style="font-size:0.8rem;font-weight:700;color:rgba(255,255,255,0.9);text-transform:capitalize;">' + day.day_label + '</div>' +
                '<img src="https://openweathermap.org/img/wn/' + day.icon + '@2x.png" alt="' + day.description + '" style="width:48px;height:48px;margin:4px auto;display:block;">' +
                '<div style="font-size:0.75rem;color:rgba(255,255,255,0.6);text-transform:capitalize;margin-bottom:6px;">' + day.description + '</div>' +
                '<div style="font-size:1.1rem;font-weight:800;color:white;">' + day.temp_max + '°<span style="font-size:0.85rem;font-weight:400;color:rgba(255,255,255,0.5);margin-left:4px;">' + day.temp_min + '°</span></div>' +
                (day.rain_prob > 0 ? '<div style="font-size:0.72rem;color:#93c5fd;margin-top:4px;">💧 ' + day.rain_prob + '%</div>' : '') +
                '<div style="font-size:0.72rem;color:rgba(255,255,255,0.4);margin-top:4px;">💨 ' + day.wind + ' km/h</div>' +
                '</div>';
        }).join('');
    }

    function fetchForecast(lat, lon) {
        var url = '/api/weather/forecast?lat=' + lat + '&lon=' + lon;
        fetch(url)
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (data && data.forecast) {
                    renderForecast(data.forecast);
                } else {
                    var fc = document.getElementById('weather-forecast');
                    if (fc) fc.innerHTML = '<div style="color:rgba(255,255,255,0.4);font-size:0.8rem;text-align:center;padding:8px;">Prévisions indisponibles</div>';
                }
            })
            .catch(function() {
                var fc = document.getElementById('weather-forecast');
                if (fc) fc.innerHTML = '';
            });
    }

    function fetchWeather(lat, lon) {
        var url = '/api/weather?lat=' + lat + '&lon=' + lon;
        fetch(url)
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                if (data && data.error) {
                    console.warn('Météo:', data.error);
                    showError('Service indisponible');
                } else {
                    renderWeather(data);
                    fetchForecast(lat, lon);
                }
            })
            .catch(function(err) {
                console.warn('Erreur météo:', err);
                showError('Service indisponible');
            });
    }

    function init() {
        showLoading();
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    fetchWeather(pos.coords.latitude, pos.coords.longitude);
                },
                function() {
                    // Géolocalisation refusée ou échouée → Dakar par défaut
                    fetchWeather(DAKAR_LAT, DAKAR_LON);
                },
                { timeout: 5000, maximumAge: 300000 }
            );
        } else {
            fetchWeather(DAKAR_LAT, DAKAR_LON);
        }
    }

    // Lancer au chargement
    init();

    // Rafraîchissement automatique toutes les 10 minutes
    refreshInterval = setInterval(function() {
        fetchWeather(DAKAR_LAT, DAKAR_LON);
    }, 600000);
})();
</script>
@endsection