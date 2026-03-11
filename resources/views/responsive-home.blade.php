@extends('layouts.responsive-public')

@section('title', 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')

@section('description', 'Plateforme numérique du CSAR pour garantir la sécurité alimentaire et renforcer la résilience des populations sénégalaises')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Sécurité Alimentaire & 
                    <span class="text-csar-green-600">Résilience</span>
                    pour le Sénégal
                </h1>
                <p class="hero-subtitle">
                    Le Commissariat à la Sécurité Alimentaire et à la Résilience œuvre pour garantir la sécurité alimentaire et renforcer la résilience des populations.
                </p>
                <p class="hero-description">
                    Découvrez nos services, consultez nos actualités et accédez aux demandes d'aide alimentaire, d'audience et de partenariat.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('requests.create', ['type' => 'aide']) }}" class="btn-primary">
                        <i class="fas fa-hand-holding-heart mr-2"></i>
                        Demander de l'aide
                    </a>
                    <a href="{{ route('about') }}" class="btn-secondary">
                        <i class="fas fa-info-circle mr-2"></i>
                        En savoir plus
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/csar-hero-image.jpg') }}" 
                     alt="CSAR - Sécurité Alimentaire Sénégal" 
                     class="w-full h-auto rounded-lg shadow-lg"
                     data-mobile-src="{{ asset('images/csar-hero-mobile.jpg') }}">
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats -->
<section class="stats-section">
    <div class="responsive-container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">13</div>
                <div class="stat-label">Entrepôts régionaux</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">100+</div>
                <div class="stat-label">Agents sur le terrain</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">50K+</div>
                <div class="stat-label">Familles aidées</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Disponibilité</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="responsive-container">
        <div class="text-center mb-12">
            <h2 class="responsive-title mb-4">Nos Services</h2>
            <p class="responsive-text text-gray-600 max-w-2xl mx-auto">
                Le CSAR propose une gamme complète de services pour assurer la sécurité alimentaire et renforcer la résilience des populations sénégalaises.
            </p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hand-holding-heart text-2xl sm:text-3xl text-csar-green-600"></i>
                </div>
                <h3 class="feature-title">Aide Alimentaire</h3>
                <p class="feature-description">
                    Demandez une aide alimentaire d'urgence pour vous ou votre famille. Notre système de distribution couvre tout le territoire national.
                </p>
                <a href="{{ route('requests.create', ['type' => 'aide']) }}" class="mt-4 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                    Faire une demande <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-check text-2xl sm:text-3xl text-csar-green-600"></i>
                </div>
                <h3 class="feature-title">Demande d'Audience</h3>
                <p class="feature-description">
                    Sollicitez une audience avec les responsables du CSAR pour discuter de questions importantes concernant la sécurité alimentaire.
                </p>
                <a href="{{ route('requests.create', ['type' => 'audience']) }}" class="mt-4 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                    Demander une audience <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-handshake text-2xl sm:text-3xl text-csar-green-600"></i>
                </div>
                <h3 class="feature-title">Partenariat</h3>
                <p class="feature-description">
                    Devenez partenaire du CSAR et contribuez à renforcer la sécurité alimentaire et la résilience au Sénégal.
                </p>
                <a href="{{ route('requests.create', ['type' => 'partenariat']) }}" class="mt-4 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                    Proposer un partenariat <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt text-2xl sm:text-3xl text-csar-green-600"></i>
                </div>
                <h3 class="feature-title">Carte Interactive</h3>
                <p class="feature-description">
                    Explorez notre carte interactive pour localiser les entrepôts, les zones d'intervention et suivre les demandes en temps réel.
                </p>
                <a href="{{ route('map') }}" class="mt-4 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                    Voir la carte <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search text-2xl sm:text-3xl text-csar-green-600"></i>
                </div>
                <h3 class="feature-title">Suivi de Demande</h3>
                <p class="feature-description">
                    Suivez l'état d'avancement de votre demande en temps réel grâce à votre code de suivi unique.
                </p>
                <a href="{{ route('requests.track') }}" class="mt-4 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                    Suivre ma demande <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-newspaper text-2xl sm:text-3xl text-csar-green-600"></i>
                </div>
                <h3 class="feature-title">Actualités</h3>
                <p class="feature-description">
                    Restez informé des dernières actualités du CSAR, des discours officiels et des initiatives en cours.
                </p>
                <a href="{{ route('news.index') }}" class="mt-4 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                    Lire les actualités <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Recent News Section -->
