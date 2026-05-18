<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace DRH') — CSAR</title>

    <link rel="icon" type="image/png" href="{{ asset('images/csar-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --drh-primary: #047857;
            --drh-primary-dark: #065f46;
            --drh-accent: #10b981;
            --drh-light: #f0fdf4;
            --shadow-soft: 0 2px 10px rgba(0,0,0,0.06);
            --shadow-medium: 0 8px 24px rgba(0,0,0,0.10);
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
            margin: 0;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: linear-gradient(180deg, var(--drh-primary-dark) 0%, var(--drh-primary) 100%);
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: var(--shadow-medium);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, width 0.3s ease;
        }
        .sidebar.collapsed { width: 75px; }
        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .brand-text,
        .sidebar.collapsed .menu-section-title { display: none; }
        .sidebar.collapsed .menu-link { justify-content: center; padding: 12px; }
        .sidebar.collapsed .menu-link i { margin: 0; font-size: 1.2rem; }

        .sidebar-header {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header img {
            height: 38px;
            width: 38px;
            object-fit: contain;
            background: white;
            border-radius: 8px;
            padding: 4px;
        }
        .brand-text {
            color: white;
            line-height: 1.2;
        }
        .brand-text strong {
            display: block;
            font-size: 1rem;
            font-weight: 700;
        }
        .brand-text small {
            font-size: 0.7rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 12px 0;
        }
        .sidebar-menu::-webkit-scrollbar { width: 6px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 3px; }

        .menu-section-title {
            color: rgba(255,255,255,0.55);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 14px 22px 6px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 22px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .menu-link i {
            font-size: 1.05rem;
            width: 22px;
            text-align: center;
        }
        .menu-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: rgba(255,255,255,0.5);
        }
        .menu-link.active {
            background: rgba(255,255,255,0.18);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }
        .sidebar-footer form { margin: 0; }
        .btn-logout {
            width: 100%;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.22); }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .main-wrapper.expanded { margin-left: 75px; }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .top-navbar .page-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-toggle {
            background: none;
            border: none;
            color: #475569;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .sidebar-toggle:hover { background: #f1f5f9; }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px;
            background: var(--drh-light);
            border-radius: 30px;
        }
        .user-badge .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--drh-accent), var(--drh-primary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .user-badge .info {
            line-height: 1.2;
        }
        .user-badge .info strong {
            display: block;
            font-size: 0.85rem;
            color: #1e293b;
        }
        .user-badge .info span {
            font-size: 0.72rem;
            color: #64748b;
        }

        .main-content {
            padding: 24px;
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
            .main-wrapper { margin-left: 0 !important; }
            .user-badge .info { display: none; }
        }
        @media (max-width: 576px) {
            .main-content { padding: 16px; }
            .top-navbar { padding: 12px 16px; }
            .top-navbar .page-title { font-size: 1rem; }
        }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>

@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive = fn($pattern) => str_contains($currentRoute, $pattern) ? 'active' : '';
@endphp

{{-- Sidebar --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR">
        <div class="brand-text">
            <strong>DRH</strong>
            <small>CSAR</small>
        </div>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section-title">Principal</div>
        <a href="{{ route('admin.drh.dashboard') }}" class="menu-link {{ $isActive('drh.dashboard') }}">
            <i class="fas fa-chart-line"></i>
            <span class="menu-text">Tableau de Bord</span>
        </a>

        <div class="menu-section-title">Gestion RH</div>
        <a href="{{ route('admin.drh.personnel.index') }}" class="menu-link {{ $isActive('drh.personnel') }}">
            <i class="fas fa-users"></i>
            <span class="menu-text">Personnel</span>
        </a>
        <a href="{{ route('admin.drh.personnel.create') }}" class="menu-link {{ $currentRoute === 'admin.drh.personnel.create' ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i>
            <span class="menu-text">Ajouter un agent</span>
        </a>

        <div class="menu-section-title">Programmes</div>
        <a href="{{ route('admin.drh.tabaski.index') }}" class="menu-link {{ $isActive('drh.tabaski') }}">
            <i class="fas fa-coins"></i>
            <span class="menu-text">Avances Tabaski</span>
        </a>
        <a href="{{ route('admin.drh.health-survey.index') }}" class="menu-link {{ $isActive('drh.health-survey') }}">
            <i class="fas fa-heartbeat"></i>
            <span class="menu-text">Enquête Assurance Maladie</span>
        </a>

        <div class="menu-section-title">Liens</div>
        <a href="{{ url('/') }}" target="_blank" class="menu-link">
            <i class="fas fa-external-link-alt"></i>
            <span class="menu-text">Site public</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('drh.logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fas fa-sign-out-alt me-1"></i>
                <span class="menu-text">Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Main wrapper --}}
<div class="main-wrapper" id="mainWrapper">

    {{-- Top navbar --}}
    <div class="top-navbar">
        <div class="d-flex align-items-center gap-2">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <h4 class="page-title">@yield('page-title', 'Espace DRH')</h4>
        </div>
        <div class="user-badge">
            <div class="avatar">
                {{ strtoupper(substr(Auth::user()->name ?? 'D', 0, 1)) }}
            </div>
            <div class="info">
                <strong>{{ Auth::user()->name ?? 'Utilisateur' }}</strong>
                <span>DRH</span>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <main class="main-content">
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
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function() {
        const sidebar = document.getElementById('sidebar');
        const wrapper = document.getElementById('mainWrapper');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const isMobile = () => window.innerWidth <= 992;

        toggle.addEventListener('click', function() {
            if (isMobile()) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                wrapper.classList.toggle('expanded');
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
