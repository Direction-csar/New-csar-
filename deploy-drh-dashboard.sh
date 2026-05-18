#!/bin/bash
# Déploiement Dashboard DRH complet (Tableau de Bord + Personnel + Tabaski + Enquête)
set -e
APP=/var/www/csar
cd "$APP"

echo "=== 1. Controller Dashboard DRH ==="
sudo mkdir -p app/Http/Controllers/Drh
sudo tee app/Http/Controllers/Drh/DashboardController.php > /dev/null << 'PHPEOF'
<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $total      = Personnel::count();
        $hommes     = Personnel::where('sexe', 'Masculin')->count();
        $femmes     = Personnel::where('sexe', 'Féminin')->count();
        $demissions = Personnel::whereIn('statut', ['Demission', 'Démission', 'Démissionnaire'])->count();

        $ageMoyen = (int) round(
            Personnel::whereNotNull('date_naissance')->get()
                ->avg(fn($p) => Carbon::parse($p->date_naissance)->age) ?? 0
        );

        $stats = [
            'effectif' => $total, 'demissions' => $demissions,
            'hommes' => $hommes, 'femmes' => $femmes,
            'pct_hommes' => $total > 0 ? round($hommes / $total * 100) : 0,
            'pct_femmes' => $total > 0 ? round($femmes / $total * 100) : 0,
            'age_moyen' => $ageMoyen,
        ];

        $tranchesAge = Personnel::selectRaw('tranche_age, COUNT(*) as c')
            ->whereNotNull('tranche_age')->groupBy('tranche_age')
            ->pluck('c', 'tranche_age')->toArray();

        $anciennete = ['0-2 ans' => 0, '3-5 ans' => 0, '6-10 ans' => 0, '11-15 ans' => 0, '15+ ans' => 0];
        Personnel::whereNotNull('date_recrutement_csar')->chunk(200, function ($rows) use (&$anciennete) {
            foreach ($rows as $p) {
                $years = Carbon::parse($p->date_recrutement_csar)->diffInYears(now());
                if ($years <= 2) $anciennete['0-2 ans']++;
                elseif ($years <= 5) $anciennete['3-5 ans']++;
                elseif ($years <= 10) $anciennete['6-10 ans']++;
                elseif ($years <= 15) $anciennete['11-15 ans']++;
                else $anciennete['15+ ans']++;
            }
        });

        $parDirection = Personnel::selectRaw('direction_service, COUNT(*) as c')
            ->whereNotNull('direction_service')->groupBy('direction_service')
            ->orderByDesc('c')->limit(10)->pluck('c', 'direction_service')->toArray();

        $parPoste = Personnel::selectRaw('poste_actuel, COUNT(*) as c')
            ->whereNotNull('poste_actuel')->groupBy('poste_actuel')
            ->orderByDesc('c')->limit(10)->pluck('c', 'poste_actuel')->toArray();

        $parRegion = Personnel::selectRaw('localisation_region, COUNT(*) as c')
            ->whereNotNull('localisation_region')->groupBy('localisation_region')
            ->orderByDesc('c')->pluck('c', 'localisation_region')->toArray();

        $parStatut = Personnel::selectRaw('statut, COUNT(*) as c')
            ->whereNotNull('statut')->groupBy('statut')->pluck('c', 'statut')->toArray();

        $currentYear = (int) date('Y');
        $evolutionRecrutements = [];
        for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
            $evolutionRecrutements[$y] = Personnel::whereYear('date_recrutement_csar', $y)->count();
        }

        $plusAnciens = Personnel::whereNotNull('date_recrutement_csar')
            ->orderBy('date_recrutement_csar', 'asc')->limit(5)->get();

        $plusRecents = Personnel::whereNotNull('date_recrutement_csar')
            ->orderBy('date_recrutement_csar', 'desc')->limit(5)->get();

        return view('admin.drh.dashboard.index', compact(
            'stats', 'tranchesAge', 'anciennete', 'parDirection',
            'parPoste', 'parRegion', 'parStatut',
            'evolutionRecrutements', 'plusAnciens', 'plusRecents'
        ));
    }
}
PHPEOF
echo "✅ DashboardController créé"

echo "=== 2. Vue Dashboard DRH ==="
sudo mkdir -p resources/views/admin/drh/dashboard
sudo tee resources/views/admin/drh/dashboard/index.blade.php > /dev/null << 'BLADEEOF'
@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')

@section('title', 'Tableau de Bord Ressources Humaines')
@section('page-title', 'Tableau de Bord Ressources Humaines')

