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
            --ctc-dark: #1e293b;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; min-height: 100vh; color: #334155; }
        .sidebar {
            background: linear-gradient(180deg, var(--ctc-primary) 0%, var(--ctc-secondary) 100%);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.25);
        }
        .sidebar-header .logo {
            color: white;
            font-size: 1.35rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            letter-spacing: 0.5px;
        }
        .sidebar-header .logo img { width: 40px; height: 40px; margin-right: 12px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .sidebar-menu { padding: 1rem 0; }
        .menu-link {
            color: rgba(255,255,255,0.95);
            text-decoration: none;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.25s ease;
            border-radius: 0 28px 28px 0;
            margin: 0.25rem 0.5rem 0.25rem 0;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .menu-link:hover, .menu-link.active { 
            color: white; 
            background: rgba(255,255,255,0.2); 
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .menu-link i { width: 24px; margin-right: 12px; text-align: center; font-size: 1rem; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .user-avatar {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ctc-primary), var(--ctc-secondary));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; cursor: pointer;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
            transition: transform 0.2s;
        }
        .user-avatar:hover { transform: scale(1.05); }
        .content-area { padding: 2rem; }
        .ctc-badge {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(10, 88, 202, 0.1));
            color: var(--ctc-primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(13, 110, 253, 0.2);
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
    @yield('scripts')
</body>
</html>
