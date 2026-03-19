@extends('layouts.public')

@section('title', __('pages.projets'))
@section('meta_description', 'Projets nationaux du CSAR : renforcement du SIM, achats locaux, réponses d\'urgence. Résultats par région et documents téléchargeables.')
@section('meta_keywords', 'CSAR, projets, interventions, SIM, sécurité alimentaire, résilience, Sénégal, marchés, rapports')

@section('content')
<style>
/* Hero Section Ultra Modern */
.hero-projets {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    padding: 120px 0 80px;
    position: relative;
    overflow: hidden;
}

.hero-projets::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(34, 197, 94, 0.15), transparent);
    border-radius: 50%;
    filter: blur(80px);
    animation: float-orb 20s ease-in-out infinite;
}

.hero-projets::after {
    content: '';
    position: absolute;
    bottom: -50%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent);
    border-radius: 50%;
    filter: blur(100px);
    animation: float-orb 25s ease-in-out infinite reverse;
}

@keyframes float-orb {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -30px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

/* Project Cards Ultra */
.project-card-ultra {
    background: white;
    border-radius: 24px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    height: 100%;
}

.project-card-ultra::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(135deg, #22c55e, #3b82f6, #22c55e);
    background-size: 200% 200%;
    border-radius: 24px;
    opacity: 0;
    z-index: -1;
    transition: opacity 0.5s;
    animation: border-flow 3s linear infinite;
}

.project-card-ultra:hover::before {
    opacity: 1;
}

.project-card-ultra:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.3);
}

@keyframes border-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.project-icon-ultra {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    position: relative;
    transition: all 0.5s;
}

.project-card-ultra:hover .project-icon-ultra {
    transform: scale(1.1) rotate(5deg);
}

/* Region Badges */
.region-badge {
    display: inline-block;
    padding: 10px 20px;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05));
    border: 2px solid rgba(34, 197, 94, 0.2);
    border-radius: 50px;
    color: #059669;
    font-weight: 600;
    margin: 0.5rem;
    transition: all 0.3s;
}

.region-badge:hover {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.1));
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(34, 197, 94, 0.2);
}

/* Document Cards */
.doc-card-ultra {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    border: 2px solid #f3f4f6;
    transition: all 0.4s;
    height: 100%;
}

.doc-card-ultra:hover {
    border-color: #22c55e;
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(34, 197, 94, 0.15);
}

