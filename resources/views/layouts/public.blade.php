<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'CSAR - Comité de Secours et d\'Assistance aux Réfugiés')</title>
    
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
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #00b894;
            --warning-color: #fdcb6e;
            --danger-color: #e17055;
            --info-color: #74b9ff;
            --light-color: #f8f9fa;
            --dark-color: #2d3436;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .navbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            margin-right: 2rem;
            color: #00a86b !important;
            text-decoration: none;
            position: relative;
            display: flex;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            text-shadow: 0 2px 4px rgba(0, 168, 107, 0.3);
        }

        .navbar-brand:hover {
            color: #00c851 !important;
            transform: scale(1.1) translateY(-2px);
            text-shadow: 0 4px 8px rgba(0, 168, 107, 0.5);
            filter: drop-shadow(0 4px 12px rgba(0, 200, 81, 0.4));
        }

        .navbar-brand::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(0, 168, 107, 0.1), rgba(0, 200, 81, 0.1));
            border-radius: 8px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: -1;
        }

        .navbar-brand:hover::before {
            opacity: 1;
            transform: scale(1.05);
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #00a86b, #00c851, #00a86b);
            border-radius: 2px;
            transition: all 0.4s ease;
            transform: translateX(-50%);
            box-shadow: 0 2px 8px rgba(0, 168, 107, 0.4);
        }

        .navbar-brand:hover::after {
            width: 100%;
        }

        /* Effets pour le logo image */
        .navbar-brand img {
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            filter: drop-shadow(0 2px 4px rgba(0, 168, 107, 0.3));
        }

        .navbar-brand:hover img {
            transform: scale(1.15) rotate(5deg);
            filter: drop-shadow(0 4px 12px rgba(0, 200, 81, 0.5)) brightness(1.1);
        }

        /* Animation pulsante pour attirer l'attention */
        @keyframes pulse-csar {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 2px 4px rgba(0, 168, 107, 0.3));
            }
            50% {
                transform: scale(1.05);
                filter: drop-shadow(0 4px 8px rgba(0, 168, 107, 0.5));
            }
            100% {
                transform: scale(1);
                filter: drop-shadow(0 2px 4px rgba(0, 168, 107, 0.3));
            }
        }

        .navbar-brand {
            animation: pulse-csar 3s ease-in-out infinite;
        }

        .navbar-brand:hover {
            animation: none;
        }

        /* Style pour le texte CSAR */
        .csar-text {
            font-weight: 700;
            font-size: 1.5rem;
            color: #00a86b;
            text-shadow: 0 2px 4px rgba(0, 168, 107, 0.3);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .navbar-brand:hover .csar-text {
            color: #00c851;
            text-shadow: 0 4px 8px rgba(0, 168, 107, 0.5);
        }

        .navbar-nav {
            display: flex;
            justify-content: center;
            flex: 1;
            margin: 0 auto;
            max-width: 800px;
        }

        .navbar-nav .nav-item {
            margin: 0 0.5rem;
        }

        .navbar-nav .nav-link {
            color: #495057 !important;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            white-space: nowrap;
        }

        .navbar-nav .nav-link:hover {
            color: #007bff !important;
            background: rgba(0, 123, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: #007bff;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        /* Responsive navbar */
        @media (max-width: 991.98px) {
            .navbar-nav {
                flex-direction: column;
                align-items: center;
                margin: 1rem 0;
            }
            
            .navbar-nav .nav-item {
                margin: 0.25rem 0;
                width: 100%;
                text-align: center;
            }
            
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                margin: 0;
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
            height: 80px;
            width: 80px;
            object-fit: contain;
            filter: brightness(1.1);
            transition: all 0.3s ease;
        }

        .logo-item-link:hover .institutional-logo {
            transform: scale(1.1);
            filter: brightness(1.3) drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }

        .logo-label {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #ffffff;
            text-align: center;
            max-width: 120px;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        .logo-item-link:hover .logo-label {
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
            font-weight: 600;
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
                height: 60px;
                width: 60px;
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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <!-- Logo CSAR à gauche -->
            <a class="navbar-brand" href="{{ route('home', ['locale' => 'fr']) }}">
                <img src="{{ asset('images/csar-logo.png') }}" alt="Logo CSAR" style="height: 45px; width: auto; margin-right: 10px;" onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}';">
                <span class="csar-text">CSAR</span>
            </a>
            
            <!-- Bouton toggle pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Contenu de la navbar -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Navigation principale centrée -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-home me-1"></i>{{ __('messages.nav.home') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-info-circle me-1"></i>{{ __('messages.nav.about') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('news.index', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-newspaper me-1"></i>{{ __('messages.nav.news') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sim-reports.index') }}">
                            <i class="fas fa-chart-line me-1"></i>{{ __('messages.nav.sim') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('institution', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-building me-1"></i>{{ __('messages.nav.institution') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-handshake me-1"></i>{{ __('messages.nav.partners') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-envelope me-1"></i>{{ __('messages.nav.contact') }}
                        </a>
                    </li>
                </ul>
                
                <!-- Sélecteur de langue à droite -->
                <div class="language-selector">
                    <a href="{{ route('home', ['locale' => 'fr']) }}" class="d-flex align-items-center text-decoration-none" title="Français">
                        <img src="{{ asset('images/flags/fr.svg') }}" alt="Français" class="language-flag {{ app()->getLocale() == 'fr' ? 'active' : '' }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                        <span class="language-text" style="display: none;">FR</span>
                    </a>
                    <a href="{{ route('home', ['locale' => 'en']) }}" class="d-flex align-items-center text-decoration-none" title="English">
                        <img src="{{ asset('images/flags/en.svg') }}" alt="English" class="language-flag {{ app()->getLocale() == 'en' ? 'active' : '' }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                        <span class="language-text" style="display: none;">EN</span>
                    </a>
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
                            <p class="typing-text" id="typing-text">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
                        </div>
                    <div class="social-links">
                            <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" title="LinkedIn">
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
                    <h5 class="footer-section-title">Liens rapides</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.home') }}</a></li>
                        <li><a href="{{ route('about', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.about') }}</a></li>
                        <li><a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.news') }}</a></li>
                        <li><a href="{{ route('sim-reports.index') }}">{{ __('messages.nav.sim') }}</a></li>
                        <li><a href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.partners') }}</a></li>
                        <li><a href="{{ route('contact', ['locale' => app()->getLocale()]) }}">{{ __('messages.nav.contact') }}</a></li>
                    </ul>
                </div>
                
                <!-- Colonne 3: Newsletter -->
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-section-title">Newsletter</h5>
                    <p class="footer-newsletter-desc">Restez informé de nos dernières actualités</p>
                    <form id="newsletter-form" class="newsletter-form">
                        @csrf
                        <div class="input-group">
                            <input type="email" name="email" class="form-control newsletter-input" placeholder="Votre adresse email" required>
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
                        <h6 class="logos-title">Nos partenaires institutionnels</h6>
                        <div class="logos-container">
                            <a href="https://primature.sn/" target="_blank" class="logo-item-link">
                                <div class="logo-item">
                                    <img src="{{ asset('images/primature.jpg') }}" alt="Primature" class="institutional-logo" onerror="this.style.display='none';">
                                    <span class="logo-label">Primature</span>
                                </div>
                            </a>
                            <a href="https://femme.gouv.sn/" target="_blank" class="logo-item-link">
                                <div class="logo-item">
                                    <img src="{{ asset('images/mfs.png') }}" alt="Ministère de la famille de l'action sociale et des solidarités" class="institutional-logo" onerror="this.style.display='none';">
                                    <span class="logo-label">Ministère de la famille de l'action sociale et des solidarités</span>
                                </div>
                            </a>
                            <a href="https://www.presidence.sn/fr/" target="_blank" class="logo-item-link">
                                <div class="logo-item">
                                    <img src="{{ asset('images/presidence.png') }}" alt="Présidence" class="institutional-logo" onerror="this.style.display='none';">
                                    <span class="logo-label">Présidence</span>
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
                                <p class="copyright">&copy; {{ date('Y') }} CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                                <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="legal-link me-3">
                                    <i class="fas fa-shield-alt me-1"></i>Politique de confidentialité
                                </a>
                                <a href="{{ route('terms', ['locale' => app()->getLocale()]) }}" class="legal-link">
                                    <i class="fas fa-file-contract me-1"></i>Conditions d'utilisation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
                const text = 'Commissariat à la Sécurité Alimentaire et à la Résilience';
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
                if (element && element.innerHTML === 'Commissariat à la Sécurité Alimentaire et à la Résilience') {
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
                    fetch('{{ route("newsletter.store", ["locale" => app()->getLocale()]) }}', {
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
    
    <!-- AOS - Animate On Scroll -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        AOS.init({ duration: 600, easing: 'ease-out-cubic', offset: 40, once: true });
      });
    </script>
    
    @stack('scripts')
</body>
</html>