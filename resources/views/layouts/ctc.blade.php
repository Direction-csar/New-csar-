<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CTC') - CSAR Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('images/csar-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ctc-primary: #0d6efd;
            --ctc-secondary: #0a58ca;
            --ctc-dark: #2c3e50;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .sidebar {
            background: linear-gradient(135deg, var(--ctc-primary) 0%, var(--ctc-secondary) 100%);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-header .logo {
            color: white;
            font-size: 1.25rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .sidebar-header .logo img { width: 36px; height: 36px; margin-right: 10px; border-radius: 8px; }
        .sidebar-menu { padding: 1rem 0; }
        .menu-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            border-radius: 0 25px 25px 0;
            margin: 0 0.5rem 0 0;
        }
        .menu-link:hover, .menu-link.active { color: white; background: rgba(255,255,255,0.15); }
        .menu-link i { width: 22px; margin-right: 10px; text-align: center; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .user-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ctc-primary), var(--ctc-secondary));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: bold; cursor: pointer;
        }
        .content-area { padding: 1.5rem; }
        .ctc-badge {
            background: rgba(13, 110, 253, 0.15);
            color: var(--ctc-primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 260px; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('ctc.dashboard') }}" class="logo">
                <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" onerror="this.style.display='none'">
                <span><i class="fas fa-bullhorn me-2"></i>CTC CSAR</span>
            </a>
        </div>
        <nav class="sidebar-menu">
            <a href="{{ route('ctc.dashboard') }}" class="menu-link {{ request()->routeIs('ctc.dashboard') || request()->routeIs('ctc.communications.*') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="{{ route('ctc.actualites.index') }}" class="menu-link {{ request()->routeIs('ctc.actualites.*') ? 'active' : '' }}">
                <i class="fas fa-newspaper"></i>
                <span>Actualités</span>
            </a>
            <a href="{{ route('ctc.sim-reports.index') }}" class="menu-link {{ request()->routeIs('ctc.sim-reports.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>Rapports</span>
            </a>
            <a href="{{ route('ctc.newsletter.index') }}" class="menu-link {{ request()->routeIs('ctc.newsletter.*') ? 'active' : '' }}">
                <i class="fas fa-mail-bulk"></i>
                <span>Newsletter</span>
            </a>
            <a href="{{ route('ctc.galerie.index') }}" class="menu-link {{ request()->routeIs('ctc.galerie.*') ? 'active' : '' }}">
                <i class="fas fa-images"></i>
                <span>Galerie</span>
            </a>
            <a href="{{ route('ctc.messages.index') }}" class="menu-link {{ request()->routeIs('ctc.messages.*') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <a href="{{ url('/') }}" class="menu-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Voir le site public</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-link d-md-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <h5 class="mb-0">@yield('page-title', 'Conseil Technique Communication')</h5>
                <span class="ctc-badge">Espace CTC</span>
            </div>
            <div class="dropdown">
                <div class="user-avatar" data-bs-toggle="dropdown">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ auth()->user()->name ?? 'Utilisateur' }}</h6></li>
                    <li><a class="dropdown-item" href="{{ url('/') }}" target="_blank"><i class="fas fa-globe me-2"></i>Site public</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('ctc.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