/* Fade In Animation */
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

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<!-- Hero Section Ultra Modern -->
<section class="hero-projets">
    <div class="container" style="position: relative; z-index: 1;">
        <div style="text-align: center; max-width: 900px; margin: 0 auto;">
            <div style="display: inline-block; padding: 10px 24px; background: rgba(34, 197, 94, 0.15); backdrop-filter: blur(10px); border-radius: 50px; margin-bottom: 2rem; border: 1px solid rgba(34, 197, 94, 0.3);">
                <span style="color: #22c55e; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px;">
                    <i class="fas fa-project-diagram" style="margin-right: 8px;"></i>
                    Nos Initiatives
                </span>
            </div>
            
            <h1 style="font-size: 3.5rem; font-weight: 800; color: white; margin-bottom: 1.5rem; line-height: 1.2; text-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);">
                {{ __('messages.projets.title') }}
            </h1>
            
            <p style="font-size: 1.3rem; color: rgba(255, 255, 255, 0.85); line-height: 1.8; margin-bottom: 2rem;">
                {{ __('messages.projets.subtitle') }}
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="#projets-nationaux" class="btn btn-success" style="padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-rocket"></i>
                    Découvrir nos projets
                </a>
                <a href="{{ route('map', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-light" style="padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-map-marked-alt"></i>
                    Carte interactive
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Projets Nationaux Section Ultra -->
<section id="projets-nationaux" style="padding: 100px 0; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); position: relative; overflow: hidden;">
    <!-- Animated Background -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(34, 197, 94, 0.08), transparent); border-radius: 50%; filter: blur(60px); animation: float-orb 15s ease-in-out infinite;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 4rem;" class="fade-in-up">
            <div style="display: inline-block; padding: 8px 20px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); border-radius: 50px; margin-bottom: 1.5rem;">
                <span style="color: #22c55e; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.2px;">
                    <i class="fas fa-flag" style="margin-right: 8px;"></i>
                    {{ __('messages.projets.national') }}
                </span>
            </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; color: #1f2937; margin-bottom: 1rem;">
                Nos Projets Stratégiques
            </h2>
            <p style="color: #6b7280; font-size: 1.15rem; max-width: 700px; margin: 0 auto;">
                Des initiatives concrètes pour renforcer la sécurité alimentaire et la résilience
            </p>
        </div>
        
        <div class="row g-4">
            @foreach($projets as $index => $p)
            <div class="col-md-6 col-lg-4" style="animation: fadeInUp 0.6s ease-out; animation-delay: {{ $index * 0.1 }}s; animation-fill-mode: both;">
                <div class="project-card-ultra">
                    <div class="project-icon-ultra" style="background: linear-gradient(135deg, #22c55e, #10b981); color: white; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);">
                        <i class="fas fa-{{ $p['icon'] }}"></i>
                    </div>
                    
                    <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
                        {{ $p['titre'] }}
                    </h3>
                    
                    <p style="color: #6b7280; line-height: 1.7; margin-bottom: 1.5rem;">
                        {{ $p['description'] }}
                    </p>
                    
                    @if(!empty($p['lien_sim']))
                    <a href="{{ route('sim-reports.index') }}" class="btn btn-success" style="border-radius: 12px; padding: 10px 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        {{ __('messages.projets.voir_sim') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Résultats par Région Section -->
<section style="padding: 100px 0; background: linear-gradient(135deg, #1f2937 0%, #111827 100%); position: relative; overflow: hidden;">
    <!-- Animated Background -->
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent); border-radius: 50%; filter: blur(100px); animation: float-orb 20s ease-in-out infinite;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 4rem;">
            <div style="display: inline-block; padding: 8px 20px; background: rgba(34, 197, 94, 0.15); backdrop-filter: blur(10px); border-radius: 50px; margin-bottom: 1.5rem; border: 1px solid rgba(34, 197, 94, 0.3);">
                <span style="color: #22c55e; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.2px;">
                    <i class="fas fa-map-marked-alt" style="margin-right: 8px;"></i>
                    Couverture Nationale
                </span>
            </div>
            
            <h2 style="font-size: 2.8rem; font-weight: 800; color: white; margin-bottom: 1.2rem;">
                Résultats par Région
            </h2>
            
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.15rem; max-width: 700px; margin: 0 auto 3rem;">
                Le CSAR intervient dans toutes les régions du Sénégal. Consultez la carte interactive et les rapports SIM pour les indicateurs par zone.
            </p>
        </div>
        
        <div style="text-align: center; margin-bottom: 3rem;">
            @foreach($regions as $r)
            <span class="region-badge">{{ $r }}</span>
            @endforeach
        </div>
        
        <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('map', ['locale' => app()->getLocale()]) }}" class="btn btn-success" style="padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);">
                <i class="fas fa-map-marked-alt"></i>
                {{ __('messages.projets.carte') }}
            </a>
            <a href="{{ route('sim-reports.index') }}" class="btn btn-outline-light" style="padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px;">
                <i class="fas fa-chart-line"></i>
                {{ __('messages.projets.rapports_sim') }}
            </a>
        </div>
    </div>
</section>

