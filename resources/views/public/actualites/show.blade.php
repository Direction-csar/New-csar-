@extends('layouts.public')

@section('title', $actualite->titre . ' - Actualités CSAR')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
        margin-top: -2rem;
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
    
    .return-button {
        background: rgba(255,255,255,0.2);
        border: 2px solid white;
        color: white;
        padding: 10px 25px;
        border-radius: 50px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .return-button:hover {
        background: white;
        color: #2c3e50;
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    
    .article-header {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        margin-top: -50px;
        position: relative;
        z-index: 10;
        box-shadow: 0 10px 40px rgba(0,0,0,0.12);
    }
    
    .article-image {
        width: 100%;
        height: 450px;
        object-fit: cover;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }
    
    .video-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }
    
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 20px;
    }
    
    .article-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        border-left: 5px solid #3498db;
    }
    
    .article-meta > div {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .article-content {
        font-size: 1.125rem;
        line-height: 1.9;
        color: #2c3e50;
        margin-bottom: 3rem;
    }
    
    .article-content h1, 
    .article-content h2, 
    .article-content h3 {
        color: #2c3e50;
        margin-top: 2.5rem;
        margin-bottom: 1.25rem;
        font-weight: 700;
    }
    
    .article-content p {
        margin-bottom: 1.5rem;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 15px;
        margin: 2rem 0;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .document-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 20px;
        padding: 2.5rem;
        margin: 3rem 0;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        border-left: 6px solid #27ae60;
        transition: all 0.3s ease;
    }
    
    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    
    .document-preview {
        width: 100%;
        height: 220px;
        background: #ecf0f1;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .document-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .document-preview .document-icon {
        font-size: 5rem;
        color: #e74c3c;
    }
    
    .download-button {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        padding: 14px 30px;
        border-radius: 50px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 1.05rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
    }
    
    .download-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        color: white;
    }
    
    .related-articles {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 25px;
        padding: 3rem;
        margin-top: 4rem;
    }
    
    .related-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        height: 100%;
    }
    
    .related-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }
    
    .related-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    .related-content {
        padding: 1.5rem;
    }
    
    .related-content h5 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #2c3e50;
    }
    
    .related-content a {
        text-decoration: none;
        color: inherit;
    }
    
    .related-content a:hover {
        color: #3498db;
    }
    
    @media (max-width: 768px) {
        .hero-section {
            padding: 40px 0 30px;
        }
        
        .article-header {
            padding: 2rem 1.5rem;
            margin-top: -30px;
        }
        
        .article-image {
            height: 250px;
        }
        
        .article-meta {
            gap: 1rem;
            font-size: 0.9rem;
        }
        
        .article-content {
            font-size: 1rem;
        }
        
        .document-card {
            padding: 1.5rem;
        }
        
        .related-articles {
            padding: 2rem 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="hero-section">
    <div class="container hero-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-5 fw-bold mb-0">{{ $actualite->titre }}</h1>
            <a href="{{ route('public.actualites') }}" class="return-button">
                <i class="fas fa-arrow-left"></i> Retour aux actualités
            </a>
        </div>
        <p class="lead opacity-90 mb-0">Actualité CSAR - {{ $actualite->published_at ? $actualite->published_at->format('d/m/Y') : $actualite->created_at->format('d/m/Y') }}</p>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="article-header">
                @php
                    // Déterminer ce qu'il faut afficher selon le choix de couverture
                    $showVideo = false;
                    $showImage = false;
                    
                    switch($actualite->cover_choice ?? 'auto') {
                        case 'video':
                            $showVideo = !empty($actualite->youtube_url);
                            break;
                        case 'image':
                            $showImage = !empty($actualite->image);
                            break;
                        case 'auto':
                        default:
                            // Priorité à la vidéo si elle existe, sinon image
                            $showVideo = !empty($actualite->youtube_url);
                            $showImage = !$showVideo && !empty($actualite->image);
                            break;
                    }
                @endphp

                <!-- Image de couverture -->
                @if($showImage && $actualite->image)
                    <img src="{{ $actualite->image }}" alt="{{ $actualite->titre }}" class="article-image">
                @endif

                <!-- Vidéo -->
                @if($showVideo && $actualite->youtube_url)
                    <div class="video-container">
                        @php
                            // Convertir l'URL YouTube en URL d'embed
                            $embedUrl = $actualite->youtube_url;
                            if (strpos($embedUrl, 'youtube.com/watch?v=') !== false) {
                                $embedUrl = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $embedUrl);
                            } elseif (strpos($embedUrl, 'youtu.be/') !== false) {
                                $embedUrl = str_replace('youtu.be/', 'youtube.com/embed/', $embedUrl);
                            }
                            // Supprimer les paramètres supplémentaires
                            $embedUrl = strtok($embedUrl, '&');
                            // Ajouter si besoin https://
                            if (!str_starts_with($embedUrl, 'http')) {
                                $embedUrl = 'https://' . ltrim($embedUrl, '//');
                            }
                        @endphp
                        <iframe src="{{ $embedUrl }}" 
                                title="{{ $actualite->titre }}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    </div>
                @endif

                <!-- Article Meta -->
                <div class="article-meta">
                    <div>
                        <i class="fas fa-user text-primary"></i>
                        <strong>{{ $actualite->auteur }}</strong>
                    </div>
                    <div>
                        <i class="fas fa-calendar text-primary"></i>
                        {{ $actualite->published_at ? $actualite->published_at->format('d/m/Y à H:i') : $actualite->created_at->format('d/m/Y à H:i') }}
                    </div>
                    <div>
                        <i class="fas fa-eye text-primary"></i>
                        {{ $actualite->vues }} vues
                    </div>
                    <div>
                        <span class="badge bg-primary px-3 py-2">{{ ucfirst(str_replace('_', ' ', $actualite->categorie)) }}</span>
                    </div>
                </div>

                <!-- Article Content -->
                <div class="article-content">
                    {!! $actualite->contenu !!}
                </div>

                <!-- Document associé -->
                @if($actualite->document_file)
                    <div class="document-card">
                        <h4 class="mb-4 fw-bold">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            Document associé
                        </h4>
                        
                        <div class="document-preview">
                            @php
                                $extension = strtolower(pathinfo($actualite->document_file, PATHINFO_EXTENSION));
                            @endphp
                            
                            @if($actualite->document_cover_image && \Storage::disk('public')->exists($actualite->document_cover_image))
                                <!-- Afficher l'image de couverture personnalisée -->
                                <img src="{{ asset('storage/' . $actualite->document_cover_image) }}" 
                                     alt="Couverture du document">
                            @elseif($extension === 'pdf')
                                <!-- Afficher l'icône PDF par défaut -->
                                <div class="document-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                            @else
                                <!-- Pour d'autres types de documents, afficher l'icône -->
                                <div class="document-icon">
                                    <i class="fas fa-file-alt text-secondary"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="mb-2 fw-bold">{{ $actualite->document_title ?? 'Document associé' }}</h5>
                                <small class="text-muted">
                                    <i class="fas fa-download me-2"></i>
                                    {{ $actualite->downloads_count ?? 0 }} téléchargement(s)
                                </small>
                            </div>
                            <a href="{{ route('public.actualites.download', $actualite->id) }}" 
                               class="download-button"
                               target="_blank">
                                <i class="fas fa-download"></i>
                                Télécharger
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Actions sociales -->
                <div class="d-flex gap-3 flex-wrap mt-4 pt-4" style="border-top: 1px solid #e5e7eb;">
                    <button class="btn btn-outline-danger" id="likeBtn" onclick="toggleLike()">
                        <i class="far fa-heart me-2"></i>Aimer
                    </button>
                    <a href="#comments" class="btn btn-outline-primary">
                        <i class="far fa-comment me-2"></i>Commenter ({{ isset($comments) ? $comments->count() : 0 }})
                    </a>
                    <button class="btn btn-outline-success" onclick="shareArticle()">
                        <i class="fas fa-share-alt me-2"></i>Partager
                    </button>
                </div>
            </div>

            <!-- Section Commentaires -->
            <div id="comments" style="background: white; border-radius: 20px; padding: 2.5rem; margin-top: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 30px;">
                    <i class="fas fa-comments me-2" style="color: #3498db;"></i>
                    Commentaires ({{ isset($comments) ? $comments->count() : 0 }})
                </h3>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Formulaire d'ajout de commentaire -->
                <div style="background: #f8f9fa; border-radius: 15px; padding: 25px; margin-bottom: 30px;">
                    <h5 style="font-weight: 600; margin-bottom: 20px; color: #1f2937;">
                        <i class="fas fa-pen me-2" style="color: #22c55e;"></i>
                        Laisser un commentaire
                    </h5>
                    <form action="{{ route('news.comment.store', ['locale' => app()->getLocale(), 'id' => $actualite->id]) }}" method="POST">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <input type="text" name="author_name" class="form-control" placeholder="Votre nom *" required
                                       style="border-radius: 10px; padding: 12px 15px; border: 2px solid #e5e7eb;">
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="author_email" class="form-control" placeholder="Votre email *" required
                                       style="border-radius: 10px; padding: 12px 15px; border: 2px solid #e5e7eb;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea name="content" class="form-control" rows="4" placeholder="Votre commentaire..." required
                                      style="border-radius: 10px; padding: 12px 15px; border: 2px solid #e5e7eb; resize: vertical;"></textarea>
                        </div>
                        <button type="submit" class="btn" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 12px 30px; border-radius: 10px; font-weight: 600; border: none;">
                            <i class="fas fa-paper-plane me-2"></i>Publier le commentaire
                        </button>
                    </form>
                </div>

                <!-- Liste des commentaires -->
                @if(isset($comments) && $comments->count() > 0)
                    @foreach($comments as $comment)
                        <div class="comment-item" style="background: white; border: 1px solid #e5e7eb; border-radius: 15px; padding: 20px; margin-bottom: 15px; transition: all 0.3s ease;" id="comment-{{ $comment->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #3498db, #2ecc71); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem;">
                                        {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 style="margin: 0; font-weight: 600; color: #1f2937;">{{ $comment->author_name }}</h6>
                                        <small style="color: #9ca3af;">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $comment->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                
                                @auth
                                    <button onclick="deleteComment({{ $comment->id }})" class="btn btn-sm btn-outline-danger" title="Supprimer ce commentaire" style="border-radius: 8px;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @endauth
                            </div>
                            <p style="color: #4b5563; line-height: 1.7; margin: 0; padding-left: 58px;">{{ $comment->content }}</p>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                        <i class="far fa-comment-dots" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                        <p style="font-size: 1.1rem;">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
                    </div>
                @endif
            </div>

            <!-- Actualités similaires -->
            @if(isset($related) && $related->count() > 0)
                <div class="related-articles">
                    <h3 class="mb-4 fw-bold text-center">
                        <i class="fas fa-newspaper me-2 text-primary"></i>
                        Actualités similaires
                    </h3>
                    <div class="row g-4">
                        @foreach($related as $relatedArticle)
                            <div class="col-md-4">
                                <div class="related-card">
                                    @if($relatedArticle->image)
                                        <img src="{{ $relatedArticle->image }}" 
                                             alt="{{ $relatedArticle->titre }}" 
                                             class="related-image">
                                    @else
                                        <div class="related-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                    @endif
                                    <div class="related-content">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $relatedArticle->published_at ? $relatedArticle->published_at->format('d/m/Y') : $relatedArticle->created_at->format('d/m/Y') }}
                                        </small>
                                        <h5 class="mt-2">
                                            <a href="{{ route('public.actualites.show', $relatedArticle->id) }}">
                                                {{ $relatedArticle->titre }}
                                            </a>
                                        </h5>
                                        <span class="badge bg-secondary mt-2">
                                            {{ ucfirst($relatedArticle->categorie) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: '{{ addslashes($actualite->titre) }}',
            url: window.location.href
        }).catch(function(err) { console.log(err); });
    } else {
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Lien copié dans le presse-papiers !');
        });
    }
}

function toggleLike() {
    var btn = document.getElementById('likeBtn');
    var icon = btn.querySelector('i');
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-danger');
        btn.innerHTML = '<i class="fas fa-heart me-2"></i>Aimé';
    } else {
        icon = btn.querySelector('i');
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
        btn.innerHTML = '<i class="far fa-heart me-2"></i>Aimer';
    }
}

function deleteComment(commentId) {
    if (!confirm('Voulez-vous vraiment supprimer ce commentaire ?')) return;
    
    fetch('/admin/news-comments/' + commentId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            var el = document.getElementById('comment-' + commentId);
            el.style.transition = 'all 0.3s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(50px)';
            setTimeout(function() { el.remove(); }, 300);
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(function(err) { alert('Erreur lors de la suppression.'); });
}
</script>
@endpush
@endsection
