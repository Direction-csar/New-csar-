@extends('layouts.public')
@section('title', 'Nos missions en images - CSAR')
@section('content')

<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%); min-height: 40vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px; position: relative; overflow: hidden;">
    <!-- Animated particles -->
    <div class="particle" style="position: absolute; top: 20%; left: 10%; width: 8px; height: 8px; background: rgba(34,197,94,0.6); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
    <div class="particle" style="position: absolute; top: 40%; right: 15%; width: 6px; height: 6px; background: rgba(56,189,248,0.5); border-radius: 50%; animation: float 8s ease-in-out infinite reverse;"></div>
    <div class="particle" style="position: absolute; bottom: 30%; left: 20%; width: 10px; height: 10px; background: rgba(251,191,36,0.4); border-radius: 50%; animation: float 7s ease-in-out infinite;"></div>

    <div style="text-align: center; position: relative; z-index: 2; max-width: 800px; margin: 0 auto;">
        <div style="display: inline-block; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 12px 24px; margin-bottom: 24px;">
            <span style="color: #22c55e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                📸 Galerie Photos
            </span>
        </div>
        <h1 style="font-size: 3rem; font-weight: 800; color: #fff; margin-bottom: 16px; letter-spacing: -1px; text-shadow: 0 4px 12px rgba(0,0,0,0.3);">
            Nos Missions en Images
        </h1>
        <p style="font-size: 1.25rem; color: rgba(255,255,255,0.85); max-width: 600px; margin: 0 auto; line-height: 1.7;">
            Découvrez nos actions humanitaires et interventions de résilience à travers le Sénégal
        </p>
    </div>
</section>

<!-- Carousel Section -->
<section style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">

        @if(count($images) > 0)
        <!-- Professional Carousel -->
        <div class="carousel-container" style="position: relative; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.15); background: #fff;">

            <!-- Main Image Display -->
            <div class="carousel-main" style="position: relative; height: 500px; overflow: hidden;">
                @foreach($images as $index => $image)
                <div class="carousel-slide {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: {{ $index === 0 ? 1 : 0 }}; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('storage/'.$image->file_path) }}" alt="{{ $image->title }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 8s ease;">
                    <!-- Overlay -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 40px; color: #fff;">
                        <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 12px; transform: translateY(20px); opacity: 0; animation: slideUp 0.5s ease forwards 0.2s;">{{ $image->title ?: 'Mission CSAR' }}</h3>
                        <p style="font-size: 1rem; line-height: 1.6; opacity: 0.9; max-width: 600px; transform: translateY(20px); opacity: 0; animation: slideUp 0.5s ease forwards 0.4s;">{{ $image->description ?: 'Action humanitaire et intervention de résilience' }}</p>
                        @if($image->category)
                        <span style="display: inline-block; background: rgba(34,197,94,0.3); backdrop-filter: blur(10px); color: #22c55e; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-top: 16px; border: 1px solid rgba(34,197,94,0.3); transform: translateY(20px); opacity: 0; animation: slideUp 0.5s ease forwards 0.6s;">
                            {{ $image->category }}
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Navigation Arrows -->
            <button onclick="prevSlide()" class="nav-arrow prev" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.95); border: none; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; z-index: 10; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #1f2937;">
                ←
            </button>
            <button onclick="nextSlide()" class="nav-arrow next" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.95); border: none; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; z-index: 10; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #1f2937;">
                →
            </button>

            <!-- Progress Bar -->
            <div class="progress-bar" style="position: absolute; bottom: 0; left: 0; height: 4px; background: linear-gradient(90deg, #22c55e, #16a34a); width: 0%; z-index: 10; transition: width 5s linear;"></div>
        </div>

        <!-- Thumbnails Navigation -->
        <div class="thumbnails" style="display: flex; gap: 12px; margin-top: 24px; overflow-x: auto; padding: 10px 0; scroll-behavior: smooth;">
            @foreach($images as $index => $image)
            <div class="thumb {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}" onclick="goToSlide({{ $index }})" style="flex-shrink: 0; width: 100px; height: 70px; border-radius: 10px; overflow: hidden; cursor: pointer; border: 3px solid {{ $index === 0 ? '#22c55e' : 'transparent' }}; transition: all 0.3s ease; opacity: {{ $index === 0 ? 1 : 0.6 }};">
                <img src="{{ asset('storage/'.$image->file_path) }}" alt="{{ $image->title }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            @endforeach
        </div>

        <!-- Image Counter -->
        <div style="text-align: center; margin-top: 20px; color: #64748b; font-weight: 500;">
            <span id="currentSlide">1</span> / <span id="totalSlides">{{ count($images) }}</span>
        </div>

        <!-- Play/Pause Button -->
        <div style="text-align: center; margin-top: 24px;">
            <button id="playPauseBtn" onclick="togglePlayPause()" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border: none; padding: 14px 32px; border-radius: 50px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(34,197,94,0.3); display: inline-flex; align-items: center; gap: 8px;">
                <span id="playPauseIcon">⏸</span>
                <span id="playPauseText">Pause</span>
            </button>
        </div>

        @else
        <!-- No Images Message -->
        <div style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 20px;">📸</div>
            <h3 style="font-size: 1.5rem; color: #6b7280; margin-bottom: 10px;">Aucune image disponible</h3>
            <p style="color: #9ca3af;">Les images de nos missions seront bientôt disponibles.</p>
        </div>
        @endif
    </div>
