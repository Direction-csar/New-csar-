@extends('layouts.public')

@section('title', __('pages.demande'))

@section('content')
<style>
/* ===== Page demande: arrière-plan adaptatif + responsive ===== */
.request-hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 16px;
  background: radial-gradient(1200px 600px at 10% 0%, rgba(34,197,94,.10), transparent 60%),
              radial-gradient(1000px 500px at 90% 100%, rgba(59,130,246,.10), transparent 60%),
              #0f172a; /* fallback */
}
.request-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: url('{{ asset('images/arriere plan/N2.jpg') }}') center/cover no-repeat;
  opacity: .25; /* image discrète pour lisibilité */
  filter: saturate(1.05) contrast(1.05);
}

.request-hero::after {
  content: '';
  position: absolute; inset: 0;
  /* voile dégradé pour rendre le texte/form lisibles */
  background: linear-gradient(180deg, rgba(2,6,23,.60) 0%, rgba(2,6,23,.70) 35%, rgba(2,6,23,.85) 100%);
}

.request-container { position: relative; z-index: 1; width: 100%; max-width: 860px; }
.request-header { margin: 0 0 18px 0; text-align: left; }
.request-title { font-weight: 800; font-size: clamp(1.6rem, 2.2vw + 1rem, 2.2rem); color: #d1fae5; margin: 0; }
.request-subtitle { color: #cbd5e1; margin: 6px 0 0 0; font-size: .98rem; }

.request-card {
  background: rgba(255,255,255,0.97);
  backdrop-filter: blur(8px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,.20);
  padding: clamp(16px, 2vw + 8px, 28px);
}

/* Inputs: pleine largeur et touch-friendly */
.request-card .form-input,
.request-card input[type="text"],
.request-card input[type="email"],
.request-card input[type="tel"],
.request-card select,
.request-card textarea {
  width: 100%;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 0.98rem;
}
.request-card label { display: block; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
.request-actions { display: flex; gap: 12px; align-items: center; }
.request-actions .btn { border-radius: 999px; padding: 12px 22px; font-weight: 700; }

/* Responsive spacing */
@media (max-width: 768px) {
  .request-hero { padding: 32px 14px; min-height: auto; }
}
@media (prefers-reduced-motion: reduce) {
  .request-hero::before { transition: none; }
}
</style>

<section class="request-hero">
  <div class="request-container">
    <div class="request-header">
      <h1 class="request-title">Formulaire de demande</h1>
      <p class="request-subtitle">Effectuez votre demande auprès du CSAR. Le traitement est confidentiel et sécurisé.</p>
    </div>

    <div class="request-card">
      <h2 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin:0 0 12px;">Type de demande</h2>
      <form action="{{ route('request.submit') }}" method="POST" id="requestForm" style="display:flex; flex-direction:column; gap:1.1rem;">
        @csrf
        <div>
            <label for="type" style="font-weight: 600;">Type de demande *</label>
            <select id="type" name="type" required class="form-input">
                <option value="">Sélectionnez un type</option>
                <option value="aide">Demande d'aide alimentaire ou matérielle</option>
                <option value="partenariat">Demande de partenariat institutionnel</option>
                <option value="audience">Demande d'audience</option>
                <option value="autre">Autre demande</option>
            </select>
        </div>
        <h2 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin:8px 0 4px;">Informations personnelles</h2>
        <div>
            <label for="full_name" style="font-weight: 600;">Nom complet *</label>
            <input type="text" id="full_name" name="full_name" required class="form-input" placeholder="Votre nom et prénom">
        </div>
        <div>
            <label for="phone" style="font-weight: 600;">Téléphone *</label>
            <input type="tel" id="phone" name="phone" required class="form-input" placeholder="Numéro de téléphone">
        </div>
        <div>
            <label for="email" style="font-weight: 600;">Email</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="Adresse email (optionnel)">
        </div>
        <div>
            <label for="address" style="font-weight: 600;">Adresse *</label>
            <input type="text" id="address" name="address" required class="form-input" placeholder="Adresse complète">
        </div>
        <div>
            <label for="region" style="font-weight: 600;">Région *</label>
            <input type="text" id="region" name="region" required class="form-input" placeholder="Région de résidence">
        </div>
        <div>
            <label for="description" style="font-weight: 600;">Description de la demande *</label>
            <textarea id="description" name="description" rows="4" required class="form-input" placeholder="Expliquez votre demande"></textarea>
        </div>
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
        <div id="geoloc-status" style="color: #059669; font-size: 0.95rem; margin-bottom: 0.5rem;"></div>
        <div class="request-actions">
          <button type="submit" class="btn btn-success" style="background:#059669; border:none;">Envoyer ma demande</button>
          <a href="{{ route('home', ['locale' => 'fr']) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
window.addEventListener('DOMContentLoaded', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        }, function(error) {
            document.getElementById('geoloc-status').textContent = "La géolocalisation n'a pas pu être récupérée (vous pouvez continuer sans).";
        });
    } else {
        document.getElementById('geoloc-status').textContent = "La géolocalisation n'est pas supportée par votre navigateur.";
    }
});
</script>
@endsection