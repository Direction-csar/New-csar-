@extends('layouts.public')

@section('title', 'Actualités CSAR')

@section('content')
@php
    $totalNews = \App\Models\News::where('is_published', true)->where('is_public', true)->count();
    $featuredNews = \App\Models\News::where('is_published', true)->where('is_public', true)->where('is_featured', true)->count();
    $categoriesCount = \App\Models\News::where('is_published', true)->where('is_public', true)->distinct('category')->count('category');
    $allCategories = \App\Models\News::where('is_published', true)->where('is_public', true)->select('category', \DB::raw('count(*) as total'))->groupBy('category')->get();
@endphp

<!-- Hero Section -->
<section style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding: 60px 0; margin-bottom: 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align: center; color: white;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 12px;">Actualités CSAR</h1>
            <p style="font-size: 1rem; opacity: 0.9;">Restez informé des dernières actualités du Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>
    </div>
</section>

<!-- Statistiques -->
<section style="background: #f8fafc; padding: 40px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;" class="stat-card">
                    <div style="font-size: 2.5rem; font-weight: 800; color: #2563eb; margin-bottom: 8px;">{{ $totalNews }}</div>
                    <div style="color: #6b7280; font-size: 0.9rem; font-weight: 500;">
                        <i class="fas fa-newspaper" style="margin-right: 6px;"></i>Actualités totales
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;" class="stat-card">
                    <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b; margin-bottom: 8px;">{{ $featuredNews }}</div>
                    <div style="color: #6b7280; font-size: 0.9rem; font-weight: 500;">
                        <i class="fas fa-star" style="margin-right: 6px;"></i>À la une
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;" class="stat-card">
                    <div style="font-size: 2.5rem; font-weight: 800; color: #3b82f6; margin-bottom: 8px;">{{ $featuredNews }}</div>
                    <div style="color: #6b7280; font-size: 0.9rem; font-weight: 500;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 6px;"></i>Carte associée
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;" class="stat-card">
                    <div style="font-size: 2.5rem; font-weight: 800; color: #22c55e; margin-bottom: 8px;">{{ $categoriesCount }}</div>
                    <div style="color: #6b7280; font-size: 0.9rem; font-weight: 500;">
                        <i class="fas fa-folder" style="margin-right: 6px;"></i>Catégories
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contenu Principal -->
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 60px 20px;">
    
    <!-- Bannière Newsletter -->
    <div style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 15px; padding: 25px 30px; margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 8px 25px rgba(6, 182, 212, 0.2);">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 12px;">
                <i class="fas fa-envelope" style="font-size: 2rem; color: white;"></i>
            </div>
            <div>
                <h3 style="color: white; font-size: 1.3rem; font-weight: 700; margin: 0 0 5px 0;">Abonnez-vous à notre newsletter</h3>
                <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 0.95rem;">Recevez les dernières actualités directement dans votre boîte mail</p>
            </div>
        </div>
        <a href="#" style="background: white; color: #0891b2; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; white-space: nowrap; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            S'ABONNER GRATUITEMENT
        </a>
    </div>
    
    <!-- Filtres par catégorie -->
    <div style="margin-bottom: 40px;">
        <h3 style="font-size: 1.2rem; font-weight: 600; color: #1f2937; margin-bottom: 20px; text-align: center;">Filtrer par catégorie</h3>
        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <button class="category-filter active" data-category="all" style="background: #2563eb; color: white; border: none; padding: 10px 24px; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);">
                Tous <span style="background: rgba(255,255,255,0.3); padding: 2px 10px; border-radius: 12px; margin-left: 8px; font-size: 0.85rem;">{{ $totalNews }}</span>
            </button>
            @foreach($allCategories as $cat)
            <button class="category-filter" data-category="{{ $cat->category }}" style="background: white; color: #1f2937; border: 2px solid #e5e7eb; padding: 10px 24px; border-radius: 25px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                {{ ucfirst($cat->category) }} <span style="background: #f3f4f6; padding: 2px 10px; border-radius: 12px; margin-left: 8px; font-size: 0.85rem;">{{ $cat->total }}</span>
            </button>
            @endforeach
        </div>
    </div>
    
    <!-- Section À la une -->
    @php
        $featuredArticles = \App\Models\News::where('is_published', true)
            ->where('is_public', true)
            ->where('is_featured', true)
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();
    @endphp
    
    @if($featuredArticles->count() > 0)
    <section style="margin-bottom: 60px;">
        <h2 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 30px; text-align: center;">À la une</h2>
        <p style="text-align: center; color: #6b7280; margin-bottom: 40px; font-size: 0.95rem;">Il ne reste que 8 jours pour le projet en cours</p>
        
        <div class="row g-4">
            @foreach($featuredArticles as $article)
            <div class="col-md-6 news-item" data-category="{{ $article->category }}">
                <div class="news-card" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; height: 100%;">
                    <!-- Badge catégorie -->
                    <div style="position: absolute; top: 15px; left: 15px; z-index: 10;">
                        <span style="background: #f59e0b; color: white; padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                            {{ ucfirst($article->category ?? 'Actualité') }}
                        </span>
                    </div>
                    
                    <!-- Image -->
                    <div style="position: relative; height: 220px; overflow: hidden;">
                        @if($article->featured_image)
                            @php
                                $imagePath = trim((string) $article->featured_image);
                                if (preg_match('/^https?:\/\//i', $imagePath)) {
                                    $imageUrl = $imagePath;
                                } else {
                                    $imageUrl = asset($imagePath);
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $article->title }}" 
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                                 onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                        @else
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2563eb, #1e40af); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-newspaper" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Contenu -->
                    <div style="padding: 25px;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: #1f2937; margin-bottom: 12px; line-height: 1.4;">
                            <a href="{{ route('news.show', ['locale' => 'fr', 'id' => $article->id]) }}" 
                               style="color: #1f2937; text-decoration: none; transition: color 0.3s ease;">
                                {{ $article->title }}
                            </a>
                        </h3>
                        
                        <p style="color: #6b7280; line-height: 1.6; margin-bottom: 20px; font-size: 0.9rem;">
                            {{ Str::limit($article->excerpt ?? strip_tags($article->content), 120) }}
                        </p>
                        
                        <!-- Métadonnées -->
                        <div style="display: flex; align-items: center; gap: 15px; color: #9ca3af; font-size: 0.85rem; margin-bottom: 15px;">
                            <span>
                                <i class="fas fa-calendar-alt" style="margin-right: 5px;"></i>
                                {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                            </span>
                            <span>
                                <i class="fas fa-eye" style="margin-right: 5px;"></i>
                                {{ $article->views_count ?? 0 }} vues
                            </span>
                        </div>
                        
                        <!-- Boutons sociaux -->
                        <div style="display: flex; gap: 10px; padding-top: 15px; border-top: 1px solid #f3f4f6;">
                            <button onclick="likeArticle({{ $article->id }})" class="social-btn" style="flex: 1; background: transparent; border: 1px solid #e5e7eb; color: #6b7280; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; font-weight: 500;">
                                <i class="far fa-heart"></i>
                                <span>Aimer</span>
                            </button>
                            <button onclick="window.location.href='{{ route('news.show', ['locale' => 'fr', 'id' => $article->id]) }}#comments'" class="social-btn" style="flex: 1; background: transparent; border: 1px solid #e5e7eb; color: #6b7280; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; font-weight: 500;">
                                <i class="far fa-comment"></i>
                                <span>Commenter</span>
                            </button>
                            <button onclick="shareArticle('{{ $article->title }}', '{{ route('news.show', ['locale' => 'fr', 'id' => $article->id]) }}')" class="social-btn" style="flex: 1; background: transparent; border: 1px solid #e5e7eb; color: #6b7280; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; font-weight: 500;">
                                <i class="fas fa-share-alt"></i>
                                <span>Partager</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
    
    <!-- Section Toutes les actualités -->
    <section>
        <h2 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 40px; text-align: center;">Toutes les actualités</h2>
        <p style="text-align: center; color: #6b7280; margin-bottom: 50px;">Retrouvez toutes nos actualités et restez informé de nos actions</p>
        
        <div class="row g-4" id="allNewsList">
            @forelse($news as $article)
            <div class="col-md-6 news-item" data-category="{{ $article->category }}">
                <div class="news-card" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; height: 100%;">
                    <!-- Badge catégorie -->
                    <div style="position: absolute; top: 15px; left: 15px; z-index: 10;">
                        <span style="background: #22c55e; color: white; padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);">
                            {{ ucfirst($article->category ?? 'Actualité') }}
                        </span>
                    </div>
                    
                    <!-- Image -->
                    <div style="position: relative; height: 220px; overflow: hidden;">
                        @if($article->featured_image)
                            @php
                                $imagePath = trim((string) $article->featured_image);
                                if (preg_match('/^https?:\/\//i', $imagePath)) {
                                    $imageUrl = $imagePath;
                                } else {
                                    $imageUrl = asset($imagePath);
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" alt="{{ $article->title }}" 
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                                 onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                        @else
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #22c55e, #16a34a); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-newspaper" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Contenu -->
                    <div style="padding: 25px;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: #1f2937; margin-bottom: 12px; line-height: 1.4;">
                            <a href="{{ route('news.show', ['locale' => 'fr', 'id' => $article->id]) }}" 
                               style="color: #1f2937; text-decoration: none; transition: color 0.3s ease;">
                                {{ $article->title }}
                            </a>
                        </h3>
                        
                        <p style="color: #6b7280; line-height: 1.6; margin-bottom: 20px; font-size: 0.9rem;">
                            {{ Str::limit($article->excerpt ?? strip_tags($article->content), 120) }}
                        </p>
                        
                        <!-- Métadonnées -->
                        <div style="display: flex; align-items: center; gap: 15px; color: #9ca3af; font-size: 0.85rem; margin-bottom: 15px;">
                            <span>
                                <i class="fas fa-calendar-alt" style="margin-right: 5px;"></i>
                                {{ $article->published_at ? $article->published_at->format('d M Y') : $article->created_at->format('d M Y') }}
                            </span>
                            <span>
                                <i class="fas fa-eye" style="margin-right: 5px;"></i>
                                {{ $article->views_count ?? 0 }} vues
                            </span>
                        </div>
                        
                        <!-- Boutons sociaux -->
                        <div style="display: flex; gap: 10px; padding-top: 15px; border-top: 1px solid #f3f4f6;">
                            <button onclick="likeArticle({{ $article->id }})" class="social-btn" style="flex: 1; background: transparent; border: 1px solid #e5e7eb; color: #6b7280; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; font-weight: 500;">
                                <i class="far fa-heart"></i>
                                <span>Aimer</span>
                            </button>
                            <button onclick="window.location.href='{{ route('news.show', ['locale' => 'fr', 'id' => $article->id]) }}#comments'" class="social-btn" style="flex: 1; background: transparent; border: 1px solid #e5e7eb; color: #6b7280; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; font-weight: 500;">
                                <i class="far fa-comment"></i>
                                <span>Commenter</span>
                            </button>
                            <button onclick="shareArticle('{{ $article->title }}', '{{ route('news.show', ['locale' => 'fr', 'id' => $article->id]) }}')" class="social-btn" style="flex: 1; background: transparent; border: 1px solid #e5e7eb; color: #6b7280; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9rem; font-weight: 500;">
                                <i class="fas fa-share-alt"></i>
                                <span>Partager</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div style="background: white; border-radius: 15px; padding: 60px 30px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <i class="fas fa-newspaper" style="font-size: 4rem; color: #d1d5db; margin-bottom: 20px;"></i>
                    <h3 style="color: #6b7280; font-size: 1.5rem; margin-bottom: 10px;">Aucune actualité disponible</h3>
                    <p style="color: #9ca3af;">Revenez bientôt pour découvrir nos dernières actualités.</p>
                </div>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($news && $news->hasPages())
        <div style="margin-top: 50px; display: flex; justify-content: center;">
            {{ $news->links() }}
        </div>
        @endif
    </section>
    
    <!-- Section Newsletter bas de page -->
    <div style="background: white; border-radius: 15px; padding: 50px 40px; margin-top: 80px; text-align: center; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
        <i class="fas fa-bell" style="font-size: 3.5rem; color: #2563eb; margin-bottom: 20px;"></i>
        <h3 style="font-size: 1.8rem; font-weight: 700; color: #1f2937; margin-bottom: 15px;">Soyez alerté des nouveautés importantes</h3>
        <p style="color: #6b7280; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
            Inscrivez-vous pour recevoir des notifications par email sur nos dernières actualités
        </p>
        <a href="#" style="background: #2563eb; color: white; padding: 14px 35px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);">
            S'inscrire maintenant
        </a>
    </div>
</div>

<style>
/* Statistiques */
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
}

/* Filtres catégories */
.category-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

.category-filter.active {
    background: #2563eb !important;
    color: white !important;
    border-color: #2563eb !important;
}

/* Articles */
.news-card {
    position: relative;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.news-card:hover img {
    transform: scale(1.1);
}

.news-card h3 a:hover {
    color: #2563eb !important;
}

.news-card a[href*="news.show"]:hover {
    background: #16a34a !important;
    transform: scale(1.05);
}

/* Newsletter */
.newsletter-banner:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6, 182, 212, 0.3) !important;
}

