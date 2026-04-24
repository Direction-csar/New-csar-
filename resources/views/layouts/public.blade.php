<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
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
    <meta property="og:title" content="@yield('title', __('messages.meta.default_title'))">
    <meta property="og:description" content="@yield('meta_description', __('messages.meta.default_description'))">
    <meta property="og:image" content="@yield('og_image', asset('images/logos/LOGO CSAR vectoriel-01.png'))">
    <meta property="og:site_name" content="CSAR">
    <meta property="og:locale" content="{{ app()->getLocale() == 'fr' ? 'fr_SN' : (app()->getLocale() == 'ar' ? 'ar_SA' : 'en_US') }}">
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
    
    <!-- DNS Prefetch & Resource Hints -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    
    <!-- Fonts with display=swap (non-blocking) -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /></noscript>
    @if(app()->getLocale() == 'ar')
    <!-- Arabic font (Amiri) - loaded only for Arabic locale -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet" />
    @endif
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Font Awesome (non-blocking) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'" />
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <!-- AOS removed for performance -->
    
    <!-- Custom CSS -->
    @stack('styles')
    <style>
        :root {
            --primary-color: #00a86b;
            --secondary-color: #0078d4;
            --success-color: #00a86b;
            --warning-color: #fdcb6e;
            --danger-color: #c41e3a;
            --info-color: #0078d4;
            --light-color: #f8f9fa;
            --dark-color: #2d3436;
            --gradient-primary: linear-gradient(135deg, #00a86b 0%, #0078d4 100%);
            --gradient-success: linear-gradient(135deg, #00a86b 0%, #008f5a 100%);
            --gradient-warning: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
            --gradient-danger: linear-gradient(135deg, #c41e3a 0%, #a01830 100%);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.1);
            --shadow-xl: 0 16px 32px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ========== NAVBAR style WFP/CSAR (utilitaire + principale, FAITES UN DON) ========== */
        /* Barre utilitaire : Recherche, FAQ, Médias & ressources, Langue */
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
        .nav-top-search:hover, .nav-top-faq:hover { color: #00a86b; }
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
            border-bottom-color: rgba(4, 120, 87, 0.35);
        }
        .navbar-nav .nav-link.show,
        .navbar-nav .nav-item.dropdown.show .nav-link {
            color: #00a86b !important;
            background: transparent;
            border-bottom-color: #00a86b;
        }
        .navbar-nav .nav-item.dropdown .nav-link::after { display: inline-block; margin-left: 0.35em; }
        .navbar-nav .nav-item.dropdown .nav-link:not(.dropdown-toggle)::after { display: none; }
        .navbar-nav .dropdown-toggle::after {
            border-width: 0.35em;
            opacity: 0.8;
        }
        /* Mega-menu style (PAM) - dropdowns larges */
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
        .navbar-nav .dropdown {
            position: relative;
        }
        .navbar .d-flex.align-items-center {
            position: relative;
            z-index: 1;
        }
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

        /* RTL Support for Arabic */
        [dir="rtl"] body,
        [dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3,
        [dir="rtl"] h4, [dir="rtl"] h5, [dir="rtl"] h6,
        [dir="rtl"] p, [dir="rtl"] span, [dir="rtl"] li,
        [dir="rtl"] a, [dir="rtl"] button, [dir="rtl"] input,
        [dir="rtl"] textarea, [dir="rtl"] select, [dir="rtl"] label {
            font-family: 'Amiri', 'Noto Naskh Arabic', serif;
            direction: rtl;
        }
        [dir="rtl"] body {
            text-align: right;
        }
        [dir="rtl"] .navbar-nav {
            padding-right: 0;
        }
        /* Bootstrap spacing overrides */
        [dir="rtl"] .ms-auto { margin-left: unset !important; margin-right: auto !important; }
        [dir="rtl"] .ms-2 { margin-left: unset !important; margin-right: 0.5rem !important; }
        [dir="rtl"] .ms-3 { margin-left: unset !important; margin-right: 1rem !important; }
        [dir="rtl"] .ms-lg-3 { margin-left: unset !important; margin-right: 1rem !important; }
        [dir="rtl"] .me-2 { margin-right: unset !important; margin-left: 0.5rem !important; }
        [dir="rtl"] .me-3 { margin-right: unset !important; margin-left: 1rem !important; }
        [dir="rtl"] .me-auto { margin-right: unset !important; margin-left: auto !important; }
        [dir="rtl"] .ps-3 { padding-left: unset !important; padding-right: 1rem !important; }
        [dir="rtl"] .pe-3 { padding-right: unset !important; padding-left: 1rem !important; }
        [dir="rtl"] .ps-4 { padding-left: unset !important; padding-right: 1.5rem !important; }
        [dir="rtl"] .pe-4 { padding-right: unset !important; padding-left: 1.5rem !important; }
        /* Text alignment */
        [dir="rtl"] .text-start { text-align: right !important; }
        [dir="rtl"] .text-end { text-align: left !important; }
        /* Dropdowns */
        [dir="rtl"] .dropdown-menu { text-align: right; }
        [dir="rtl"] .dropdown-menu-end { right: auto !important; left: 0 !important; }
        [dir="rtl"] .dropdown-item { text-align: right; }
        /* Borders */
        [dir="rtl"] .border-left,
        [dir="rtl"] .border-start { border-left: none !important; border-right: 1px solid #e2e8f0 !important; }
        [dir="rtl"] .border-end { border-right: none !important; border-left: 1px solid #e2e8f0 !important; }
        /* Navigation */
        [dir="rtl"] .language-selector { padding-left: 0; padding-right: 0.75rem; border-left: none; border-right: 1px solid #e2e8f0; }
        [dir="rtl"] .nav-top-bar .d-flex { direction: rtl; }
        [dir="rtl"] .navbar-brand { margin-right: 0; margin-left: 1rem; }
        [dir="rtl"] .navbar-toggler { margin-left: 0; margin-right: auto; }
        /* Icons direction swap */
        [dir="rtl"] .fa-chevron-right:before { content: "\f053"; }
        [dir="rtl"] .fa-chevron-left:before { content: "\f054"; }
        [dir="rtl"] .fa-arrow-right:before { content: "\f060"; }
        [dir="rtl"] .fa-arrow-left:before { content: "\f061"; }
        /* Forms */
        [dir="rtl"] .form-control,
        [dir="rtl"] .form-select { text-align: right; }
        [dir="rtl"] .input-group { flex-direction: row-reverse; }
        [dir="rtl"] .input-group .form-control { border-radius: 0 0.375rem 0.375rem 0 !important; }
        [dir="rtl"] .input-group .btn { border-radius: 0.375rem 0 0 0.375rem !important; }
        /* Cards & lists */
        [dir="rtl"] .card-body { text-align: right; }
        [dir="rtl"] ul.list-unstyled { padding-right: 0; padding-left: revert; }
        [dir="rtl"] .list-group-item { text-align: right; }
        /* Footer */
        [dir="rtl"] .footer-links { text-align: right; }
        /* Float utilities */
        [dir="rtl"] .float-start { float: right !important; }
        [dir="rtl"] .float-end { float: left !important; }

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

        /* Styles pour le nouveau footer */
        .footer {
            background: linear-gradient(135deg, #1e3a8a 0%, #22c55e 50%, #1e3a8a 100%);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.08) 100%);
            pointer-events: none;
        }

        .footer::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .footer-brand {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .footer-logo {
            height: 80px;
            width: auto;
            margin: 0 auto;
            filter: brightness(1.1);
            display: block;
        }

        .footer-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ecf0f1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
        }

        .typing-text-container {
            margin-top: 1.5rem;
            text-align: center;
        }

        .typing-text {
            font-size: 1.5rem;
            color: #ffffff;
            line-height: 1.6;
            text-shadow: 0 2px 8px rgba(0,0,0,0.8), 0 0 15px rgba(0, 168, 107, 0.5);
            min-height: 3rem;
            font-weight: 700;
            letter-spacing: 1px;
            background: linear-gradient(135deg, rgba(0, 168, 107, 0.2), rgba(0, 200, 81, 0.2));
            padding: 20px 25px;
            border-radius: 15px;
            border: 2px solid rgba(0, 168, 107, 0.4);
            box-shadow: 0 6px 20px rgba(0, 168, 107, 0.3);
            margin: 1rem auto;
            display: block;
            width: 90%;
            max-width: 500px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }

        .typing-text:hover {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 168, 107, 0.4);
            border-color: rgba(0, 200, 81, 0.6);
            background: linear-gradient(135deg, rgba(0, 168, 107, 0.3), rgba(0, 200, 81, 0.3));
        }

        .typing-text::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .typing-text:hover::before {
            left: 100%;
        }

        .typing-text::after {
            content: '▍';
            animation: blink 1.2s infinite;
            color: #ffffff;
            font-weight: bold;
            font-size: 1.2em;
            text-shadow: 0 0 10px rgba(255,255,255,0.8);
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        .footer-section-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #ffffff;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .footer-section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #ffffff, #f0f0f0);
            border-radius: 2px;
            box-shadow: 0 2px 4px rgba(255,255,255,0.3);
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 0;
            font-weight: 500;
        }

        .footer-links a::before {
            content: '→';
            position: absolute;
            left: -20px;
            opacity: 0;
            transition: all 0.3s ease;
            color: #ffffff;
        }

        .footer-links a:hover {
            color: #ffffff;
            padding-left: 20px;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
        }

        .footer-links a:hover::before {
            opacity: 1;
            left: 0;
        }

        .footer-newsletter-desc {
            color: #ffffff;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .newsletter-form {
            position: relative;
        }

        .newsletter-input {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 2px solid rgba(255, 255, 255, 0.4) !important;
            color: white !important;
            border-radius: 25px 0 0 25px !important;
            padding: 12px 20px !important;
            font-weight: 500 !important;
        }

        .newsletter-input::placeholder {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500 !important;
        }

        .newsletter-input:focus {
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25) !important;
        }

        .newsletter-btn {
            background: linear-gradient(135deg, #ffffff, #f0f0f0) !important;
            border: none !important;
            border-radius: 0 25px 25px 0 !important;
            padding: 12px 20px !important;
            color: #00a86b !important;
            transition: all 0.3s ease;
        }

        .newsletter-btn:hover {
            background: linear-gradient(135deg, #ffffff, #f0f0f0) !important;
            color: #00a86b !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.4);
        }

        .newsletter-message {
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 5px;
            display: none;
        }

        .newsletter-message.success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .newsletter-message.error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .footer-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 2rem 0;
        }

        .institutional-logos {
            text-align: center;
            padding: 2rem 0;
        }

        .logos-title {
            color: #ffffff;
            margin-bottom: 2rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .logos-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 3rem;
            flex-wrap: wrap;
        }

        .logo-item-link {
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .logo-item-link:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-5px);
        }

        .logo-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
            padding: 1rem;
            border-radius: 10px;
            min-height: 120px;
            justify-content: center;
        }

        .logo-item-link:hover .logo-item {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .institutional-logo {
            height: 180px;
            width: 180px;
            object-fit: contain;
            filter: brightness(1.1);
            transition: all 0.3s ease;
        }

        .logo-item-link:hover .institutional-logo {
            transform: scale(1.1);
            filter: brightness(1.3) drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }

        .logo-label {
            display: none;
        }

        .footer-bottom {
            padding: 1.5rem 0;
        }

        .copyright {
            color: #ffffff;
            font-size: 0.9rem;
            margin: 0;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .legal-link {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .legal-link:hover {
            color: #ffffff;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .logos-container {
                gap: 2rem;
            }
            
            .logo-item {
                padding: 0.5rem;
            }
            
            .institutional-logo {
                height: 130px;
                width: 130px;
            }
            
            .footer-title {
                font-size: 1.5rem;
            }
        }

        .icon-3d {
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .icon-3d:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-lg);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background: var(--gradient-primary) !important;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
        }

        .alert-success {
            background: var(--gradient-success);
            color: white;
        }

        .alert-danger {
            background: var(--gradient-danger);
            color: white;
        }

        .alert-warning {
            background: var(--gradient-warning);
            color: white;
        }

        .alert-info {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .badge {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .pagination .page-link {
            border-radius: 10px;
            margin: 0 0.25rem;
            border: none;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-2px);
        }

        .pagination .page-item.active .page-link {
            background: var(--gradient-primary);
            border: none;
        }

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
    {{-- Barre utilitaire : Recherche, FAQ, Médias & ressources, Langue --}}
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
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="nav-top-dropdown dropdown-toggle" type="button" id="dropdownLang" data-bs-toggle="dropdown" aria-expanded="false">@if(app()->getLocale() == 'fr') {{ __('messages.language.french') }} @elseif(app()->getLocale() == 'ar') العربية @else {{ __('messages.language.english') }} @endif</button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLang">
                        <li><a class="dropdown-item" href="{{ locale_url('fr') }}">{{ __('messages.language.french') }}</a></li>
                        <li><a class="dropdown-item" href="{{ locale_url('en') }}">{{ __('messages.language.english') }}</a></li>
                        <li><a class="dropdown-item" href="{{ locale_url('ar') }}">العربية</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre principale (style WFP/CSAR : Qui sommes-nous, Notre mission, Interventions, Impliquez-vous, FAITES UN DON) --}}
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
                            <li><a class="dropdown-item" href="{{ route('home', ['locale' => app()->getLocale()]) }}"><i class="fas fa-home me-2 text-muted"></i>{{ __('messages.nav.home') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}"><i class="fas fa-info-circle me-2 text-muted"></i>{{ __('messages.nav.about') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('institution', ['locale' => app()->getLocale()]) }}"><i class="fas fa-building me-2 text-muted"></i>{{ __('messages.nav.institution') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}#gouvernance"><i class="fas fa-users me-2 text-muted"></i>{{ __('messages.nav.governance') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}#histoire"><i class="fas fa-history me-2 text-muted"></i>{{ __('messages.nav.history') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('about', ['locale' => app()->getLocale()]) }}#mot-introductif"><i class="fas fa-comment-dots me-2 text-muted"></i>{{ __('messages.nav.mot_introductif') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}" id="menuMission" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.our_mission') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuMission">
                            <li><a class="dropdown-item" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-bullseye me-2 text-muted"></i>{{ __('messages.nav.food_security') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('map', ['locale' => app()->getLocale()]) }}"><i class="fas fa-warehouse me-2 text-muted"></i>{{ __('messages.nav.stock_management') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}"><i class="fas fa-hand-holding-heart me-2 text-muted"></i>{{ __('messages.nav.humanitarian_assistance') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}#resilience"><i class="fas fa-shield-alt me-2 text-muted"></i>{{ __('messages.nav.resilience') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('sim.index', ['locale' => app()->getLocale()]) }}" id="menuInterventions" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.interventions') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuInterventions">
                            <li><a class="dropdown-item" href="{{ route('sim.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-chart-line me-2 text-muted"></i>{{ __('messages.nav.sim') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-tasks me-2 text-muted"></i>{{ __('messages.nav.ongoing_programs') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('sim.carte-marches', ['locale' => app()->getLocale()]) }}"><i class="fas fa-map-marked-alt me-2 text-muted"></i>{{ __('messages.nav.carte_marches_sim') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports', ['locale' => app()->getLocale()]) }}"><i class="fas fa-file-alt me-2 text-muted"></i>{{ __('messages.nav.reports') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}" id="menuImpliquez" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('messages.nav.get_involved') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuImpliquez">
                            <li><a class="dropdown-item" href="{{ route('demande.create', ['locale' => app()->getLocale()]) }}"><i class="fas fa-hand-holding-heart me-2 text-muted"></i>{{ __('messages.nav.request_assistance') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-handshake me-2 text-muted"></i>{{ __('messages.nav.partners') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('contact', ['locale' => app()->getLocale()]) }}#collaborer"><i class="fas fa-users me-2 text-muted"></i>{{ __('messages.nav.collaboration') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('contact', ['locale' => app()->getLocale()]) }}"><i class="fas fa-envelope me-2 text-muted"></i>{{ __('messages.nav.contact') }}</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2 ms-lg-3">
                    <a href="{{ route('donations.index', ['locale' => app()->getLocale()]) }}" class="nav-btn-don text-decoration-none">{{ __('messages.nav.make_donation') }}</a>
                    {{-- Sur mobile : langue (la barre utilitaire est masquée) --}}
                    <div class="language-selector d-lg-none ms-2">
                        <a href="{{ locale_url('fr') }}" class="d-flex align-items-center text-decoration-none text-dark" title="Français"><span class="small fw-bold">FR</span></a>
                        <a href="{{ locale_url('en') }}" class="d-flex align-items-center text-decoration-none text-dark ms-2" title="English"><span class="small fw-bold">EN</span></a>
                        <a href="{{ locale_url('ar') }}" class="d-flex align-items-center text-decoration-none text-dark ms-2" title="العربية"><span class="small fw-bold">AR</span></a>
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
                <!-- Colonne 1: Logo CSAR + Texte avec effet machine à écrire -->
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand text-center">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="Logo CSAR" class="footer-logo" onerror="this.style.display='none';">
                        </div>
                        <div class="typing-text-container">
                            <p class="typing-text" id="typing-text" data-typing-text="{{ __('messages.home.title') }}">{{ __('messages.home.title') }}</p>
                        </div>
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
                        <li><a href="{{ route('about', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.about') }}</a></li>
                        <li><a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.news') }}</a></li>
                        <li><a href="{{ route('donations.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.make_donation_short') }}</a></li>
                        <li><a href="{{ route('projets.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.projects') }}</a></li>
                        <li><a href="{{ route('ressources.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.resources') }}</a></li>
                        <li><a href="{{ route('faq.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.faq') }}</a></li>
                        <li><a href="{{ route('sim-reports.index') }}">{{ __('messages.nav.sim') }}</a></li>
                        <li><a href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.partners') }}</a></li>
                        <li><a href="{{ route('contact', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.contact') }}</a></li>
                    </ul>
                </div>
                
                <!-- Colonne 3: Newsletter -->
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-section-title">{{ __('messages.footer.newsletter') }}</h5>
                    <p class="footer-newsletter-desc">{{ __('messages.footer.newsletter_desc') }}</p>
                    <form id="newsletter-form" class="newsletter-form" method="POST" action="/{{ app()->getLocale() }}/newsletter">
                        @csrf
                        <div class="input-group">
                            <input type="email" name="email" class="form-control newsletter-input" placeholder="{{ __('messages.footer.email_placeholder') }}" required>
                            <button type="submit" class="btn newsletter-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="newsletter-message" class="newsletter-message mt-2"></div>
                    </form>
                </div>
                </div>
                
            <!-- Logos institutionnels -->
            <div class="row">
                <div class="col-12">
                    <hr class="footer-divider">
                    <div class="institutional-logos">
                        <h6 class="logos-title">{{ __('messages.footer.institutional_partners') }}</h6>
                        <div class="logos-container">
                            <a href="https://femme.gouv.sn/" target="_blank" class="logo-item-link" title="Ministère de la Famille et des Solidarités">
                                <div class="logo-item">
                                    <img src="{{ asset('images/mfs.png') }}" alt="Ministère de la Famille et des Solidarités" class="institutional-logo" loading="lazy" onerror="this.style.display='none';">
                                </div>
                            </a>
                            <a href="https://www.finances.gouv.sn/" target="_blank" class="logo-item-link" title="Ministère des Finances et du Budget">
                                <div class="logo-item">
                                    <img src="{{ asset('images/ministere-des-finances-et-du-budget.png') }}" alt="Ministère des Finances et du Budget" class="institutional-logo" loading="lazy" onerror="this.style.display='none';">
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copyright et liens légaux -->
            <div class="row">
                <div class="col-12">
                    <hr class="footer-divider">
                    <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                                <p class="copyright">&copy; {{ date('Y') }} CSAR - {{ __('messages.home.title') }}. {{ __('messages.footer.copyright') }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                                <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="legal-link me-3">
                                    <i class="fas fa-shield-alt me-1"></i>{{ __('messages.footer.privacy') }}
                                </a>
                                <a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="legal-link">
                                    <i class="fas fa-file-contract me-1"></i>{{ __('messages.footer.terms') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Chatbot included via partial -->
    @include('partials.chatbot')

    <!-- Bootstrap JS (deferred) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <!-- Custom JS -->
    <script>
        // Effet machine à écrire pour le footer
        function typeWriter(element, text, speed = 50) {
            let i = 0;
            element.innerHTML = '';
            
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                } else {
                    // Une fois terminé, attendre 3 secondes puis recommencer
                    setTimeout(() => {
                        typeWriter(element, text, speed);
                    }, 3000);
                }
            }
            
            type();
        }

        // Initialiser l'effet machine à écrire
        function initTypeWriter() {
            const typingElement = document.getElementById('typing-text');
            if (typingElement) {
                const text = typingElement.getAttribute('data-typing-text') || typingElement.textContent;
                console.log('✅ Element typing-text trouvé, démarrage de l\'effet machine à écrire...');
                console.log('📝 Texte à afficher:', text);
                
                // Vider l'élément et démarrer l'effet
                typingElement.innerHTML = '';
                typeWriter(typingElement, text, 60);
            } else {
                console.log('❌ Element typing-text non trouvé, nouvelle tentative dans 1 seconde...');
                setTimeout(initTypeWriter, 1000);
            }
        }

        // Démarrer l'effet machine à écrire
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM chargé, initialisation de l\'effet machine à écrire...');
            console.log('🔍 Recherche de l\'élément avec ID: typing-text');
            
            // Démarrer immédiatement
            initTypeWriter();
            
            // Backup au cas où
            setTimeout(() => {
                const element = document.getElementById('typing-text');
                const expectedText = element ? element.getAttribute('data-typing-text') : '';
                if (element && expectedText && element.innerHTML === expectedText) {
                    console.log('🔄 Backup: Redémarrage de l\'effet machine à écrire...');
                    initTypeWriter();
                }
            }, 2000);
        });

        // Gestion du formulaire newsletter
        document.addEventListener('DOMContentLoaded', function() {
            const newsletterForm = document.getElementById('newsletter-form');
            const messageDiv = document.getElementById('newsletter-message');
            
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const email = formData.get('email');
                    
                    // Validation basique
                    if (!email || !email.includes('@')) {
                        showNewsletterMessage('Veuillez saisir une adresse email valide.', 'error');
                        return;
                    }
                    
                    // Désactiver le bouton
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    submitBtn.disabled = true;
                    
                    // Envoyer la requête
                    fetch('/{{ app()->getLocale() }}/newsletter', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }
                            showNewsletterMessage(data.message, 'success');
                            this.reset();
                        } else {
                            showNewsletterMessage(data.message || 'Une erreur est survenue.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNewsletterMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
                    })
                    .finally(() => {
                        // Réactiver le bouton
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }
            
            function showNewsletterMessage(message, type) {
                if (messageDiv) {
                    messageDiv.textContent = message;
                    messageDiv.className = `newsletter-message ${type}`;
                    messageDiv.style.display = 'block';
                    
                    // Masquer le message après 5 secondes
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 5000);
                }
            }
        });

        // Toast notifications
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            const toast = createToast(message, type);
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }
        
        function createToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${getToastIcon(type)} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            return toast;
        }
        
        function getToastIcon(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
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
        
        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Chargement...';
                    
                    // Re-enable after 10 seconds as fallback
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 10000);
                }
            });
        });
        
        // Add fade-in animation to elements
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);
        
        // Observe all cards and sections
        document.querySelectorAll('.card, .section, .stat-card, .info-card').forEach(el => {
            observer.observe(el);
        });
    </script>
    
    <!-- Toast Notifications -->
    <x-toast-notification />
    
    <!-- AOS removed for performance -->
    
    <!-- Cookie Consent deferred for performance -->
    <script>
    function loadCookieConsent() {
        var s = document.createElement('script');
        s.src = '{{ asset("js/cookie-consent.js") }}';
        s.defer = true;
        document.head.appendChild(s);
    }
    // Load cookie consent after 3 seconds
    setTimeout(loadCookieConsent, 3000);
    </script>

    @stack('scripts')
</body>
</html>