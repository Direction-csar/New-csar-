<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Direction Générale') - CSAR Platform</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/csar-logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/csar-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/csar-logo.png') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Responsive CSS -->
    <link href="{{ asset('css/dg-responsive.css') }}" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo img {
            width: 40px;
            height: 40px;
            margin-right: 0.75rem;
            border-radius: 8px;
        }

        .logo i {
            margin-right: 0.5rem;
            font-size: 2rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-item {
            margin: 0.25rem 0;
        }

        .menu-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }

        .menu-link:hover,
        .menu-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .menu-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .content-area {
            padding: 2rem;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-color);
        }

        .page-title {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .page-subtitle {
            color: #6c757d;
            margin: 0.5rem 0 0 0;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
        }

        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .table thead th {
            background: var(--light-color);
            border: none;
            font-weight: 600;
            color: var(--dark-color);
            padding: 1rem;
        }

        .table tbody td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-success {
            background: var(--success-color);
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .badge-danger {
            background: var(--danger-color);
        }

        .badge-info {
            background: var(--info-color);
        }

        .user-dropdown {
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--light-color);
            color: var(--primary-color);
        }

        .dg-badge {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .readonly-indicator {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.2);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1050;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-navbar {
                padding: 0.5rem 1rem;
                position: sticky;
                top: 0;
                z-index: 1000;
            }
            
            .content-area {
                padding: 1rem;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .btn-primary {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .table {
                font-size: 0.85rem;
            }
            
            .menu-link {
                padding: 0.75rem 1rem;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .logo i {
                font-size: 1.5rem;
            }

            .csar-logo {
                width: 34px;
                height: 34px;
                object-fit: contain;
                margin-right: 10px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dg.dashboard') }}" class="logo">
                <img class="csar-logo" src="{{ asset('images/csar-logo-vectoriel-01.png') }}" alt="CSAR" onerror="this.style.display='none';">
                <span class="logo-text">DG</span>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-item">
                <a href="{{ route('dg.dashboard') }}" class="menu-link {{ request()->routeIs('dg.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.requests.index') }}" class="menu-link {{ request()->routeIs('dg.requests.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Demandes</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.users.index') }}" class="menu-link {{ request()->routeIs('dg.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.warehouses.index') }}" class="menu-link {{ request()->routeIs('dg.warehouses.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Entrepôts</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.stocks.index') }}" class="menu-link {{ request()->routeIs('dg.stocks.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i>
                    <span>Stocks</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.personnel.index') }}" class="menu-link {{ request()->routeIs('dg.personnel.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Personnel</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.map') }}" class="menu-link {{ request()->routeIs('dg.map') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Carte Interactive</span>
                </a>
            </div>

            <!-- SIM — Suivi collecteurs terrain temps réel -->
            <div class="menu-item">
                <a href="{{ route('dg.sim-suivi.index') }}" class="menu-link {{ request()->routeIs('dg.sim-suivi.*') ? 'active' : '' }}">
                    <i class="fas fa-broadcast-tower"></i>
                    <span>SIM — Suivi terrain</span>
                    <span class="badge bg-success ms-auto" style="font-size:0.65rem;">Live</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('dg.reports.index') }}" class="menu-link {{ request()->routeIs('dg.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Rapports</span>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="{{ route('admin.profile') }}" class="menu-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Profil</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 ms-3">@yield('page-title', 'Direction Générale')</h4>
                <span class="dg-badge">DG</span>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="user-dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="fas fa-user me-2"></i>Profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('dg.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="readonly-indicator">
                <i class="fas fa-eye me-2"></i>
                <strong>Mode Lecture Seule :</strong> Interface de supervision et de pilotage stratégique
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS for Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                // Add overlay for mobile
                if (sidebar.classList.contains('show')) {
                    const overlay = document.createElement('div');
                    overlay.id = 'sidebar-overlay';
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 1040;
                        transition: opacity 0.3s ease;
                    `;
                    overlay.addEventListener('click', closeMobileSidebar);
                    document.body.appendChild(overlay);
                    // Prevent body scroll when sidebar is open
                    document.body.style.overflow = 'hidden';
                } else {
                    closeMobileSidebar();
                }
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        });

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.remove('show');
            if (overlay) {
                overlay.remove();
            }
            // Restore body scroll
            document.body.style.overflow = '';
        }

        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileSidebar();
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('show') && 
                !sidebar.contains(event.target) && 
                !sidebarToggle.contains(event.target)) {
                closeMobileSidebar();
            }
        });

        // Handle escape key to close sidebar
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMobileSidebar();
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Real-time stats update
        function updateStats() {
            fetch('{{ route("dg.api.realtime") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stats cards
                        document.querySelectorAll('[data-stat]').forEach(element => {
                            const statKey = element.getAttribute('data-stat');
                            if (data.data[statKey] !== undefined) {
                                element.textContent = data.data[statKey];
                            }
                        });
                    }
                })
                .catch(error => console.error('Error updating stats:', error));
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);
    </script>
    
    @stack('scripts')
</body>
</html> 
