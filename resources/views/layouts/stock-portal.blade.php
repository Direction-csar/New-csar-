<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace ' . strtoupper($portalLabel ?? 'CSAR')) - CSAR Stocks</title>
    <link rel="stylesheet" href="{{ asset('css/stock-portal.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @yield('styles')
    @stack('styles')
</head>
<body>
@php
    $portal = $portal ?? 'dtl';
    $portalLabel = $portalLabel ?? 'CSAR Stocks';
    $currentRoute = request()->route()?->getName() ?? '';
    $user = Auth::guard($portal)->user() ?? Auth::user();
    $logoutRoute = $logoutRoute ?? ($portal . '.logout');
    $dashboardRoute = $dashboardRoute ?? ($portal . '.dashboard');
@endphp

<aside class="sidebar" id="sb">
    <div class="sb-brand">
        <div class="logo">CSAR</div>
        <div class="t"><strong>{{ $portalLabel }}</strong><small>Gestion des stocks</small></div>
    </div>
    <nav class="sb-nav">
        @yield('sidebar')
        <a class="sb-link {{ $currentRoute === $dashboardRoute || str_ends_with($currentRoute, '.dashboard') ? 'active' : '' }}" href="{{ route($dashboardRoute) }}">
            <i class="fas fa-tachometer-alt"></i><span class="lbl">Tableau de bord</span>
        </a>
        <a class="sb-link {{ str_contains($currentRoute, 'stock-operations.index') ? 'active' : '' }}" href="{{ route($portal . '.stock-operations.index') }}">
            <i class="fas fa-dolly"></i><span class="lbl">Opérations de stock</span>
        </a>
        @if(Route::has($portal . '.stock-operations.pending'))
        <a class="sb-link {{ str_contains($currentRoute, 'stock-operations.pending') ? 'active' : '' }}" href="{{ route($portal . '.stock-operations.pending') }}">
            <i class="fas fa-clock"></i><span class="lbl">Mouvements en attente</span>
        </a>
        @endif
        @yield('sidebar-extra')
    </nav>
    <div class="sb-user">
        <div class="sb-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</div>
        <div class="u"><b>{{ $user->name ?? 'Utilisateur' }}</b><small>{{ $user->email ?? '' }}</small></div>
        <form method="POST" action="{{ route($logoutRoute) }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-toggle" title="Déconnexion" style="border:none;background:none;cursor:pointer"><i class="fas fa-sign-out-alt"></i></button>
        </form>
        <button class="sb-toggle" onclick="document.getElementById('sb').classList.toggle('collapsed');document.getElementById('mn').classList.toggle('expanded')" title="Réduire le menu"><i class="fas fa-chevron-left"></i></button>
    </div>
</aside>

<div class="main" id="mn">
    <div class="topbar">
        <div>
            <h1>@yield('page-title', 'Tableau de bord')</h1>
            <div class="crumb">@yield('breadcrumb', 'CSAR › ' . $portalLabel)</div>
        </div>
        <div class="right">
            <span class="refresh"><span class="dot"></span> @yield('refresh-label', 'Dernière actualisation : ' . now()->format('d/m/Y H:i'))</span>
            <div class="avatar-top">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</div>
        </div>
    </div>

    <div class="content">
        @if(session('success'))
            <div class="alert-strip alert-info"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="alert-strip alert-danger"><i class="fas fa-exclamation-circle"></i><div>{{ session('error') }}</div></div>
        @endif
        @yield('content')
    </div>
</div>

<script>
    (function(){
        var sb=document.getElementById('sb'),mn=document.getElementById('mn');
        if(localStorage.getItem('sb-collapsed')==='1'){sb.classList.add('collapsed');mn.classList.add('expanded');}
        document.querySelector('.sb-toggle').addEventListener('click',function(){
            sb.classList.toggle('collapsed');mn.classList.toggle('expanded');
            localStorage.setItem('sb-collapsed',sb.classList.contains('collapsed')?'1':'0');
        });
    })();
</script>
@yield('scripts')
@stack('scripts')
</body>
</html>
