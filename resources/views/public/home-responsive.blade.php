@extends('layouts.public')

@section('title', 'Accueil - CSAR')

@section('content')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si le slider existe et contient des diapositives
    const slider = document.querySelector('.background-slider');
    if (!slider) return;
    
    const slides = document.querySelectorAll('.background-slide');
    // Appliquer les backgrounds depuis data-bg (évite les erreurs de lint sur style inline)
    slides.forEach(function(s){
        var bg = s.getAttribute('data-bg');
        if (bg) {
            s.style.backgroundImage = "url('" + bg + "')";
        }
    });
    if (slides.length <= 1) return; // Pas besoin d'animer s'il n'y a qu'une seule image
    
    let currentSlide = 0;
    const slideCount = slides.length;
    const slideInterval = 5000; // 5 secondes entre chaque transition
    
    // Fonction pour passer à la diapositive suivante
    function nextSlide() {
        // Masquer la diapositive actuelle
        slides[currentSlide].style.opacity = '0';
        
        // Passer à la diapositive suivante
        currentSlide = (currentSlide + 1) % slideCount;
        
        // Afficher la nouvelle diapositive
        slides[currentSlide].style.opacity = '1';
    }
    
    // Démarrer l'animation du slider
    let slideTimer = setInterval(nextSlide, slideInterval);
    
    // Arrêter le slider quand l'utilisateur interagit avec la page
    function pauseSlider() {
        clearInterval(slideTimer);
    }
    
    // Reprendre le slider après une période d'inactivité
    function resumeSlider() {
        clearInterval(slideTimer);
        slideTimer = setInterval(nextSlide, slideInterval);
    }
    
    // Gérer la pause/reprise au survol (optionnel)
    slider.addEventListener('mouseenter', pauseSlider);
    slider.addEventListener('mouseleave', resumeSlider);
    
    // Reprendre le slider quand la fenêtre est à nouveau visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            pauseSlider();
        } else {
            resumeSlider();
        }
    });
    
    // Pour les appareils tactiles
    let touchStartX = 0;
    let touchEndX = 0;
    
    slider.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        pauseSlider();
    }, { passive: true });
    
    slider.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        resumeSlider();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeThreshold = 50; // Seuil de balayage minimal en pixels
        const swipeDifference = touchStartX - touchEndX;
        
        // Balayage vers la gauche (diapositive suivante)
        if (swipeDifference > swipeThreshold) {
            nextSlide();
        } 
        // Balayage vers la droite (diapositive précédente)
        else if (swipeDifference < -swipeThreshold) {
            // Masquer la diapositive actuelle
            slides[currentSlide].style.opacity = '0';
            
            // Passer à la diapositive précédente
            currentSlide = (currentSlide - 1 + slideCount) % slideCount;
            
            // Afficher la nouvelle diapositive
            slides[currentSlide].style.opacity = '1';
        }
    }
});
</script>

<!-- Hero Section with Dynamic Background -->
<section class="hero hero-responsive">
    <!-- Slider d'images de fond -->
    <div class="background-slider">
        @if(!empty($backgroundSlider) && count($backgroundSlider) > 0)
            @foreach($backgroundSlider as $index => $slide)
                <div class="background-slide {{ $index === 0 ? 'active' : '' }}" data-bg="{{ $slide['image'] }}"></div>
            @endforeach
        @else
            <!-- Image de fond par défaut si aucune image n'est configurée -->
            @php($defaultBg = !empty($backgroundImage) ? $backgroundImage : asset('img/1.jpg'))
            <div class="background-slide active" data-bg="{{ $defaultBg }}"></div>
        @endif

        <!-- Assombrissement doux au-dessus de l'image -->
        <div class="background-overlay"></div>
        
        <!-- Motifs décoratifs animés -->
        <div class="floating-patterns">
            <div class="floating-circle"></div>
            <div class="floating-circle"></div>
            <div class="floating-circle"></div>
            <div class="floating-circle"></div>
        </div>
    </div>
    
    <div class="container hero-content">
        <h1 class="hero-title">
            <span data-typewriter="Commissariat à la Sécurité Alimentaire et à la Résilience"
                  data-typewriter-mode="letter"
                  data-typewriter-letter-delay="35"
                  data-typewriter-erase-delay="20"
                  data-typewriter-loop="true"
                  data-typewriter-loop-ms="10000"
                  data-typewriter-caret="true"></span>
        </h1>
        
        <p class="hero-subtitle">
            {{ __('messages.home.subtitle') }}
        </p>
        
        <div class="hero-buttons">
            <a href="{{ route('action', ['locale' => app()->getLocale()]) }}" class="hero-btn hero-btn-primary">
                <i class="fas fa-clipboard-list"></i> 
                <span>{{ __('messages.home.request_button') }}</span>
            </a>
            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="hero-btn hero-btn-secondary">
                <i class="fas fa-info-circle"></i> 
                <span>{{ __('messages.home.discover_button') }}</span>
            </a>
        </div>
    </div>
