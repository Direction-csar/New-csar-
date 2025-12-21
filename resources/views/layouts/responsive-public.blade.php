@extends('layouts.responsive-base')

@section('title', 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience')

@section('styles')
<style>
    /* Public site responsive styles */
    .public-layout {
        @apply min-h-screen bg-white;
    }
    
    /* Header responsive */
    .public-header {
        @apply bg-white shadow-sm sticky top-0 z-40;
    }
    
    .header-container {
        @apply responsive-container py-4;
    }
    
    .header-content {
        @apply flex items-center justify-between;
    }
    
    .logo-container {
        @apply flex items-center space-x-3;
    }
    
    .logo {
        @apply w-10 h-10 sm:w-12 sm:h-12;
    }
    
    .logo-text {
        @apply hidden sm:block;
    }
    
    .logo-title {
        @apply text-lg sm:text-xl font-bold text-csar-blue-600;
    }
    
    .logo-subtitle {
        @apply text-xs sm:text-sm text-gray-600;
    }
    
    /* Navigation responsive */
    .desktop-nav {
        @apply hidden lg:flex items-center space-x-8;
    }
    
    .nav-item {
        @apply text-gray-700 hover:text-csar-green-600 font-medium transition-colors duration-200;
    }
    
    .nav-item.active {
        @apply text-csar-green-600;
    }
    
    /* Mobile menu */
    .mobile-menu-btn {
        @apply lg:hidden p-2 text-gray-600 hover:text-gray-900;
    }
    
    .mobile-menu {
        @apply fixed inset-0 bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out;
    }
    
    .mobile-menu.open {
        @apply translate-x-0;
    }
    
    .mobile-menu-header {
        @apply flex items-center justify-between p-4 border-b border-gray-200;
    }
    
    .mobile-menu-close {
        @apply p-2 text-gray-600 hover:text-gray-900;
    }
    
    .mobile-nav {
        @apply p-4 space-y-4;
    }
    
    .mobile-nav-item {
        @apply block py-3 text-lg font-medium text-gray-700 hover:text-csar-green-600 border-b border-gray-100;
    }
    
    /* Hero section responsive */
    .hero-section {
        @apply bg-gradient-to-br from-csar-blue-50 to-csar-green-50 py-12 sm:py-16 lg:py-20;
    }
    
    .hero-container {
        @apply responsive-container;
    }
    
    .hero-content {
        @apply text-center lg:text-left lg:flex lg:items-center lg:justify-between;
    }
    
    .hero-text {
        @apply lg:flex-1 lg:pr-8;
    }
    
    .hero-title {
        @apply text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold text-gray-900 leading-tight;
    }
    
    .hero-subtitle {
        @apply text-lg sm:text-xl lg:text-2xl text-gray-600 mt-4 lg:mt-6;
    }
    
    .hero-description {
        @apply text-base sm:text-lg text-gray-600 mt-4 lg:mt-6 max-w-2xl mx-auto lg:mx-0;
    }
    
    .hero-actions {
        @apply flex flex-col sm:flex-row gap-4 mt-8 lg:mt-10 justify-center lg:justify-start;
    }
    
    .hero-image {
        @apply mt-8 lg:mt-0 lg:flex-1;
    }
    
    .hero-image img {
        @apply w-full h-auto rounded-lg shadow-lg;
    }
    
    /* Features section responsive */
    .features-section {
        @apply py-12 sm:py-16 lg:py-20;
    }
    
    .features-grid {
        @apply responsive-grid-2 lg:responsive-grid-3 gap-8 sm:gap-12;
    }
    
    .feature-card {
        @apply text-center;
    }
    
    .feature-icon {
        @apply w-16 h-16 sm:w-20 sm:h-20 bg-csar-green-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6;
    }
    
    .feature-title {
        @apply text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-4;
    }
    
    .feature-description {
        @apply text-base sm:text-lg text-gray-600;
    }
    
    /* Stats section responsive */
    .stats-section {
        @apply bg-csar-blue-600 py-12 sm:py-16 lg:py-20;
    }
    
    .stats-grid {
        @apply responsive-grid-2 lg:responsive-grid-4 gap-8;
    }
    
    .stat-item {
        @apply text-center text-white;
    }
    
    .stat-value {
        @apply text-3xl sm:text-4xl lg:text-5xl font-bold mb-2;
    }
    
    .stat-label {
        @apply text-sm sm:text-base opacity-90;
    }
    
    /* News section responsive */
    .news-section {
        @apply py-12 sm:py-16 lg:py-20 bg-gray-50;
    }
    
    .news-grid {
        @apply responsive-grid-2 lg:responsive-grid-3 gap-6 sm:gap-8;
    }
    
    .news-card {
        @apply responsive-card hover:shadow-lg transition-shadow duration-300;
    }
    
    .news-image {
        @apply w-full h-48 sm:h-56 object-cover rounded-lg mb-4;
    }
    
    .news-date {
        @apply text-sm text-gray-500 mb-2;
    }
    
    .news-title {
        @apply text-lg sm:text-xl font-bold text-gray-900 mb-3 line-clamp-2;
    }
    
    .news-excerpt {
        @apply text-gray-600 line-clamp-3;
    }
    
    /* Contact section responsive */
    .contact-section {
        @apply py-12 sm:py-16 lg:py-20;
    }
    
    .contact-grid {
        @apply responsive-grid-2 gap-8 sm:gap-12;
    }
    
    .contact-info {
        @apply space-y-6;
    }
    
    .contact-item {
        @apply flex items-start space-x-4;
    }
    
    .contact-icon {
        @apply w-8 h-8 bg-csar-green-100 rounded-lg flex items-center justify-center flex-shrink-0;
    }
    
    .contact-text {
        @apply text-gray-600;
    }
    
    .contact-title {
        @apply font-semibold text-gray-900 mb-1;
    }
    
    /* Forms responsive */
    .contact-form {
        @apply responsive-card;
    }
    
    .form-grid {
        @apply responsive-grid-2 gap-4 sm:gap-6;
    }
    
    .form-group {
        @apply space-y-2;
    }
    
    .form-label {
        @apply block text-sm font-medium text-gray-700;
    }
    
    .form-input {
        @apply responsive-input;
    }
    
    .form-textarea {
        @apply responsive-input resize-none;
        min-height: 120px;
    }
    
    .form-button {
        @apply responsive-btn responsive-btn-primary w-full sm:w-auto;
    }
    
    /* Footer responsive */
    .public-footer {
        @apply bg-gray-900 text-white py-12 sm:py-16;
    }
    
    .footer-container {
        @apply responsive-container;
    }
    
    .footer-content {
        @apply responsive-grid-2 lg:responsive-grid-4 gap-8;
    }
    
    .footer-section {
        @apply space-y-4;
    }
    
    .footer-title {
        @apply text-lg font-semibold mb-4;
    }
    
    .footer-links {
        @apply space-y-2;
    }
    
    .footer-link {
        @apply text-gray-300 hover:text-white transition-colors duration-200;
    }
    
    .footer-bottom {
        @apply border-t border-gray-800 mt-8 pt-8 text-center text-gray-400;
    }
    
    /* Map responsive */
    .map-container {
        @apply responsive-card;
        height: 300px;
    }
    
    @media (min-width: 768px) {
        .map-container {
            height: 400px;
        }
    }
    
    @media (min-width: 1024px) {
        .map-container {
            height: 500px;
        }
    }
    
    /* Accessibility improvements */
    .skip-link {
        @apply sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-csar-green-600 text-white px-4 py-2 rounded-lg z-50;
    }
    
    /* Loading states */
    .loading-skeleton {
        @apply animate-pulse bg-gray-200 rounded;
    }
    
    /* Print styles */
    @media print {
        .mobile-menu-btn,
        .mobile-menu,
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="public-layout">
    <!-- Skip to main content -->
    <a href="#main-content" class="skip-link">
        Aller au contenu principal
    </a>
    
    <!-- Header -->
    <header class="public-header">
        <div class="header-container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo-container">
                    <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" class="logo">
                    <div class="logo-text">
                        <h1 class="logo-title">CSAR</h1>
                        <p class="logo-subtitle">Sécurité Alimentaire & Résilience</p>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="desktop-nav">
                    <a href="{{ url('/') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                        Accueil
                    </a>
                    <a href="{{ route('about') }}" class="nav-item {{ request()->routeIs('about') ? 'active' : '' }}">
                        À propos
                    </a>
                    <a href="{{ route('news.index') }}" class="nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}">
                        Actualités
                    </a>
                    <a href="{{ route('partners', app()->getLocale()) }}" class="nav-item {{ request()->routeIs('partners') ? 'active' : '' }}">
                        Partenaires
                    </a>
                    <a href="{{ route('requests.index') }}" class="nav-item {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                        Demandes
                    </a>
                    <a href="{{ route('contact.simple') }}" class="nav-item {{ request()->routeIs('contact') ? 'active' : '' }}">
                        Contact
                    </a>
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
            <a href="{{ url('/') }}" class="mobile-nav-item {{ request()->is('/') ? 'active' : '' }}">
                Accueil
            </a>
            <a href="{{ route('about') }}" class="mobile-nav-item {{ request()->routeIs('about') ? 'active' : '' }}">
                À propos
            </a>
            <a href="{{ route('news.index') }}" class="mobile-nav-item {{ request()->routeIs('news.*') ? 'active' : '' }}">
                Actualités
            </a>
            <a href="{{ route('partners', app()->getLocale()) }}" class="mobile-nav-item {{ request()->routeIs('partners') ? 'active' : '' }}">
                Partenaires
            </a>
            <a href="{{ route('requests.index') }}" class="mobile-nav-item {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                Demandes
            </a>
            <a href="{{ route('contact.simple') }}" class="mobile-nav-item {{ request()->routeIs('contact') ? 'active' : '' }}">
                Contact
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <main id="main-content">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Logo & Description -->
                <div class="footer-section">
                    <div class="logo-container mb-4">
                        <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" class="logo">
                        <div>
                            <h3 class="logo-title">CSAR</h3>
                            <p class="logo-subtitle">Sécurité Alimentaire & Résilience</p>
                        </div>
                    </div>
                    <p class="text-gray-300">
                        Le Commissariat à la Sécurité Alimentaire et à la Résilience du Sénégal œuvre pour garantir la sécurité alimentaire et renforcer la résilience des populations.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h4 class="footer-title">Liens rapides</h4>
                    <div class="footer-links">
                        <a href="{{ url('/') }}" class="footer-link">Accueil</a>
                        <a href="{{ route('about') }}" class="footer-link">À propos</a>
                        <a href="{{ route('news.index') }}" class="footer-link">Actualités</a>
                        <a href="{{ route('requests.index') }}" class="footer-link">Demandes</a>
                    </div>
                </div>
                
                <!-- Services -->
                <div class="footer-section">
                    <h4 class="footer-title">Services</h4>
                    <div class="footer-links">
                        <a href="{{ route('requests.create', ['type' => 'aide']) }}" class="footer-link">Demande d'aide alimentaire</a>
                        <a href="{{ route('requests.create', ['type' => 'audience']) }}" class="footer-link">Demande d'audience</a>
                        <a href="{{ route('requests.create', ['type' => 'partenariat']) }}" class="footer-link">Demande de partenariat</a>
                        <a href="{{ route('contact.simple') }}" class="footer-link">Contact</a>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="footer-section">
                    <h4 class="footer-title">Contact</h4>
                    <div class="footer-links">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt text-csar-green-600"></i>
                            </div>
                            <div class="contact-text">
                                <p class="contact-title">Adresse</p>
                                <p>Dakar, Sénégal</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone text-csar-green-600"></i>
                            </div>
                            <div class="contact-text">
                                <p class="contact-title">Téléphone</p>
                                <p>+221 33 XXX XX XX</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope text-csar-green-600"></i>
                            </div>
                            <div class="contact-text">
                                <p class="contact-title">Email</p>
                                <p>contact@csar.sn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</div>

<script>
// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    
    if (mobileMenuBtn && mobileMenu && mobileMenuClose) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
        
        mobileMenuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        });
        
        // Close menu when clicking on links
        mobileMenu.querySelectorAll('.mobile-nav-item').forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
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
</script>
@endsection

