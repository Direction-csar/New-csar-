@extends('layouts.public')

@section('title', __('pages.partners'))

@section('content')
<style>
/* Hero Section - Style CSAR */
.partners-hero {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #fff;
    padding: 80px 0 60px;
    position: relative;
    overflow: hidden;
}

.partners-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.partners-hero .container {
    text-align: center;
    position: relative;
    z-index: 2;
}

.partners-hero .title {
    font-size: 3.5rem;
    font-weight: 800;
    margin: 0 0 20px 0;
    text-shadow: 0 4px 8px rgba(0,0,0,0.3);
    letter-spacing: -1px;
}

.partners-hero .subtitle {
    font-size: 1.3rem;
    opacity: 0.9;
    margin: 0;
    font-weight: 400;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}


/* Logo Marquee Section - Style Professionnel */
.logo-hero {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.logo-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(5,150,105,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
    opacity: 0.6;
}

.logo-hero h2 {
    font-weight: 800;
    text-align: center;
    margin: 0 0 50px 0;
    font-size: 2.8rem;
    color: #059669;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 2;
}

.logo-hero h2::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #059669, #3b82f6, #f59e0b);
    border-radius: 2px;
}

.marquee {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 25px;
    padding: 40px 0;
    box-shadow: 0 20px 40px rgba(5, 150, 105, 0.1);
    border: 2px solid rgba(5, 150, 105, 0.1);
}

.marquee::before,
.marquee::after {
    content: "";
    position: absolute;
    top: 0;
    width: 100px;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}

.marquee::before {
    left: 0;
    background: linear-gradient(to right, #ffffff, rgba(255, 255, 255, 0));
}

.marquee::after {
    right: 0;
    background: linear-gradient(to left, #ffffff, rgba(255, 255, 255, 0));
}

.marquee .track {
    display: flex;
    gap: 100px;
    align-items: center;
    animation: logos-scroll 60s linear infinite;
    will-change: transform;
}

.marquee:hover .track {
    animation-play-state: paused;
}

.marquee img {
    height: 120px;
    width: auto;
    filter: grayscale(0%) contrast(1.4) brightness(1.1) saturate(1.2);
    opacity: 1;
    transition: all 0.4s ease;
    border-radius: 12px;
    padding: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border: 2px solid rgba(5, 150, 105, 0.1);
    object-fit: contain;
    max-width: 200px;
}

/* Prevent shrinking of items inside marquee so scroll is smooth */
.marquee .track a { flex: 0 0 auto; }

.marquee a:hover img {
    filter: none;
    opacity: 1;
    transform: scale(1.15) translateY(-5px);
    box-shadow: 0 15px 35px rgba(5, 150, 105, 0.2);
    border-color: #059669;
}

@keyframes logos-scroll {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

/* Partners Grid */
.partners-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    padding: 60px 0;
}

.partner-card {
    background: #fff;
    border: 2px solid #f3f4f6;
    border-radius: 20px;
    padding: 2rem;
    display: flex;
    gap: 1.5rem;
    align-items: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    text-decoration: none;
    color: inherit;
}

.partner-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #059669, #3b82f6, #f59e0b);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.partner-card:hover::before {
    transform: scaleX(1);
}

.partner-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-color: #059669;
}

.partner-logo {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid #e5e7eb;
}

.partner-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: grayscale(0%) contrast(1.3) brightness(1.1) saturate(1.1);
    transition: all 0.3s ease;
}

.partner-card:hover .partner-logo img {
    filter: none;
    transform: scale(1.05);
}

.partner-logo i {
    font-size: 2rem;
    color: #9ca3af;
}

.partner-info {
    flex: 1;
    min-width: 0;
}

.partner-name {
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    line-height: 1.3;
}

.partner-meta {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.4;
}