/* Boutons sociaux */
.social-btn:hover {
    background: #f3f4f6 !important;
    border-color: #22c55e !important;
    color: #22c55e !important;
    transform: translateY(-2px);
}

.social-btn:active {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 15px;
    }
    
    .newsletter-banner {
        flex-direction: column !important;
        text-align: center;
        gap: 20px;
    }
    
    .newsletter-banner a {
        width: 100%;
    }
    
    .category-filter {
        font-size: 0.85rem;
        padding: 8px 16px;
    }
    
    .social-btn span {
        display: none;
    }
    
    .social-btn {
        padding: 8px !important;
    }
}

.news-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.news-card:hover .news-image img {
    transform: scale(1.05);
}

.news-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.news-category {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.news-content {
    padding: 1.5rem;
}

.news-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.news-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.news-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.news-title a:hover {
    color: #667eea;
}

.news-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.news-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.news-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.news-views {
    color: #666;
    font-size: 0.9rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .news-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .news-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryButtons = document.querySelectorAll('.category-filter');
    const newsItems = document.querySelectorAll('.news-item');

    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const selectedCategory = this.dataset.category;
            
            // Mettre à jour les boutons actifs
            categoryButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = '#1f2937';
                btn.style.borderColor = '#e5e7eb';
            });
            
            this.classList.add('active');
            this.style.background = '#2563eb';
            this.style.color = 'white';
            this.style.borderColor = '#2563eb';
            
            // Filtrer les articles
            newsItems.forEach(item => {
                if (selectedCategory === 'all' || item.dataset.category === selectedCategory) {
                    item.style.display = 'block';
                    // Animation d'apparition
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.style.transition = 'opacity 0.4s ease';
                        item.style.opacity = '1';
                    }, 50);
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

// Fonction pour aimer un article
function likeArticle(articleId) {
    const btn = event.target.closest('.social-btn');
    const icon = btn.querySelector('i');
    
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.style.color = '#ef4444';
        btn.style.borderColor = '#ef4444';
        
        // Animation
        icon.style.transform = 'scale(1.3)';
        setTimeout(() => {
            icon.style.transform = 'scale(1)';
        }, 200);
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        btn.style.color = '#6b7280';
        btn.style.borderColor = '#e5e7eb';
    }
}

// Fonction pour partager un article
function shareArticle(title, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(err => console.log('Erreur de partage:', err));
    } else {
        // Fallback: copier le lien
        navigator.clipboard.writeText(url).then(() => {
            alert('Lien copié dans le presse-papiers !');
        });
    }
}
</script>
@endsection