<!-- Documents et Rapports Section -->
<section style="padding: 100px 0; background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 4rem;">
            <div style="display: inline-block; padding: 8px 20px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1)); border-radius: 50px; margin-bottom: 1.5rem;">
                <span style="color: #3b82f6; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.2px;">
                    <i class="fas fa-file-alt" style="margin-right: 8px;"></i>
                    Ressources
                </span>
            </div>
            
            <h2 style="font-size: 2.8rem; font-weight: 800; color: #1f2937; margin-bottom: 1rem;">
                Documents et Rapports
            </h2>
            
            <p style="color: #6b7280; font-size: 1.15rem; max-width: 700px; margin: 0 auto;">
                Études, bilans semestriels et rapports officiels à télécharger.
            </p>
        </div>
        
        <div class="row g-4">
            @forelse($rapports->take(4) as $index => $r)
            <div class="col-md-6" style="animation: fadeInUp 0.6s ease-out; animation-delay: {{ $index * 0.1 }}s; animation-fill-mode: both;">
                <div class="doc-card-ultra">
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);">
                            <i class="fas fa-file-pdf" style="font-size: 1.8rem; color: white;"></i>
                        </div>
                        
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">
                                {{ $r->title }}
                            </h4>
                            @if($r->published_at)
                            <p style="color: #6b7280; font-size: 0.9rem; margin: 0;">
                                <i class="fas fa-calendar" style="margin-right: 6px;"></i>
                                {{ $r->published_at->translatedFormat('d F Y') }}
                            </p>
                            @endif
                        </div>
                        
                        <a href="{{ route('sim-reports.show', $r->id) }}" class="btn btn-outline-success" style="border-radius: 12px; padding: 10px 20px; font-weight: 600; white-space: nowrap;">
                            {{ __('messages.projets.voir') }}
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 20px; border: 2px dashed #e5e7eb;">
                    <i class="fas fa-file-alt" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280; margin: 0;">
                        {{ __('messages.projets.no_reports') }} 
                        <a href="{{ route('sim-reports.index') }}" style="color: #22c55e; font-weight: 600;">{{ __('messages.projets.rapports_sim') }}</a>.
                    </p>
                </div>
            </div>
            @endforelse
        </div>
        
        @if($rapports->isNotEmpty())
        <div style="text-align: center; margin-top: 3rem;">
            <a href="{{ route('sim-reports.index') }}" class="btn btn-success" style="padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px;">
                Tous les rapports
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @endif
    </div>
</section>

@if($publications->isNotEmpty())
<!-- Publications Section -->
<section style="padding: 100px 0; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 4rem;">
            <div style="display: inline-block; padding: 8px 20px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.1)); border-radius: 50px; margin-bottom: 1.5rem;">
                <span style="color: #8b5cf6; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.2px;">
                    <i class="fas fa-newspaper" style="margin-right: 8px;"></i>
                    Actualités
                </span>
            </div>
            
            <h2 style="font-size: 2.8rem; font-weight: 800; color: #1f2937; margin-bottom: 1rem;">
                {{ __('messages.projets.publications') }}
            </h2>
        </div>
        
        <div class="row g-4">
            @foreach($publications as $index => $pub)
            <div class="col-md-6 col-lg-3" style="animation: fadeInUp 0.6s ease-out; animation-delay: {{ $index * 0.1 }}s; animation-fill-mode: both;">
                <div style="background: white; border-radius: 20px; overflow: hidden; border: 2px solid #f3f4f6; transition: all 0.4s; height: 100%;" class="doc-card-ultra">
                    @if($pub->featured_image)
                    <div style="position: relative; overflow: hidden; height: 180px;">
                        <img src="{{ asset('storage/' . $pub->featured_image) }}" alt="" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);"></div>
                    </div>
                    @endif
                    
                    <div style="padding: 1.5rem;">
                        <h4 style="font-size: 1rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; line-height: 1.4;">
                            {{ Str::limit($pub->title, 50) }}
                        </h4>
                        
                        <a href="{{ route('news.show', ['locale' => app()->getLocale(), 'id' => $pub->id]) }}" class="btn btn-success" style="width: 100%; border-radius: 12px; padding: 10px; font-weight: 600;">
                            Lire l'article
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
