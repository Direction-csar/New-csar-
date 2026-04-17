@extends('layouts.public')

@section('title', $news->title)

@section('content')
<div class="container-fluid py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('news') }}">Actualités</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $news->title }}</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Article principal -->
            <div class="col-lg-8">
                <article class="news-article">
                    <!-- En-tête de l'article -->
                    <header class="article-header">
                        <div class="article-meta">
                            <span class="article-category">
                                <i class="fas fa-tag me-1"></i>
                                {{ ucfirst($news->category ?? 'Actualité') }}
                            </span>
                            <span class="article-date">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $news->created_at->format('d/m/Y') }}
                            </span>
                            @if($news->author)
                            <span class="article-author">
                                <i class="fas fa-user me-1"></i>
                                {{ $news->author->name }}
                            </span>
                            @endif
                            <span class="article-views">
                                <i class="fas fa-eye me-1"></i>
                                {{ $news->views_count ?? 0 }} vues
                            </span>
                        </div>
                        
                        <h1 class="article-title">{{ $news->title }}</h1>
                        
                        @if($news->excerpt)
                        <p class="article-excerpt">{{ $news->excerpt }}</p>
                        @endif
                    </header>

                    <!-- Image mise en avant -->
                    @if($news->featured_image)
                    <div class="article-image">
                        <img src="{{ asset('storage/' . $news->featured_image) }}" alt="{{ $news->title }}" class="img-fluid">
                    </div>
                    @endif

                    <!-- Contenu de l'article -->
                    <div class="article-content">
                        {!! $news->content !!}
                    </div>

                    <!-- Tags -->
                    @if($news->tags && count($news->tags) > 0)
                    <div class="article-tags">
                        <h6 class="tags-title">Tags :</h6>
                        <div class="tags-list">
                            @foreach($news->tags as $tag)
                            <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="article-actions">
                        <button class="btn btn-outline-primary" onclick="shareArticle()">
                            <i class="fas fa-share me-2"></i>Partager
                        </button>
                        <button class="btn btn-outline-secondary" onclick="printArticle()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Articles liés -->
                @if($relatedNews && count($relatedNews) > 0)
                <div class="sidebar-section">
                    <h4 class="sidebar-title">
                        <i class="fas fa-newspaper me-2"></i>Articles liés
                    </h4>
                    <div class="related-articles">
                        @foreach($relatedNews as $related)
                        <div class="related-article">
                            <div class="related-image">
                                @if($related->featured_image)
                                <img src="{{ asset('storage/' . $related->featured_image) }}" alt="{{ $related->title }}" class="img-fluid">
                                @else
                                <div class="related-image-placeholder">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                @endif
                            </div>
                            <div class="related-content">
                                <h5 class="related-title">
                                    <a href="{{ route('news.show', ['locale' => 'fr', 'id' => $related->id]) }}">{{ $related->title }}</a>
                                </h5>
                                <p class="related-date">{{ $related->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Newsletter -->
                <div class="sidebar-section">
                    <div class="newsletter-card">
                        <h4 class="newsletter-title">
                            <i class="fas fa-envelope me-2"></i>Newsletter
                        </h4>
                        <p class="newsletter-description">Restez informé de nos dernières actualités</p>
                        <form id="newsletterForm" class="newsletter-form">
                            @csrf
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Votre email" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Votre nom (optionnel)">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>S'abonner
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="sidebar-section">
                    <div class="stats-card">
                        <h4 class="stats-title">
                            <i class="fas fa-chart-bar me-2"></i>Statistiques
                        </h4>
                        <div class="stats-list">
                            <div class="stat-item">
                                <span class="stat-label">Vues de cet article</span>
                                <span class="stat-value">{{ $news->views_count ?? 0 }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Date de publication</span>
                                <span class="stat-value">{{ $news->created_at->format('d/m/Y') }}</span>
                            </div>
                            @if($news->updated_at && $news->updated_at != $news->created_at)
                            <div class="stat-item">
                                <span class="stat-label">Dernière mise à jour</span>
                                <span class="stat-value">{{ $news->updated_at->format('d/m/Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.news-article {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.article-header {
    padding: 2rem;
    border-bottom: 1px solid #eee;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.article-meta span {
    display: flex;
    align-items: center;
}

.article-title {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.article-excerpt {
    font-size: 1.1rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: 0;
}

.article-image {
    margin-bottom: 2rem;
}

.article-image img {
    width: 100%;
    height: auto;
    border-radius: 0;
}

.article-content {
    padding: 2rem;
    line-height: 1.8;
    color: #333;
}

.article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6 {
    color: #333;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.article-content p {
    margin-bottom: 1.5rem;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.article-tags {
    padding: 0 2rem 2rem;
}

.tags-title {
    color: #333;
    margin-bottom: 1rem;
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.tag {
    background: #f8f9fa;
    color: #667eea;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
    border: 1px solid #e9ecef;
}

.article-actions {
    padding: 2rem;
    border-top: 1px solid #eee;
    display: flex;
    gap: 1rem;
}

.sidebar-section {
    margin-bottom: 2rem;
}

.sidebar-title {
    color: #333;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.related-articles {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.related-article {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.related-article:hover {
    transform: translateY(-2px);
}

.related-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.related-content {
    flex: 1;
}

.related-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.related-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.related-title a:hover {
    color: #667eea;
}

.related-date {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.newsletter-card, .stats-card {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.newsletter-title, .stats-title {
    color: #333;
    margin-bottom: 1rem;
    font-weight: 600;
}

.newsletter-description {
    color: #666;
    margin-bottom: 1.5rem;
}

.stats-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.stat-value {
    color: #333;
    font-weight: 600;
}

@media (max-width: 768px) {
    .article-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .article-title {
        font-size: 1.5rem;
    }
    
    .article-actions {
        flex-direction: column;
    }
    
    .related-article {
        flex-direction: column;
    }
    
    .related-image {
        width: 100%;
        height: 150px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Incrémenter le compteur de vues
        fetch('{{ route("news.show", ['locale' => 'fr', 'id' => $news->id]) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    });

    // Gestion du formulaire de newsletter
    document.getElementById('newsletterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("newsletter.subscribe", ["locale" => app()->getLocale()]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Inscription à la newsletter réussie !');
                this.reset();
            } else {
                alert('Erreur lors de l\'inscription : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'inscription à la newsletter');
        });
    });
});

function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $news->title }}',
            text: '{{ $news->excerpt ?? Str::limit(strip_tags($news->content), 150) }}',
            url: window.location.href
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Lien copié dans le presse-papiers !');
        });
    }
}

function printArticle() {
    window.print();
}
</script>
@endsection