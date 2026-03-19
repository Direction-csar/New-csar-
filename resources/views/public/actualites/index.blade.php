@extends('layouts.public')

@section('title', 'Actualités CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')

@push('styles')
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --light-bg: #f8f9fa;
        --dark-bg: #2c3e50;
    }
    
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
        margin-top: 0;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .news-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }
    
    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .news-image {
        height: 200px;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    
    .news-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .featured-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--warning-color);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .news-content {
        padding: 25px;
    }
    
    .news-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .news-excerpt {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .news-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        color: #888;
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: none;
        height: 100%;
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .stats-label {
        font-size: 1.1rem;
        color: #666;
        font-weight: 500;
    }
    
    .filter-btn {
        border-radius: 25px;
        padding: 8px 20px;
        margin: 5px;
        transition: all 0.3s ease;
    }
    
    .filter-btn.active {
        background: var(--secondary-color);
        color: white;
        border-color: var(--secondary-color);
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 20px;
        text-align: center;
    }
    
    .section-subtitle {
        font-size: 1.2rem;
        color: #666;
        text-align: center;
        margin-bottom: 50px;
    }
    
    .real-time-badge {
        background: linear-gradient(45deg, var(--success-color), #2ecc71);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content text-center">
            <h1 class="display-4 fw-bold mb-4">Actualités CSAR</h1>
            <p class="lead mb-4">Restez informé des dernières nouvelles du Commissariat à la Sécurité Alimentaire et à la Résilience</p>
            <div class="mt-4">
                <span class="real-time-badge">
                    <i class="fas fa-sync-alt pulse"></i>
                    Mises à jour en temps réel
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Statistiques -->
<section class="py-5" style="background: var(--light-bg);">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="total-actualites">{{ $stats['total'] }}</div>
                    <div class="stats-label">📰 Actualités totales</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="featured-actualites">{{ $stats['featured'] }}</div>
                    <div class="stats-label">⭐ À la une</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="this-week-actualites">{{ $stats['this_week'] }}</div>
                    <div class="stats-label">📅 Cette semaine</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number">{{ $stats['categories']->count() }}</div>
                    <div class="stats-label">🏷️ Catégories</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA LinkedIn - Intégration stratégique -->
<section class="py-4">
    <div class="container">
        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden">
            <div class="card-body py-4 px-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener" class="text-white">
                        <i class="fab fa-linkedin fa-3x opacity-75"></i>
                    </a>
                    <div>
                        <h4 class="h5 mb-1">Suivez l'actualité CSAR sur LinkedIn</h4>
                        <p class="mb-0 small opacity-90">Communiqués, résultats terrain et partenariats. Hashtags officiels : <strong>#CSAR</strong> <strong>#SécuritéAlimentaire</strong> <strong>#Résilience</strong> <strong>#SIM</strong> <strong>#PartenariatInternational</strong></p>
                    </div>
                </div>
                <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener" class="btn btn-light btn-lg flex-shrink-0">
                    <i class="fab fa-linkedin-in me-2"></i>Suivez-nous sur LinkedIn
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Filtres -->
<section class="py-4">
    <div class="container">
        <div class="text-center mb-4">
            <h3>Filtrer par catégorie</h3>
            <div class="d-flex flex-wrap justify-content-center">
                <button class="btn btn-outline-primary filter-btn active" data-category="all">
                    Toutes <span class="badge bg-primary ms-1">{{ $stats['total'] }}</span>
                </button>
                @foreach($stats['categories'] as $category => $count)
                    <button class="btn btn-outline-primary filter-btn" data-category="{{ $category }}">
                        {{ ucfirst(str_replace('_', ' ', $category)) }} <span class="badge bg-primary ms-1">{{ $count }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Actualités à la une -->
@if($featured->count() > 0)
<section class="py-5">
    <div class="container">
        <h2 class="section-title">À la une</h2>
        <p class="section-subtitle">Les actualités les plus importantes</p>
        
        <div class="row">
            @foreach($featured as $actualite)
                <div class="col-lg-{{ $actualite->youtube_url ? '8' : '4' }} col-md-6 mb-4">
                    <div class="news-card {{ $actualite->youtube_url ? 'd-flex flex-column flex-md-row' : '' }}">
                        @if($actualite->youtube_url)
                            {{-- Layout avec vidéo YouTube à côté --}}
                            <div class="flex-shrink-0" style="width:100%;max-width:420px;">
                                @php
                                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $actualite->youtube_url, $ytMatches);
                                    $ytId = $ytMatches[1] ?? null;
                                @endphp
                                @if($ytId)
                                    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:15px 0 0 15px;">
                                        <iframe src="https://www.youtube.com/embed/{{ $ytId }}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>
                                    </div>
                                @endif
                            </div>
                            <div class="news-content flex-grow-1">
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-dark">{{ ucfirst(str_replace('_', ' ', $actualite->categorie)) }}</span>
                                    <span class="badge bg-warning text-white">⭐ À la une</span>
                                    <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>Vidéo</span>
                                </div>
                                <h3 class="news-title">{{ $actualite->titre }}</h3>
                                <p class="news-excerpt">{{ $actualite->extrait }}</p>
                                <div class="news-meta">
                                    <div>
                                        <i class="fas fa-user me-1"></i>{{ $actualite->auteur }}
                                        <br>
                                        <i class="fas fa-calendar me-1"></i>{{ $actualite->published_at->format('d/m/Y') }}
                                    </div>
                                    <div>
                                        <i class="fas fa-eye me-1"></i>{{ $actualite->vues }} vues
                                    </div>
                                </div>
                                <a href="{{ route('public.actualites.show', $actualite->id) }}" class="btn btn-primary mt-3 w-100">
                                    Lire la suite <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        @else
                            {{-- Layout classique avec image --}}
                            <div class="news-image" @if($actualite->image) style="background-image: url('{{ $actualite->image }}')" @else style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);" @endif>
                                <div class="news-badge">{{ ucfirst(str_replace('_', ' ', $actualite->categorie)) }}</div>
                                <div class="featured-badge">⭐ À la une</div>
                                @if(!$actualite->image)
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; text-align: center;">
                                        <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" style="width:80px;opacity:0.7;margin-bottom:8px;">
                                        <div style="font-weight:600;">Actualité CSAR</div>
                                    </div>
                                @endif
                            </div>
                            <div class="news-content">
                                <h3 class="news-title">{{ $actualite->titre }}</h3>
                                <p class="news-excerpt">{{ $actualite->extrait }}</p>
                                <div class="news-meta">
                                    <div>
                                        <i class="fas fa-user me-1"></i>{{ $actualite->auteur }}
                                        <br>
                                        <i class="fas fa-calendar me-1"></i>{{ $actualite->published_at->format('d/m/Y') }}
                                    </div>
                                    <div>
                                        <i class="fas fa-eye me-1"></i>{{ $actualite->vues }} vues
                                    </div>
                                </div>
                                <a href="{{ route('public.actualites.show', $actualite->id) }}" class="btn btn-primary mt-3 w-100">
                                    Lire la suite <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Actualités récentes -->
@php
    $recentItems = $regular->take(6);
    $historicItems = $regular->skip(6);
@endphp
<section class="py-5" style="background: var(--light-bg);">
    <div class="container">
        <h2 class="section-title">Actualités récentes</h2>
        <p class="section-subtitle">Découvrez nos dernières actualités et communiqués</p>
        
        <div class="row" id="actualites-container">
            @forelse ($recentItems as $actualite)
                <div class="col-lg-4 col-md-6 mb-4 actualite-item" data-category="{{ $actualite->categorie }}">
                    <div class="news-card">
                        <div class="news-image" @if($actualite->image) style="background-image: url('{{ $actualite->image }}')" @else style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);" @endif>
                            <div class="news-badge">{{ ucfirst(str_replace('_', ' ', $actualite->categorie)) }}</div>
                            @if($actualite->youtube_url)
                                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;">
                                    <div style="width:60px;height:60px;background:rgba(255,0,0,0.85);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fab fa-youtube" style="font-size:1.8rem;color:white;"></i>
                                    </div>
                                </div>
                            @endif
                            @if(!$actualite->image)
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; text-align: center;">
                                    <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" style="width:80px;opacity:0.7;margin-bottom:8px;">
                                    <div style="font-weight:600;">Actualité CSAR</div>
                                </div>
                            @endif
                        </div>
                        <div class="news-content">
                            <h3 class="news-title">{{ $actualite->titre }}</h3>
                            <p class="news-excerpt">{{ $actualite->extrait }}</p>
                            <div class="news-meta">
                                <div>
                                    <i class="fas fa-user me-1"></i>{{ $actualite->auteur }}
                                    <br>
                                    <i class="fas fa-calendar me-1"></i>{{ $actualite->published_at->format('d/m/Y') }}
                                </div>
                                <div>
                                    <i class="fas fa-eye me-1"></i>{{ $actualite->vues }} vues
                                </div>
                            </div>
                            <a href="{{ route('public.actualites.show', $actualite->id) }}" class="btn btn-primary mt-3 w-100">
                                Lire la suite <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune actualité disponible</h5>
                        <p class="text-muted">Revenez bientôt pour découvrir nos dernières actualités.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Historique des actualités -->
@if($historicItems->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="section-title text-start mb-1" style="font-size:2rem;">
                    <i class="fas fa-history me-2" style="color:var(--secondary-color);"></i>Historique
                </h2>
                <p class="text-muted mb-0">Articles précédents</p>
            </div>
            <button class="btn btn-outline-secondary" id="toggle-historique" onclick="document.getElementById('historique-list').classList.toggle('d-none'); this.querySelector('i').classList.toggle('fa-chevron-down'); this.querySelector('i').classList.toggle('fa-chevron-up');">
                <i class="fas fa-chevron-down me-1"></i> Afficher
            </button>
        </div>
        <div id="historique-list" class="d-none">
            <div class="row">
                @foreach($historicItems as $actualite)
                    <div class="col-12 mb-3 actualite-item" data-category="{{ $actualite->categorie }}">
                        <a href="{{ route('public.actualites.show', $actualite->id) }}" class="text-decoration-none">
                            <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm" style="transition:all 0.3s ease;" onmouseover="this.style.transform='translateX(5px)';this.style.boxShadow='0 5px 20px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                                <div class="flex-shrink-0" style="width:80px;height:60px;border-radius:10px;overflow:hidden;">
                                    @if($actualite->image)
                                        <img src="{{ $actualite->image }}" alt="{{ $actualite->titre }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <div style="width:100%;height:100%;background:linear-gradient(135deg,#22c55e,#16a34a);display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-newspaper" style="color:rgba(255,255,255,0.6);"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" style="color:var(--primary-color);font-weight:600;">{{ $actualite->titre }}</h6>
                                    <div class="d-flex gap-3" style="font-size:0.8rem;color:#888;">
                                        <span><i class="fas fa-tag me-1"></i>{{ ucfirst(str_replace('_', ' ', $actualite->categorie)) }}</span>
                                        <span><i class="fas fa-calendar me-1"></i>{{ $actualite->published_at->format('d/m/Y') }}</span>
                                        <span><i class="fas fa-eye me-1"></i>{{ $actualite->vues }} vues</span>
                                        @if($actualite->youtube_url)<span class="text-danger"><i class="fab fa-youtube me-1"></i>Vidéo</span>@endif
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chevron-right" style="color:#ccc;"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
    // Filtrage par catégorie
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            const category = this.dataset.category;
            const items = document.querySelectorAll('.actualite-item');
            
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Actualisation automatique des statistiques
    function updateStats() {
        fetch('/actualites/stats')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('total-actualites').textContent = data.total;
                    document.getElementById('featured-actualites').textContent = data.featured;
                    document.getElementById('this-week-actualites').textContent = data.this_week;
                    document.getElementById('last-update').textContent = new Date(data.derniere_mise_a_jour).toLocaleString('fr-FR');
                }
            })
            .catch(error => {
                console.log('Erreur lors de la mise à jour des statistiques:', error);
            });
    }

    // Actualisation toutes les 30 secondes
    setInterval(updateStats, 30000);

    // Animation des cartes au scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    // Observer toutes les cartes d'actualités
    document.querySelectorAll('.news-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>
@endpush
