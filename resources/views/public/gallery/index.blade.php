@extends('layouts.public')

@section('title', __('pages.gallery'))

@section('content')
<!-- Hero Section -->
<section class="hero fade-in" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(34, 197, 94, 0.9) 100%), url('{{ asset('img/1.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 80px 0; position: relative; overflow: hidden;">
    
    <!-- Grid pattern animé -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 60px 60px; animation: gridMove 20s linear infinite; opacity: 0.2;"></div>
    
    <!-- Particules flottantes améliorées -->
    <div style="position: absolute; top: 15%; left: 10%; width: 10px; height: 10px; background: rgba(34,197,94,0.8); border-radius: 50%; animation: float 6s ease-in-out infinite; box-shadow: 0 0 25px rgba(34,197,94,0.8), 0 0 50px rgba(34,197,94,0.4);"></div>
    <div style="position: absolute; top: 25%; right: 15%; width: 8px; height: 8px; background: rgba(59,130,246,0.7); border-radius: 50%; animation: float 8s ease-in-out infinite reverse; box-shadow: 0 0 20px rgba(59,130,246,0.7), 0 0 40px rgba(59,130,246,0.3);"></div>
    <div style="position: absolute; bottom: 20%; left: 20%; width: 12px; height: 12px; background: rgba(245,158,11,0.7); border-radius: 50%; animation: float 7s ease-in-out infinite; box-shadow: 0 0 30px rgba(245,158,11,0.7), 0 0 60px rgba(245,158,11,0.3);"></div>
    <div style="position: absolute; top: 40%; left: 5%; width: 6px; height: 6px; background: rgba(236,72,153,0.6); border-radius: 50%; animation: float 9s ease-in-out infinite; box-shadow: 0 0 15px rgba(236,72,153,0.6);"></div>
    <div style="position: absolute; top: 60%; right: 8%; width: 7px; height: 7px; background: rgba(168,85,247,0.6); border-radius: 50%; animation: float 7.5s ease-in-out infinite reverse; box-shadow: 0 0 18px rgba(168,85,247,0.6);"></div>
    <div style="position: absolute; bottom: 35%; right: 25%; width: 5px; height: 5px; background: rgba(14,165,233,0.5); border-radius: 50%; animation: float 10s ease-in-out infinite; box-shadow: 0 0 12px rgba(14,165,233,0.5);"></div>
    <div style="position: absolute; top: 50%; left: 35%; width: 4px; height: 4px; background: rgba(34,197,94,0.4); border-radius: 50%; animation: float 11s ease-in-out infinite reverse; box-shadow: 0 0 10px rgba(34,197,94,0.4);"></div>
    <div style="position: absolute; bottom: 45%; right: 40%; width: 6px; height: 6px; background: rgba(251,191,36,0.5); border-radius: 50%; animation: float 8.5s ease-in-out infinite; box-shadow: 0 0 15px rgba(251,191,36,0.5);"></div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
        <!-- Badge moderne animé -->
        <div style="display: inline-block; background: rgba(255,255,255,0.1); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 12px 25px; margin-bottom: 30px; opacity: 0; animation: zoomIn 0.8s ease forwards; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
            <span style="color: #22c55e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 8px;">
                <span style="display: inline-block; animation: rotate3d 3s linear infinite; transform-style: preserve-3d;">📸</span>
                <span style="background: linear-gradient(135deg, #22c55e, #10b981, #22c55e); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: shimmer 3s linear infinite;">Galerie CSAR</span>
            </span>
        </div>
        
        <h1 class="main-title animated-title" style="font-size: 4rem; font-weight: 900; color: #fff; margin-bottom: 25px; letter-spacing: -2px; line-height: 1.1; text-shadow: 0 6px 12px rgba(0,0,0,0.4); opacity: 0; animation: slideInDown 1s ease forwards 0.3s;">
            <span style="display: inline-block; background: linear-gradient(135deg, #fff 0%, #22c55e 50%, #fff 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: shimmer 3s linear infinite;">Nos Missions en Images</span>
        </h1>
        <p class="main-subtitle" style="font-size: 1.4rem; color: rgba(255,255,255,0.9); max-width: 700px; margin: 0 auto; line-height: 1.7; text-shadow: 0 3px 6px rgba(0,0,0,0.3); opacity: 0; animation: slideInUp 1s ease forwards 0.6s;">
            Découvrez nos actions humanitaires et interventions de résilience à travers le Sénégal
        </p>
    </div>
</section>

<!-- Diaporama Section -->
<section class="slideshow-section" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f1f5f9 100%); padding: 80px 0; position: relative; overflow: hidden;">
    <!-- Décoration arrière-plan -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(circle at 20% 80%, rgba(34,197,94,0.03) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(59,130,246,0.03) 0%, transparent 50%);"></div>
    
    <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 2;">
        <!-- En-tête -->
        <div class="section-header" style="text-align: center; margin-bottom: 60px; opacity: 0; animation: fadeInUp 1s ease forwards 0.2s;">
            <div style="display: inline-block; background: rgba(34,197,94,0.1); backdrop-filter: blur(10px); color: #22c55e; padding: 10px 24px; border-radius: 25px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 25px; border: 2px solid rgba(34,197,94,0.2); box-shadow: 0 4px 15px rgba(34,197,94,0.15); animation: pulse 3s ease infinite;">
                <span style="display: inline-flex; align-items: center; gap: 8px;">
                    <span style="animation: bounce 2s ease infinite;">📸</span>
                    Galerie Photos
                </span>
            </div>
            <h2 style="font-size: 3.5rem; font-weight: 900; color: #1f2937; margin-bottom: 20px; background: linear-gradient(135deg, #1f2937 0%, #22c55e 50%, #1f2937 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: shimmer 4s linear infinite; letter-spacing: -1px; text-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                Nos Actions sur le Terrain
            </h2>
            <div style="width: 80px; height: 4px; background: linear-gradient(90deg, transparent, #22c55e, transparent); margin: 0 auto; border-radius: 2px; animation: pulse 2s ease infinite;"></div>
        </div>

        @if(count($images) > 0)
        <!-- Diaporama principal -->
        <div class="slideshow-container" style="position: relative; max-width: 1200px; margin: 0 auto; border-radius: 25px; overflow: hidden; box-shadow: 0 25px 80px rgba(0,0,0,0.15); background: #fff;">
            
            <!-- Images du diaporama -->
            <div class="slideshow-images" style="position: relative; height: 600px; overflow: hidden;">
                @foreach($images as $index => $image)
                <div class="slide {{ $index === 0 ? 'active' : '' }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: {{ $index === 0 ? '1' : '0' }}; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('storage/'.$image->file_path) }}" alt="{{ $image->title }}" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(34,197,94,0.3) 100%);"></div>
                    <div class="slide-content" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); color: #fff; padding: 50px; z-index: 2;">
                        <h3 class="slide-title" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 0 4px 8px rgba(0,0,0,0.5); transform: translateY(20px); opacity: 0; animation: slideUp 0.8s ease forwards 0.2s;">{{ $image->title ?: 'Mission CSAR' }}</h3>
                        <p class="slide-description" style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 20px; opacity: 0; transform: translateY(20px); animation: slideUp 0.8s ease forwards 0.4s;">{{ $image->description ?: 'Action humanitaire et intervention de résilience' }}</p>
                        <div class="slide-badges" style="display: flex; align-items: center; gap: 15px; transform: translateY(20px); opacity: 0; animation: slideUp 0.8s ease forwards 0.6s;">
                            @if($image->category)
                            <span style="background: rgba(34,197,94,0.3); backdrop-filter: blur(10px); color: #22c55e; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; border: 1px solid rgba(34,197,94,0.3); animation: pulse 2s ease infinite;">{{ $image->category }}</span>
                            @endif
                            <span style="font-size: 0.9rem; opacity: 0.8; display: flex; align-items: center; gap: 5px;">
                                <span style="display: inline-block; animation: bounce 2s ease infinite;">📍</span>
                                {{ $image->category ?: 'Sénégal' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Contrôles de navigation -->
            <button class="prev-btn" onclick="changeSlide(-1)" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.3s ease; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left" style="font-size: 20px; color: #1f2937; margin-right: 2px;"></i>
            </button>
            
            <button class="next-btn" onclick="changeSlide(1)" style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.3s ease; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-right" style="font-size: 20px; color: #1f2937; margin-left: 2px;"></i>
            </button>

            <!-- Barre de progression animée -->
            <div class="progress-bar" style="position: absolute; bottom: 0; left: 0; height: 4px; background: linear-gradient(90deg, #22c55e, #10b981, #22c55e); background-size: 200% auto; width: 0%; z-index: 10; animation: progressBar 5s linear infinite, shimmer 2s linear infinite; box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);"></div>
            
            <!-- Indicateurs -->
            <div class="slide-indicators" style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 12px; z-index: 10;">
                @foreach($images as $index => $image)
                <button class="indicator {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})" style="width: 12px; height: 12px; border-radius: 50%; border: none; background: {{ $index === 0 ? '#22c55e' : 'rgba(255,255,255,0.5)' }}; cursor: pointer; transition: all 0.3s ease; {{ $index === 0 ? 'box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);' : '' }}"></button>
                @endforeach
            </div>
        </div>

        <!-- Contrôles de lecture ultra-modernes -->
        <div style="text-align: center; margin-top: 50px; opacity: 0; animation: fadeInUp 1s ease forwards 0.8s;">
            <button id="playPauseBtn" onclick="toggleAutoplay()" class="play-pause-btn" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; border: none; padding: 18px 40px; border-radius: 50px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4), 0 0 20px rgba(34, 197, 94, 0.2); display: inline-flex; align-items: center; gap: 12px; position: relative; overflow: hidden;">
                <i class="fas fa-pause" id="playPauseIcon" style="font-size: 1.2rem; transition: transform 0.3s ease;"></i>
                <span id="playPauseText" style="letter-spacing: 0.5px;">Pause</span>
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: slideShine 3s ease infinite;"></div>
            </button>
        </div>
        @else
        <!-- Message si aucune image -->
        <div style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 2px dashed #e5e7eb;">
            <div style="font-size: 4rem; margin-bottom: 20px;">📸</div>
            <h3 style="font-size: 1.5rem; color: #6b7280; margin-bottom: 10px;">Aucune image disponible</h3>
            <p style="color: #9ca3af;">Les images de nos missions seront bientôt disponibles.</p>
        </div>
        @endif
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.95); backdrop-filter: blur(10px);">
    <div class="lightbox-content" onclick="event.stopPropagation()" style="position: relative; margin: auto; padding: 30px; width: 90%; max-width: 900px; top: 50%; transform: translateY(-50%);">
        <span class="lightbox-close" onclick="closeLightbox()" style="position: absolute; top: 15px; right: 25px; color: white; font-size: 40px; font-weight: bold; cursor: pointer; z-index: 1001; transition: all 0.3s ease;">&times;</span>
        <img id="lightbox-image" src="" alt="" style="width: 100%; max-height: 70vh; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <div class="lightbox-info" style="background: rgba(255, 255, 255, 0.98); padding: 30px; border-radius: 12px; margin-top: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h3 id="lightbox-title" style="color: #1f2937; margin-bottom: 15px; font-size: 1.5rem; font-weight: 700;"></h3>
            <p id="lightbox-description" style="color: #6b7280; line-height: 1.6; font-size: 1.1rem;"></p>
        </div>
    </div>
