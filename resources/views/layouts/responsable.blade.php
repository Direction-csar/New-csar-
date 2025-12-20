<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', 'CSAR Responsable')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/csar-logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/csar-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/csar-logo.png') }}">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Professional CSS -->
    <link rel="stylesheet" href="{{ asset('css/professional-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsable-professional.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsable-enhanced.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stock-management.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-complete.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-mobile-first.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-tables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-helpers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsable-mobile-fix.css') }}">
    
    <!-- Custom Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            /* Palette professionnelle teal/green */
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Arrière-plan avec logo CSAR */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(34, 197, 94, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(251, 191, 36, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            z-index: -2;
        }
        
        /* Logo CSAR en arrière-plan */
        body::after {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-5deg);
            width: 600px;
            height: 600px;
            background-image: url('{{ asset("images/logos/LOGO CSAR vectoriel-01.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.03;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(-50%, -50%) rotate(-5deg) translateY(0px); }
            50% { transform: translate(-50%, -50%) rotate(-5deg) translateY(-20px); }
        }
        
        /* Particules flottantes */
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(34, 197, 94, 0.3);
            border-radius: 50%;
            animation: particleFloat 8s linear infinite;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 6s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 1s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 3s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 5s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 7s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 0.5s; }
        
        @keyframes particleFloat {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }
        
        /* Sidebar */
        .responsable-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #134e4a 0%, #0f766e 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #fbbf24;
            border: 3px solid #22c55e;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }
        
        .brand-info {
            margin-left: 15px;
        }
        
        .brand-info h2 {
            font-size: 20px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }
        
        .brand-info p {
            font-size: 12px;
            opacity: 0.8;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .user-profile {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .user-details h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .user-role {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Navigation */
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-section {
            margin-bottom: 24px;
        }
        
        .nav-section-title {
            padding: 0 24px 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-right: 3px solid #60a5fa;
        }
        
        .nav-icon {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }
        
        .nav-text {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-badge {
            background: #0ea5a4;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        
        /* Main Content */
        .responsable-main {
            margin-left: 280px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        /* Content Area */
        .responsable-content {
            padding: 24px;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .responsable-sidebar {
                transform: translateX(-100%);
            }
            
            .responsable-sidebar.open {
                transform: translateX(0);
            }
            
            .responsable-main {
                margin-left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .responsable-sidebar {
                width: 100%;
            }
            
            .responsable-content {
                padding: 16px;
            }
        }
        
        /* Overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.open {
            display: block;
        }
        
        /* Custom styles for dashboard */
        @yield('styles')
    </style>
</head>
<body class="interface-responsable">
    {{-- Navbar Mobile --}}
    @include('components.mobile-navbar', ['interface' => 'Responsable Entrepôt'])
    
    <!-- Particules flottantes -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="responsable-sidebar sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="logo">
                <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="brand-info">
                <h2>CSAR</h2>
                <p>Interface Responsable</p>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="user-profile">
            <div class="user-info">
                <div class="user-avatar">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        <i class="fas fa-user"></i>
                    @endif
                </div>
                <div class="user-details">
                    <h4>{{ auth()->user()->name }}</h4>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="sidebar-nav">
            <!-- Main Navigation -->
            <div class="nav-section">
                <div class="nav-section-title">Navigation principale</div>
                
                <a href="{{ route('responsable.dashboard') }}" class="nav-item {{ request()->routeIs('responsable.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-home"></i>
                    <span class="nav-text">Tableau de bord</span>
                </a>
                
                <a href="{{ route('responsable.stock') }}" class="nav-item {{ request()->routeIs('responsable.stock.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-boxes"></i>
                    <span class="nav-text">Gestion des stocks</span>
                </a>
                
                <a href="{{ route('responsable.movements') }}" class="nav-item {{ request()->routeIs('responsable.movements.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-exchange-alt"></i>
                    <span class="nav-text">Mouvements</span>
                </a>
                
                <a href="{{ route('responsable.location') }}" class="nav-item {{ request()->routeIs('responsable.location.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-map-marker-alt"></i>
                    <span class="nav-text">Localisation</span>
                </a>
            </div>
            
            <!-- Profile Management -->
            <div class="nav-section">
                <div class="nav-section-title">Profil</div>
                
                <a href="{{ route('admin.profile') }}" class="nav-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-edit"></i>
                    <span class="nav-text">Mon profil</span>
                </a>

                <!-- Déconnexion -->
                <a href="#" class="nav-item" onclick="event.preventDefault(); document.getElementById('responsableLogoutForm').submit();">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span class="nav-text">Déconnexion</span>
                </a>
                <form id="responsableLogoutForm" action="{{ route('responsable.logout', [], false) }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="responsable-main">
        <!-- Content Area -->
        <div class="responsable-content">
            @if(session('success'))
                <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0;">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>
    
    <!-- JavaScript -->
    {{-- Mobile sidebar toggling is handled by `components/mobile-navbar` to avoid duplicated/buggy scripts. --}}
    
    {{-- JavaScript Responsive --}}
    <script src="{{ asset('js/responsive-mobile.js') }}"></script>
    <script>
    // Wrap tables for horizontal scroll on small screens in Responsable layout
    (function(){
        const container = document.querySelector('.responsable-content');
        if (!container) return;
        container.querySelectorAll('table').forEach(function(table) {
            if (!table.closest('.table-responsive-wrapper')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    })();
    </script>
    
    @stack('scripts')
</body>
</html> 