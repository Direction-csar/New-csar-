@extends('layouts.stock-portal')

@section('title', 'Vue nationale des stocks — DG')
@section('page-title', 'Vue nationale des stocks alimentaires')
@section('breadcrumb', 'Direction Générale › Pilotage national des stocks')

@section('sidebar-extra')
    <div class="sb-section">Pilotage national</div>
    <a class="sb-link" href="{{ route('dg.stock-operations.index') }}">
        <i class="fas fa-dolly"></i><span class="lbl">Opérations de stock</span>
    </a>
    <a class="sb-link" href="{{ route('dg.stock-operations.create') }}">
        <i class="fas fa-plus-circle"></i><span class="lbl">Nouvelle instruction</span>
    </a>
    <a class="sb-link" href="{{ route('dg.situation-stocks.index') }}">
        <i class="fas fa-chart-bar"></i><span class="lbl">Situation des stocks</span>
    </a>
    <a class="sb-link" href="{{ route('dg.inspector-assignments.index') }}">
        <i class="fas fa-user-check"></i><span class="lbl">Affectations inspecteurs</span>
    </a>
    <a class="sb-link" href="{{ route('dg.stock-staff.index') }}">
        <i class="fas fa-users"></i><span class="lbl">Personnel stock</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-reports') ? 'active' : '' }}" href="{{ route('dg.stock-reports.index') }}">
        <i class="fas fa-chart-bar"></i><span class="lbl">Rapports & statistiques</span>
    </a>
    <div class="sb-section">Espace DG</div>
    <a class="sb-link" href="{{ route('dg.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i><span class="lbl">Tableau de bord DG</span>
    </a>
    <a class="sb-link" href="{{ route('dg.demandes.index') }}">
        <i class="fas fa-clipboard-list"></i><span class="lbl">Demandes</span>
    </a>
    <a class="sb-link" href="{{ route('dg.map') }}">
        <i class="fas fa-map-marked-alt"></i><span class="lbl">Carte interactive</span>
    </a>
@endsection

@section('content')

<div class="card" style="background:linear-gradient(135deg,#0B1F3A,#16345E);border:none">
    <div class="card-b" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="color:#fff;font-weight:700;margin:0 0 4px">Vision exécutive nationale</h3>
            <div style="color:rgba(255,255,255,.75);font-size:13.5px">{{ $stats['magasins_actifs'] }} magasins actifs — {{ $stats['regions'] }} régions — {{ number_format($stats['stock_national'], 0, ',', ' ') }} sacs en stock officiel — {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('dg.stock-operations.create') }}" class="btn btn-orange"><i class="fas fa-plus"></i> Créer une instruction</a>
            <a href="{{ route('dg.stock-operations.index') }}" class="btn btn-ghost" style="border-color:rgba(255,255,255,.3);color:#fff"><i class="fas fa-list"></i> Opérations</a>
            <a href="{{ route('dg.map') }}" class="btn btn-ghost" style="border-color:rgba(255,255,255,.3);color:#fff"><i class="fas fa-map-marked-alt"></i> Carte</a>
        </div>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div class="ic ic-blue"><i class="fas fa-warehouse"></i></div><div><div class="v">{{ number_format($stats['magasins_actifs'], 0, ',', ' ') }}</div><div class="l">Magasins actifs</div><div class="d">14 régions</div></div></div>
    <div class="kpi"><div class="ic ic-green"><i class="fas fa-boxes"></i></div><div><div class="v">{{ number_format($stats['stock_national'], 0, ',', ' ') }}</div><div class="l">Stock alimentaire national</div><div class="d" style="color:var(--green)">Sacs officiels</div></div></div>
    <div class="kpi"><div class="ic ic-green"><i class="fas fa-sign-in-alt"></i></div><div><div class="v">{{ number_format($stats['entrees_mois'], 0, ',', ' ') }}</div><div class="l">Entrées ce mois</div><div class="d" style="color:var(--green)">Sacs</div></div></div>
    <div class="kpi"><div class="ic ic-red"><i class="fas fa-sign-out-alt"></i></div><div><div class="v">{{ number_format($stats['sorties_mois'], 0, ',', ' ') }}</div><div class="l">Sorties ce mois</div><div class="d" style="color:var(--red)">Sacs</div></div></div>
    <div class="kpi"><div class="ic ic-orange"><i class="fas fa-exchange-alt"></i></div><div><div class="v">{{ number_format($stats['transferts_mois'], 0, ',', ' ') }}</div><div class="l">Transferts ce mois</div><div class="d">Mouvements</div></div></div>
    <div class="kpi"><div class="ic ic-night"><i class="fas fa-tasks"></i></div><div><div class="v">{{ number_format($stats['ops_cours'], 0, ',', ' ') }}</div><div class="l">Opérations en cours</div><div class="d">Circuit DG→Magasinier</div></div></div>
    <div class="kpi"><div class="ic ic-amber"><i class="fas fa-clock"></i></div><div><div class="v">{{ number_format($stats['mouvements_attente'], 0, ',', ' ') }}</div><div class="l">Mouvements en attente</div><div class="d" style="color:#B45309">À vérifier</div></div></div>
    <div class="kpi"><div class="ic ic-red"><i class="fas fa-exclamation-triangle"></i></div><div><div class="v">{{ number_format($stats['anomalies'], 0, ',', ' ') }}</div><div class="l">Anomalies signalées</div><div class="d" style="color:var(--red)">Alertes actives</div></div></div>
    <div class="kpi"><div class="ic ic-orange"><i class="fas fa-thermometer-half"></i></div><div><div class="v">{{ number_format($stats['stocks_critique'], 0, ',', ' ') }}</div><div class="l">Stocks critiques</div><div class="d" style="color:var(--orange)">≤ 100 sacs</div></div></div>
    <div class="kpi"><div class="ic ic-blue"><i class="fas fa-user-shield"></i></div><div><div class="v">{{ number_format($inspecteurs, 0, ',', ' ') }} / {{ number_format($magasiniers, 0, ',', ' ') }}</div><div class="l">Inspecteurs / Magasiniers</div><div class="d">Personnel actif</div></div></div>