</div>

<style>
/* RESPONSIVE GLOBAL - TOUTES LES PAGES */
@media (max-width: 1024px) {
    .container { max-width: 100%; padding-left: 1.5rem; padding-right: 1.5rem; }
    div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; gap: 1.5rem !important; }
    .hero, .hero-section, .partners-hero { min-height: 50vh !important; padding: 50px 0 !important; }
    .main-title, h1 { font-size: 2.2rem !important; }
    .section-title, h2 { font-size: 1.8rem !important; }
}

@media (max-width: 768px) {
    .container { padding-left: 1rem; padding-right: 1rem; }
    section, .section { padding: 50px 0 !important; }
    .main-title, h1 { font-size: 1.8rem !important; }
    .section-title, h2 { font-size: 1.5rem !important; }
    .main-subtitle, .section-subtitle { font-size: 1rem !important; }
    div[style*="grid-template-columns"],
    div[style*="display: grid"] { grid-template-columns: 1fr !important; gap: 1rem !important; }
    div[style*="display: flex"],
    .flex-row { flex-direction: column !important; gap: 1rem !important; }
    .card, .news-card, .partner-card { margin-bottom: 1rem; padding: 1.5rem !important; }
    .btn, button[type="submit"] { width: 100%; justify-content: center; }
    .search-box, input[type="text"], input[type="search"] { width: 100% !important; }
    .filter-btn { font-size: 0.85rem; padding: 10px 15px; }
}

