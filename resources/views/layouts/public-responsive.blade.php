<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')</title>
    
    <!-- Meta tags -->
    <meta name="description" content="Plateforme numérique du CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience du Sénégal">
    <meta name="keywords" content="CSAR, sécurité alimentaire, résilience, Sénégal, entrepôts, stocks">
    <meta name="author" content="CSAR">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')">
    <meta property="og:description" content="@yield('description', 'Plateforme numérique du CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience du Sénégal')">
    <meta property="og:image" content="@yield('og_image', asset('images/logos/LOGO CSAR vectoriel-01.png'))">
    <meta property="og:site_name" content="CSAR">
    <meta property="og:locale" content="fr_FR">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')">
    <meta property="twitter:description" content="@yield('description', 'Plateforme numérique du CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience du Sénégal')">
    <meta property="twitter:image" content="@yield('og_image', asset('images/logos/LOGO CSAR vectoriel-01.png'))">
    <meta property="twitter:site" content="@csar_sn">
    <meta property="twitter:creator" content="@csar_sn">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/csar-logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/csar-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/csar-logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-optimizations.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-helpers.css') }}?v={{ time() }}">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="public-header">
        <div class="header-container">
            <div class="header-content">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="logo-container">
                    <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="Logo CSAR" class="logo">
                    <div class="logo-text">
                        <h1 class="logo-title">CSAR</h1>
                        <p class="logo-subtitle">Sécurité Alimentaire & Résilience</p>
                    </div>
                </a>
                
                <!-- Navigation Desktop -->
                <nav class="nav-desktop">
                    <a href="{{ url('/') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">Accueil</a>
                    <a href="{{ route('about') }}" class="nav-item {{ request()->routeIs('about') ? 'active' : '' }}">À propos</a>
                    <a href="{{ route('institution') }}" class="nav-item {{ request()->routeIs('institution') ? 'active' : '' }}">Institution</a>
                    <a href="{{ route('news') }}" class="nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}">Actualités</a>
                    <a href="{{ route('map') }}" class="nav-item {{ request()->routeIs('map') ? 'active' : '' }}">Carte</a>
                    <a href="{{ route('partners.index') }}" class="nav-item {{ request()->routeIs('partners.*') ? 'active' : '' }}">Partenaires</a>
                    <a href="{{ route('sim.index') }}" class="nav-item {{ request()->routeIs('sim.*') ? 'active' : '' }}">SIM</a>
                    <a href="{{ route('gallery') }}" class="nav-item {{ request()->routeIs('gallery') || request()->routeIs('gallery.*') ? 'active' : '' }}">Missions</a>
                    <a href="{{ route('contact.simple') }}" class="nav-item {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </nav>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Ouvrir le menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <div class="logo-container">
                <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" class="logo">
                <div>
                    <h2 class="logo-title">CSAR</h2>
                    <p class="logo-subtitle">Sécurité Alimentaire & Résilience</p>
                </div>
            </div>
            <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Fermer le menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mobile-nav">
            <a href="{{ url('/') }}" class="mobile-nav-item {{ request()->is('/') ? 'active' : '' }}">Accueil</a>
            <a href="{{ route('about') }}" class="mobile-nav-item {{ request()->routeIs('about') ? 'active' : '' }}">À propos</a>
            <a href="{{ route('institution') }}" class="mobile-nav-item {{ request()->routeIs('institution') ? 'active' : '' }}">Institution</a>
            <a href="{{ route('news') }}" class="mobile-nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}">Actualités</a>
            <a href="{{ route('map') }}" class="mobile-nav-item {{ request()->routeIs('map') ? 'active' : '' }}">Carte</a>
            <a href="{{ route('partners.index') }}" class="mobile-nav-item {{ request()->routeIs('partners.*') ? 'active' : '' }}">Partenaires</a>
            <a href="{{ route('sim.index') }}" class="mobile-nav-item {{ request()->routeIs('sim.*') ? 'active' : '' }}">SIM</a>
            <a href="{{ route('gallery') }}" class="mobile-nav-item {{ request()->routeIs('gallery') || request()->routeIs('gallery.*') ? 'active' : '' }}">Missions</a>
            <a href="{{ route('contact.simple') }}" class="mobile-nav-item {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
        </nav>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.public-footer')

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/advanced-validation.js') }}"></script>
    @vite(['resources/js/app.js'])
    
    <script>
    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        
        if (mobileMenuBtn && mobileMenu && mobileMenuClose && mobileMenuOverlay) {
            // Open menu
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.add('active');
                mobileMenuOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            // Close menu
            function closeMenu() {
                mobileMenu.classList.remove('active');
                mobileMenuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            mobileMenuClose.addEventListener('click', closeMenu);
            mobileMenuOverlay.addEventListener('click', closeMenu);
            
            // Close menu when clicking on links
            mobileMenu.querySelectorAll('.mobile-nav-item').forEach(link => {
                link.addEventListener('click', closeMenu);
            });
            
            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                    closeMenu();
                }
            });
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        document.querySelectorAll('.feature-card, .news-card, .stat-item').forEach(el => {
            observer.observe(el);
        });
    });

    // Wrap tables for horizontal scroll on small screens
    document.addEventListener('DOMContentLoaded', function() {
        const main = document.querySelector('main');
        if (!main) return;
        main.querySelectorAll('table').forEach(function(table) {
            if (!table.closest('.table-responsive-wrapper')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    });
    </script>
    
    @stack('scripts')
</body>
</html>

