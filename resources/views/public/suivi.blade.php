@extends('layouts.public')

@section('title', 'Suivre ma demande - CSAR')

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
                <form action="{{ request()->is('suivi') || request()->is('*/suivi') ? '/suivi' : route('track.request', ['locale' => app()->getLocale() ?? 'fr']) }}" method="POST" id="trackForm">
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
                        <small style="display: block; color: #6b7280; margin-top: 8px;">
                            <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                            Le code de suivi vous a été envoyé par SMS lors de la soumission de votre demande
                        </small>
                    </div>
                    <div style="margin-bottom: 40px;">
                        <label for="phone" style="display: block; margin-bottom: 12px; font-weight: 600; font-size: 1.1rem; color: #1f2937;">
                            <i class="fas fa-phone" style="color: #22c55e; margin-right: 8px;"></i> Numéro de téléphone (facultatif)
                        </label>
                        <input type="text" id="phone" name="phone" placeholder="+221 77 123 45 67" 
                               style="width: 100%; padding: 18px 20px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1.1rem; background: #f9fafb;">
                        <small style="display: block; color: #6b7280; margin-top: 8px;">
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
@if(isset($request))
<!-- Results Section -->
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
                        <h3 style="font-size: 1.4rem; font-weight: 700; color: #0284c7; margin-bottom: 6px;">Demande #{{ $request->tracking_code }}</h3>
                        <p style="color: #64748b; font-size: 1rem; margin-bottom: 0;">
                            <i class="fas fa-calendar-alt" style="margin-right: 8px; color: #22c55e;"></i>
                            Soumise le {{ $request->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    <div>
                        @if($request->status === 'rejetée')
                            <span class="status-badge status-rejected"><i class="fas fa-times-circle"></i> Rejetée</span>
                        @elseif($request->status === 'approuvée')
                            <span class="status-badge status-approved"><i class="fas fa-check-circle"></i> Approuvée</span>
                        @elseif($request->status === 'traitée')
                            <span class="status-badge status-processing"><i class="fas fa-spinner"></i> Traitée</span>
                        @else
                            <span class="status-badge status-pending"><i class="fas fa-clock"></i> En attente</span>
                        @endif
                    </div>
                </div>
                <!-- Request Details (à adapter selon ton modèle) -->
                <div style="display: flex; flex-wrap: wrap; gap: 40px; margin-bottom: 40px;">
                    <div style="flex:1;min-width:210px;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #0d9488; margin-bottom: 10px;">Informations du demandeur</h4>
                        <div style="color: #334155; font-size: 1rem;">
                            <div><b>Nom :</b> {{ $request->name }}</div>
                            <div><b>Téléphone :</b> {{ $request->phone }}</div>
                            <div><b>Email :</b> {{ $request->email }}</div>
                            <div><b>Région :</b> {{ $request->region }}</div>
                            <div><b>Type :</b> {{ $request->type }}</div>
                        </div>
                    </div>
                    <div style="flex:1;min-width:210px;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #0d9488; margin-bottom: 10px;">Localisation</h4>
                        <div style="color: #334155; font-size: 1rem;">
                            <div><b>Adresse :</b> {{ $request->address }}</div>
                            <div><b>Coordonnées :</b> {{ $request->latitude }}, {{ $request->longitude }}</div>
                        </div>
                    </div>
                </div>
                <!-- Description -->
                <div style="margin-bottom: 40px;">
                    <h4 style="font-size: 1.1rem; font-weight: 700; color: #0d9488; margin-bottom: 10px;">Description de la demande</h4>
                    <div style="background: #f8fafc; padding: 18px 22px; border-radius: 10px; color: #334155;">
                        {{ $request->description }}
                    </div>
                </div>
                <!-- Timeline (à adapter selon ton modèle) -->
                <div style="margin-bottom: 0;">
                    <h4 style="font-size: 1.1rem; font-weight: 700; color: #0d9488; margin-bottom: 10px;">Historique de traitement</h4>
                    <div class="timeline">
                        @foreach($request->history as $step)
                            <div class="timeline-item {{ $step['status'] }}">
                                <div class="timeline-icon">
                                    @if($step['status'] === 'completed')
                                        <i class="fas fa-check"></i>
                                    @elseif($step['status'] === 'current')
                                        <i class="fas fa-spinner"></i>
                                    @elseif($step['status'] === 'rejected')
                                        <i class="fas fa-times"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <h5>{{ $step['title'] }}</h5>
                                    <p>{{ $step['date'] }}</p>
                                    @if(isset($step['comment']))
                                    <div style="color:#991b1b;font-size:0.95em;">{{ $step['comment'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
@endsection

@push('styles')
<style>
/* ... (styles identiques à track.blade.php, voir fichier source) ... */
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission
    document.getElementById('trackForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche en cours...';
    });
    // Simple map placeholder (tu peux intégrer Google Maps ou Leaflet)
    const mapElement = document.getElementById('map');
    if (mapElement) {
        mapElement.innerHTML = '<i class="fas fa-map" style="font-size: 48px;"></i><br><span>Carte interactive</span>';
    }
});
</script>
@endpush
