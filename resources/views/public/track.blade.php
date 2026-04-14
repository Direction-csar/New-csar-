@extends('layouts.public')

@section('title', __('pages.track'))

@section('content')
<!-- Hero Section -->
<section class="hero fade-in" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); min-height: 50vh; display: flex; align-items: center; justify-content: center; padding: 80px 0; position: relative; overflow: hidden;">
    <!-- Motifs décoratifs animés -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div class="floating-circle" style="position: absolute; top: 10%; left: 10%; width: 80px; height: 80px; background: #fff; border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
        <div class="floating-circle" style="position: absolute; top: 20%; right: 15%; width: 60px; height: 60px; background: #fff; border-radius: 50%; animation: float 8s ease-in-out infinite reverse;"></div>
        <div class="floating-circle" style="position: absolute; bottom: 30%; left: 20%; width: 100px; height: 100px; background: #fff; border-radius: 50%; animation: float 7s ease-in-out infinite;"></div>
    </div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
        <h1 class="main-title animated-title" style="font-size: 3.2rem; font-weight: 800; color: #fff; margin-bottom: 20px; letter-spacing: -1px; line-height: 1.2; text-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <span class="title-word title-word-1">Suivre</span>
            <span class="title-word title-word-2">ma</span>
            <span class="title-word title-word-3">demande</span>
        </h1>
        <p class="main-subtitle animated-subtitle" style="font-size: 1.3rem; color: #e5e7eb; max-width: 700px; margin: 0 auto; line-height: 1.6; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            Consultez l'état d'avancement de votre demande avec votre code de suivi unique
        </p>
    </div>
</section>