</section>

@push('styles')
<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Carousel animations */
.carousel-slide.active img {
    transform: scale(1.05);
}

.nav-arrow:hover {
    background: #22c55e !important;
    color: #fff !important;
    transform: translateY(-50%) scale(1.1) !important;
}

.thumb:hover {
    opacity: 1 !important;
    transform: scale(1.05);
}

.thumb.active {
    box-shadow: 0 4px 12px rgba(34,197,94,0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .carousel-main { height: 300px !important; }
    .carousel-main h3 { font-size: 1.3rem !important; }
    .carousel-main p { font-size: 0.9rem !important; }
    .nav-arrow { width: 40px !important; height: 40px !important; font-size: 16px !important; }
    .thumb { width: 70px !important; height: 50px !important; }
    .hero-section h1 { font-size: 2rem !important; }
}
</style>
@endpush

@push('scripts')
<script>
let currentSlide = 0;
let totalSlides = {{ count($images) }};
let isPlaying = true;
let autoplayInterval;

function showSlide(index) {
    const slides = document.querySelectorAll('.carousel-slide');
    const thumbs = document.querySelectorAll('.thumb');
    const progressBar = document.querySelector('.progress-bar');

    // Remove active from all
    slides.forEach(s => { s.classList.remove('active'); s.style.opacity = '0'; });
    thumbs.forEach(t => { t.classList.remove('active'); t.style.borderColor = 'transparent'; t.style.opacity = '0.6'; });

    // Activate current
    currentSlide = index;
    if (currentSlide >= totalSlides) currentSlide = 0;
    if (currentSlide < 0) currentSlide = totalSlides - 1;

    slides[currentSlide].classList.add('active');
    slides[currentSlide].style.opacity = '1';
    thumbs[currentSlide].classList.add('active');
    thumbs[currentSlide].style.borderColor = '#22c55e';
    thumbs[currentSlide].style.opacity = '1';

    // Update counter
    document.getElementById('currentSlide').textContent = currentSlide + 1;

    // Reset progress bar
    if (progressBar) {
        progressBar.style.transition = 'none';
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.transition = 'width 5s linear';
            progressBar.style.width = '100%';
        }, 50);
    }

    // Scroll thumbnail into view
    thumbs[currentSlide].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
}

function nextSlide() {
    showSlide(currentSlide + 1);
    if (isPlaying) restartAutoplay();
}

function prevSlide() {
    showSlide(currentSlide - 1);
    if (isPlaying) restartAutoplay();
}

function goToSlide(index) {
    showSlide(index);
    if (isPlaying) restartAutoplay();
}

function startAutoplay() {
    if (totalSlides <= 1) return;
    autoplayInterval = setInterval(() => {
        if (isPlaying) nextSlide();
    }, 5000);
}

function stopAutoplay() {
    clearInterval(autoplayInterval);
}

function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
}

function togglePlayPause() {
    isPlaying = !isPlaying;
    const icon = document.getElementById('playPauseIcon');
    const text = document.getElementById('playPauseText');

    if (isPlaying) {
        icon.textContent = '⏸';
        text.textContent = 'Pause';
        startAutoplay();
    } else {
        icon.textContent = '▶';
        text.textContent = 'Lecture';
        stopAutoplay();
    }
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') prevSlide();
    if (e.key === 'ArrowRight') nextSlide();
    if (e.key === ' ') { e.preventDefault(); togglePlayPause(); }
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    if (totalSlides > 1) {
        startAutoplay();

        // Pause on hover
        const container = document.querySelector('.carousel-container');
        if (container) {
            container.addEventListener('mouseenter', () => { if (isPlaying) stopAutoplay(); });
            container.addEventListener('mouseleave', () => { if (isPlaying) startAutoplay(); });
        }
    }

    // Initialize progress bar
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        setTimeout(() => { progressBar.style.width = '100%'; }, 100);
    }
});
</script>
@endpush

@endsection
