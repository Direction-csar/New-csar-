<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', __('messages.meta.default_title'))</title>
    <meta name="description" content="@yield('meta_description', __('messages.meta.default_description'))">
    <meta name="keywords" content="@yield('meta_keywords', __('messages.meta.default_keywords'))">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="author" content="CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience">
    <!-- Open Graph / LinkedIn -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')">
    <meta property="og:description" content="@yield('meta_description', 'Commissariat à la Sécurité Alimentaire et à la Résilience du Sénégal.')">
    <meta property="og:image" content="@yield('og_image', asset('images/logos/LOGO CSAR vectoriel-01.png'))">
    <meta property="og:site_name" content="CSAR">
    <meta property="og:locale" content="{{ app()->getLocale() == 'fr' ? 'fr_SN' : 'en_US' }}">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', __('messages.meta.twitter_title'))">
    <meta name="twitter:description" content="@yield('meta_description', 'CSAR Sénégal - Sécurité alimentaire et résilience.')">
    @stack('head')
    <!-- Schema.org Organization (SEO) -->
    <script type="application/ld+json">@json(\App\Services\SeoService::generateOrganizationSchema())</script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/csar-logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/csar-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/csar-logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS - Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    @stack('styles')
    <style>
        :root {
            --primary-color: #00a86b;
            --secondary-color: #0078d4;
            --success-color: #00b894;
            --warning-color: #fdcb6e;
            --danger-color: #e17055;
            --info-color: #74b9ff;
            --light-color: #f8f9fa;
            --dark-color: #2d3436;
            --gradient-primary: linear-gradient(135deg, #00a86b 0%, #0078d4 100%);
            --gradient-success: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            --gradient-warning: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
            --gradient-danger: linear-gradient(135deg, #e17055 0%, #d63031 100%);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.1);
            --shadow-xl: 0 16px 32px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* ========== NAVBAR style WFP inspiré ========== */
        /* Barre utilitaire : recherche, FAQ, MÉDIAS & RESSOURCES, Langue */
        .nav-top-bar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8125rem;
            padding: 0.4rem 0;
        }
        .nav-top-bar .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding-left: 1rem;
            padding-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .nav-top-utils {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .nav-top-search, .nav-top-faq {
            color: #6b7280;
            text-decoration: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: color 0.2s;
        }
        .nav-top-search:hover, .nav-top-faq:hover { 
            color: #00a86b; 
        }
        .nav-top-dropdown {
            color: #6b7280 !important;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-decoration: none !important;
            padding: 0.25rem 0.5rem;
            background: none !important;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .nav-top-dropdown:hover { color: #374151 !important; }
        .nav-top-dropdown::after { margin-left: 0.25rem; }
        .nav-top-bar {
            position: relative;
            z-index: 1200;
        }
        .nav-top-bar .dropdown-menu {
            font-size: 0.875rem;
            text-transform: none;
            border-radius: 6px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            z-index: 1201 !important;
        }
        .nav-top-bar .dropdown { position: relative; }
        .nav-top-bar .dropdown-item { padding: 0.4rem 1rem; }
        @media (max-width: 991.98px) {
            .nav-top-bar { display: none; }
        }
        /* Barre principale */
        .navbar {
            background: #fff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            padding: 0.5rem 0;
            font-size: 0.9375rem;
            border-bottom: 2px solid #00a86b;
        }
        .navbar .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.35rem;
            color: #00a86b !important;
            text-decoration: none;
            letter-spacing: -0.02em;
            transition: color 0.2s ease;
        }
        .navbar-brand:hover {
            color: #008f5a !important;
        }
        .navbar-brand img {
            height: 42px;
            width: auto;
            object-fit: contain;
        }
        .csar-text {
            font-weight: 700;
            color: inherit;
            letter-spacing: 0.02em;
        }
        .navbar-toggler {
            border: 1px solid rgba(0, 168, 107, 0.35);
            border-radius: 6px;
            padding: 0.5rem 0.65rem;
        }
        .navbar-toggler:focus { box-shadow: 0 0 0 2px rgba(0, 168, 107, 0.2); }
        .navbar-toggler-icon { width: 1.25em; height: 1.25em; }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 0.05rem;
            margin: 0 auto;
        }
        .navbar-nav .nav-item { margin: 0; }
        .navbar-nav .nav-link {
            color: #374151 !important;
            font-weight: 600;
            padding: 0.55rem 0.9rem;
            border-radius: 0;
            transition: color 0.2s ease, box-shadow 0.2s ease;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
        }
        .navbar-nav .nav-link:hover {
            color: #00a86b !important;
            background: transparent;
            border-bottom-color: rgba(0, 168, 107, 0.35);
        }
        .navbar-nav .nav-link.show,
        .navbar-nav .nav-item.dropdown.show .nav-link {
            color: #00a86b !important;
            background: transparent;
            border-bottom-color: #00a86b;
        }
        .navbar-nav .nav-item.dropdown .nav-link::after { display: inline-block; margin-left: 0.35em; }
        .navbar-nav .dropdown-toggle::after {
            border-width: 0.35em;
            opacity: 0.8;
        }
        /* Mega-menu style (WFP) - dropdowns larges */
        .navbar-nav .dropdown-menu {
            margin-top: 0;
            padding: 0;
            min-width: 280px;
            border: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            border-top: 3px solid #00a86b;
            z-index: 1100 !important;
        }
        .navbar-nav .dropdown { position: relative; }
        .navbar-nav .dropdown-menu .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            color: #374151;
            transition: background 0.15s ease, color 0.15s ease;
            border-left: 3px solid transparent;
        }
        .navbar-nav .dropdown-menu .dropdown-item i {
            width: 1.1em; margin-right: 0.6rem; opacity: 0.7;
        }
        .navbar-nav .dropdown-menu .dropdown-item:hover,
        .navbar-nav .dropdown-menu .dropdown-item:focus {
            background: rgba(0, 168, 107, 0.08);
            color: #00a86b;
            border-left-color: #00a86b;
        }
        .nav-btn-don {
            background: #c41e3a !important;
            color: #fff !important;
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            border: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .nav-btn-don:hover {
            background: #a01830 !important;
            color: #fff !important;
            transform: translateY(-1px);
        }
        .navbar-nav .nav-link {
            font-weight: 700;
            color: #1f2937 !important;
        }
        .language-selector {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-left: 0.75rem;
            padding-left: 0.75rem;
            border-left: 1px solid #e2e8f0;
        }
        .language-selector .language-flag {
            width: 22px;
            height: auto;
            opacity: 0.9;
        }
        .language-selector a:hover .language-flag,
        .language-selector .language-flag.active { opacity: 1; }
        @media (max-width: 991.98px) {
            .navbar-nav {
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                margin: 0.75rem 0 0;
                padding-top: 0.5rem;
                border-top: 1px solid #e2e8f0;
            }
            .navbar-nav .nav-link { border-bottom: none !important; padding: 0.65rem 1rem; }
            .navbar-nav .nav-item { border-bottom: 1px solid #e2e8f0; }
            .navbar-nav .nav-item:last-child { border-bottom: 0; }
            .navbar-nav .dropdown-menu {
                margin: 0.25rem 0 0.5rem 1rem;
                border-radius: 8px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-top: 3px solid #00a86b;
            }
            .nav-btn-don { margin: 0.5rem 1rem 0; text-align: center; }
            .language-selector {
                margin-left: 0; padding-left: 0; border-left: 0;
                padding-top: 0.5rem; margin-top: 0.5rem;
                justify-content: center;
            }
        }

        /* Styles pour le sélecteur de langue */
        .language-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .language-flag {
            width: 24px;
            height: 18px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .language-flag:hover {
            transform: scale(1.1);
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .language-flag.active {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.5);
        }

        .language-text {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 0;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .footer {
            background: linear-gradient(135deg, #1e3a8a 0%, #22c55e 50%, #1e3a8a 100%);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 5rem;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        }

        .footer h5 {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        /* Ensure page content is not hidden under sticky navbar */
        main {
            padding-top: 2rem;
        }

        .social-links {
            margin-top: 1.5rem;
            text-align: center;
        }

        .social-links a {
            display: inline-block;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            text-align: center;
            line-height: 45px;
            margin-right: 0.8rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .social-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .social-links a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .social-links a:hover::before {
            left: 100%;
        }

        .social-links a i {
            font-size: 1.2rem;
            color: white;
            position: relative;
            z-index: 2;
        }

        .newsletter-section input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .newsletter-section input:focus {
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
            color: white !important;
            box-shadow: none !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .display-4 {
                font-size: 2rem;
            }
            
            .lead {
                font-size: 1rem;
            }
        }

        /* Animations */
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

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    {{-- Barre utilitaire : Recherche, FAQ, MÉDIAS & RESSOURCES, Langue --}}
    <div class="nav-top-bar">
        <div class="container-fluid">
            <div class="nav-top-utils">
                <a href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" class="nav-top-search" title="{{ __('messages.nav.search') }}"><i class="fas fa-search"></i> {{ __('messages.nav.search') }}</a>
                <a href="{{ route('faq.index', ['locale' => app()->getLocale()]) }}" class="nav-top-faq" title="{{ __('messages.nav.faq') }}"><i class="fas fa-question-circle"></i> {{ __('messages.nav.faq') }}</a>
                <div class="dropdown">
                    <button class="nav-top-dropdown dropdown-toggle" type="button" id="dropdownMedias" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.media_resources') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMedias">
                        <li><a class="dropdown-item" href="{{ route('news.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.news') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('ressources.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.resources') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('reports', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.reports') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('gallery', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.gallery') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('speeches', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.speeches') }}</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="nav-top-dropdown dropdown-toggle" type="button" id="dropdownLang" data-bs-toggle="dropdown" aria-expanded="false">{{ app()->getLocale() == 'fr' ? __('messages.language.french') : __('messages.language.english') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLang">
                        <li><a class="dropdown-item" href="{{ locale_url('fr') }}">{{ __('messages.language.french') }}</a></li>
                        <li><a class="dropdown-item" href="{{ locale_url('en') }}">{{ __('messages.language.english') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre principale (style WFP : logo, liens, CTA don, recherche) --}}
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}';">
                <span class="csar-text">CSAR</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto align-items-lg-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('about', ['locale' => app()->getLocale()]) }}" id="menuQui" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.who_we_are') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuQui">
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}"><i class="fas fa-info-circle me-2 text-muted"></i>{{ __('messages.nav.about_csar') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('institution', ['locale' => app()->getLocale()]) }}"><i class="fas fa-building me-2 text-muted"></i>{{ __('messages.nav.institution') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}#gouvernance"><i class="fas fa-users me-2 text-muted"></i>{{ __('messages.nav.governance') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}#histoire"><i class="fas fa-history me-2 text-muted"></i>{{ __('messages.nav.history') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}#strategie"><i class="fas fa-chess me-2 text-muted"></i>{{ __('messages.nav.strategy') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}" id="menuMission" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.our_mission') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuMission">
                            <li><a class="dropdown-item" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-bullseye me-2 text-muted"></i>{{ __('messages.nav.food_security') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('map', ['locale' => app()->getLocale()]) }}"><i class="fas fa-warehouse me-2 text-muted"></i>{{ __('messages.nav.stock_management') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}"><i class="fas fa-hand-holding-heart me-2 text-muted"></i>{{ __('messages.nav.humanitarian_assistance') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}#resilience"><i class="fas fa-shield-alt me-2 text-muted"></i>{{ __('messages.nav.resilience_development') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('sim-reports.index') }}" id="menuInterventions" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.interventions') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuInterventions">
                            <li><a class="dropdown-item" href="{{ route('sim-reports.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-chart-line me-2 text-muted"></i>{{ __('messages.nav.sim_full') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-tasks me-2 text-muted"></i>{{ __('messages.nav.ongoing_programs') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('map', ['locale' => app()->getLocale()]) }}"><i class="fas fa-map-marked-alt me-2 text-muted"></i>{{ __('messages.nav.intervention_areas') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports', ['locale' => app()->getLocale()]) }}"><i class="fas fa-file-alt me-2 text-muted"></i>{{ __('messages.nav.activity_reports') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}" id="menuImpliquez" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.get_involved') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuImpliquez">
                            <li><a class="dropdown-item" href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}"><i class="fas fa-hand-holding-heart me-2 text-muted"></i>{{ __('messages.nav.request_help') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-handshake me-2 text-muted"></i>{{ __('messages.nav.become_partner') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('contact', ['locale' => app()->getLocale()]) }}#collaborer"><i class="fas fa-users me-2 text-muted"></i>{{ __('messages.nav.collaborate_with_us') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('contact', ['locale' => app()->getLocale()]) }}"><i class="fas fa-envelope me-2 text-muted"></i>{{ __('messages.nav.contact_us') }}</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('don.index', ['locale' => app()->getLocale()]) }}" class="nav-btn-don text-decoration-none">{{ __('messages.nav.make_donation') }}</a>
                    {{-- Sur mobile : langue (la barre utilitaire est masquée) --}}
                    <div class="language-selector d-lg-none ms-2">
                        <a href="{{ locale_url('fr') }}" class="d-flex align-items-center text-decoration-none text-dark" title="Français"><span class="small fw-bold">FR</span></a>
                        <a href="{{ locale_url('en') }}" class="d-flex align-items-center text-decoration-none text-dark ms-2" title="English"><span class="small fw-bold">EN</span></a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="fade-in-up">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <!-- Contenu principal du footer -->
            <div class="row">
                <!-- Colonne 1: Logo CSAR + Texte -->
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand text-center">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="Logo CSAR" class="footer-logo" onerror="this.style.display='none';">
                        </div>
                        <h5 class="footer-title">CSAR</h5>
                        <p class="small opacity-75 mb-2">{{ __('messages.home.title') }}</p>
                        <div class="social-links">
                            <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener" title="Suivez-nous sur LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://www.facebook.com/people/Commissariat-%C3%A0-la-S%C3%A9curit%C3%A9-Alimentaire-et-%C3%A0-la-R%C3%A9silience/61562947586356/?mibextid=wwXIfr&rdid=rdi0HoJAMnm5SUWB&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1A15LpvcqT%2F%3Fmibextid%3DwwXIfr" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://x.com/csar_sn?s=21" target="_blank" title="Twitter/X">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.instagram.com/csar.sn/?igsh=MWcxbTJnNzBnZGo5Mg%3D%3D&utm_source=qr" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne 2: Liens rapides -->
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-section-title">{{ __('messages.nav.quick_links') }}</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.home') }}</a></li>
                        <li><a href="{{ route('about', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.who_we_are') }}</a></li>
                        <li><a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.news') }}</a></li>
                        <li><a href="{{ route('don.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.make_donation_short') }}</a></li>
                        <li><a href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.our_mission') }}</a></li>
                        <li><a href="{{ route('sim-reports.index') }}">{{ __('messages.nav.interventions') }}</a></li>
                        <li><a href="{{ route('faq.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.faq') }}</a></li>
                    </ul>
                </div>
                
                <!-- Colonne 3: Contact -->
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-section-title">{{ __('messages.nav.contact') }}</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('contact', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.contact_us') }}</a></li>
                        <li><a href="{{ route('action', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.request_assistance') }}</a></li>
                        <li><a href="{{ route('track', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.track_request') }}</a></li>
                        <li><a href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.partners') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Séparateur -->
            <hr class="footer-divider">
            
            <!-- Footer bottom -->
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright mb-0">&copy; {{ date('Y') }} CSAR - {{ __('messages.home.title') }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="legal-link me-3">{{ __('messages.footer.privacy') }}</a>
                        <a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="legal-link">{{ __('messages.footer.terms') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialiser AOS
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
    @stack('scripts')
</body>
</html>