</section>

<script>
(function () {
  const el = document.querySelector('[data-typewriter]');
  if (!el) return;

  const text = el.dataset.typewriter || el.textContent.trim();
  const mode = el.dataset.typewriterMode || 'letter'; // 'word' | 'letter'
  const wordDelay = Number(el.dataset.typewriterWordDelay || 420);
  const letterDelay = Number(el.dataset.typewriterLetterDelay || 35);
  const eraseDelay = Number(el.dataset.typewriterEraseDelay || 20);
  const loop = (el.dataset.typewriterLoop || 'true') === 'true';
  const loopMs = Number(el.dataset.typewriterLoopMs || 10000);
  const showCaret = (el.dataset.typewriterCaret || 'true') === 'true';

  el.setAttribute('aria-live', 'polite');
  el.setAttribute('aria-atomic', 'true');
  el.style.whiteSpace = 'pre-wrap';

  // caret
  const styleId = 'tw-caret-style';
  if (!document.getElementById(styleId)) {
    const s = document.createElement('style');
    s.id = styleId;
    s.textContent = '@keyframes twblink{0%{opacity:1}50%{opacity:0}100%{opacity:1}}';
    document.head.appendChild(s);
  }
  const caret = document.createElement('span');
  caret.textContent = '▍';
  caret.style.marginLeft = '6px';
  caret.style.opacity = '0.85';
  caret.style.animation = 'twblink 1s steps(1,end) infinite';
  if (showCaret) el.appendChild(caret);

  const setText = (t) => {
    if (el.firstChild && el.firstChild.nodeType === Node.TEXT_NODE) {
      el.firstChild.nodeValue = t;
    } else {
      el.insertBefore(document.createTextNode(t), el.firstChild || el);
    }
  };
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

  async function wordByWord() {
    setText('');
    const words = text.split(/\s+/);
    let current = '';
    for (let i = 0; i < words.length; i++) {
      current += (i ? ' ' : '') + words[i];
      setText(current);
      await sleep(wordDelay);
    }
  }

  async function letterByLetter() {
    setText('');
    for (let i = 0; i < text.length; i++) {
      setText(text.slice(0, i + 1));
      await sleep(letterDelay);
    }
  }

  async function eraseLetters() {
    for (let i = text.length; i >= 0; i--) {
      setText(text.slice(0, i));
      await sleep(eraseDelay);
    }
  }

  async function runOnce() {
    if (mode === 'letter') {
      await letterByLetter();
    } else {
      await wordByWord();
    }
  }

  (async function loopAnim() {
    if (!loop) { await runOnce(); return; }
    // Calcule un cycle total de ~loopMs
    const typeTime = (mode === 'letter') ? text.length * letterDelay : (text.split(/\s+/).length * wordDelay);
    const eraseTime = (mode === 'letter') ? text.length * eraseDelay : (text.split(/\s+/).length * 60);
    const rest = Math.max(0, loopMs - typeTime - eraseTime);
    const pauseBeforeErase = Math.floor(rest / 2);
    const pauseAfterErase = Math.max(0, rest - pauseBeforeErase);
    // boucle infinie
    // eslint-disable-next-line no-constant-condition
    while (true) {
      await runOnce();
      await sleep(pauseBeforeErase);
      await eraseLetters();
      await sleep(pauseAfterErase);
    }
  })();
})();
</script>

