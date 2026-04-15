<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') - CSAR Platform</title>
    
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
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
            --shadow-strong: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Icônes 3D et effets de profondeur */
        .icon-3d {
            background: var(--gradient-primary);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-medium);
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .icon-3d::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: var(--gradient-primary);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.3;
            filter: blur(8px);
        }

        .icon-3d:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-strong);
        }

        .icon-3d i {
            color: white;
            font-size: 24px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Cards modernes avec effets 3D */
        .card-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .card-modern:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-strong);
        }

        /* Badges de notification modernes */
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gradient-danger);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: var(--shadow-soft);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Sidebar moderne */
        .sidebar {
            background: var(--gradient-primary);
            box-shadow: var(--shadow-medium);
            position: relative;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
        }

        /* Assurer que tous les liens sont cliquables */
        .menu-link {
            cursor: pointer !important;
            pointer-events: auto !important;
            z-index: 10 !important;
            position: relative !important;
        }
        
        .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        .menu-item {
            cursor: pointer !important;
            pointer-events: auto !important;
        }
        
        /* Boutons cliquables */
        .btn {
            cursor: pointer !important;
            pointer-events: auto !important;
            z-index: 10 !important;
        }
        
        /* Liens dans le contenu */
        a {
            cursor: pointer !important;
            pointer-events: auto !important;
            z-index: 10 !important;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }

        @media (max-width: 576px) {
            .icon-3d {
                width: 50px;
                height: 50px;
            }
            
            .icon-3d i {
                font-size: 20px;
            }
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            overflow-x: hidden;
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
            height: calc(100vh - 120px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Style de la scrollbar pour le sidebar */
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
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
            padding: 0;
            overflow-x: auto;
        }

        .main-content.expanded {
            margin-left: 80px;
        }
        
        .content-wrapper {
            padding: 20px;
            min-height: calc(100vh - 80px);
            overflow-y: auto;
        }

        .main-content {
            overflow-y: auto;
            max-height: 100vh;
        }

        .page-content {
            padding: 0;
            margin: 0;
        }

        .card {
            margin-bottom: 1.5rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .stats-card {
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }

        .activity-content h6 {
            margin: 0;
            font-size: 0.9rem;
        }

        .activity-content p {
            margin: 0;
            font-size: 0.8rem;
        }

        .task-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-priority {
            width: 4px;
            height: 40px;
            border-radius: 2px;
            margin-right: 1rem;
        }

        .task-priority.high {
            background-color: #dc3545;
        }

        .task-priority.medium {
            background-color: #ffc107;
        }

        .task-priority.low {
            background-color: #28a745;
        }

        .task-content {
            flex: 1;
        }

        .task-content h6 {
            margin: 0;
            font-size: 0.9rem;
        }

        .task-content p {
            margin: 0;
            font-size: 0.8rem;
        }

        .task-progress {
            width: 100px;
        }

        .progress {
            height: 6px;
        }

        .scroll-indicator {
            text-align: center;
            padding: 1rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 1rem;
        }

        .scroll-indicator i {
            display: block;
            margin-bottom: 0.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .navbar-right .btn-link {
            color: #6c757d;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .navbar-right .btn-link:hover {
            color: var(--primary-color);
            background-color: rgba(102, 126, 234, 0.1);
        }

        .navbar-right .position-relative {
            position: relative;
        }

        .badge {
            font-size: 0.7rem;
            min-width: 18px;
            height: 18px;
            line-height: 1;
            padding: 0.25rem 0.4rem;
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
            color: var(--dark-color);
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

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            width: 100%;
            }
            
            .content-wrapper {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .content-wrapper {
                padding: 10px;
            }
            
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        /* Styles pour le système de notifications professionnel */
        .notification-bell {
            position: relative;
            transition: all 0.3s ease;
        }

        .notification-bell:hover {
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--gradient-danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: pulse 2s infinite;
        }

        .notification-badge:empty {
            display: none;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .notification-dropdown {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 15px;
            padding: 0;
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .notification-item.unread {
            background-color: rgba(102, 126, 234, 0.08);
            border-left: 4px solid var(--primary-color);
        }

        .notification-item.read {
            opacity: 0.7;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .notification-content {
            flex: 1;
            margin-left: 12px;
        }

        .notification-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #2c3e50;
        }

        .notification-message {
            font-size: 0.8rem;
            color: #6c757d;
            line-height: 1.4;
            margin-bottom: 4px;
        }

        .notification-time {
            font-size: 0.7rem;
            color: #adb5bd;
        }

        .notification-type-info .notification-icon {
            background: var(--gradient-info);
        }

        .notification-type-success .notification-icon {
            background: var(--gradient-success);
        }

        .notification-type-warning .notification-icon {
            background: var(--gradient-warning);
        }

        .notification-type-error .notification-icon {
            background: var(--gradient-danger);
        }
    </style>
    
    <!-- Système de notifications CSAR -->
    <link href="{{ asset('css/notifications.css') }}" rel="stylesheet">
    <link href="{{ asset('css/notifications-custom.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route(request()->routeIs('ctc.*') ? 'ctc.dashboard' : 'admin.dashboard') }}" class="logo">
                <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <i class="fas fa-shield-alt" style="display: none;"></i>
                <span class="logo-text">{{ request()->routeIs('ctc.*') ? 'CTC CSAR' : 'CSAR Admin' }}</span>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            @if(request()->routeIs('ctc.*'))
            {{-- Menu CTC - Conseil Technique Communication --}}
            <div class="menu-item">
                <a href="{{ route('ctc.dashboard') }}" class="menu-link {{ request()->routeIs('ctc.dashboard') || request()->routeIs('ctc.communications.*') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('ctc.actualites.index') }}" class="menu-link {{ request()->routeIs('ctc.actualites.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper"></i>
                    <span>Actualités</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('ctc.sim-reports.index') }}" class="menu-link {{ request()->routeIs('ctc.sim-reports.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Rapports</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('ctc.newsletter.index') }}" class="menu-link {{ request()->routeIs('ctc.newsletter.*') ? 'active' : '' }}">
                    <i class="fas fa-mail-bulk"></i>
                    <span>Newsletter</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('ctc.galerie.index') }}" class="menu-link {{ request()->routeIs('ctc.galerie.*') ? 'active' : '' }}">
                    <i class="fas fa-images"></i>
                    <span>Galerie</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('ctc.messages.index') }}" class="menu-link {{ request()->routeIs('ctc.messages.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
            </div>
            <div class="menu-item mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
                <a href="{{ url('/') }}" class="menu-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Voir le site public</span>
                </a>
            </div>
            @else
            {{-- Menu Admin complet --}}
            <!-- Tableau de bord -->
            <div class="menu-item">
                <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            
            <!-- Gestion des demandes -->
            <div class="menu-item">
                <a href="{{ route('admin.demandes.index') }}" class="menu-link {{ request()->routeIs('admin.demandes.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Demandes</span>
                </a>
            </div>
            <div class="menu-item">
                <a href="{{ route('admin.carte-demandes.index') }}" class="menu-link {{ request()->routeIs('admin.carte-demandes.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Carte des demandes d'aide</span>
                </a>
            </div>
            
            <!-- Gestion des utilisateurs -->
            <div class="menu-item">
                <a href="{{ route('admin.users.index') }}" class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </div>
            
            <!-- Gestion des entrepôts -->
            <div class="menu-item">
                <a href="{{ route('admin.entrepots.index') }}" class="menu-link {{ request()->routeIs('admin.entrepots.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Entrepôts</span>
                </a>
            </div>
            
            <!-- Gestion des stocks -->
            <div class="menu-item">
                <a href="{{ route('admin.stock.index') }}" class="menu-link {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i>
                    <span>Gestion des Stocks</span>
                </a>
            </div>
            
            <!-- Gestion du personnel -->
            <div class="menu-item">
                <a href="{{ route('admin.personnel.index') }}" class="menu-link {{ request()->routeIs('admin.personnel.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Personnel</span>
                </a>
            </div>
            
            <!-- Gestion du contenu - SUPPRIMÉ (section non utilisée) -->
            {{-- <div class="menu-item">
                <a href="{{ route('admin.contenu.index') }}" class="menu-link {{ request()->routeIs('admin.contenu.*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Gestion du contenu</span>
                </a>
            </div> --}}
            
            <!-- Statistiques -->
            <div class="menu-item">
                <a href="{{ route('admin.statistics') }}" class="menu-link {{ request()->routeIs('admin.statistics*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistiques</span>
                </a>
            </div>
            
            <!-- Chiffres Clés -->
            <div class="menu-item">
                <a href="{{ route('admin.chiffres-cles.index') }}" class="menu-link {{ request()->routeIs('admin.chiffres-cles*') ? 'active' : '' }}">
                    <i class="fas fa-calculator"></i>
                    <span>Chiffres Clés</span>
                </a>
            </div>
            
            <!-- Communication & Publications (hub central : actualités, rapports, newsletter, galerie, messages) -->
            <div class="menu-item">
                <a href="{{ route('admin.communications.index') }}" class="menu-link {{ request()->routeIs('admin.communications.*') || request()->routeIs('admin.communication.*') || request()->routeIs('admin.actualites.*') || request()->routeIs('admin.newsletter.*') || request()->routeIs('admin.sim-reports.*') || request()->routeIs('admin.galerie.*') || request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn"></i>
                    <span>Communication & Publications</span>
                </a>
            </div>

            <!-- SIM — Régions, Départements, Marchés, Assignations -->
            <div class="menu-item">
                <a href="{{ route('admin.sim.dashboard') }}" class="menu-link {{ request()->routeIs('admin.sim.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-store"></i>
                    <span>SIM (Marchés & Assignations)</span>
                </a>
            </div>

            <!-- DRH — Avances Tabaski 2026 -->
            <div class="menu-item">
                <a href="{{ route('admin.drh.tabaski.index') }}" class="menu-link {{ request()->routeIs('admin.drh.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Avances Tabaski</span>
                    <span class="badge bg-warning text-dark ms-auto" style="font-size:0.6rem;padding:2px 5px;">2026</span>
                </a>
            </div>

            <!-- SIM — Suivi collecteurs terrain temps réel -->
            <div class="menu-item">
                <a href="{{ route('admin.sim.suivi') }}" class="menu-link {{ request()->routeIs('admin.sim.suivi') || request()->routeIs('admin.sim.live') || request()->routeIs('admin.sim.collectes') ? 'active' : '' }}">
                    <i class="fas fa-broadcast-tower"></i>
                    <span>SIM — Suivi terrain</span>
                    <span class="badge bg-success ms-auto" style="font-size:0.65rem;padding:2px 6px;">Live</span>
                </a>
            </div>
            
            <!-- Audit & Sécurité -->
            <div class="menu-item">
                <a href="{{ route('admin.audit.index') }}" class="menu-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    <span>Audit & Sécurité</span>
                </a>
            </div>
            
            
            <!-- Profil utilisateur -->
            <div class="menu-item">
                <a href="{{ route('admin.profile') }}" class="menu-link">
                    <i class="fas fa-user-cog"></i>
                    <span>Profil</span>
                </a>
            </div>
            @endif
            
            <!-- Indicateur de défilement -->
            <div class="scroll-indicator">
                <i class="fas fa-chevron-down"></i>
                <span>Défiler pour voir plus</span>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="navbar-left">
                <button class="btn btn-link" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 ms-3">@yield('page-title', 'Administration')</h4>
            </div>
            
            <div class="navbar-right">
                <!-- Notifications unifiées -->
                <div class="dropdown">
                    @include('components.notification-bell')
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 400px; max-height: 500px; overflow-y: auto;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell me-2"></i>Notifications</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="markAllNotificationsAsRead()" id="markAllReadBtn">
                                <i class="fas fa-check-double"></i> Tout marquer lu
                            </button>
                                </li>
                        <li><hr class="dropdown-divider"></li>
                        <div id="notifications-container">
                            <div class="text-center py-3" id="notifications-loading">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                </div>
                                <div class="mt-2 text-muted">Chargement des notifications...</div>
                                                </div>
                        </div>
                        <li><hr class="dropdown-divider"></li>
                        <li class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Les notifications sont générées automatiquement depuis la plateforme publique
                            </small>
                        </li>
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown">
                        A
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ request()->routeIs('ctc.*') ? 'CTC' : 'Administrateur' }}</h6></li>
                        @if(!request()->routeIs('ctc.*'))
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="fas fa-user me-2"></i>Profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route(request()->routeIs('ctc.*') ? 'ctc.logout' : 'admin.logout') }}" class="d-inline">
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
            
            <div class="content-wrapper">
            @yield('content')
        </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
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
            fetch('{{ route("admin.dashboard.realtime-stats") }}')
                .then(response => response.json())
                .then(data => {
                    // Traitement des données
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

        // Système de notifications professionnel et dynamique
        let notificationInterval;
        let isNotificationDropdownOpen = false;

        // Initialiser le système de notifications
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            startNotificationPolling();
            
            // Gérer l'ouverture/fermeture du dropdown
            const notificationBell = document.getElementById('notificationBell');
            const dropdown = notificationBell.nextElementSibling;
            
            dropdown.addEventListener('show.bs.dropdown', function() {
                isNotificationDropdownOpen = true;
                loadNotifications();
            });
            
            dropdown.addEventListener('hide.bs.dropdown', function() {
                isNotificationDropdownOpen = false;
            });
        });

        // Le système de notifications est maintenant géré par le composant notification-bell

        // Rendre les notifications dans le dropdown
        function renderNotifications(notifications) {
            const container = document.getElementById('notifications-container');
            
            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <div class="notification-icon mx-auto mb-3" style="background: var(--gradient-secondary); width: 60px; height: 60px;">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h6 class="text-muted">Aucune notification</h6>
                        <small class="text-muted">Les notifications apparaîtront ici automatiquement</small>
                    </div>
                `;
                return;
            }

            const notificationsHtml = notifications.map(notification => `
                <div class="notification-item ${notification.read ? 'read' : 'unread'} notification-type-${notification.type}" 
                     onclick="handleNotificationClick(${notification.id}, '${notification.url}')">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon">
                            <i class="fas fa-${getNotificationIcon(notification.type)}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notification.title}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-time">
                                <i class="fas fa-clock me-1"></i>${notification.created_at}
                            </div>
                        </div>
                        ${!notification.read ? '<div class="notification-dot"></div>' : ''}
                    </div>
                </div>
            `).join('');

            container.innerHTML = notificationsHtml;
        }

        // Obtenir l'icône appropriée selon le type de notification
        function getNotificationIcon(type) {
            const icons = {
                'info': 'info-circle',
                'success': 'check-circle',
                'warning': 'exclamation-triangle',
                'error': 'times-circle'
            };
            return icons[type] || 'info-circle';
        }

        // Gérer le clic sur une notification
        function handleNotificationClick(notificationId, url) {
            // Marquer comme lu
            markNotificationAsRead(notificationId);
            
            // Rediriger vers l'URL appropriée
            if (url && url !== '#') {
                window.location.href = url;
            }
        }

        // Marquer une notification comme lue
        function markNotificationAsRead(notificationId) {
            fetch('{{ route("notifications.mark-read", ":id") }}'.replace(':id', notificationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                updateNotificationCount(data.count);
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

        // Marquer toutes les notifications comme lues
        function markAllNotificationsAsRead() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Traitement des données
                updateNotificationCount(0);
                    loadNotifications();
                    showToast('Toutes les notifications ont été marquées comme lues', 'success');
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
                showToast('Erreur lors de la mise à jour', 'error');
            });
        }

        // Mettre à jour le compteur de notifications
        function updateNotificationCount(count) {
            const badge = document.getElementById('notification-count');
            if (badge) {
                badge.textContent = count;
                if (count === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'flex';
                }
            }
        }

        // Démarrer le polling des notifications
        function startNotificationPolling() {
            // Vérifier les nouvelles notifications toutes les 30 secondes
            notificationInterval = setInterval(() => {
                if (!isNotificationDropdownOpen) {
                    checkForNewNotifications();
                }
            }, 30000);
        }

        // Vérifier les nouvelles notifications
        function checkForNewNotifications() {
            fetch('{{ route("admin.notifications.api") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Traitement des données
                updateNotificationCount(data.count);
            .catch(error => console.error('Error checking notifications:', error));
        }

        // Afficher une erreur de notification
        function showNotificationError(message) {
            const container = document.getElementById('notifications-container');
            container.innerHTML = `
                <div class="text-center py-4">
                    <div class="notification-icon mx-auto mb-3" style="background: var(--gradient-danger); width: 60px; height: 60px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h6 class="text-danger">Erreur</h6>
                    <small class="text-muted">${message}</small>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="loadNotifications()">
                            <i class="fas fa-refresh me-1"></i>Réessayer
                        </button>
                    </div>
                </div>
            `;
        }

        // Afficher un toast de notification
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Nettoyer l'intervalle quand la page se ferme
        window.addEventListener('beforeunload', function() {
            if (notificationInterval) {
                clearInterval(notificationInterval);
            }
        });

        // Marquer les notifications individuelles comme lues
        document.addEventListener('DOMContentLoaded', function() {
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    // Logique pour marquer une notification spécifique comme lue
                    console.log('Notification cliquée:', this.dataset.id);
                });
            });
            
            // S'assurer que tous les liens sont cliquables
            const allLinks = document.querySelectorAll('a');
            allLinks.forEach(function(link) {
                link.style.cursor = 'pointer';
                link.style.pointerEvents = 'auto';
                link.style.zIndex = '10';
            });
            
            // S'assurer que tous les boutons sont cliquables
            const allButtons = document.querySelectorAll('button, .btn');
            allButtons.forEach(function(button) {
                button.style.cursor = 'pointer';
                button.style.pointerEvents = 'auto';
                button.style.zIndex = '10';
            });
            
            // S'assurer que les éléments de menu sont cliquables
            const menuLinks = document.querySelectorAll('.menu-link');
            menuLinks.forEach(function(link) {
                link.style.cursor = 'pointer';
                link.style.pointerEvents = 'auto';
                link.style.zIndex = '10';
            });

            // Améliorer l'expérience de défilement du sidebar
            const sidebarMenu = document.querySelector('.sidebar-menu');
            const scrollIndicator = document.querySelector('.scroll-indicator');
            
            if (sidebarMenu && scrollIndicator) {
                // Masquer l'indicateur si tout le contenu est visible
                function checkScrollIndicator() {
                    if (sidebarMenu.scrollHeight <= sidebarMenu.clientHeight) {
                        scrollIndicator.style.display = 'none';
                    } else {
                        scrollIndicator.style.display = 'block';
                    }
                }
                
                // Vérifier au chargement
                checkScrollIndicator();
                
                // Vérifier lors du redimensionnement
                window.addEventListener('resize', checkScrollIndicator);
                
                // Masquer l'indicateur quand on fait défiler
                sidebarMenu.addEventListener('scroll', function() {
                    if (sidebarMenu.scrollTop > 50) {
                        scrollIndicator.style.opacity = '0.3';
                    } else {
                        scrollIndicator.style.opacity = '1';
                    }
                });
            }
        });
    </script>
    
    <!-- Système de notifications CSAR -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    @stack('scripts')
</body>
</html> 