@media (max-width: 480px) {
    .main-title, h1 { font-size: 1.5rem !important; }
    .section-title, h2 { font-size: 1.3rem !important; }
    h3 { font-size: 1.1rem !important; }
    .card { padding: 1rem !important; }
}
</style>
@endsection

@push('styles')
<style>
/* ============================================
   ANIMATIONS ULTRA-MODERNES ET ATTRACTIVES
   ============================================ */

/* Animations de base */
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    25% { transform: translateY(-15px) rotate(2deg); }
    50% { transform: translateY(-20px) rotate(0deg); }
    75% { transform: translateY(-15px) rotate(-2deg); }
}

@keyframes gridMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(60px, 60px); }
}

/* Animations de texte */
@keyframes slideInDown {
    from { 
        opacity: 0; 
        transform: translateY(-50px) scale(0.9);
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1);
    }
}

@keyframes slideInUp {
    from { 
        opacity: 0; 
        transform: translateY(50px);
    }
    to { 
        opacity: 1; 
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from { 
        opacity: 0; 
        transform: translateY(20px);
    }
    to { 
        opacity: 1; 
        transform: translateY(0);
    }
}

/* Effet de brillance sur le titre */
@keyframes shimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
}

/* Animation de pulsation */
@keyframes pulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 0 20px 5px rgba(34, 197, 94, 0);
    }
}