<section class="news-section">
    <div class="responsive-container">
        <div class="flex items-center justify-between mb-8">
            <h2 class="responsive-title">Actualités Récentes</h2>
            <a href="{{ route('news.index') }}" class="text-csar-green-600 hover:text-csar-green-700 font-medium">
                Voir toutes les actualités <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="news-grid">
            @forelse($recent_news ?? [] as $news)
                <article class="news-card">
                    @if($news->featured_image)
                        <img src="{{ asset('storage/' . $news->featured_image) }}" 
                             alt="{{ $news->title }}" 
                             class="news-image">
                    @endif
                    <div class="p-4">
                        <div class="news-date">{{ $news->created_at->format('d/m/Y') }}</div>
                        <h3 class="news-title">{{ $news->title }}</h3>
                        <p class="news-excerpt">{{ Str::limit($news->excerpt, 120) }}</p>
                        <a href="{{ route('news.show', $news->slug) }}" class="mt-3 inline-block text-csar-green-600 hover:text-csar-green-700 font-medium">
                            Lire la suite <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucune actualité récente</h3>
                    <p class="text-gray-500">Les actualités du CSAR seront bientôt disponibles.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Interactive Map Preview -->
<section class="py-12 sm:py-16 lg:py-20 bg-gray-50">
    <div class="responsive-container">
        <div class="text-center mb-8">
            <h2 class="responsive-title mb-4">Carte Interactive</h2>
            <p class="responsive-text text-gray-600 max-w-2xl mx-auto">
                Découvrez notre réseau d'entrepôts et les zones d'intervention du CSAR à travers le Sénégal.
            </p>
        </div>
        
        <div class="map-container">
            <div id="homeMap" class="w-full h-full rounded-lg"></div>
        </div>
        
        <div class="text-center mt-6">
            <a href="{{ route('map') }}" class="btn-primary">
                <i class="fas fa-map-marked-alt mr-2"></i>
                Voir la carte complète
            </a>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="responsive-container">
        <div class="text-center mb-12">
            <h2 class="responsive-title mb-4">Contactez-nous</h2>
            <p class="responsive-text text-gray-600 max-w-2xl mx-auto">
                Une question ? Un besoin d'information ? N'hésitez pas à nous contacter. Notre équipe est à votre disposition.
            </p>
        </div>
        
        <div class="contact-grid">
            <div class="contact-info">
                <h3 class="responsive-subtitle mb-6">Informations de contact</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt text-csar-green-600"></i>
                    </div>
                    <div class="contact-text">
                        <p class="contact-title">Adresse principale</p>
                        <p>Dakar, Sénégal<br>Zone Administrative</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone text-csar-green-600"></i>
                    </div>
                    <div class="contact-text">
                        <p class="contact-title">Téléphone</p>
                        <p>+221 33 XXX XX XX<br>Plateforme: 24h/24</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope text-csar-green-600"></i>
                    </div>
                    <div class="contact-text">
                        <p class="contact-title">Email</p>
                        <p>contact@csar.sn<br>aide@csar.sn</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock text-csar-green-600"></i>
                    </div>
                    <div class="contact-text">
                        <p class="contact-title">Horaires d'ouverture</p>
                        <p>Plateforme: 24h/24 - 7j/7<br>Bureau: Lundi - Vendredi 8h-17h</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h3 class="responsive-subtitle mb-6">Envoyez-nous un message</h3>
                <form action="{{ route('contact.store') }}" method="POST" class="responsive-form">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name" class="form-label">Nom complet *</label>
                            <input type="text" id="name" name="name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="form-label">Sujet *</label>
                        <input type="text" id="subject" name="subject" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">Message *</label>
                        <textarea id="message" name="message" class="form-textarea" required></textarea>
                    </div>
                    <button type="submit" class="form-button">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize home map if element exists
    const mapElement = document.getElementById('homeMap');
    if (mapElement && typeof L !== 'undefined') {
        // Initialize map centered on Senegal
        const map = L.map('homeMap').setView([14.4974, -14.4524], 6);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add warehouse markers (simplified version)
        const warehouses = [
            { name: 'Dakar', lat: 14.7167, lng: -17.4677 },
            { name: 'Thiès', lat: 14.7833, lng: -16.9333 },
            { name: 'Kaolack', lat: 14.1500, lng: -16.0833 },
            { name: 'Saint-Louis', lat: 16.0167, lng: -16.4833 },
            { name: 'Ziguinchor', lat: 12.5833, lng: -16.2667 }
        ];
        
        warehouses.forEach(warehouse => {
            const marker = L.marker([warehouse.lat, warehouse.lng]).addTo(map);
            marker.bindPopup(`<strong>Entrepôt CSAR ${warehouse.name}</strong>`);
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.feature-card, .news-card, .stat-item').forEach(el => {
        observer.observe(el);
    });
});
</script>

<style>
/* Animation styles */
.animate-fade-in {
    animation: fadeInUp 0.6s ease-out forwards;
}

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

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection

