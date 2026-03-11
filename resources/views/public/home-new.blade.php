@extends('layouts.public')

@section('title', 'Accueil - CSAR')

@section('content')
<style>
/* Hero Section moderne */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset("images/hero-bg.jpg") }}') center/cover;
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 10;
    text-align: center;
    color: white;
    padding: 2rem;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.hero-subtitle {
    font-size: 1.25rem;
    line-height: 1.6;
    margin-bottom: 3rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    opacity: 0.95;
}

.hero-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-hero {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
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
    box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
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
    background: #f8f9fa;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.service-card {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.service-card:hover::before {
    transform: scaleX(1);
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(34, 197, 94, 0.15);
}

.service-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.service-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
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

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.2rem;
    color: #6b7280;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.news-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
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
    color: #d1d5db;
}

.news-content {
    padding: 1.5rem;
}

.news-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.news-excerpt {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.news-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #9ca3af;
    font-size: 0.875rem;
}

.btn-all-news {
    background: #22c55e;
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-all-news:hover {
    background: #16a34a;
    transform: translateX(5px);
}

/* Gallery Section */
.gallery-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-hero {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .services-grid,
    .news-grid {
        grid-template-columns: 1fr;
    }
    
    .section-title {
        font-size: 2rem;
    }
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Commissariat à la Sécurité Alimentaire et à la Résilience</h1>
            <p class="hero-subtitle">
                Le Commissariat à la Sécurité Alimentaire et à la Résilience œuvre pour garantir l'accès à une alimentation suffisante et nutritive pour tous les Sénégalais, tout en renforçant leur capacité à faire face aux crises et aux défis climatiques
            </p>
            <div class="hero-buttons">
                <a href="{{ route('demande.index') }}" class="btn-hero btn-primary-hero">
                    <i class="fas fa-file-alt"></i>
                    Effectuer une demande
                </a>
                <a href="{{ route('about') }}" class="btn-hero btn-secondary-hero">
                    <i class="fas fa-info-circle"></i>
                    Découvrir le CSAR
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="services-grid">
            <!-- Distribution alimentaire -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3 class="service-title">Distributions alimentaires</h3>
                <p class="service-description">
                    Nos équipes distribuent des denrées alimentaires aux populations dans le besoin à travers tout le Sénégal
                </p>
            </div>

            <!-- Magasins de stockage -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h3 class="service-title">Magasins de stockage CSAR</h3>
                <p class="service-description">
                    Notre réseau de magasins de stockage stratégiques assure le stockage et la distribution des denrées alimentaires
                </p>
            </div>

            <!-- Suivi de demande -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="service-title">Suivre ma demande</h3>
                <p class="service-description">
                    Consultez l'état d'avancement de votre demande avec votre code de suivi unique
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Actualités Section -->
<section class="news-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Actualités & Informations</h2>
            <p class="section-subtitle">Restez informés des dernières nouvelles et initiatives du CSAR</p>
        </div>

        <div class="news-grid">
            @forelse($latestNews as $news)
                <div class="news-card">
                    <div class="news-image">
                        @if($news->featured_image)
                            <img src="{{ $news->featured_image }}" alt="{{ $news->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="far fa-newspaper"></i>
                        @endif
                    </div>
                    <div class="news-content">
                        <h4 class="news-title">{{ $news->title }}</h4>
                        <p class="news-excerpt">{{ $news->excerpt }}</p>
                        <div class="news-meta">
                            <i class="far fa-calendar-alt"></i>
                            <span>{{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div style="padding: 0 1.5rem 1.5rem;">
                        <a href="{{ route('news.show', $news->id) }}" class="btn btn-sm btn-success">Lire plus</a>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Aucune actualité disponible pour le moment.</p>
                </div>
            @endforelse
        </div>

        @if($latestNews->count() > 0)
            <div class="text-center">
                <a href="{{ route('news') }}" class="btn-all-news">
                    Voir toutes les actualités
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        @endif
    </div>
</section>

<!-- Galerie Section -->
<section class="gallery-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Galerie de missions</h2>
            <p class="section-subtitle">Découvrez nos actions sur le terrain</p>
        </div>

        <div class="text-center">
            <a href="{{ route('gallery') }}" class="btn-all-news">
                Voir la galerie complète
                <i class="fas fa-images"></i>
            </a>
        </div>
    </div>
</section>

@endsection