@section('styles')
<style>
    .hr-dashboard { font-family: 'Segoe UI', sans-serif; }
    .hr-title-bar { background: white; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; }
    .hr-title-bar h1 { font-size: 1.8rem; font-weight: 800; color: #1e293b; margin: 0; }
    .kpi-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; border: 1px solid #e5e7eb; transition: transform 0.2s; }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
    .kpi-icon { width: 56px; height: 56px; margin: 0 auto 12px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: white; }
    .kpi-icon.effectif    { background: linear-gradient(135deg, #6b7280, #4b5563); }
    .kpi-icon.demissions  { background: linear-gradient(135deg, #f97316, #ea580c); }
    .kpi-icon.femmes      { background: linear-gradient(135deg, #ec4899, #db2777); }
    .kpi-icon.hommes      { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .kpi-icon.age         { background: linear-gradient(135deg, #10b981, #047857); }
    .kpi-label { font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .kpi-value { font-size: 2rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .kpi-suffix { font-size: 1rem; color: #64748b; font-weight: 600; }
    .chart-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; height: 100%; }
    .chart-card-title { font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f1f5f9; }
    .list-mini { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .list-mini-title { font-size: 0.85rem; font-weight: 700; color: #047857; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px; }
    .list-mini-item { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.82rem; border-bottom: 1px dashed #f1f5f9; }
    .list-mini-item:last-child { border-bottom: none; }
    .list-mini-item .name { color: #1e293b; font-weight: 500; }
    .list-mini-item .value { color: #6b7280; font-size: 0.78rem; }
    .badge-anciennete { background: #d1fae5; color: #047857; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
</style>
@endsection

@section('content')
<div class="hr-dashboard">
    <div class="hr-title-bar">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-home text-success" style="font-size: 1.8rem;"></i>
            <h1>Tableau de Bord Ressources Humaines</h1>
        </div>
        <div class="text-muted small"><i class="fas fa-calendar-alt me-1"></i> {{ now()->format('d/m/Y') }}</div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md col-6"><div class="kpi-card"><div class="kpi-icon effectif"><i class="fas fa-users"></i></div><div class="kpi-label">Effectif</div><div class="kpi-value">{{ $stats['effectif'] }}</div></div></div>
        <div class="col-md col-6"><div class="kpi-card"><div class="kpi-icon demissions"><i class="fas fa-door-open"></i></div><div class="kpi-label">Démissions</div><div class="kpi-value">{{ $stats['demissions'] }}</div></div></div>
        <div class="col-md col-6"><div class="kpi-card"><div class="kpi-icon femmes"><i class="fas fa-female"></i></div><div class="kpi-label">Femmes</div><div class="kpi-value">{{ $stats['pct_femmes'] }}<span class="kpi-suffix">%</span></div><div class="text-muted small mt-1">{{ $stats['femmes'] }} agents</div></div></div>
        <div class="col-md col-6"><div class="kpi-card"><div class="kpi-icon hommes"><i class="fas fa-male"></i></div><div class="kpi-label">Hommes</div><div class="kpi-value">{{ $stats['pct_hommes'] }}<span class="kpi-suffix">%</span></div><div class="text-muted small mt-1">{{ $stats['hommes'] }} agents</div></div></div>
        <div class="col-md col-12"><div class="kpi-card"><div class="kpi-icon age"><i class="fas fa-user-clock"></i></div><div class="kpi-label">Âge Moyen</div><div class="kpi-value">{{ $stats['age_moyen'] }}<span class="kpi-suffix">ans</span></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4"><div class="chart-card"><div class="chart-card-title"><i class="fas fa-chart-line text-success me-1"></i> Effectif — Évolution recrutements</div><canvas id="chartEvolution" height="220"></canvas></div></div>
        <div class="col-lg-4"><div class="chart-card"><div class="chart-card-title"><i class="fas fa-birthday-cake text-warning me-1"></i> Tranche d'âges</div><canvas id="chartTranchesAge" height="220"></canvas></div></div>
        <div class="col-lg-4"><div class="chart-card"><div class="chart-card-title"><i class="fas fa-medal text-primary me-1"></i> Ancienneté</div><canvas id="chartAnciennete" height="220"></canvas></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3"><div class="list-mini"><div class="list-mini-title"><i class="fas fa-award text-success"></i> TOP 5 PLUS ANCIENS</div>
            @forelse($plusAnciens as $p)<div class="list-mini-item"><span class="name">{{ \Illuminate\Support\Str::limit($p->prenoms_nom, 22) }}</span><span class="value badge-anciennete">{{ $p->date_recrutement_csar?->format('Y') }}</span></div>@empty<div class="text-muted small">Aucune donnée</div>@endforelse
        </div></div>
        <div class="col-lg-3"><div class="list-mini"><div class="list-mini-title"><i class="fas fa-bolt text-warning"></i> TOP 5 PLUS RÉCENTS</div>
            @forelse($plusRecents as $p)<div class="list-mini-item"><span class="name">{{ \Illuminate\Support\Str::limit($p->prenoms_nom, 22) }}</span><span class="value">{{ $p->date_recrutement_csar?->format('d/m/Y') }}</span></div>@empty<div class="text-muted small">Aucune donnée</div>@endforelse
        </div></div>
        <div class="col-lg-3"><div class="list-mini"><div class="list-mini-title"><i class="fas fa-briefcase text-primary"></i> POSTES (TOP)</div>
            @forelse($parPoste as $poste => $count)<div class="list-mini-item"><span class="name">{{ \Illuminate\Support\Str::limit($poste, 22) }}</span><span class="value"><strong>{{ $count }}</strong></span></div>@empty<div class="text-muted small">Aucune donnée</div>@endforelse
        </div></div>
        <div class="col-lg-3"><div class="list-mini"><div class="list-mini-title"><i class="fas fa-id-badge text-info"></i> STATUTS</div>
            @forelse($parStatut as $statut => $count)<div class="list-mini-item"><span class="name">{{ $statut }}</span><span class="value"><strong>{{ $count }}</strong></span></div>@empty<div class="text-muted small">Aucune donnée</div>@endforelse
        </div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7"><div class="chart-card"><div class="chart-card-title"><i class="fas fa-sitemap text-success me-1"></i> Effectif par Direction</div><canvas id="chartDirections" height="120"></canvas></div></div>
        <div class="col-lg-5"><div class="chart-card"><div class="chart-card-title"><i class="fas fa-map-marked-alt text-danger me-1"></i> Effectif par Région</div><canvas id="chartRegions" height="120"></canvas></div></div>
    </div>

    <div class="text-center mb-4">
        <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-success btn-lg"><i class="fas fa-users me-2"></i> Gérer le personnel ({{ $stats['effectif'] }} agents)</a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'Segoe UI', sans-serif";
    Chart.defaults.font.size = 11;

    new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: { labels: {!! json_encode(array_keys($evolutionRecrutements)) !!}, datasets: [{ label: 'Recrutements', data: {!! json_encode(array_values($evolutionRecrutements)) !!}, borderColor: '#f97316', backgroundColor: 'rgba(249, 115, 22, 0.1)', tension: 0.4, fill: true, pointRadius: 5, pointBackgroundColor: '#f97316' }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    new Chart(document.getElementById('chartTranchesAge'), {
        type: 'bar',
        data: { labels: {!! json_encode(array_keys($tranchesAge)) !!}, datasets: [{ label: 'Effectif', data: {!! json_encode(array_values($tranchesAge)) !!}, backgroundColor: ['#f97316','#fb923c','#fdba74','#fed7aa','#ffedd5'], borderRadius: 6 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    new Chart(document.getElementById('chartAnciennete'), {
        type: 'bar',
        data: { labels: {!! json_encode(array_keys($anciennete)) !!}, datasets: [{ label: 'Agents', data: {!! json_encode(array_values($anciennete)) !!}, backgroundColor: ['#3b82f6','#60a5fa','#93c5fd','#bfdbfe','#dbeafe'], borderRadius: 6 }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    new Chart(document.getElementById('chartDirections'), {
        type: 'bar',
        data: { labels: {!! json_encode(array_keys($parDirection)) !!}, datasets: [{ label: 'Agents', data: {!! json_encode(array_values($parDirection)) !!}, backgroundColor: '#10b981', borderRadius: 6 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    new Chart(document.getElementById('chartRegions'), {
        type: 'doughnut',
        data: { labels: {!! json_encode(array_keys($parRegion)) !!}, datasets: [{ data: {!! json_encode(array_values($parRegion)) !!}, backgroundColor: ['#10b981','#3b82f6','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#ef4444','#84cc16','#f97316','#14b8a6','#a855f7','#22c55e'] }] },
        options: { plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
    });
</script>
@endsection
BLADEEOF
echo "✅ Vue dashboard créée"

echo "=== 3. Mise à jour du layout DRH (menu 4 onglets) ==="
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
        .drh-topbar { background: linear-gradient(135deg, #065f46 0%, #047857 100%); color: white; padding: 0 24px; height: 64px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.15); position: sticky; top: 0; z-index: 100; }
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
    <a href="{{ route('admin.drh.dashboard') }}" class="{{ str_contains($currentRoute, 'dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-line me-1"></i> Tableau de Bord RH
    </a>
    <a href="{{ route('admin.drh.personnel.index') }}" class="{{ str_contains($currentRoute, 'personnel') ? 'active' : '' }}">
        <i class="fas fa-users me-1"></i> Personnel
    </a>
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
echo "✅ Layout DRH mis à jour"

echo "=== 4. Routes web.php ==="
# Vérifier si dashboard DRH déjà dans routes
if ! grep -q "Drh\\\\DashboardController" routes/web.php; then
    sudo python3 << 'PY'
path = 'routes/web.php'
content = open(path).read()

old = """// Routes DRH — Avances Tabaski (accès drh + admin)
Route::prefix('admin/drh')->name('admin.drh.')->middleware(['drh-access'])->group(function () {
    Route::get('/avances-tabaski', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'index'])->name('tabaski.index');
    Route::post('/avances-tabaski/settings', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'updateSettings'])->name('tabaski.settings');
    Route::get('/avances-tabaski/export-csv', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'exportCsv'])->name('tabaski.export-csv');
    Route::get('/avances-tabaski/print', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'exportPdf'])->name('tabaski.print');

    // Enquête Assurance Maladie
    Route::get('/enquete-assurance',           [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'index'])->name('health-survey.index');
    Route::get('/enquete-assurance/export',    [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'exportCsv'])->name('health-survey.export');
    Route::get('/enquete-assurance/{id}',      [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'show'])->name('health-survey.show');
});"""

new = """// Routes DRH — Espace Direction RH (accès drh + admin)
Route::prefix('admin/drh')->name('admin.drh.')->middleware(['drh-access'])->group(function () {

    // 📊 Tableau de Bord RH
    Route::get('/',          [\\App\\Http\\Controllers\\Drh\\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [\\App\\Http\\Controllers\\Drh\\DashboardController::class, 'index'])->name('dashboard.alt');

    // 👥 Gestion du Personnel (CRUD complet)
    Route::resource('personnel', \\App\\Http\\Controllers\\Admin\\PersonnelController::class);
    Route::post('/personnel/{id}/toggle-status', [\\App\\Http\\Controllers\\Admin\\PersonnelController::class, 'toggleStatus'])->name('personnel.toggle-status');
    Route::post('/personnel/{id}/reset-password', [\\App\\Http\\Controllers\\Admin\\PersonnelController::class, 'resetPassword'])->name('personnel.reset-password');
    Route::get('/personnel-export', [\\App\\Http\\Controllers\\Admin\\PersonnelController::class, 'export'])->name('personnel.export');

    // 🕌 Avances Tabaski
    Route::get('/avances-tabaski', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'index'])->name('tabaski.index');
    Route::post('/avances-tabaski/settings', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'updateSettings'])->name('tabaski.settings');
    Route::get('/avances-tabaski/export-csv', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'exportCsv'])->name('tabaski.export-csv');
    Route::get('/avances-tabaski/print', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'exportPdf'])->name('tabaski.print');

    // 📋 Enquête Assurance Maladie
    Route::get('/enquete-assurance',           [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'index'])->name('health-survey.index');
    Route::get('/enquete-assurance/export',    [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'exportCsv'])->name('health-survey.export');
    Route::get('/enquete-assurance/{id}',      [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'show'])->name('health-survey.show');
});"""

if old in content:
    open(path, 'w').write(content.replace(old, new))
    print("OK routes mises à jour")
else:
    print("⚠️  Pattern non trouvé — vérifier manuellement")
PY
else
    echo "↩ Routes Dashboard déjà présentes"
fi

echo "=== 5. Mise à jour redirection login DRH ==="
sudo sed -i "s|redirect()->route('admin.drh.tabaski.index')|redirect()->route('admin.drh.dashboard')|g" app/Http/Controllers/Auth/DRHLoginController.php
echo "✅ Login DRH redirige vers dashboard"

echo "=== 6. Permissions + cache ==="
sudo chown -R www-data:www-data app/Http/Controllers/Drh resources/views/admin/drh resources/views/layouts/drh-portal.blade.php app/Http/Controllers/Auth/DRHLoginController.php
sudo php artisan view:clear
sudo php artisan route:clear
sudo php artisan config:clear

echo ""
echo "==========================================="
echo "✅ DASHBOARD DRH DÉPLOYÉ !"
echo ""
echo "👉 Connecte-toi : https://csar.sn/drh/login"
echo ""
echo "Le DRH verra maintenant un menu avec 4 onglets :"
echo "  📊 Tableau de Bord RH (nouveau, style Excel)"
echo "  👥 Personnel (CRUD complet)"
echo "  💰 Avances Tabaski"
echo "  📋 Enquête Assurance Maladie"
echo "==========================================="
