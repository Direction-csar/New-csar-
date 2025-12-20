<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #51cf66;
            --warning-color: #ffd43b;
            --danger-color: #ff6b6b;
            --info-color: #74c0fc;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-warning: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --gradient-danger: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --gradient-info: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
            --shadow-strong: 0 20px 40px rgba(0, 0, 0, 0.2);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Modern */
        .sidebar {
            background: var(--gradient-primary);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: var(--transition);
            box-shadow: var(--shadow-strong);
            backdrop-filter: blur(10px);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .logo:hover {
            color: white;
            transform: scale(1.05);
        }

        .logo i {
            margin-right: 0.75rem;
            font-size: 2rem;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
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
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            transition: var(--transition);
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
            position: relative;
            overflow: hidden;
        }

        .menu-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .menu-link:hover::before {
            left: 100%;
        }

        .menu-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .menu-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            box-shadow: inset 0 0 20px rgba(255,255,255,0.1);
        }

        .menu-link i {
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: var(--transition);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Top Bar */
        .top-bar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-soft);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .top-bar-content {
            display: flex;
            justify-content: between;
            align-items: center;
        }

        /* Cards Modern */
        .card-modern {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            overflow: hidden;
        }

        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        /* Icons 3D */
        .icon-3d {
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: var(--shadow-medium);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .icon-3d::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: var(--transition);
        }

        .icon-3d:hover::before {
            animation: shine 0.6s ease-in-out;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .icon-3d:hover {
            transform: scale(1.1) rotate(5deg);
        }

        /* Stats Cards */
        .stats-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
            font-weight: 500;
        }

        /* Buttons Modern */
        .btn-modern {
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-success-modern {
            background: var(--gradient-success);
            color: white;
        }

        .btn-warning-modern {
            background: var(--gradient-warning);
            color: white;
        }

        .btn-danger-modern {
            background: var(--gradient-danger);
            color: white;
        }

        .btn-info-modern {
            background: var(--gradient-info);
            color: white;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
            }
            
            .stats-number {
                font-size: 2rem;
            }
            
            .top-bar {
                padding: 0.75rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                z-index: 1050;
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-bar {
                padding: 0.5rem 1rem;
                position: sticky;
                top: 0;
                z-index: 1000;
            }
            
            .top-bar-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .stats-number {
                font-size: 1.8rem;
            }
            
            .card-modern {
                margin-bottom: 1rem;
                padding: 1rem;
            }
            
            .btn-modern {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .icon-3d {
                width: 30px !important;
                height: 30px !important;
                font-size: 1rem !important;
            }
            
            .content-wrapper {
                padding: 1rem !important;
            }
            
            .menu-link {
                padding: 0.75rem 1rem;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .logo i {
                font-size: 1.5rem;
                padding: 0.3rem;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
            }
            
            .content-wrapper {
                padding: 1rem !important;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .stats-card {
                padding: 1rem;
            }
            
            .card-modern {
                padding: 1rem;
            }
            
            .btn-modern {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .badge {
                font-size: 0.7rem;
            }
            
            .menu-link {
                padding: 0.75rem 1rem;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .logo i {
                font-size: 1.5rem;
                padding: 0.3rem;
            }
        }

        @media (max-width: 480px) {
            .stats-number {
                font-size: 1.3rem;
            }
            
            .card-modern {
                padding: 0.75rem;
            }
            
            .btn-modern {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }
            
            .table-responsive {
                font-size: 0.75rem;
            }
            
            .icon-3d {
                width: 25px !important;
                height: 25px !important;
                font-size: 0.8rem !important;
            }
        }

        /* Dark Mode */
        .dark-mode {
            --primary-color: #4a5568;
            --secondary-color: #2d3748;
            --success-color: #38a169;
            --warning-color: #d69e2e;
            --danger-color: #e53e3e;
            --info-color: #3182ce;
            --dark-color: #1a202c;
            --light-color: #2d3748;
        }

        .dark-mode body {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
        }

        .dark-mode .card-modern,
        .dark-mode .stats-card {
            background: rgba(45, 55, 72, 0.95);
            color: white;
        }

        .dark-mode .top-bar {
            background: rgba(26, 32, 44, 0.95);
            color: white;
        }

        .csar-logo {
            width: 34px;
            height: 34px;
            object-fit: contain;
            margin-right: 10px;
            filter: drop-shadow(0 6px 12px rgba(0,0,0,0.25));
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dg.dashboard') }}" class="logo">
                    <img
                        class="csar-logo"
                        src="{{ asset('images/csar-logo-vectoriel-01.png') }}"
                        alt="CSAR"
                        onerror="this.style.display='none';"
                    >
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
                    <a href="{{ route('dg.demandes.index') }}" class="menu-link {{ request()->routeIs('dg.demandes.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Demandes</span>
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
                        <i class="fas fa-users"></i>
                        <span>Personnel</span>
                    </a>
                </div>
                
                <div class="menu-item">
                    <a href="{{ route('dg.reports.index') }}" class="menu-link {{ request()->routeIs('dg.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Rapports</span>
                    </a>
                </div>
                
                <div class="menu-item">
                    <a href="#" class="menu-link" onclick="toggleDarkMode()">
                        <i class="fas fa-moon"></i>
                        <span>Mode Sombre</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-content">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-primary btn-sm me-3" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="h4 mb-0">@yield('page-title', 'Direction Générale')</h1>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm position-relative" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    0
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div class="dropdown-item-text text-muted">Aucune notification</div>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                {{ auth()->user()->name ?? 'DG' }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a>
                                <a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Paramètres</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('dg.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="content-wrapper p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('dg.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Dark mode toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        }

        // Load dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }

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
                    // Update stats cards
                    document.querySelectorAll('[data-stat]').forEach(element => {
                        const statKey = element.getAttribute('data-stat');
                        if (data[statKey] !== undefined) {
                            element.textContent = data[statKey];
                        }
                    });
                })
                .catch(error => console.error('Error updating stats:', error));
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card-modern, .stats-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in-up');
            });
        });

        // Mobile sidebar toggle
        function toggleMobileSidebar() {
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
        }

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

        // Add event listener for sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', toggleMobileSidebar);

        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileSidebar();
            }
        });

        // Handle window resize for responsive adjustments
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
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
    </script>
    
    @yield('scripts')
</body>
</html>