/* Animation de rebond */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* Animation de rotation 3D */
@keyframes rotate3d {
    0% { transform: perspective(1000px) rotateY(0deg); }
    100% { transform: perspective(1000px) rotateY(360deg); }
}

/* Animation de zoom */
@keyframes zoomIn {
    from { 
        opacity: 0; 
        transform: scale(0.5);
    }
    to { 
        opacity: 1; 
        transform: scale(1);
    }
}

/* Animation de slide fancy */
@keyframes slideIn {
    from { 
        opacity: 0; 
        transform: translateX(-50px) rotateY(-15deg);
    }
    to { 
        opacity: 1; 
        transform: translateX(0) rotateY(0);
    }
}

/* Animation de fondu élégant */
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

/* ============================================
   STYLES DU DIAPORAMA AVEC EFFETS 3D
   ============================================ */

.slideshow-container {
    position: relative;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
    perspective: 1000px;
}

.slideshow-container:hover {
    transform: translateY(-5px) scale(1.01);
    box-shadow: 0 35px 120px rgba(0,0,0,0.25), 0 0 60px rgba(34,197,94,0.15);
}

/* Animation de l'image au hover */
.slide img {
    transition: transform 10s ease-out;
}

.slide.active:hover img {
    transform: scale(1.1);
}

/* Styles des slides */
.slide {
    transition: all 1s cubic-bezier(0.645, 0.045, 0.355, 1);
    will-change: opacity, transform;
}

.slide.active {
    opacity: 1 !important;
    transform: translateX(0) scale(1) !important;
    z-index: 2;
}

.slide:not(.active) {
    opacity: 0 !important;
    transform: translateX(50px) scale(0.95) !important;
    z-index: 1;
}

/* Animation du contenu de la slide */
.slide.active .slide-title {
    animation: slideUp 0.8s ease forwards 0.2s;
}