<!-- Section Actualités & Informations -->
<section class="section section-responsive">
    <div class="container">
        <h2 class="section-title">Actualités & Informations</h2>
        <p class="section-subtitle">Restez informés des dernières nouvelles et initiatives du CSAR</p>
        
        <div class="grid grid-3 news-grid">
            @if(isset($latestNews) && $latestNews->count() > 0)
                @foreach($latestNews as $news)
                <div class="card news-card">
                    @if($news->image)
                    <div class="news-image">
                        <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}">
                    </div>
                    @endif
                    <div class="card-body">
                        <h3 class="news-title">{{ $news->title }}</h3>
                        <p class="news-excerpt">{{ $news->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($news->content), 120) }}</p>
                        <div class="news-meta">
                            <span class="news-date">
                                <i class="fas fa-calendar"></i> 
                                {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}
                            </span>
                            <a href="{{ route('news.show', $news->id) }}" class="btn btn-primary btn-sm">Lire plus</a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="news-empty">
                    <div class="empty-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>Aucune actualité disponible</h3>
                    <p>Les actualités seront publiées prochainement</p>
                </div>
            @endif
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('news') }}" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i>
                Voir toutes les actualités
            </a>
        </div>
    </div>
</section>

<!-- Section Galerie d'actions -->
<section class="section section-responsive">
    <div class="container">
        <h2 class="section-title">Nos Actions</h2>
        <p class="section-subtitle">Découvrez nos missions et nos réalisations sur le terrain</p>
        
        <div class="grid grid-3 gallery-grid">
            <a href="{{ route('gallery') }}" class="card gallery-card">
                <div class="gallery-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3>Distributions alimentaires</h3>
                <p>Nos équipes distribuent des denrées alimentaires aux populations dans le besoin à travers tout le Sénégal</p>
            </a>
            
            <a href="{{ route('map') }}" class="card gallery-card">
                <div class="gallery-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Magasins de stockage CSAR</h3>
                <p>Notre réseau de magasins de stockage stratégiques assure le stockage et la distribution des denrées alimentaires</p>
            </a>
            
            <a href="{{ route('suivi.direct') }}" class="card gallery-card">
                <div class="gallery-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Suivre ma demande</h3>
                <p>Consultez l'état d'avancement de votre demande avec votre code de suivi unique</p>
            </a>
        </div>
    </div>
</section>

<!-- Section Statistiques -->
@if(isset($stats) && count($stats) > 0)
<section class="section stats-section">
    <div class="container">
        <h2 class="section-title text-center">Nos Réalisations</h2>
        <div class="stats-grid">
            @foreach($stats as $stat)
            <div class="stat-item">
                <div class="stat-number">{{ $stat['number'] }}</div>
                <div class="stat-label">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
/* Hero Section Responsive */
.hero-responsive {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    position: relative;
    overflow: hidden;
    background-image: url('/img/1.jpg');
    background-size: cover;
    background-position: center;
}

.background-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 0;
}

