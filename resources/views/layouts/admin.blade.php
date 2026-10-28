<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') - CSAR Platform</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/csar-logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/csar-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/csar-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* =========================================================
           CSAR Admin — Design system
           ========================================================= */
        :root {
            --adm-navy: #12304A;
            --adm-navy-700: #0E2639;
            --adm-navy-600: #1A3D5C;
            --adm-green: #16845B;
            --adm-green-600: #127050;
            --adm-green-50: #E8F5EF;
            --adm-bg: #F4F7FA;
            --adm-surface: #FFFFFF;
            --adm-text: #243447;
            --adm-muted: #64748B;
            --adm-border: #E2E8F0;
            --adm-subtle: #F8FAFC;
            --adm-warning: #B7791F;
            --adm-warning-50: #FDF6E7;
            --adm-danger: #C0392B;
            --adm-danger-50: #FBECEA;
            --adm-info: #1D6FA5;
            --adm-info-50: #E8F1F8;
            --adm-radius: 10px;
            --adm-radius-sm: 8px;
            --adm-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 1px 3px rgba(16, 24, 40, .06);
            --adm-shadow-md: 0 4px 12px rgba(16, 24, 40, .08);
            --adm-sidebar-w: 268px;
            --adm-sidebar-w-collapsed: 76px;
            --adm-topbar-h: 64px;

            /* Bootstrap 5.3 theming */
            --bs-primary: #12304A;
            --bs-primary-rgb: 18, 48, 74;
            --bs-success: #16845B;
            --bs-success-rgb: 22, 132, 91;
            --bs-warning-rgb: 183, 121, 31;
            --bs-danger-rgb: 192, 57, 43;
            --bs-info-rgb: 29, 111, 165;
            --bs-body-color: #243447;
            --bs-body-bg: #F4F7FA;
            --bs-border-color: #E2E8F0;
            --bs-secondary-color: #64748B;
            --bs-link-color: #12304A;
            --bs-link-color-rgb: 18, 48, 74;
            --bs-link-hover-color: #16845B;
            --bs-link-hover-color-rgb: 22, 132, 91;
            --bs-body-font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --bs-border-radius: .5rem;

            /* Variables historiques conservées pour les vues existantes (aplaties) */
            --primary-color: #12304A;
            --secondary-color: #475569;
            --success-color: #16845B;
            --warning-color: #B7791F;
            --danger-color: #C0392B;
            --info-color: #1D6FA5;
            --dark-color: #243447;
            --light-color: #F8FAFC;
            --gradient-primary: linear-gradient(#12304A, #12304A);
            --gradient-secondary: linear-gradient(#475569, #475569);
            --gradient-success: linear-gradient(#16845B, #16845B);
            --gradient-warning: linear-gradient(#B7791F, #B7791F);
            --gradient-danger: linear-gradient(#C0392B, #C0392B);
            --gradient-info: linear-gradient(#1D6FA5, #1D6FA5);
            --shadow-soft: var(--adm-shadow);
            --shadow-medium: var(--adm-shadow-md);
            --shadow-strong: var(--adm-shadow-md);
        }

        html { scroll-behavior: auto; }

        body {
            font-family: var(--bs-body-font-family);
            font-size: .9375rem;
            background: var(--adm-bg);
            color: var(--adm-text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 { color: var(--adm-text); font-weight: 600; letter-spacing: -.01em; }
        .text-muted { color: var(--adm-muted) !important; }

        /* ---------- Sidebar ---------- */
        .adm-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--adm-sidebar-w);
            background: var(--adm-navy);
            color: #fff;
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: width .2s ease, transform .2s ease;
        }

        .adm-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            height: var(--adm-topbar-h);
            padding: 0 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            color: #fff;
            text-decoration: none;
            flex-shrink: 0;
        }
        .adm-brand:hover { color: #fff; }
        .adm-brand-logo {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: #fff;
            padding: 3px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .adm-brand-fallback {
            width: 36px; height: 36px; border-radius: 8px;
            background: var(--adm-green);
            display: none; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .adm-brand-text { display: flex; flex-direction: column; line-height: 1.15; min-width: 0; }
        .adm-brand-name { font-weight: 700; font-size: 1rem; letter-spacing: .02em; }
        .adm-brand-sub { font-size: .75rem; color: rgba(255, 255, 255, .6); white-space: nowrap; }

        .adm-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem .75rem 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .2) transparent;
        }
        .adm-nav::-webkit-scrollbar { width: 6px; }
        .adm-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, .2); border-radius: 3px; }

        .adm-nav-section { margin-top: 1rem; }
        .adm-nav-section:first-child { margin-top: 0; }
        .adm-nav-section-title {
            font-size: .6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255, 255, 255, .45);
            padding: .25rem .75rem .375rem;
            white-space: nowrap;
        }

        .adm-nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .5rem .75rem;
            margin: 1px 0;
            border-radius: var(--adm-radius-sm);
            color: rgba(255, 255, 255, .78);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            position: relative;
            white-space: nowrap;
            transition: background-color .15s ease, color .15s ease;
        }
        .adm-nav-link:hover { background: rgba(255, 255, 255, .07); color: #fff; }
        .adm-nav-link.active { background: rgba(255, 255, 255, .1); color: #fff; }
        .adm-nav-link.active::before {
            content: '';
            position: absolute;
            left: -.75rem; top: 6px; bottom: 6px;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: var(--adm-green);
        }
        .adm-nav-link:focus-visible { outline: 2px solid #fff; outline-offset: -2px; }
        .adm-nav-icon { width: 20px; text-align: center; font-size: .9375rem; flex-shrink: 0; opacity: .9; }
        .adm-nav-label { flex: 1; overflow: hidden; text-overflow: ellipsis; }
        .adm-nav-badge {
            font-size: .6875rem;
            font-weight: 600;
            line-height: 1;
            padding: .25rem .45rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
            color: #fff;
        }
        .adm-nav-badge.is-alert { background: var(--adm-danger); }
        .adm-nav-badge.is-live { background: var(--adm-green); }

        .adm-sidebar-footer {
            padding: .75rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
            flex-shrink: 0;
        }

        /* Collapsed (desktop) */
        @media (min-width: 992px) {
            body.adm-collapsed .adm-sidebar { width: var(--adm-sidebar-w-collapsed); }
            body.adm-collapsed .adm-brand { padding: 0; justify-content: center; }
            body.adm-collapsed .adm-brand-text,
            body.adm-collapsed .adm-nav-label,
            body.adm-collapsed .adm-nav-section-title { display: none; }
            body.adm-collapsed .adm-nav-section { margin-top: .5rem; padding-top: .5rem; border-top: 1px solid rgba(255, 255, 255, .06); }
            body.adm-collapsed .adm-nav-section:first-child { border-top: 0; padding-top: 0; }
            body.adm-collapsed .adm-nav-link { justify-content: center; padding: .625rem 0; }
            body.adm-collapsed .adm-nav-badge {
                position: absolute; top: 4px; right: 10px;
                padding: 0; width: 8px; height: 8px; font-size: 0;
            }
            body.adm-collapsed .adm-main { margin-left: var(--adm-sidebar-w-collapsed); }
        }

        /* ---------- Main ---------- */
        .adm-main {
            margin-left: var(--adm-sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .2s ease;
            min-width: 0;
        }

        .adm-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            height: var(--adm-topbar-h);
            background: var(--adm-surface);
            border-bottom: 1px solid var(--adm-border);
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0 1.5rem;
        }
        .adm-topbar-title { min-width: 0; flex: 1; }
        .adm-topbar-title h1 {
            font-size: 1.0625rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .adm-breadcrumb { margin: 0; padding: 0; font-size: .75rem; --bs-breadcrumb-divider-color: var(--adm-muted); --bs-breadcrumb-item-active-color: var(--adm-muted); }
        .adm-breadcrumb a { color: var(--adm-muted); text-decoration: none; }
        .adm-breadcrumb a:hover { color: var(--adm-navy); }

        .adm-topbar-actions { display: flex; align-items: center; gap: .5rem; }
        .adm-date { font-size: .8125rem; color: var(--adm-muted); white-space: nowrap; padding-right: .5rem; }

        .adm-icon-btn {
            width: 38px; height: 38px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: var(--adm-radius-sm);
            border: 1px solid transparent;
            background: transparent;
            color: var(--adm-muted);
            transition: background-color .15s ease, color .15s ease;
        }
        .adm-icon-btn:hover { background: var(--adm-subtle); color: var(--adm-navy); border-color: var(--adm-border); }

        .adm-topbar .notification-bell-btn { color: var(--adm-muted); padding: .5rem .65rem; border-radius: var(--adm-radius-sm); }
        .adm-topbar .notification-bell-btn:hover { color: var(--adm-navy); background: var(--adm-subtle); transform: none; }
        .adm-topbar .notification-bell-btn:hover .fa-bell { animation: none; }
        .adm-topbar .notification-bell-btn .fs-5 { font-size: 1.05rem !important; }
        .adm-topbar .notification-badge { animation: none; box-shadow: none; background: var(--adm-danger) !important; }

        .adm-user-btn {
            display: flex; align-items: center; gap: .625rem;
            border: 1px solid transparent;
            background: transparent;
            border-radius: var(--adm-radius-sm);
            padding: .25rem .5rem .25rem .25rem;
            color: var(--adm-text);
        }
        .adm-user-btn:hover, .adm-user-btn[aria-expanded="true"] { background: var(--adm-subtle); border-color: var(--adm-border); }
        .adm-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--adm-navy);
            color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .8125rem; font-weight: 600;
            flex-shrink: 0;
        }
        .adm-user-meta { display: flex; flex-direction: column; text-align: left; line-height: 1.2; }
        .adm-user-name { font-size: .8125rem; font-weight: 600; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .adm-user-role { font-size: .6875rem; color: var(--adm-muted); }

        .adm-content {
            flex: 1;
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .adm-footer {
            padding: 1rem 1.5rem;
            font-size: .75rem;
            color: var(--adm-muted);
            border-top: 1px solid var(--adm-border);
            background: var(--adm-surface);
        }

        .adm-backdrop {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 1035;
            display: none;
        }

        /* Mobile / tablette */
        @media (max-width: 991.98px) {
            .adm-sidebar { transform: translateX(-100%); box-shadow: none; }
            body.adm-sidebar-open .adm-sidebar { transform: translateX(0); box-shadow: var(--adm-shadow-md); }
            body.adm-sidebar-open .adm-backdrop { display: block; }
            body.adm-sidebar-open { overflow: hidden; }
            .adm-main { margin-left: 0; }
            .adm-topbar { padding: 0 1rem; }
            .adm-content { padding: 1rem; }
        }
        @media (max-width: 575.98px) {
            .adm-content { padding: .75rem; }
            .adm-breadcrumb { display: none; }
        }

        /* ---------- Composants communs ---------- */
        .adm-flash { border-radius: var(--adm-radius-sm); }

        .card {
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: var(--adm-shadow);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: var(--adm-surface);
            border-bottom: 1px solid var(--adm-border);
            font-weight: 600;
            padding: .875rem 1.25rem;
        }
        .card-header:first-child { border-radius: var(--adm-radius) var(--adm-radius) 0 0; }
        .card-footer { background: var(--adm-subtle); border-top: 1px solid var(--adm-border); }

        .btn { border-radius: var(--adm-radius-sm); font-weight: 500; }
        .btn-primary {
            --bs-btn-bg: var(--adm-navy); --bs-btn-border-color: var(--adm-navy);
            --bs-btn-hover-bg: var(--adm-navy-600); --bs-btn-hover-border-color: var(--adm-navy-600);
            --bs-btn-active-bg: var(--adm-navy-700); --bs-btn-active-border-color: var(--adm-navy-700);
            --bs-btn-disabled-bg: var(--adm-navy); --bs-btn-disabled-border-color: var(--adm-navy);
            --bs-btn-focus-shadow-rgb: 18, 48, 74;
        }
        .btn-success {
            --bs-btn-bg: var(--adm-green); --bs-btn-border-color: var(--adm-green);
            --bs-btn-hover-bg: var(--adm-green-600); --bs-btn-hover-border-color: var(--adm-green-600);
            --bs-btn-active-bg: var(--adm-green-600); --bs-btn-active-border-color: var(--adm-green-600);
            --bs-btn-disabled-bg: var(--adm-green); --bs-btn-disabled-border-color: var(--adm-green);
            --bs-btn-focus-shadow-rgb: 22, 132, 91;
        }
        .btn-outline-primary {
            --bs-btn-color: var(--adm-navy); --bs-btn-border-color: var(--adm-border);
            --bs-btn-hover-color: var(--adm-navy); --bs-btn-hover-bg: var(--adm-subtle); --bs-btn-hover-border-color: #CBD5E1;
            --bs-btn-active-color: var(--adm-navy); --bs-btn-active-bg: var(--adm-border); --bs-btn-active-border-color: #CBD5E1;
        }
        .btn-outline-success {
            --bs-btn-color: var(--adm-green); --bs-btn-border-color: var(--adm-green);
            --bs-btn-hover-bg: var(--adm-green); --bs-btn-hover-border-color: var(--adm-green);
            --bs-btn-active-bg: var(--adm-green-600); --bs-btn-active-border-color: var(--adm-green-600);
        }
        .btn-light { --bs-btn-bg: var(--adm-surface); --bs-btn-border-color: var(--adm-border); --bs-btn-hover-bg: var(--adm-subtle); --bs-btn-hover-border-color: #CBD5E1; }

        .form-control, .form-select {
            border-color: var(--adm-border);
            border-radius: var(--adm-radius-sm);
            color: var(--adm-text);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--adm-navy-600);
            box-shadow: 0 0 0 .2rem rgba(18, 48, 74, .12);
        }
        .form-check-input:checked { background-color: var(--adm-green); border-color: var(--adm-green); }
        .form-label { font-weight: 500; font-size: .875rem; color: var(--adm-text); }

        .table { --bs-table-bg: var(--adm-surface); --bs-table-hover-bg: var(--adm-subtle); color: var(--adm-text); }
        .table > :not(caption) > * > * { border-bottom-color: var(--adm-border); padding: .75rem; vertical-align: middle; }
        .table thead th {
            background: var(--adm-subtle);
            color: var(--adm-muted);
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .table-sm > :not(caption) > * > * { padding: .5rem .75rem; }
        .table-responsive { overflow-x: auto; }

        .badge { font-weight: 600; font-size: .72em; padding: .4em .65em; border-radius: 6px; letter-spacing: .01em; }
        .badge-success { background: var(--adm-green); color: #fff; }
        .badge-warning { background: var(--adm-warning); color: #fff; }
        .badge-danger { background: var(--adm-danger); color: #fff; }
        .badge-info { background: var(--adm-info); color: #fff; }

        .alert { border-radius: var(--adm-radius-sm); border-width: 1px; }
        .modal-content { border: 1px solid var(--adm-border); border-radius: var(--adm-radius); box-shadow: var(--adm-shadow-md); }
        .modal-header, .modal-footer { border-color: var(--adm-border); }
        .dropdown-menu {
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius-sm);
            box-shadow: var(--adm-shadow-md);
            padding: .375rem;
            font-size: .875rem;
        }
        .dropdown-item { border-radius: 6px; padding: .5rem .75rem; }
        .dropdown-item:hover, .dropdown-item:focus { background: var(--adm-subtle); color: var(--adm-navy); }
        .dropdown-header { color: var(--adm-muted); font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; }
        .pagination { --bs-pagination-color: var(--adm-navy); --bs-pagination-active-bg: var(--adm-navy); --bs-pagination-active-border-color: var(--adm-navy); --bs-pagination-border-color: var(--adm-border); }
        .nav-tabs { --bs-nav-tabs-border-color: var(--adm-border); }
        .nav-link { color: var(--adm-muted); }
        .nav-tabs .nav-link.active { color: var(--adm-navy); font-weight: 600; }
        .progress { height: 6px; background: var(--adm-border); }

        body > .toast { position: fixed; top: 1rem; right: 1rem; z-index: 1090; }

        /* ---------- Classes historiques (vues existantes) ---------- */
        .card-modern {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            box-shadow: var(--adm-shadow);
            position: relative;
        }

        .icon-3d {
            background: var(--gradient-primary);
            border-radius: var(--adm-radius-sm);
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .icon-3d i { color: #fff; font-size: 20px; }

        .page-header {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--adm-shadow);
        }
        .page-title { color: var(--adm-text); font-size: 1.5rem; font-weight: 700; margin: 0; }
        .page-subtitle { color: var(--adm-muted); margin: .25rem 0 0; }

        .stats-card {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: var(--adm-radius);
            padding: 1.25rem;
            box-shadow: var(--adm-shadow);
        }
        .stats-icon {
            width: 44px; height: 44px;
            border-radius: var(--adm-radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.125rem; color: #fff;
            margin-bottom: .75rem;
        }
        .stats-number { font-size: 1.75rem; font-weight: 700; color: var(--adm-text); margin: 0; }
        .stats-label { color: var(--adm-muted); font-size: .875rem; margin: 0; }

        .activity-item { display: flex; align-items: center; padding: .875rem 0; border-bottom: 1px solid var(--adm-border); }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; margin-right: .875rem; }
        .activity-content h6 { margin: 0; font-size: .875rem; }
        .activity-content p { margin: 0; font-size: .8125rem; }

        .task-item { display: flex; align-items: center; padding: .875rem 0; border-bottom: 1px solid var(--adm-border); }
        .task-item:last-child { border-bottom: none; }
        .task-priority { width: 4px; height: 36px; border-radius: 2px; margin-right: .875rem; }
        .task-priority.high { background-color: var(--adm-danger); }
        .task-priority.medium { background-color: var(--adm-warning); }
        .task-priority.low { background-color: var(--adm-green); }
        .task-content { flex: 1; }
        .task-content h6 { margin: 0; font-size: .875rem; }
        .task-content p { margin: 0; font-size: .8125rem; }
        .task-progress { width: 100px; }

        /* Notifications (dropdown) */
        .notification-dropdown { padding: 0; width: 380px; max-width: calc(100vw - 2rem); max-height: 480px; overflow-y: auto; }
        .notification-dropdown .dropdown-header { padding: .75rem 1rem; }
        .notification-item { padding: .75rem 1rem; border-bottom: 1px solid var(--adm-border); cursor: pointer; }
        .notification-item:hover { background-color: var(--adm-subtle); }
        .notification-item.unread { background-color: var(--adm-info-50); box-shadow: inset 3px 0 0 var(--adm-navy); }
        .notification-item.read { opacity: .75; }
        .notification-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .875rem; background: var(--adm-navy); }
        .notification-content { flex: 1; margin-left: .75rem; }
        .notification-title { font-weight: 600; font-size: .875rem; margin-bottom: 2px; color: var(--adm-text); }
        .notification-message { font-size: .8125rem; color: var(--adm-muted); line-height: 1.4; margin-bottom: 2px; }
        .notification-time { font-size: .75rem; color: #94A3B8; }
        .notification-type-info .notification-icon { background: var(--adm-info); }
        .notification-type-success .notification-icon { background: var(--adm-green); }
        .notification-type-warning .notification-icon { background: var(--adm-warning); }
        .notification-type-error .notification-icon { background: var(--adm-danger); }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>

    <link href="{{ asset('css/notifications.css') }}" rel="stylesheet">
    <link href="{{ asset('css/notifications-custom.css') }}" rel="stylesheet">
    <style>
        .btn-primary {
            background: var(--bs-btn-bg) !important;
            border: var(--bs-btn-border-width) solid var(--bs-btn-border-color) !important;
        }
        .btn-primary:hover, .btn-primary:focus-visible {
            background: var(--bs-btn-hover-bg) !important;
            border-color: var(--bs-btn-hover-border-color) !important;
            transform: none; box-shadow: none;
        }
        .btn-primary:active, .btn-primary.active {
            background: var(--bs-btn-active-bg) !important;
            border-color: var(--bs-btn-active-border-color) !important;
        }
    </style>

    @stack('styles')
</head>
@php
    $admIsCtc = request()->routeIs('ctc.*');
    $admUser = auth()->user();
    $admUserName = $admUser->name ?? ($admIsCtc ? 'CTC' : 'Administrateur');
    $admInitials = collect(preg_split('/\s+/', trim($admUserName)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'A';
    $admRoleLabels = [
        'super_admin' => 'Super administrateur',
        'admin' => 'Administrateur',
        'dg' => 'Direction générale',
        'directeur_general' => 'Direction générale',
        'signataire' => 'Signataire',
        'scanneur' => 'Scanneur',
    ];
    $admRole = $admUser->role ?? null;
    $admRoleLabel = $admIsCtc ? 'Conseil Technique Communication' : ($admRoleLabels[$admRole] ?? ($admRole ? ucfirst(str_replace('_', ' ', $admRole)) : 'Administrateur'));
    $admPageTitle = trim($__env->yieldContent('page-title', 'Administration'));
    $admHomeRoute = $admIsCtc ? 'ctc.dashboard' : 'admin.dashboard';
    $admOnHome = request()->routeIs($admHomeRoute);

    if ($admIsCtc) {
        $admMenu = [
            ['title' => 'CTC', 'items' => [
                ['label' => 'Tableau de bord', 'icon' => 'fa-gauge', 'url' => route('ctc.dashboard'), 'active' => request()->routeIs('ctc.dashboard') || request()->routeIs('ctc.communications.*')],
                ['label' => 'Actualités', 'icon' => 'fa-newspaper', 'url' => route('ctc.actualites.index'), 'active' => request()->routeIs('ctc.actualites.*')],
                ['label' => 'Rapports', 'icon' => 'fa-file-lines', 'url' => route('ctc.sim-reports.index'), 'active' => request()->routeIs('ctc.sim-reports.*')],
                ['label' => 'Newsletter', 'icon' => 'fa-envelope-open-text', 'url' => route('ctc.newsletter.index'), 'active' => request()->routeIs('ctc.newsletter.*')],
                ['label' => 'Galerie', 'icon' => 'fa-images', 'url' => route('ctc.galerie.index'), 'active' => request()->routeIs('ctc.galerie.*')],
                ['label' => 'QR Media Share', 'icon' => 'fa-qrcode', 'url' => route('ctc.media-share.index'), 'active' => request()->routeIs('ctc.media-share.*')],
                ['label' => 'Messages', 'icon' => 'fa-envelope', 'url' => \Illuminate\Support\Facades\Route::has('ctc.messages.index') ? route('ctc.messages.index') : null, 'active' => request()->routeIs('ctc.messages.*'), 'show' => \Illuminate\Support\Facades\Route::has('ctc.messages.index')],
            ]],
        ];
    } else {
        $userRole = $admRole ?? 'admin';
        $isAdmin = in_array($userRole, ['admin', 'super_admin']);
        $isSignataire = $isAdmin || $userRole === 'signataire';
        $isScanneur = $isAdmin || $userRole === 'scanneur';
        $isDg = $isAdmin || $userRole === 'dg';

        $wfCounts = [];
        if ($isSignataire || $isScanneur || $isDg) {
            $wfCounts = \App\Models\PublicRequest::whereIn('workflow_status', ['document_attente', 'signee', 'scannee'])
                ->selectRaw('workflow_status, COUNT(*) as total')
                ->groupBy('workflow_status')
                ->pluck('total', 'workflow_status')
                ->toArray();
        }
        $wfCount = ($isSignataire ? ($wfCounts['document_attente'] ?? 0) : 0)
            + ($isScanneur ? ($wfCounts['signee'] ?? 0) : 0)
            + ($isDg ? ($wfCounts['scannee'] ?? 0) : 0);
        $dgPendingCount = $wfCounts['scannee'] ?? 0;

        $admMenu = [
            ['title' => 'Principal', 'items' => [
                ['label' => 'Tableau de bord', 'icon' => 'fa-gauge', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ]],
            ['title' => 'Demandes', 'items' => [
                ['label' => 'Demandes', 'icon' => 'fa-clipboard-list', 'url' => route('admin.demandes.index'), 'active' => request()->routeIs('admin.demandes.*') && !request()->routeIs('admin.demandes.duplicates.*') && !request()->routeIs('admin.demandes.dg-*') && !request()->routeIs('admin.demandes.workflow-history')],
                ['label' => 'Doublons', 'icon' => 'fa-clone', 'url' => route('admin.demandes.duplicates.index'), 'active' => request()->routeIs('admin.demandes.duplicates.*'), 'show' => $isAdmin],
                ['label' => 'Workflow', 'icon' => 'fa-list-check', 'url' => route('admin.demandes.dg-dashboard'), 'active' => request()->routeIs('admin.demandes.dg-dashboard'), 'show' => $isSignataire || $isScanneur || $isDg, 'badge' => $wfCount > 0 ? $wfCount : null, 'badgeClass' => 'is-alert'],
                ['label' => 'Validation DG', 'icon' => 'fa-stamp', 'url' => route('admin.demandes.dg-pending'), 'active' => request()->routeIs('admin.demandes.dg-pending'), 'show' => $isDg, 'badge' => $dgPendingCount > 0 ? $dgPendingCount : null, 'badgeClass' => 'is-alert'],
                ['label' => 'Historique', 'icon' => 'fa-clock-rotate-left', 'url' => route('admin.demandes.workflow-history'), 'active' => request()->routeIs('admin.demandes.workflow-history'), 'show' => $isAdmin || $isDg],
                ['label' => 'Carte des demandes', 'icon' => 'fa-map-location-dot', 'url' => route('admin.carte-demandes.index'), 'active' => request()->routeIs('admin.carte-demandes.*'), 'show' => $isAdmin],
            ]],
            ['title' => 'Opérations', 'items' => [
                ['label' => 'Distribution', 'icon' => 'fa-hands-holding-circle', 'url' => url('/admin/distribution'), 'active' => request()->is('admin/distribution*')],
                ['label' => 'Entrepôts', 'icon' => 'fa-warehouse', 'url' => route('admin.entrepots.index'), 'active' => request()->routeIs('admin.entrepots.*')],
                ['label' => 'Gestion des stocks', 'icon' => 'fa-boxes-stacked', 'url' => route('admin.stock.index'), 'active' => request()->routeIs('admin.stock.*')],
                ['label' => 'Produits', 'icon' => 'fa-seedling', 'url' => route('admin.products.index'), 'active' => request()->routeIs('admin.products.*')],
            ]],
            ['title' => 'SIM — Marchés', 'items' => [
                ['label' => 'Marchés & assignations', 'icon' => 'fa-store', 'url' => route('admin.sim.dashboard'), 'active' => request()->routeIs('admin.sim.dashboard')],
                ['label' => 'Collecteurs SIM', 'icon' => 'fa-mobile-screen', 'url' => route('admin.sim.collectors'), 'active' => request()->routeIs('admin.sim.collectors*')],
                ['label' => 'Suivi terrain', 'icon' => 'fa-tower-broadcast', 'url' => route('admin.sim.suivi'), 'active' => request()->routeIs('admin.sim.suivi') || request()->routeIs('admin.sim.live') || request()->routeIs('admin.sim.collectes'), 'badge' => 'Live', 'badgeClass' => 'is-live'],
            ]],
            ['title' => 'Ressources humaines', 'items' => [
                ['label' => 'Personnel', 'icon' => 'fa-user-tie', 'url' => route('admin.personnel.index'), 'active' => request()->routeIs('admin.personnel.*')],
                ['label' => 'Avances Tabaski', 'icon' => 'fa-hand-holding-dollar', 'url' => route('admin.drh.tabaski.index'), 'active' => request()->routeIs('admin.drh.*'), 'badge' => '2026'],
            ]],
            ['title' => 'Communication', 'items' => [
                ['label' => 'Communication & publications', 'icon' => 'fa-bullhorn', 'url' => route('admin.communications.index'), 'active' => request()->routeIs('admin.communications.*') || request()->routeIs('admin.communication.*') || request()->routeIs('admin.actualites.*') || request()->routeIs('admin.newsletter.*') || request()->routeIs('admin.sim-reports.*') || request()->routeIs('admin.galerie.*') || request()->routeIs('admin.messages.*')],
                ['label' => 'QR Media Share', 'icon' => 'fa-qrcode', 'url' => route('admin.media-share.index'), 'active' => request()->routeIs('admin.media-share.*')],
                ['label' => 'Gestion des dons', 'icon' => 'fa-hand-holding-heart', 'url' => route('admin.donations.index'), 'active' => request()->routeIs('admin.donations.*')],
            ]],
            ['title' => 'Pilotage', 'items' => [
                ['label' => 'Statistiques', 'icon' => 'fa-chart-column', 'url' => route('admin.statistics'), 'active' => request()->routeIs('admin.statistics*')],
                ['label' => 'Chiffres clés', 'icon' => 'fa-calculator', 'url' => route('admin.chiffres-cles.index'), 'active' => request()->routeIs('admin.chiffres-cles*')],
            ]],
            ['title' => 'Archives', 'items' => [
                ['label' => 'Gestion des archives', 'icon' => 'fa-box-archive', 'url' => route('admin.archives.index'), 'active' => request()->routeIs('admin.archives.*')],
                ['label' => 'Archives DFC', 'icon' => 'fa-folder', 'url' => route('archives.dfc.index'), 'active' => request()->routeIs('archives.dfc.*')],
                ['label' => 'Archives CPM', 'icon' => 'fa-folder', 'url' => route('archives.cpm.index'), 'active' => request()->routeIs('archives.cpm.*')],
                ['label' => 'Archives DTL', 'icon' => 'fa-folder', 'url' => route('archives.dtl.index'), 'active' => request()->routeIs('archives.dtl.*')],
                ['label' => 'Archives DPSE', 'icon' => 'fa-folder', 'url' => route('archives.dpse.index'), 'active' => request()->routeIs('archives.dpse.*')],
                ['label' => 'Archives DRH', 'icon' => 'fa-folder', 'url' => route('archives.drh.index'), 'active' => request()->routeIs('archives.drh.*')],
                ['label' => 'Archives Inspecteur', 'icon' => 'fa-folder', 'url' => route('archives.inspecteur.index'), 'active' => request()->routeIs('archives.inspecteur.*')],
                ['label' => 'Archives Comptable', 'icon' => 'fa-folder', 'url' => route('archives.comptable.index'), 'active' => request()->routeIs('archives.comptable.*')],
            ]],
            ['title' => 'Administration', 'items' => [
                ['label' => 'Utilisateurs', 'icon' => 'fa-users', 'url' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')],
                ['label' => 'Audit & sécurité', 'icon' => 'fa-shield-halved', 'url' => route('admin.audit.index'), 'active' => request()->routeIs('admin.audit.*')],
                ['label' => 'Mon profil', 'icon' => 'fa-user-gear', 'url' => route('admin.profile'), 'active' => request()->routeIs('admin.profile')],
            ]],
        ];
    }
@endphp
<body>
    <script>
        try {
            if (window.matchMedia('(min-width: 992px)').matches && localStorage.getItem('csar.admin.sidebarCollapsed') === '1') {
                document.body.classList.add('adm-collapsed');
            }
        } catch (e) {}
    </script>

    <aside class="adm-sidebar" id="admSidebar" aria-label="Navigation principale">
        <a href="{{ route($admHomeRoute) }}" class="adm-brand">
            <img src="{{ asset('images/csar-logo.png') }}" alt="Logo CSAR" class="adm-brand-logo"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <span class="adm-brand-fallback"><i class="fas fa-shield-halved"></i></span>
            <span class="adm-brand-text">
                <span class="adm-brand-name">{{ $admIsCtc ? 'CTC CSAR' : 'CSAR' }}</span>
                <span class="adm-brand-sub">{{ $admIsCtc ? 'Espace communication' : 'Plateforme d’administration' }}</span>
            </span>
        </a>

        <nav class="adm-nav" id="admNav">
            @foreach($admMenu as $section)
                @php $visibleItems = array_filter($section['items'], fn ($i) => $i['show'] ?? true); @endphp
                @if(count($visibleItems))
                <div class="adm-nav-section">
                    <div class="adm-nav-section-title">{{ $section['title'] }}</div>
                    @foreach($visibleItems as $item)
                        <a href="{{ $item['url'] }}"
                           class="adm-nav-link {{ $item['active'] ? 'active' : '' }}"
                           title="{{ $item['label'] }}"
                           @if($item['active']) aria-current="page" @endif>
                            <i class="fas {{ $item['icon'] }} adm-nav-icon" aria-hidden="true"></i>
                            <span class="adm-nav-label">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <span class="adm-nav-badge {{ $item['badgeClass'] ?? '' }}">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
                @endif
            @endforeach
        </nav>

        <div class="adm-sidebar-footer">
            <a href="{{ url('/') }}" class="adm-nav-link" target="_blank" rel="noopener" title="Voir le site public">
                <i class="fas fa-arrow-up-right-from-square adm-nav-icon" aria-hidden="true"></i>
                <span class="adm-nav-label">Voir le site public</span>
            </a>
        </div>
    </aside>

    <div class="adm-backdrop" id="admBackdrop"></div>

    <div class="adm-main" id="mainContent">
        <header class="adm-topbar">
            <button class="adm-icon-btn" id="sidebarToggle" type="button" aria-controls="admSidebar" aria-expanded="true" aria-label="Afficher ou masquer le menu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="adm-topbar-title">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @elseif(!$admOnHome)
                    <nav aria-label="Fil d'Ariane">
                        <ol class="breadcrumb adm-breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route($admHomeRoute) }}">Accueil</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ strip_tags(html_entity_decode($admPageTitle)) }}</li>
                        </ol>
                    </nav>
                @endif
                <h1>{!! $admPageTitle !!}</h1>
            </div>

            <div class="adm-topbar-actions">
                <span class="adm-date d-none d-xl-inline">
                    <i class="far fa-calendar me-1"></i>{{ ucfirst(now()->locale('fr')->translatedFormat('l j F Y')) }}
                </span>

                <div class="dropdown">
                    @include('components.notification-bell')
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Notifications</span>
                            <button class="btn btn-sm btn-light" type="button" onclick="markAllNotificationsAsRead()" id="markAllReadBtn">
                                <i class="fas fa-check-double me-1"></i>Tout marquer lu
                            </button>
                        </div>
                        <div id="notifications-container">
                            <div class="text-center py-3" id="notifications-loading">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                        @unless($admIsCtc)
                        <div class="border-top text-center p-2">
                            <a href="{{ route('admin.notifications.index') }}" class="small text-decoration-none">Voir toutes les notifications</a>
                        </div>
                        @endunless
                    </div>
                </div>

                <div class="dropdown">
                    <button class="adm-user-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="adm-avatar">{{ $admInitials }}</span>
                        <span class="adm-user-meta d-none d-md-flex">
                            <span class="adm-user-name">{{ $admUserName }}</span>
                            <span class="adm-user-role">{{ $admRoleLabel }}</span>
                        </span>
                        <i class="fas fa-chevron-down small text-muted d-none d-md-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ $admRoleLabel }}</h6></li>
                        @unless($admIsCtc)
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.2fa.setup') }}"><i class="fas fa-key me-2"></i>Double authentification</a></li>
                        @endunless
                        <li><a class="dropdown-item" href="{{ url('/') }}" target="_blank" rel="noopener"><i class="fas fa-globe me-2"></i>Site public</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route($admIsCtc ? 'ctc.logout' : 'admin.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-right-from-bracket me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="adm-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show adm-flash" role="alert">
                    <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show adm-flash" role="alert">
                    <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show adm-flash" role="alert">
                    <i class="fas fa-triangle-exclamation me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="adm-footer d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience</span>
            <span>Plateforme d’administration</span>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function () {
            var body = document.body;
            var toggle = document.getElementById('sidebarToggle');
            var backdrop = document.getElementById('admBackdrop');
            var nav = document.getElementById('admNav');
            var mobileQuery = window.matchMedia('(max-width: 991.98px)');
            var STORAGE_KEY = 'csar.admin.sidebarCollapsed';

            function syncAria() {
                var expanded = mobileQuery.matches
                    ? body.classList.contains('adm-sidebar-open')
                    : !body.classList.contains('adm-collapsed');
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }

            function closeMobile() {
                body.classList.remove('adm-sidebar-open');
                syncAria();
            }

            toggle.addEventListener('click', function () {
                if (mobileQuery.matches) {
                    body.classList.toggle('adm-sidebar-open');
                } else {
                    var collapsed = body.classList.toggle('adm-collapsed');
                    try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch (e) {}
                }
                syncAria();
            });

            backdrop.addEventListener('click', closeMobile);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && body.classList.contains('adm-sidebar-open')) {
                    closeMobile();
                }
            });

            nav.addEventListener('click', function (e) {
                if (mobileQuery.matches && e.target.closest('.adm-nav-link')) {
                    closeMobile();
                }
            });

            var onBreakpointChange = function () {
                body.classList.remove('adm-sidebar-open');
                if (mobileQuery.matches) {
                    body.classList.remove('adm-collapsed');
                } else {
                    try {
                        body.classList.toggle('adm-collapsed', localStorage.getItem(STORAGE_KEY) === '1');
                    } catch (e) {}
                }
                syncAria();
            };
            if (mobileQuery.addEventListener) {
                mobileQuery.addEventListener('change', onBreakpointChange);
            } else if (mobileQuery.addListener) {
                mobileQuery.addListener(onBreakpointChange);
            }

            var activeLink = nav.querySelector('.adm-nav-link.active');
            if (activeLink && activeLink.offsetTop > nav.clientHeight - 80) {
                nav.scrollTop = activeLink.offsetTop - nav.clientHeight / 2;
            }

            setTimeout(function () {
                document.querySelectorAll('.adm-flash').forEach(function (el) {
                    if (window.bootstrap && bootstrap.Alert) {
                        bootstrap.Alert.getOrCreateInstance(el).close();
                    }
                });
            }, 6000);

            syncAria();
        })();
    </script>

    <script src="{{ asset('js/notifications.js') }}"></script>

    @stack('scripts')
</body>
</html>