<!-- Track Form Section -->
<section class="section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">Rechercher ma demande</h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Entrez votre code de suivi pour consulter l'état de votre demande en temps réel
            </p>
        </div>
        
        <div style="max-width: 700px; margin: 0 auto;">
            <div class="track-card zoom-hover" style="background: #fff; border-radius: 20px; padding: 50px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                <form action="{{ route('track.request') }}" method="POST" id="trackForm">
                    @csrf
                    
                    <div style="margin-bottom: 35px;">
                        <label for="tracking_code" style="display: block; margin-bottom: 12px; font-weight: 600; font-size: 1.1rem; color: #1f2937;">
                            <i class="fas fa-barcode" style="color: #22c55e; margin-right: 8px;"></i> Code de suivi *
                        </label>
                        <div style="position: relative;">
                            <input type="text" id="tracking_code" name="tracking_code" required 
                                   placeholder="Ex: CSAR000001" 
                                   style="width: 100%; padding: 18px 20px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.1rem; text-align: center; letter-spacing: 3px; font-weight: 600; background: #f9fafb; transition: all 0.3s ease;">
                            <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af;">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <small style="color: #6b7280; margin-top: 8px; display: block; font-size: 0.95rem;">
                            <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                            Le code de suivi vous a été envoyé par SMS lors de la soumission de votre demande
                        </small>
                    </div>
                    
                    <div style="margin-bottom: 40px;">
                        <label for="phone" style="display: block; margin-bottom: 12px; font-weight: 600; font-size: 1.1rem; color: #1f2937;">
                            <i class="fas fa-phone" style="color: #3b82f6; margin-right: 8px;"></i> Numéro de téléphone (facultatif)
                        </label>
                        <input type="tel" id="phone" name="phone" 
                               placeholder="+221 77 123 45 67" 
                               style="width: 100%; padding: 18px 20px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.1rem; background: #f9fafb; transition: all 0.3s ease;">
                        <small style="color: #6b7280; margin-top: 8px; display: block; font-size: 0.95rem;">
                            <i class="fas fa-shield-alt" style="margin-right: 5px;"></i>
                            Pour une vérification supplémentaire de votre identité
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary zoom-hover" style="width: 100%; padding: 18px; font-size: 1.2rem; font-weight: 600; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);">
                        <i class="fas fa-search" style="margin-right: 10px;"></i>
                        Suivre ma demande
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Results Section -->
@if(isset($request))
<section class="section fade-in" style="background: #fff; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">Résultats de votre recherche</h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Voici les détails de votre demande et son état d'avancement
            </p>
        </div>
        
        <div style="max-width: 1000px; margin: 0 auto;">
            <div class="result-card zoom-hover" style="background: #fff; border-radius: 20px; padding: 50px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                <!-- Request Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 25px; border-bottom: 2px solid #e5e7eb;">
                    <div>
                        <h3 style="color: #1f2937; margin-bottom: 8px; font-size: 1.5rem; font-weight: 700;">Demande #{{ $request->tracking_code }}</h3>
                        <p style="color: #6b7280; margin: 0; font-size: 1.1rem;">
                            <i class="fas fa-calendar-alt" style="margin-right: 8px; color: #22c55e;"></i>
                            Soumise le {{ $request->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span class="status-badge status-{{ $request->status }}">
                            @switch($request->status)
                                @case('pending')
                                    <i class="fas fa-clock"></i> En attente
                                    @break
                                @case('approved')
                                    <i class="fas fa-check-circle"></i> Approuvée
                                    @break
                                @case('rejected')
                                    <i class="fas fa-times-circle"></i> Rejetée
                                    @break
                                @case('processing')
                                    <i class="fas fa-cogs"></i> En traitement
                                    @break
                                @default
                                    <i class="fas fa-question-circle"></i> {{ ucfirst($request->status) }}
                            @endswitch
                        </span>
                    </div>
                </div>
                
                <!-- Request Details -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
                    <div class="info-card" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 15px; padding: 30px; border: 1px solid #bbf7d0;">
                        <h4 style="color: #1f2937; margin-bottom: 20px; border-bottom: 2px solid #bbf7d0; padding-bottom: 12px; font-size: 1.3rem; font-weight: 700;">
                            <i class="fas fa-user" style="color: #22c55e; margin-right: 10px;"></i> Informations du demandeur
                        </h4>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Nom :</strong> 
                            <span style="color: #1f2937; font-weight: 600;">{{ $request->full_name }}</span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Téléphone :</strong> 
                            <span style="color: #1f2937; font-weight: 600;">{{ $request->phone }}</span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Email :</strong> 
                            <span style="color: #1f2937; font-weight: 600;">{{ $request->email }}</span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Région :</strong> 
                            <span style="color: #1f2937; font-weight: 600;">{{ $request->region }}</span>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Type :</strong> 
                            <span class="type-badge type-{{ $request->type_demande }}">
                                @php
                                    $typeLabels = [
                                        'aide_alimentaire' => '📦 Aide alimentaire',
                                        'demande_audience' => '🙋 Demande d\'audience',
                                        'information_generale' => 'ℹ️ Information générale',
                                        'autre' => '📝 Autre demande',
                                    ];
                                    echo $typeLabels[$request->type_demande] ?? ucfirst(str_replace('_', ' ', $request->type_demande));
                                @endphp
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 15px; padding: 30px; border: 1px solid #bfdbfe;">
                        <h4 style="color: #1f2937; margin-bottom: 20px; border-bottom: 2px solid #bfdbfe; padding-bottom: 12px; font-size: 1.3rem; font-weight: 700;">
                            <i class="fas fa-map-marker-alt" style="color: #3b82f6; margin-right: 10px;"></i> Localisation
                        </h4>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Adresse :</strong> 
                            <span style="color: #1f2937; font-weight: 600;">{{ $request->address }}</span>
                        </div>
                        @if($request->latitude && $request->longitude)
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #374151;">Coordonnées :</strong> 
                            <span style="color: #1f2937; font-weight: 600;">{{ $request->latitude }}, {{ $request->longitude }}</span>
                        </div>
                        <div id="map" style="height: 200px; border-radius: 12px; margin-top: 15px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); display: flex; align-items: center; justify-content: center; color: #6b7280; border: 2px solid #d1d5db;">
                            <div style="text-align: center;">
                                <i class="fas fa-map" style="font-size: 48px; margin-bottom: 10px; color: #3b82f6;"></i>
                                <br><span style="font-weight: 600;">Carte interactive</span>
                            </div>
                        </div>
                        @else
                        <div style="color: #6b7280; font-style: italic; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                            Aucune géolocalisation disponible
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Request Description -->
                <div style="margin-bottom: 40px;">
                    <h4 style="color: #1f2937; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; font-size: 1.3rem; font-weight: 700;">
                        <i class="fas fa-file-alt" style="color: #f59e0b; margin-right: 10px;"></i> Description de la demande
                    </h4>
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 25px; border-radius: 12px; border-left: 4px solid #f59e0b; border: 1px solid #fbbf24;">
                        <p style="color: #92400e; font-size: 1.1rem; line-height: 1.6; margin: 0; font-weight: 500;">
                            {{ $request->description }}
                        </p>
                    </div>
                </div>
                
                <!-- Processing Timeline -->
                <div style="margin-bottom: 40px;">
                    <h4 style="color: #1f2937; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; font-size: 1.3rem; font-weight: 700;">
                        <i class="fas fa-history" style="color: #8b5cf6; margin-right: 10px;"></i> Historique de traitement
                    </h4>
                    
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>Demande soumise</h5>
                                <p>{{ $request->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($request->status !== 'pending')
                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>Demande traitée</h5>
                                <p>{{ $request->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @else
                        <div class="timeline-item current">
                            <div class="timeline-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>En cours de traitement</h5>
                                <p>Votre demande est en cours d'examen par nos équipes</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($request->status === 'approved')
                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>Demande approuvée</h5>
                                <p>Votre demande a été approuvée et sera traitée dans les plus brefs délais</p>
                            </div>
                        </div>
                        @elseif($request->status === 'rejected')
                        <div class="timeline-item rejected">
                            <div class="timeline-icon">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>Demande rejetée</h5>
                                <p>Votre demande n'a pas pu être approuvée</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Admin Comments -->
                @if($request->admin_comment)
                <div style="margin-bottom: 40px;">
                    <h4 style="color: #1f2937; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; font-size: 1.3rem; font-weight: 700;">
                        <i class="fas fa-comment" style="color: #ef4444; margin-right: 10px;"></i> Commentaire de l'administration
                    </h4>
                    <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 25px; border-radius: 12px; border-left: 4px solid #ef4444; border: 1px solid #fca5a5;">
                        <p style="color: #991b1b; font-size: 1.1rem; line-height: 1.6; margin: 0; font-weight: 500;">
                            {{ $request->admin_comment }}
                        </p>
                    </div>
                </div>
                @endif
                
                <!-- Actions -->
                <div style="text-align: center; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                    <a href="{{ route('track.download', ['locale' => app()->getLocale(), 'code' => $request->tracking_code]) }}" class="btn btn-secondary zoom-hover" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3); margin-right: 20px;">
                        <i class="fas fa-download"></i>
                        Télécharger le PDF
                    </a>
                    
                    <a href="{{ route('action') }}" class="btn btn-primary zoom-hover" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);">
                        <i class="fas fa-plus"></i>
                        Nouvelle demande
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Not Found Section -->
@if(isset($notFound))
<section class="section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="max-width: 700px; margin: 0 auto; text-align: center;">
            <div class="not-found-card zoom-hover" style="background: #fff; border-radius: 20px; padding: 60px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                <div style="font-size: 80px; color: #6b7280; margin-bottom: 30px; animation: float 3s ease-in-out infinite;">
                    <i class="fas fa-search"></i>
                </div>
                <h3 style="color: #1f2937; margin-bottom: 20px; font-size: 2rem; font-weight: 700;">Demande non trouvée</h3>
                <p style="color: #6b7280; margin-bottom: 40px; font-size: 1.2rem; line-height: 1.6;">
                    Aucune demande trouvée avec le code de suivi <strong style="color: #1f2937;">{{ request('tracking_code') }}</strong>.
                    <br>Veuillez vérifier votre code de suivi et réessayer.
                </p>
                <a href="{{ route('track') }}" class="btn btn-primary zoom-hover" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);">
                    <i class="fas fa-arrow-left"></i>
                    Nouvelle recherche
                </a>
            </div>
        </div>
    </div>
</section>
@endif

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

@section('styles')
<style>
/* Animations pour les cercles flottants */
@keyframes float {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg); 
    }
    50% { 
        transform: translateY(-20px) rotate(180deg); 
    }
}

.floating-circle {
    animation: float 6s ease-in-out infinite;
}

/* Animation du titre principal - effet de typewriter */
.animated-title {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}

.title-word {
    animation: color-animation 4s linear infinite;
    display: inline-block;
    position: relative;
    overflow: hidden;
}

.title-word::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 3s infinite;
}

.title-word-1 {
    --color-1: #ffffff;
    --color-2: #e5e7eb;
    --color-3: #d1d5db;
    animation-delay: 0s;
}

.title-word-2 {
    --color-1: #e5e7eb;
    --color-2: #ffffff;
    --color-3: #d1d5db;
    animation-delay: 0.5s;
}

.title-word-3 {
    --color-1: #d1d5db;
    --color-2: #ffffff;
    --color-3: #e5e7eb;
    animation-delay: 1s;
}

@keyframes color-animation {
    0%    { color: var(--color-1) }
    32%   { color: var(--color-1) }
    33%   { color: var(--color-2) }
    65%   { color: var(--color-2) }
    66%   { color: var(--color-3) }
    99%   { color: var(--color-3) }
    100%  { color: var(--color-1) }
}

@keyframes shine {
    0% { left: -100%; }
    50% { left: 100%; }
    100% { left: 100%; }
}

/* Animation du sous-titre - effet de fade-in progressif */
.animated-subtitle {
    animation: subtitle-animation 2s ease-out;
    opacity: 0;
    animation-fill-mode: forwards;
    animation-delay: 1s;
}

@keyframes subtitle-animation {
    0% {
        opacity: 0;
        transform: translateY(30px);
        filter: blur(5px);
    }
    50% {
        opacity: 0.5;
        transform: translateY(15px);
        filter: blur(2px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
    }
}

/* Animation de fade-in générale */
.fade-in {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Effet de zoom au hover */
.zoom-hover:hover {
    transform: scale(1.02);
    transition: all 0.3s ease;
}

/* Status badges */
.status-badge {
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.status-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border: 1px solid #fbbf24;
}

.status-approved {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border: 1px solid #34d399;
}

.status-rejected {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border: 1px solid #f87171;
}

.status-processing {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border: 1px solid #60a5fa;
}

/* Type badges */
.type-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.type-aide {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border: 1px solid #f87171;
}

.type-partenariat {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #3730a3;
    border: 1px solid #818cf8;
}

.type-audience {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    color: #0c4a6e;
    border: 1px solid #38bdf8;
}

.type-autre {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border: 1px solid #fbbf24;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 40px;
}

.timeline-icon {
    position: absolute;
    left: -30px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.timeline-item.completed .timeline-icon {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

.timeline-item.current .timeline-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    animation: pulse 2s infinite;
}

.timeline-item.rejected .timeline-icon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.timeline-content {
    margin-left: 30px;
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.timeline-content h5 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-weight: 700;
    font-size: 1.1rem;
}

.timeline-content p {
    margin: 0;
    color: #6b7280;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Effet de pulsation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Input focus effects */
input:focus {
    outline: none;
    border-color: #22c55e !important;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1) !important;
    transform: translateY(-2px);
}

/* Button hover effects */
.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(34, 197, 94, 0.4) !important;
}

.btn-secondary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(107, 114, 128, 0.4) !important;
}

/* Card hover effects */
.track-card:hover,
.result-card:hover,
.not-found-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .main-title {
        font-size: 2.5rem !important;
    }
    
    .title-word {
        font-size: 2.2rem !important;
    }
    
    .main-subtitle {
        font-size: 1.1rem !important;
    }
    
    .animated-title {
        flex-direction: column;
        gap: 4px;
    }
    
    .timeline {
        padding-left: 30px;
    }
    
    .timeline-icon {
        left: -20px;
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
    
    .timeline-content {
        margin-left: 20px;
        padding: 15px;
    }
    
    .info-card {
        margin-bottom: 20px;
    }
}

@media (max-width: 480px) {
    .title-word {
        font-size: 1.8rem !important;
    }
    
    .main-subtitle {
        font-size: 1rem !important;
    }
    
    .track-card,
    .result-card,
    .not-found-card {
        padding: 30px 20px !important;
    }
}
</style>

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    document.getElementById('trackForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche en cours...';
    });
    
    // Simple map placeholder (you can integrate with Google Maps or Leaflet)
    const mapElement = document.getElementById('map');
    if (mapElement) {
        mapElement.innerHTML = '<i class="fas fa-map" style="font-size: 48px;"></i><br><span>Carte interactive</span>';
    }
});
</script>
@endpush 