.slide.active .slide-description {
    animation: slideUp 0.8s ease forwards 0.4s;
}

.slide.active .slide-badges {
    animation: slideUp 0.8s ease forwards 0.6s;
}

/* ============================================
   BOUTONS DE NAVIGATION ULTRA-ATTRACTIFS
   ============================================ */

.prev-btn, .next-btn {
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
    overflow: hidden;
}

.prev-btn::before, .next-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    transition: width 0.4s ease, height 0.4s ease;
    transform: translate(-50%, -50%);
    z-index: -1;
}

.prev-btn:hover, .next-btn:hover {
    background: linear-gradient(135deg, #22c55e, #16a34a) !important;
    color: #fff !important;
    transform: translateY(-50%) scale(1.15) rotate(5deg);
    box-shadow: 0 15px 40px rgba(34, 197, 94, 0.5), 
                0 0 30px rgba(34, 197, 94, 0.3);
}

.prev-btn:hover::before, .next-btn:hover::before {
    width: 100%;
    height: 100%;
}

.prev-btn:hover i, .next-btn:hover i {
    color: #fff !important;
    animation: bounce 0.5s ease infinite;
}

.prev-btn:active, .next-btn:active {
    transform: translateY(-50%) scale(0.95);
}

/* ============================================
   INDICATEURS AVEC ANIMATIONS
   ============================================ */

.indicator {
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
    cursor: pointer;
}

.indicator::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(34, 197, 94, 0.3);
    transform: translate(-50%, -50%);
    transition: all 0.3s ease;
}

.indicator:hover {
    transform: scale(1.4);
}

.indicator:hover::before {
    width: 200%;
    height: 200%;
}

.indicator.active {
    background: linear-gradient(135deg, #22c55e, #16a34a) !important;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.6),
                0 0 20px rgba(34, 197, 94, 0.4);
    transform: scale(1.3);
    animation: pulse 2s ease infinite;
}

/* ============================================
   ANIMATIONS D'APPARITION
   ============================================ */

.fade-in {
    animation: fadeInUp 1s ease forwards;
}

/* Effet de scan line */
@keyframes scanLine {
    0% { top: -100%; }
    100% { top: 100%; }
}

/* Animation de brillance */
@keyframes slideShine {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Animation de la barre de progression */
@keyframes progressBar {
    0% { width: 0%; }
    100% { width: 100%; }
}

/* ============================================
   BOUTON PLAY/PAUSE ULTRA-ATTRACTIF
   ============================================ */

.play-pause-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(34, 197, 94, 0.5), 
                0 0 30px rgba(34, 197, 94, 0.3) !important;
}

.play-pause-btn:hover #playPauseIcon {
    transform: scale(1.2) rotate(10deg);
}

.play-pause-btn:active {
    transform: translateY(-1px) scale(1.02);
}

/* Responsive */
@media (max-width: 768px) {
    .slideshow-images {
        height: 400px !important;
    }
    
    .slide h3 {
        font-size: 1.8rem !important;
    }
    
    .slide p {
        font-size: 1rem !important;
    }
    
    .slide > div:last-child {
        padding: 30px 20px !important;
    }
    
    .prev-btn, .next-btn {
        width: 50px !important;
        height: 50px !important;
    }
    
    .prev-btn {
        left: 15px !important;
    }
    
    .next-btn {
        right: 15px !important;
    }
    
    .main-title {
        font-size: 2.5rem !important;
    }
    
    .main-subtitle {
        font-size: 1.1rem !important;
    }
}