.type-badge {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 0.75rem;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.type-ong {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.type-agency {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.type-institution {
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    color: #374151;
    border: 1px solid #d1d5db;
}

.type-government {
    background: linear-gradient(135deg, #fff7ed, #fed7aa);
    color: #9a3412;
    border: 1px solid #fdba74;
}

.type-private {
    background: linear-gradient(135deg, #fef2f2, #fecaca);
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.partner-arrow {
    position: absolute;
    right: 1rem;
    bottom: 1rem;
    color: #9ca3af;
    opacity: 0.6;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.partner-card:hover .partner-arrow {
    opacity: 1;
    color: #059669;
    transform: translate(3px, -3px);
}

/* Reveal Animation */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s ease;
}

.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 768px) {
    .partners-hero .title {
        font-size: 2.5rem;
    }
    
    .partners-hero .subtitle {
        font-size: 1.1rem;
    }
    
    .partners-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .partner-card {
        padding: 1.5rem;
        gap: 1rem;
    }
    
    .partner-logo {
        width: 60px;
        height: 60px;
    }
    
    .logo-hero { padding: 60px 0; }
    /* Mobile: slightly slower but normal */
    .marquee .track { gap: 50px; animation: logos-scroll 100s linear infinite !important; }
    .marquee img { height: 56px; padding: 12px; max-width: 140px; }
    /* Disable aggressive hover effects on touch */
    .marquee a:hover img { transform: none; box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
}

/* Loading Animation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.loading {
    animation: pulse 2s infinite;
}
</style>

<!-- Hero Section -->
<div class="partners-hero">
  <div class="container">
    <h1 class="title">Nos Partenaires</h1>
    <p class="subtitle">Découvrez les organisations qui nous accompagnent dans notre mission de sécurité alimentaire</p>
  </div>
</div>

@php 
  $allItems = collect($grouped)->flatMap(fn($c) => $c)->values();
  $totalPartners = $allItems->count();
  $governmentPartners = $grouped['government'] ?? collect();
  $institutionPartners = $grouped['institution'] ?? collect();
  $agencyPartners = $grouped['agency'] ?? collect();
@endphp


<!-- Logo Marquee Section -->
@if($allItems->count() > 0)
<div class="logo-hero">
  <div class="container">
    <h2>Nos Partenaires en Action</h2>
    <div class="marquee">
      <div class="track">
        @for($dup=0;$dup<2;$dup++)
          @foreach($allItems as $p)
            @php 
              $logo = $p['logo_url'] ?? ($p->logo ? Storage::url($p->logo) : null);
              $url  = $p['website'] ?? '#';
            @endphp
            @if($logo)
              <a href="{{ $url }}" target="_blank" rel="noopener nofollow" title="{{ $p['name'] ?? $p->name }}">
                <img src="{{ $logo }}" alt="{{ $p['name'] ?? $p->name }}">
              </a>
            @endif
          @endforeach
        @endfor
      </div>
    </div>
  </div>
</div>
@endif

<!-- Partners Grid -->
<div class="container">
  <div class="partners-grid" id="partnersGrid">
    @php $typeLabels=['ong'=>'ONG','agency'=>'Agence Internationale','institution'=>'Institution Nationale','government'=>'Gouvernement','private'=>'Secteur Privé']; @endphp
    @foreach(['government','agency','institution','private','ong'] as $type)
      @foreach(($grouped[$type] ?? collect()) as $p)
        @php 
          $logo = $p['logo_url'] ?? ($p->logo ? Storage::url($p->logo) : null);
          $name = $p['name'] ?? $p->name;
          $org  = trim($p['organization'] ?? ($p->organization ?? ''));
          $url  = $p['website'] ?? '#';
          $domain = $url && $url !== '#' ? preg_replace('/^www\./','', parse_url($url, PHP_URL_HOST) ?? '') : '';
        @endphp
        <a class="partner-card reveal" href="{{ $url }}" target="_blank" rel="noopener nofollow" data-type="{{ $type }}" data-name="{{ Str::lower($name.' '.$org) }}">
          <div class="partner-logo">
            @if($logo) 
              <img src="{{ $logo }}" alt="{{ $name }}"/> 
            @else 
              <i class="fas fa-building"></i> 
            @endif
          </div>
          <div class="partner-info">
            <p class="partner-name">{{ $name }}</p>
            @if($org || $domain)
              <p class="partner-meta">{{ $org }} @if($domain) — {{ $domain }} @endif</p>
            @endif
          </div>
          <div class="type-badge type-{{ $type }}">{{ $typeLabels[$type] }}</div>
          <span class="partner-arrow"><i class="fas fa-arrow-up-right-from-square"></i></span>
        </a>
      @endforeach
    @endforeach
  </div>
</div>

<!-- Devenir partenaire & Opportunités -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-4">
            <h2 class="h4 mb-3">Devenir partenaire</h2>
            <p class="text-muted mb-4">Vous souhaitez soutenir la sécurité alimentaire et la résilience au Sénégal ? Contactez-nous via le formulaire ci-dessous ou écrivez à <a href="mailto:contact@csar.sn">contact@csar.sn</a>.</p>
            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}?sujet=partenariat" class="btn btn-success">
              <i class="fas fa-envelope me-2"></i>Formulaire contact / partenariat
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-4">
            <h2 class="h4 mb-3">Opportunités de financement & appels à experts</h2>
            <p class="text-muted mb-4">Les opportunités de financement et appels à experts sont publiés sur le site et dans notre newsletter. Inscrivez-vous à la newsletter ou consultez la page <a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}">Actualités</a>.</p>
            <a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-success me-2">Actualités</a>
            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}?sujet=financement" class="btn btn-outline-success">Nous contacter</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
// Reveal on scroll
const io=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target);}})},{threshold:.08});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
</script>
@endpush

<style>
/* RESPONSIVE GLOBAL - TOUTES LES PAGES */
@media (max-width: 1024px) {
    .container { max-width: 100%; padding-left: 1.5rem; padding-right: 1.5rem; }
    div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; gap: 1.5rem !important; }
    .hero, .hero-section, .partners-hero { min-height: 50vh !important; padding: 50px 0 !important; }
    .main-title, h1 { font-size: 2.2rem !important; }
    .section-title, h2 { font-size: 1.8rem !important; }
    /* Tablet: normal speed */
    .marquee .track { animation: logos-scroll 80s linear infinite !important; gap: 60px; }
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

/* Respect users preferring reduced motion */
@media (prefers-reduced-motion: reduce) {
  .marquee .track { animation-duration: 150s; }
  .partner-card, .marquee a img { transition: none !important; }
}

/* Disable hover lift on touch devices to avoid layout shift */
@media (hover: none) and (pointer: coarse) {
  .partner-card:hover { transform: none; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
}
</style>
@endsection