.background-slide {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.background-slide.active {
    opacity: 1;
}

.background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.floating-patterns {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.1;
    z-index: 0;
    pointer-events: none;
}

.floating-circle {
    position: absolute;
    background: #fff;
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.floating-circle:nth-child(1) {
    top: 10%;
    left: 10%;
    width: 100px;
    height: 100px;
}

.floating-circle:nth-child(2) {
    top: 20%;
    right: 15%;
    width: 60px;
    height: 60px;
    animation: float 8s ease-in-out infinite reverse;
}

.floating-circle:nth-child(3) {
    bottom: 30%;
    left: 20%;
    width: 80px;
    height: 80px;
    animation: float 7s ease-in-out infinite;
}

.floating-circle:nth-child(4) {
    bottom: 20%;
    right: 10%;
    width: 120px;
    height: 120px;
    animation: float 9s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.hero-content {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 2;
    color: #fff;
    padding: 0 1rem;
}

.hero-title {
    font-size: 1.75rem;
    font-weight: 900;
    color: #fff;
    margin-bottom: 1.5rem;
    letter-spacing: -1px;
    line-height: 1.2;
    text-shadow: 0 8px 24px #222, 0 2px 8px #000;
}

.hero-subtitle {
    font-size: 1rem;
    color: #f3f4f6;
    max-width: 800px;
    margin: 0 auto 2rem;
    line-height: 1.6;
    text-shadow: 0 4px 16px #222, 0 1px 4px #000;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.hero-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    margin-top: 2rem;
}

.hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(.23,1.01,.32,1);
    border: none;
    outline: none;
    min-height: 44px;
    justify-content: center;
    width: 100%;
    max-width: 300px;
}

.hero-btn-primary {
    background: #2563eb;
    color: #fff;
    box-shadow: 0 8px 32px #1e293b99, 0 2px 8px #0004;
}

.hero-btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 12px 40px #1e293bcc, 0 4px 12px #0006;
}

.hero-btn-secondary {
    background: rgba(255,255,255,0.12);
    color: #fff;
    border: 2px solid #fff;
    box-shadow: 0 4px 16px #2228, 0 1px 4px #0003;
}

.hero-btn-secondary:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px #222c, 0 2px 8px #0004;
}

/* Sections */
.section-responsive {
    background: #f8fafc;
    padding: 3rem 0;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    text-align: center;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.125rem;
    color: #6b7280;
    text-align: center;
    margin-bottom: 3rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* News Grid */
.news-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: 1fr;
}

.news-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}

.news-image {
    height: 200px;
    overflow: hidden;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-body {
    padding: 1.5rem;
}

.news-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.news-excerpt {
    color: #6b7280;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.news-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.news-date {
    color: #9ca3af;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.news-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: #e5e7eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
    font-size: 2rem;
    color: #9ca3af;
}

.news-empty h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.news-empty p {
    color: #9ca3af;
}

/* Gallery Grid */
.gallery-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: 1fr;
}

.gallery-card {
    text-align: center;
    text-decoration: none;
    display: block;
    cursor: pointer;
    transition: all 0.3s ease;
}

.gallery-card:hover {
    transform: translateY(-4px);
}

.gallery-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: #fff;
}

.gallery-card:nth-child(2) .gallery-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.gallery-card:nth-child(3) .gallery-icon {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

.gallery-card h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
}

.gallery-card p {
    color: #6b7280;
    line-height: 1.6;
}

/* Stats Section */
.stats-section {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    padding: 3rem 0;
}

.stats-section .section-title {
    color: white;
}

.stats-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: repeat(2, 1fr);
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

/* Responsive Breakpoints */
@media (min-width: 768px) {
    .hero-responsive {
        min-height: 80vh;
        padding: 4rem 0;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.125rem;
    }
    
    .hero-buttons {
        flex-direction: row;
    }
    
    .hero-btn {
        width: auto;
        max-width: none;
    }
    
    .section-responsive {
        padding: 4rem 0;
    }
    
    .section-title {
        font-size: 2.5rem;
    }
    
    .news-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 1024px) {
    .hero-title {
        font-size: 3.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.4rem;
    }
    
    .news-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .gallery-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 640px) {
    .floating-circle {
        display: none;
    }
    
    .hero-responsive {
        min-height: 60vh;
        padding: 1.5rem 0;
    }
    
    .hero-title {
        font-size: 1.5rem;
    }
    
    .hero-subtitle {
        font-size: 0.9rem;
    }
    
    .hero-btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }
}
</style>
@endsection