@media (max-width: 480px) {
    .main-title {
        font-size: 2rem !important;
    }
    
    .main-subtitle {
        font-size: 1rem !important;
    }
    
    .slideshow-images {
        height: 300px !important;
    }
    
    .slide h3 {
        font-size: 1.5rem !important;
    }
    
    .slide p {
        font-size: 0.9rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentSlide = 0;
let isAutoplay = true;
let autoplayInterval;
const totalSlides = {{ count($images) }};

// Démarre le diaporama automatique
function startAutoplay() {
    if (totalSlides <= 1) return;
    
    autoplayInterval = setInterval(() => {
        if (isAutoplay) {
            changeSlide(1);
        }
    }, 5000); // Change toutes les 5 secondes
}

// Arrête le diaporama automatique
function stopAutoplay() {
    clearInterval(autoplayInterval);
}

// Change de slide
function changeSlide(direction) {
    if (totalSlides <= 1) return;
    
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    // Retirer la classe active de la slide actuelle
    slides[currentSlide].classList.remove('active');
    slides[currentSlide].style.opacity = '0';
    indicators[currentSlide].classList.remove('active');
    indicators[currentSlide].style.background = 'rgba(255,255,255,0.5)';
    indicators[currentSlide].style.boxShadow = 'none';
    
    // Calculer la nouvelle slide
    currentSlide += direction;
    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    // Activer la nouvelle slide
    setTimeout(() => {
        slides[currentSlide].classList.add('active');
        slides[currentSlide].style.opacity = '1';
        indicators[currentSlide].classList.add('active');
        indicators[currentSlide].style.background = '#22c55e';
        indicators[currentSlide].style.boxShadow = '0 2px 8px rgba(34, 197, 94, 0.4)';
    }, 100);
}

// Va directement à une slide spécifique
function goToSlide(slideIndex) {
    if (totalSlides <= 1) return;
    
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    // Retirer la classe active de toutes les slides
    slides.forEach(slide => {
        slide.classList.remove('active');
        slide.style.opacity = '0';
    });
    
    indicators.forEach(indicator => {
        indicator.classList.remove('active');
        indicator.style.background = 'rgba(255,255,255,0.5)';
        indicator.style.boxShadow = 'none';
    });
    
    // Activer la slide sélectionnée
    currentSlide = slideIndex;
    setTimeout(() => {
        slides[currentSlide].classList.add('active');
        slides[currentSlide].style.opacity = '1';
        indicators[currentSlide].classList.add('active');
        indicators[currentSlide].style.background = '#22c55e';
        indicators[currentSlide].style.boxShadow = '0 2px 8px rgba(34, 197, 94, 0.4)';
    }, 100);
}

// Toggle autoplay
function toggleAutoplay() {
    if (totalSlides <= 1) return;
    
    const playPauseIcon = document.getElementById('playPauseIcon');
    const playPauseText = document.getElementById('playPauseText');
    
    isAutoplay = !isAutoplay;
    
    if (isAutoplay) {
        playPauseIcon.className = 'fas fa-pause';
        playPauseText.textContent = 'Pause';
        startAutoplay();
    } else {
        playPauseIcon.className = 'fas fa-play';
        playPauseText.textContent = 'Lecture';
        stopAutoplay();
    }
}

// Lightbox functions
function openLightbox(imageSrc, title, description) {
    document.getElementById('lightbox-image').src = imageSrc;
    document.getElementById('lightbox-title').textContent = title;
    document.getElementById('lightbox-description').textContent = description;
    document.getElementById('lightbox').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Démarrer l'autoplay si il y a plus d'une image
    if (totalSlides > 1) {
        startAutoplay();
        
        // Pause au hover
        const slideshowContainer = document.querySelector('.slideshow-container');
        if (slideshowContainer) {
            slideshowContainer.addEventListener('mouseenter', () => {
                if (isAutoplay) stopAutoplay();
            });
            
            slideshowContainer.addEventListener('mouseleave', () => {
                if (isAutoplay) startAutoplay();
            });
        }
        
        // Navigation au clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                changeSlide(-1);
            } else if (e.key === 'ArrowRight') {
                changeSlide(1);
            } else if (e.key === ' ') {
                e.preventDefault();
                toggleAutoplay();
            }
        });
    }
    
    // Animation d'apparition des éléments
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    // Observer les sections
    document.querySelectorAll('.hero, .slideshow-section').forEach(el => {
        observer.observe(el);
    });
    
    console.log('🎬 Diaporama CSAR initialisé avec succès !');
});

// Fermer lightbox avec Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});
</script>
@endpush 