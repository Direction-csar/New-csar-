@extends('layouts.public')

@section('title', __('pages.institution'))

@section('content')
<div class="institution-page">
    {{-- Hero avec effet moderne --}}
    <section class="institution-hero inst-hero" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 45%, #14532d 100%); padding: 80px 20px; text-align: center; position: relative; overflow: hidden;">
        <div class="inst-hero-shine"></div>
        <div class="container position-relative" style="max-width: 900px;">
            <div class="inst-hero-box">
                <h1 class="inst-hero-title">{{ __('institution.hero_title') }}</h1>
                <p class="inst-hero-subtitle">{{ __('institution.hero_subtitle') }}</p>
                <a href="#statut-juridique" class="btn btn-light inst-hero-btn">{{ __('institution.hero_btn') }}</a>
            </div>
        </div>
    </section>

    {{-- Statut juridique — carte avec bannière verte et encadré missions --}}
    <section id="statut-juridique" class="inst-section inst-section-gray inst-reveal">
        <div class="container" style="max-width: 1000px;">
            <h2 class="inst-section-title">{{ __('institution.legal_status_title') }}</h2>
            <p class="inst-section-subtitle">{{ __('institution.legal_status_subtitle') }}</p>

            <div class="inst-card-legal card border-0">
                <div class="inst-legal-banner">
                    <div class="inst-legal-banner-icon"><i class="fas fa-chart-line text-white"></i></div>
                    <h3 class="inst-legal-banner-title">{{ __('institution.legal_banner_title') }}</h3>
                </div>
                <div class="card-body p-4 p-md-5 inst-card-body-readable">
                    <p class="inst-legal-intro">{{ __('institution.legal_intro') }}</p>
                    <div class="inst-missions-box">
                        <h4 class="inst-missions-title">{{ __('institution.missions_title') }}</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="inst-missions-list">
                                    <li><span class="inst-bullet">●</span>{{ __('institution.mission_1') }}</li>
                                    <li><span class="inst-bullet">●</span>{{ __('institution.mission_2') }}</li>
                                    <li><span class="inst-bullet">●</span>{{ __('institution.mission_3') }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="inst-missions-list">
                                    <li><span class="inst-bullet">●</span>{{ __('institution.mission_4') }}</li>
                                    <li><span class="inst-bullet">●</span>{{ __('institution.mission_5') }}</li>
                                    <li><span class="inst-bullet">●</span>{{ __('institution.mission_6') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tutelles — avec logos et effets dynamiques --}}
    <section class="inst-section inst-section-light inst-reveal" id="inst-tutelles">
        <div class="container" style="max-width: 1000px;">
            <h2 class="inst-section-title">{{ __('institution.tutelles_title') }}</h2>
            <p class="inst-section-subtitle">{{ __('institution.tutelles_subtitle') }}</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <a href="https://femme.gouv.sn/" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark d-block h-100 inst-reveal" data-delay="0">
                        <div class="inst-tutelle-card inst-tutelle-green">
                            <div class="inst-tutelle-bar"></div>
                            <div class="card-body p-4 text-center">
                                <div class="inst-tutelle-logo inst-tutelle-logo-green">
                                    <img src="{{ asset('images/mfs.png') }}" alt="{{ __('institution.mfs_name') }}" class="inst-tutelle-logo-img" onerror="this.style.display='none'; this.nextElementSibling.classList.add('inst-tutelle-logo-fallback-show');">
                                    <div class="inst-tutelle-logo-fallback"><i class="fas fa-landmark"></i></div>
                                </div>
                                <span class="inst-tutelle-badge">{{ __('institution.tutelle_tech') }}</span>
                                <h3 class="h5 fw-bold text-dark mb-2 mt-2">{{ __('institution.mfs_name') }}</h3>
                                <p class="text-muted small mb-3">{{ __('institution.mfs_desc') }}</p>
                                <span class="inst-tutelle-link">{{ __('institution.visit_site') }} <i class="fas fa-external-link-alt ms-1 small"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="https://www.finances.gouv.sn/" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark d-block h-100 inst-reveal" data-delay="100">
                        <div class="inst-tutelle-card inst-tutelle-blue">
                            <div class="inst-tutelle-bar"></div>
                            <div class="card-body p-4 text-center">
                                <div class="inst-tutelle-logo inst-tutelle-logo-blue">
                                    <img src="{{ asset('images/ministere-des-finances-et-du-budget.png') }}" alt="{{ __('institution.mfb_name') }}" class="inst-tutelle-logo-img" onerror="this.style.display='none'; this.nextElementSibling.classList.add('inst-tutelle-logo-fallback-show');">
                                    <div class="inst-tutelle-logo-fallback"><i class="fas fa-coins"></i></div>
                                </div>
                                <span class="inst-tutelle-badge">{{ __('institution.tutelle_fin') }}</span>
                                <h3 class="h5 fw-bold text-dark mb-2 mt-2">{{ __('institution.mfb_name') }}</h3>
                                <p class="text-muted small mb-3">{{ __('institution.mfb_desc') }}</p>
                                <span class="inst-tutelle-link">{{ __('institution.visit_site') }} <i class="fas fa-external-link-alt ms-1 small"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Organisation administrative — Bilan Social 2024 --}}
    <section class="inst-section inst-section-green inst-reveal">
        <div class="container" style="max-width: 1000px;">
            <h2 class="inst-section-title">{{ __('institution.org_title') }}</h2>
            <p class="inst-section-subtitle">{{ __('institution.org_subtitle') }}</p>

            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: linear-gradient(135deg, #16a34a, #15803d);"><i class="fas fa-landmark text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_board') }}</h4>
                            <p class="text-muted small mb-0">{{ __('institution.org_board_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"><i class="fas fa-building text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_dg') }}</h4>
                            <p class="text-muted small mb-0">{{ __('institution.org_dg_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: #64748b;"><i class="fas fa-file-alt text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_sg') }}</h4>
                            <p class="text-muted small mb-0">{{ __('institution.org_sg_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: #16a34a;"><i class="fas fa-seedling text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_dsar') }}</h4>
                            <p class="text-muted small mb-0">DSAR</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: #2563eb;"><i class="fas fa-coins text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_dfc') }}</h4>
                            <p class="text-muted small mb-0">DFC</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: #dc2626;"><i class="fas fa-bullseye text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_dpse') }}</h4>
                            <p class="text-muted small mb-0">DPSE</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: #0891b2;"><i class="fas fa-users text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_drh') }}</h4>
                            <p class="text-muted small mb-0">DRH</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-org-card card border-0 h-100">
                        <div class="card-body p-3 text-center">
                            <div class="inst-org-icon" style="background: #ea580c;"><i class="fas fa-truck text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.org_dtl') }}</h4>
                            <p class="text-muted small mb-0">DTL</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cellules spécialisées — unités techniques --}}
    <section class="inst-section inst-section-light inst-reveal">
        <div class="container" style="max-width: 1000px;">
            <h2 class="inst-section-title inst-section-title-green">{{ __('institution.cellules_title') }}</h2>
            <p class="inst-section-subtitle">{{ __('institution.cellules_subtitle') }}</p>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="inst-cellule-card card border-0 h-100">
                        <div class="card-body p-3">
                            <div class="inst-cellule-icon" style="background: #16a34a;"><i class="fas fa-chart-pie text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.cell_ccg') }}</h4>
                            <p class="text-muted small mb-1"><strong>CCG</strong></p>
                            <p class="text-muted small mb-0">{{ __('institution.cell_ccg_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-cellule-card card border-0 h-100">
                        <div class="card-body p-3">
                            <div class="inst-cellule-icon" style="background: #0ea5e9;"><i class="fas fa-search text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.cell_cia') }}</h4>
                            <p class="text-muted small mb-1"><strong>CIA</strong></p>
                            <p class="text-muted small mb-0">{{ __('institution.cell_cia_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-cellule-card card border-0 h-100">
                        <div class="card-body p-3">
                            <div class="inst-cellule-icon" style="background: #ea580c;"><i class="fas fa-file-contract text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.cell_cpm') }}</h4>
                            <p class="text-muted small mb-1"><strong>CPM</strong></p>
                            <p class="text-muted small mb-0">{{ __('institution.cell_cpm_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="inst-cellule-card card border-0 h-100">
                        <div class="card-body p-3">
                            <div class="inst-cellule-icon" style="background: #7c3aed;"><i class="fas fa-laptop-code text-white"></i></div>
                            <h4 class="h6 fw-bold mb-1">{{ __('institution.cell_ci') }}</h4>
                            <p class="text-muted small mb-1"><strong>CI</strong></p>
                            <p class="text-muted small mb-0">{{ __('institution.cell_ci_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* ===== Page Institution — template fluide, animations dynamiques ===== */
:root {
    --inst-ease-out: cubic-bezier(0.25, 0.46, 0.45, 0.94);
    --inst-ease-in-out: cubic-bezier(0.65, 0, 0.35, 1);
    --inst-ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --inst-ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
}
/* Lisibilité globale */
.institution-page {
    font-size: 1rem;
    line-height: 1.6;
    color: #1f2937;
}
.institution-page .container { padding-left: 16px; padding-right: 16px; }
.institution-page p,
.institution-page li { line-height: 1.75; }

/* Hero — shine fluide + float léger */
.inst-hero { position: relative; overflow: hidden; }
.inst-hero-shine {
    position: absolute; inset: 0;
    background: linear-gradient(105deg, transparent 0%, rgba(255,255,255,0.06) 40%, rgba(255,255,255,0.14) 50%, rgba(255,255,255,0.06) 60%, transparent 100%);
    background-size: 200% 100%;
    animation: inst-shine 10s var(--inst-ease-in-out) infinite;
    pointer-events: none;
}
@keyframes inst-shine {
    0% { background-position: 150% 0; }
    100% { background-position: -150% 0; }
}
.inst-hero-box {
    background: rgba(255,255,255,0.14);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 24px;
    padding: 44px 56px;
    display: inline-block;
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
    transition: transform 0.5s var(--inst-ease-out), box-shadow 0.5s var(--inst-ease-out);
    animation: inst-hero-float 6s var(--inst-ease-in-out) infinite;
}
@keyframes inst-hero-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}
.inst-hero-box:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 28px 56px rgba(0,0,0,0.22);
    animation: none;
}
.inst-hero-title {
    font-size: clamp(1.65rem, 4vw, 2.4rem);
    font-weight: 700;
    color: #fff;
    margin: 0 0 14px 0;
    line-height: 1.3;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 14px rgba(0,0,0,0.2);
    animation: inst-fade-in 1s var(--inst-ease-out) both;
}
.inst-hero-subtitle {
    font-size: clamp(1rem, 2vw, 1.15rem);
    color: rgba(255,255,255,0.98);
    margin: 0 0 26px 0;
    line-height: 1.7;
    animation: inst-fade-in 1s var(--inst-ease-out) 0.15s both;
}
.inst-hero-btn {
    border-radius: 50px;
    padding: 12px 32px;
    font-weight: 600;
    transition: transform 0.35s var(--inst-ease-spring), box-shadow 0.35s var(--inst-ease-out), background 0.3s ease;
    animation: inst-fade-in 1s var(--inst-ease-out) 0.3s both;
}
.inst-hero-btn:hover {
    transform: scale(1.06);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}
@keyframes inst-fade-in {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Sections */
.inst-section { padding: 64px 0; }
.inst-section-gray { background: #f1f5f9; }
.inst-section-light { background: #fff; }
.inst-section-green { background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%); }
.inst-section-title {
    text-align: center;
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
    position: relative;
    display: inline-block;
    left: 50%;
    transform: translateX(-50%);
}
.inst-section-title::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 0;
    right: 0;
    margin: 0 auto;
    width: 0;
    height: 3px;
    background: linear-gradient(90deg, #16a34a, #15803d);
    border-radius: 2px;
    transition: width 0.6s var(--inst-ease-out);
}
.inst-reveal.inst-visible .inst-section-title::after { width: 60px; left: 50%; margin-left: -30px; }
.inst-section-title-green { color: #16a34a; }
.inst-section-title-green::after { background: linear-gradient(90deg, #16a34a, #0d9488); }
.inst-section-subtitle {
    text-align: center;
    font-size: 1.05rem;
    color: #4b5563;
    margin-bottom: 44px;
    line-height: 1.6;
}

/* Carte juridique — transition fluide + lisibilité */
.inst-card-legal {
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    background: #fff;
    transition: box-shadow 0.45s var(--inst-ease-out), transform 0.45s var(--inst-ease-out);
}
.inst-card-legal:hover {
    box-shadow: 0 20px 50px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
.inst-legal-banner {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.inst-legal-banner-icon {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.25);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.inst-legal-banner-icon i { font-size: 1.2rem; }
.inst-legal-banner-title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.35;
    letter-spacing: 0.01em;
}
.inst-legal-intro {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #1f2937;
    margin-bottom: 1.5rem;
}
.inst-missions-box {
    background: #e2e8f0;
    border-radius: 12px;
    padding: 1.35rem 1.5rem;
}
.inst-missions-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}
.inst-missions-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.inst-missions-list li {
    font-size: 1rem;
    line-height: 1.75;
    color: #1f2937;
    margin-bottom: 0.65rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.inst-bullet {
    color: #16a34a;
    font-size: 0.6rem;
    margin-top: 0.45rem;
    flex-shrink: 0;
}

/* Cartes Tutelles — logos + effets fluides */
.inst-tutelle-logo {
    width: 100px;
    height: 100px;
    margin: 0 auto 18px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
    transition: transform 0.4s var(--inst-ease-spring), box-shadow 0.4s var(--inst-ease-out);
}
a:hover .inst-tutelle-logo {
    transform: scale(1.1);
    box-shadow: 0 16px 36px rgba(0,0,0,0.18);
}
.inst-tutelle-logo-green { background: #fff; border: 3px solid #16a34a; }
.inst-tutelle-logo-blue { background: #fff; border: 3px solid #2563eb; }
.inst-tutelle-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 12px;
    transition: transform 0.5s var(--inst-ease-out);
}
a:hover .inst-tutelle-logo-img { transform: scale(1.05); }
.inst-tutelle-logo-fallback {
    display: none;
    position: absolute;
    inset: 0;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #fff;
}
.inst-tutelle-logo-fallback-show { display: flex !important; }
.inst-tutelle-green .inst-tutelle-logo-fallback { background: linear-gradient(135deg, #16a34a, #15803d); }
.inst-tutelle-blue .inst-tutelle-logo-fallback { background: linear-gradient(135deg, #2563eb, #1d4ed8); }

.inst-tutelle-card {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 6px 24px rgba(0,0,0,0.06);
    transition: transform 0.4s var(--inst-ease-spring), box-shadow 0.4s var(--inst-ease-out);
    height: 100%;
    border: 1px solid rgba(0,0,0,0.04);
}
a:hover .inst-tutelle-card {
    transform: translateY(-8px);
    box-shadow: 0 24px 48px rgba(0,0,0,0.14);
}
.inst-tutelle-bar { height: 4px; width: 100%; }
.inst-tutelle-green .inst-tutelle-bar { background: linear-gradient(90deg, #16a34a, #15803d); }
.inst-tutelle-blue .inst-tutelle-bar { background: linear-gradient(90deg, #2563eb, #1d4ed8); }
.inst-tutelle-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 5px 12px;
    border-radius: 8px;
    transition: transform 0.3s var(--inst-ease-out);
}
a:hover .inst-tutelle-badge { transform: scale(1.03); }
.inst-tutelle-green .inst-tutelle-badge { background: rgba(22, 163, 74, 0.12); color: #15803d; }
.inst-tutelle-blue .inst-tutelle-badge { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
.inst-tutelle-card h3 {
    font-size: 1.15rem;
    line-height: 1.4;
    color: #1f2937;
}
.inst-tutelle-card .text-muted {
    font-size: 1rem !important;
    line-height: 1.7 !important;
    color: #4b5563 !important;
}
.inst-tutelle-link {
    font-size: 0.95rem;
    font-weight: 600;
    color: #16a34a;
    transition: color 0.3s var(--inst-ease-out);
}
a:hover .inst-tutelle-green .inst-tutelle-link { color: #15803d; }
a:hover .inst-tutelle-blue .inst-tutelle-link { color: #2563eb; }

/* Cartes Organisation — entrée en cascade fluide */
.inst-org-card {
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    transition: opacity 0.5s var(--inst-ease-out), transform 0.5s var(--inst-ease-spring), box-shadow 0.4s var(--inst-ease-out);
    opacity: 0;
    transform: translateY(20px);
}
.inst-org-card.inst-card-visible {
    opacity: 1;
    transform: translateY(0);
}
.inst-org-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(0,0,0,0.12);
}
.inst-org-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 10px;
    transition: transform 0.4s var(--inst-ease-spring);
}
.inst-org-card:hover .inst-org-icon { transform: scale(1.12) rotate(3deg); }
.inst-org-card .h6, .inst-org-card h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.35;
}
.inst-org-card .text-muted {
    font-size: 0.95rem !important;
    line-height: 1.5 !important;
    color: #4b5563 !important;
}

/* Cartes Cellules — même fluidité */
.inst-cellule-card {
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    transition: opacity 0.5s var(--inst-ease-out), transform 0.5s var(--inst-ease-spring), box-shadow 0.4s var(--inst-ease-out);
    opacity: 0;
    transform: translateY(20px);
}
.inst-cellule-card.inst-card-visible {
    opacity: 1;
    transform: translateY(0);
}
.inst-cellule-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(0,0,0,0.12);
}
.inst-cellule-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 12px;
    transition: transform 0.4s var(--inst-ease-spring);
}
.inst-cellule-card:hover .inst-cellule-icon { transform: scale(1.1); }
.inst-cellule-card .h6, .inst-cellule-card h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.35;
}
.inst-cellule-card .text-muted, .inst-cellule-card p {
    font-size: 0.95rem !important;
    line-height: 1.65 !important;
    color: #4b5563 !important;
}
.inst-cellule-card strong { color: #1f2937; font-size: 0.95rem; }

@media (max-width: 768px) {
    .institution-hero, .inst-hero { padding: 50px 16px !important; }
    .institution-page .inst-section { padding: 44px 0 !important; }
    .inst-hero-box { padding: 28px 24px; }
    .inst-hero-box:hover { transform: translateY(-4px) scale(1); }
    .inst-section-title { font-size: 1.5rem; }
    .inst-legal-intro { font-size: 1rem; }
    .inst-missions-list li { font-size: 0.95rem; }
}

/* Révélation au scroll — fluide */
.inst-reveal {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 0.7s var(--inst-ease-out), transform 0.7s var(--inst-ease-out);
}
.inst-reveal.inst-visible {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .inst-hero-box { animation: none; }
    .inst-hero { animation: none; }
    .inst-hero-shine { animation: none; }
    .inst-tutelle-card, .inst-org-card, .inst-cellule-card { transition: none; }
    a:hover .inst-tutelle-card { transform: none; }
    .inst-reveal { opacity: 1; transform: none; }
    .inst-reveal.inst-visible { opacity: 1; transform: none; }
    .inst-section-title::after { transition: none; width: 60px; left: 50%; margin-left: -30px; }
    .inst-org-card, .inst-cellule-card { opacity: 1; transform: none; }
}
</style>

<script>
(function() {
    'use strict';

    // Smooth scroll fluide pour les ancres
    document.querySelectorAll('.institution-page a[href^="#"]').forEach(function(anchor) {
        var href = anchor.getAttribute('href');
        if (href === '#') return;
        anchor.addEventListener('click', function(e) {
            var target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Révélation au scroll + cascade des cartes (Intersection Observer)
    var revealEls = document.querySelectorAll('.inst-reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var delay = parseInt(el.getAttribute('data-delay') || '0', 10);
                setTimeout(function() {
                    el.classList.add('inst-visible');
                    // Cascade fluide des cartes Organisation et Cellules
                    var cards = el.querySelectorAll('.inst-org-card, .inst-cellule-card');
                    cards.forEach(function(card, i) {
                        setTimeout(function() {
                            card.classList.add('inst-card-visible');
                        }, 80 * i);
                    });
                }, delay);
            });
        }, { rootMargin: '0px 0px -50px 0px', threshold: 0.08 });

        revealEls.forEach(function(el) { observer.observe(el); });
    } else {
        revealEls.forEach(function(el) {
            el.classList.add('inst-visible');
            el.querySelectorAll('.inst-org-card, .inst-cellule-card').forEach(function(c) { c.classList.add('inst-card-visible'); });
        });
    }
})();
</script>
@endsection
