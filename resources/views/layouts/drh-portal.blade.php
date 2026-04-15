<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace DRH') — CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .drh-topbar {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            color: white;
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .drh-topbar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .drh-topbar .brand img {
            height: 46px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }
        .drh-topbar .brand-text {
            line-height: 1.2;
        }
        .drh-topbar .brand-text strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .drh-topbar .brand-text span {
            font-size: 0.72rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .drh-topbar .right-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .drh-topbar .user-info {
            text-align: right;
            line-height: 1.2;
        }
        .drh-topbar .user-info strong {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .drh-topbar .user-info span {
            font-size: 0.7rem;
            opacity: 0.75;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.25); color: white; }
        .main-content { padding: 24px; max-width: 1400px; margin: 0 auto; }
        .text-xs { font-size: 0.72rem; }
        @media (max-width: 576px) {
            .drh-topbar .user-info { display: none; }
            .main-content { padding: 16px; }
        }
    </style>
    @yield('styles')
</head>
<body>

{{-- Barre de navigation DRH --}}
<div class="drh-topbar">
    <div class="brand">
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR">
        <div class="brand-text">
            <strong>Direction des Ressources Humaines</strong>
            <span>Avances Tabaski 2026 — CSAR</span>
        </div>
    </div>
    <div class="right-actions">
        <div class="user-info">
            <strong>{{ Auth::user()->name }}</strong>
            <span>DRH — CSAR</span>
        </div>
        <form method="POST" action="{{ route('drh.logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
            </button>
        </form>
    </div>
</div>

{{-- Contenu principal --}}
<div class="main-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
