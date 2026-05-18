<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace DRH') - CSAR DRH</title>

    <link rel="icon" type="image/png" href="{{ asset('images/csar-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #0d9488;
            --secondary-color: #065f46;
            --gradient-primary: linear-gradient(135deg, #0d9488 0%, #065f46 100%);
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            width: 280px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: var(--shadow-medium);
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, transform 0.3s ease;
            overflow: hidden;
        }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .brand-text,
        .sidebar.collapsed .menu-text { display: none; }
        .sidebar.collapsed .menu-link { justify-content: center; padding: 12px; }
        .sidebar.collapsed .menu-link i { margin: 0; }
        .sidebar.collapsed .sidebar-header { justify-content: center; padding: 18px 8px; }

        .sidebar-header {
            padding: 1.4rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .sidebar-header .logo-icon img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
        .brand-text {
            color: white;
            line-height: 1.2;
        }
        .brand-text strong {
            font-size: 1.15rem;
            font-weight: 700;
            display: block;
            letter-spacing: 0.3px;
        }
        .brand-text small {
            font-size: 0.7rem;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-menu {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar-menu::-webkit-scrollbar { width: 6px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }
        .sidebar-menu::-webkit-scrollbar-track { background: rgba(255,255,255,0.08); }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 24px;
            color: rgba(255,255,255,0.88);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.25s ease;
            white-space: nowrap;
        }
        .menu-link i {
            font-size: 1.05rem;
            width: 22px;
            text-align: center;
        }
        .menu-link:hover {
            background: rgba(255,255,255,0.12);
            color: white;
            border-left-color: rgba(255,255,255,0.6);
        }
        .menu-link.active {
            background: rgba(255,255,255,0.22);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 12px 20px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }
        .sidebar-footer form { margin: 0; }
        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: white;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.25); }

        /* ===== MAIN ===== */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .main-content.expanded { margin-left: 80px; }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-left h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .sidebar-toggle {
            background: transparent;
            border: none;
            color: var(--primary-color);
            font-size: 1.3rem;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .sidebar-toggle:hover { background: rgba(13,148,136,0.12); }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(13,148,136,0.45);
            cursor: pointer;
        }

        .content-area {
            padding: 1.5rem;
        }

        /* Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 999;
        }
        .sidebar-overlay.show { display: block; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
        @media (max-width: 576px) {
            .content-area { padding: 1rem; }
            .top-navbar { padding: 0.85rem 1rem; }
            .navbar-left h4 { font-size: 1rem; }
        }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>

@php
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

{{-- Sidebar --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">
            <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" onerror="this.style.display='none'">
        </div>
        <div class="brand-text">
            <strong>CSAR DRH</strong>
            <small>Ressources Humaines</small>
        </div>
    </div>

    <nav class="sidebar-menu">
        <a href="{{ route('admin.drh.dashboard') }}" class="menu-link {{ str_contains($currentRoute, 'drh.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span class="menu-text">Tableau de bord</span>
        </a>

        <a href="{{ route('admin.drh.personnel.index') }}" class="menu-link {{ str_contains($currentRoute, 'drh.personnel') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span class="menu-text">Personnel</span>
        </a>

        <a href="{{ route('admin.drh.personnel.create') }}" class="menu-link {{ $currentRoute === 'admin.drh.personnel.create' ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i>
            <span class="menu-text">Ajouter un agent</span>
        </a>

        <a href="{{ route('admin.drh.tabaski.index') }}" class="menu-link {{ str_contains($currentRoute, 'drh.tabaski') ? 'active' : '' }}">
            <i class="fas fa-hand-holding-usd"></i>
            <span class="menu-text">Avances Tabaski</span>
        </a>

        <a href="{{ route('admin.drh.health-survey.index') }}" class="menu-link {{ str_contains($currentRoute, 'drh.health-survey') ? 'active' : '' }}">
            <i class="fas fa-heartbeat"></i>
            <span class="menu-text">Enquête Assurance Maladie</span>
        </a>

        <a href="{{ url('/') }}" target="_blank" class="menu-link">
            <i class="fas fa-external-link-alt"></i>
            <span class="menu-text">Site public</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('drh.logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Main --}}
<div class="main-content" id="mainContent">
    <div class="top-navbar">
        <div class="navbar-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <h4>@yield('page-title', 'Espace DRH')</h4>
        </div>
        <div class="navbar-right">
            <div class="user-avatar" title="{{ Auth::user()->name ?? 'Utilisateur' }}">
                {{ strtoupper(substr(Auth::user()->name ?? 'D', 0, 1)) }}
            </div>
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const isMobile = () => window.innerWidth <= 992;

        toggle.addEventListener('click', function() {
            if (isMobile()) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        });
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    })();
</script>
@yield('scripts')
@stack('scripts')
</body>
</html>
