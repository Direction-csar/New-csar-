#!/bin/bash
# Mise à jour menu DRH + layout enquête santé
set -e
APP=/var/www/csar
cd "$APP"

echo "=== 1. Mise à jour du layout DRH (ajout menu navigation) ==="
sudo tee resources/views/layouts/drh-portal.blade.php > /dev/null << 'BLADEEOF'
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
        .drh-topbar .brand { display: flex; align-items: center; gap: 12px; }
        .drh-topbar .brand img { height: 46px; object-fit: contain; }
        .drh-topbar .brand-text { line-height: 1.2; }
        .drh-topbar .brand-text strong { display: block; font-size: 1.05rem; font-weight: 700; letter-spacing: 0.3px; }
        .drh-topbar .brand-text span { font-size: 0.72rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }
        .drh-topbar .right-actions { display: flex; align-items: center; gap: 16px; }
        .drh-topbar .user-info { text-align: right; line-height: 1.2; }
        .drh-topbar .user-info strong { display: block; font-size: 0.82rem; font-weight: 600; }
        .drh-topbar .user-info span { font-size: 0.7rem; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.4px; }
        .btn-logout { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 6px 14px; border-radius: 8px; font-size: 0.82rem; text-decoration: none; transition: background 0.2s; }
        .btn-logout:hover { background: rgba(255,255,255,0.25); color: white; }
        .main-content { padding: 24px; max-width: 1400px; margin: 0 auto; }
        .text-xs { font-size: 0.72rem; }
        .drh-nav { background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0 24px; display: flex; gap: 4px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .drh-nav a { color: #475569; text-decoration: none; padding: 14px 18px; font-size: 0.88rem; font-weight: 500; border-bottom: 3px solid transparent; transition: all 0.2s; white-space: nowrap; }
        .drh-nav a:hover { color: #047857; background: #f0fdf4; }
        .drh-nav a.active { color: #047857; border-bottom-color: #047857; background: #f0fdf4; font-weight: 600; }
        @media (max-width: 576px) {
            .drh-topbar .user-info { display: none; }
            .main-content { padding: 16px; }
            .drh-nav { padding: 0 8px; }
            .drh-nav a { padding: 12px 12px; font-size: 0.82rem; }
        }
    </style>
    @yield('styles')
</head>
<body>

<div class="drh-topbar">
    <div class="brand">
        <img src="{{ asset('images/csar-logo-white.png') }}" alt="CSAR">
        <div class="brand-text">
            <strong>Direction des Ressources Humaines</strong>
            <span>Portail DRH — CSAR</span>
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

@php $currentRoute = request()->route()?->getName() ?? ''; @endphp
<nav class="drh-nav">
    <a href="{{ route('admin.drh.tabaski.index') }}" class="{{ str_contains($currentRoute, 'tabaski') ? 'active' : '' }}">
        <i class="fas fa-coins me-1"></i> Avances Tabaski
    </a>
    <a href="{{ route('admin.drh.health-survey.index') }}" class="{{ str_contains($currentRoute, 'health-survey') ? 'active' : '' }}">
        <i class="fas fa-poll-h me-1"></i> Enquête Assurance Maladie
    </a>
</nav>

<div class="main-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
BLADEEOF
echo "✅ Layout DRH mis à jour avec menu de navigation"

echo "=== 2. Correction layout vues health-survey ==="
sudo sed -i "1c\\@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')" resources/views/admin/drh/health-survey/index.blade.php
sudo sed -i "1c\\@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')" resources/views/admin/drh/health-survey/show.blade.php
echo "✅ Vues health-survey corrigées"

echo "=== 3. Permissions ==="
sudo chown -R www-data:www-data resources/views/layouts/drh-portal.blade.php resources/views/admin/drh/health-survey

echo "=== 4. Cache ==="
sudo php artisan view:clear
sudo php artisan route:clear
sudo php artisan config:clear

echo ""
echo "==========================================="
echo "✅ MISE À JOUR TERMINÉE !"
echo ""
echo "👉 Recharge la page DRH : https://csar.sn/admin/drh/avances-tabaski"
echo "👉 Tu verras maintenant le menu avec 2 onglets :"
echo "   - Avances Tabaski"
echo "   - Enquête Assurance Maladie"
echo "==========================================="