</div>

<div class="card">
    <div class="card-h"><h3><i class="fas fa-chart-line"></i> Activité des 30 derniers jours — entrées vs sorties (sacs)</h3></div>
    <div class="card-b"><div class="chart-box"><canvas id="activiteChart"></canvas></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-map-marked-alt"></i> Stock par région</h3></div>
        <div style="overflow-x:auto">
            <table class="tbl">
                <thead><tr><th>Région</th><th>Magasins</th><th>Stock (sacs)</th><th>Part</th></tr></thead>
                <tbody>
                @php $maxRegion = $parRegion->max('stock') ?: 1; @endphp
                @forelse($parRegion as $r)
                    <tr>
                        <td><b>{{ $r->region }}</b></td>
                        <td>{{ $r->magasins }}</td>
                        <td>{{ number_format($r->stock, 0, ',', ' ') }}</td>
                        <td style="min-width:140px"><div class="progress-mini"><span style="width:{{ round($r->stock / $maxRegion * 100) }}%;background:var(--blue)"></span></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-map"></i><p>Aucune donnée régionale.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-shield-alt"></i> Couverture inspecteurs par région</h3><a href="{{ route('dg.inspector-assignments.index') }}" class="btn btn-ghost btn-sm">Gérer</a></div>
        <div class="card-b" style="max-height:340px;overflow-y:auto">
            @foreach($couverture as $c)
                @php
                    $st = $c['status'];
                    $cls = $st === 'COUVERT' ? 'b-green' : (str_starts_with($st, 'INCOMPLET') ? 'b-amber' : 'b-red');
                    $whCount = count($c['warehouses']);
                    $inspCount = collect($c['warehouses'])->flatMap(fn($w) => $w['inspecteurs'])->unique('id')->count();
                    $magCount = collect($c['warehouses'])->flatMap(fn($w) => $w['magasiniers'])->unique('id')->count();
                @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F1F3F7;font-size:13px">
                    <div><b>{{ $c['region'] }}</b><br><span style="font-size:11px;color:var(--muted)">{{ $whCount }} magasin(s) · {{ $inspCount }} inspecteur(s) · {{ $magCount }} magasinier(s)</span></div>
                    <span class="badge {{ $cls }}">{{ $st }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid-2e">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-exclamation-triangle"></i> Stocks critiques</h3><a href="{{ route('dg.situation-stocks.index') }}" class="btn btn-ghost btn-sm">Situation complète</a></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Magasin</th><th>Région</th><th>Stock</th></tr></thead>
                <tbody>
                @forelse($alertes as $a)
                    <tr>
                        <td><b>{{ $a->name }}</b></td>
                        <td>{{ $a->region ?? '—' }}</td>
                        <td><span class="badge b-red">{{ number_format($a->current_stock, 0, ',', ' ') }} sacs</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="empty"><i class="fas fa-check-circle"></i><p>Aucun stock critique.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-history"></i> Dernières opérations</h3><a href="{{ route('dg.stock-operations.index') }}" class="btn btn-ghost btn-sm">Tout voir</a></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Référence</th><th>Type</th><th>Magasin</th><th>Statut</th></tr></thead>
                <tbody>
                @forelse($dernieresOps as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>{{ $op->type_label }}</td>
                        <td>{{ $op->warehouse->name ?? '—' }}</td>
                        <td><span class="badge b-{{ $op->status === 'verifiee' ? 'green' : ($op->status === 'anomalie' || $op->status === 'rejetee' ? 'red' : 'blue') }}">{{ $op->status_label }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-inbox"></i><p>Aucune opération.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const labels = @json($activite->pluck('jour')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values());
    const entrees = @json($activite->pluck('entrees')->map(fn($v) => (float) $v)->values());
    const sorties = @json($activite->pluck('sorties')->map(fn($v) => (float) $v)->values());
    const ctx = document.getElementById('activiteChart');
    if(ctx){
        new Chart(ctx,{type:'line',data:{labels:labels,datasets:[
            {label:'Entrées',data:entrees,borderColor:'#10B981',backgroundColor:'rgba(16,185,129,.12)',fill:true,tension:.35,pointRadius:2},
            {label:'Sorties',data:sorties,borderColor:'#F97316',backgroundColor:'rgba(249,115,22,.12)',fill:true,tension:.35,pointRadius:2}
        ]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true}}}});
    }
})();
</script>
@endpush
