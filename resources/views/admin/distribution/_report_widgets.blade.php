{{-- Widgets analytiques du rapport consolidé — nécessite $report et $event ; $print (bool) optionnel --}}
@php
    $print = $print ?? false;
    $fmt = fn($v) => number_format($v, 0, ',', ' ');
    $pct = fn($v) => str_replace('.', ',', (string) $v);
    $alertClass = ['OK' => 'rep-ok', 'A SURVEILLER' => 'rep-watch', 'RETARD' => 'rep-late', 'ALERTE' => 'rep-late', 'EN COURS' => 'rep-watch'];
    $labelAlert = ['A SURVEILLER' => 'À SURVEILLER'];
@endphp

<style>
    .rep-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .rep-table th { background: #1f3864; color: #fff; padding: 9px 8px; text-align: center; font-weight: 600; border: 1px solid #1f3864; }
    .rep-table th:first-child { text-align: left; }
    .rep-table td { padding: 8px; border: 1px solid #ccd; text-align: center; background: #fff; }
    .rep-table td:first-child { text-align: left; }
    .rep-table tbody tr:nth-child(even) td { background: #f3f5f9; }
    .rep-table tfoot td { background: #dde3ef !important; font-weight: 700; }
    .rep-table .rep-rate { font-weight: 700; }
    .rep-ok { background: #d9ead3 !important; color: #2e7d32; font-weight: 700; }
    .rep-watch { background: #fff2cc !important; color: #b45f06; font-weight: 700; }
    .rep-late { background: #f4cccc !important; color: #c00000; font-weight: 700; }
    .rep-controls td:nth-child(2) { font-weight: 700; }
    .rep-controls tbody tr td { background: #f9cb9c !important; }
    .rep-controls tbody tr.rep-row-ok td { background: #d9ead3 !important; }
    .rep-controls tbody tr.rep-row-watch td { background: #fff2cc !important; }
    .rep-chart-title { text-align: center; font-weight: 700; color: #1f3864; font-size: 14px; margin-bottom: 6px; }
    .rep-caption { text-align: center; font-style: italic; color: #666; font-size: 12px; margin-top: 6px; }
    .rep-kpi { border: 1px solid #dde3ef; border-radius: 8px; padding: 10px; text-align: center; background: #fff; }
    .rep-kpi .v { font-size: 20px; font-weight: 800; color: #1f3864; }
    .rep-kpi .l { font-size: 11px; color: #667; }
    .rep-status-CRITIQUE { color: #c00000; }
    .rep-status-A.SURVEILLER, .rep-status-watch { color: #b45f06; }
    .rep-status-OK { color: #2e7d32; }
</style>

{{-- KPIs --}}
<div class="row g-2 mb-3">
    <div class="col-md-2 col-6"><div class="rep-kpi"><div class="v">{{ $fmt($report['initial']) }}</div><div class="l">Stock initial (kg)</div></div></div>
    <div class="col-md-2 col-6"><div class="rep-kpi"><div class="v">{{ $fmt($report['planned']) }}</div><div class="l">Quota planifié (kg)</div></div></div>
    <div class="col-md-2 col-6"><div class="rep-kpi"><div class="v" style="color:#2e7d32">{{ $fmt($report['executed']) }}</div><div class="l">Exécuté / servi (kg)</div></div></div>
    <div class="col-md-2 col-6"><div class="rep-kpi"><div class="v" style="color:#b45f06">{{ $fmt($report['in_progress']) }}</div><div class="l">En cours (kg)</div></div></div>
    <div class="col-md-2 col-6"><div class="rep-kpi"><div class="v {{ $report['remaining'] < 0 ? 'rep-status-CRITIQUE' : '' }}">{{ $fmt($report['remaining']) }}</div><div class="l">Stock restant (kg)</div></div></div>
    <div class="col-md-2 col-6"><div class="rep-kpi"><div class="v rep-status-{{ $report['stock_status'] === 'A SURVEILLER' ? 'watch' : $report['stock_status'] }}">{{ $labelAlert[$report['stock_status']] ?? $report['stock_status'] }}</div><div class="l">Statut du stock · conso. {{ $pct($report['consumption_rate']) }} %</div></div></div>
</div>

{{-- Donut --}}
<div class="row mb-4">
    <div class="col-md-5 mx-auto">
        <div class="rep-chart-title">Répartition du quota planifié ({{ $fmt($report['planned']) }} kg)</div>
        <div style="height:{{ $print ? 260 : 220 }}px;position:relative">
            <canvas id="repDonut"></canvas>
        </div>
        <div class="rep-caption">Répartition du quota planifié : exécuté, couvert par le stock restant, dépassement.</div>
    </div>
</div>

{{-- Tableau par planning --}}
<h5 class="fw-bold mb-2" style="color:#1f3864">1. ÉTAT D'AVANCEMENT PAR PLANNING</h5>
<table class="rep-table mb-2">
    <thead>
        <tr><th>Planning / volet</th><th>Planifié (kg)</th><th>Exécuté (kg)</th><th>En cours (kg)</th><th>Bénéf.</th><th>Tickets</th><th>Dons reçus</th><th>Taux</th><th>Alerte</th></tr>
    </thead>
    <tbody>
        @foreach($report['rows'] as $r)
        <tr>
            <td>@if(!$print)<a href="{{ route('admin.distribution.plannings.show', $r['id']) }}" class="text-decoration-none text-dark">{{ $r['name'] }}</a>@else{{ $r['name'] }}@endif</td>
            <td>{{ $fmt($r['planned']) }}</td>
            <td>{{ $fmt($r['executed']) }}</td>
            <td>{{ $fmt($r['in_progress']) }}</td>
            <td>{{ $r['beneficiaries'] }}</td>
            <td>{{ $r['tickets'] }}</td>
            <td>{{ $r['collected'] }}</td>
            <td class="rep-rate">{{ $pct($r['rate']) }} %</td>
            <td class="{{ $alertClass[$r['alert']] }}">{{ $labelAlert[$r['alert']] ?? $r['alert'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td>{{ $fmt($report['planned']) }}</td>
            <td>{{ $fmt($report['executed']) }}</td>
            <td>{{ $fmt($report['in_progress']) }}</td>
            <td>{{ $report['total_beneficiaries'] }}</td>
            <td>{{ $report['total_tickets'] }}</td>
            <td>{{ $report['total_collected'] }}</td>
            <td style="color:#1f3864">{{ $pct($report['execution_rate']) }} %</td>
            <td></td>
        </tr>
    </tfoot>
</table>
<p class="small text-muted mb-4" style="text-align:justify">{{ $report['text']['plannings'] }}</p>

{{-- Barres planifié vs exécuté --}}
<div class="mb-4">
    <div class="rep-chart-title">Quota planifié vs quantité exécutée par planning</div>
    <div style="height:{{ max(160, 40 * count($report['rows']) + 60) }}px;max-height:400px;position:relative">
        <canvas id="repBars"></canvas>
    </div>
    <div class="rep-caption">Quota planifié comparé à la quantité déjà exécutée, par planning.</div>
</div>

{{-- Taux d'exécution --}}
<div class="mb-4">
    <div class="rep-chart-title">Taux d'exécution par planning (seuil de vigilance à 80 %)</div>
    <div style="height:{{ max(160, 32 * count($report['rows']) + 60) }}px;max-height:400px;position:relative">
        <canvas id="repRates"></canvas>
    </div>
</div>

{{-- Contrôles / alertes --}}
<h5 class="fw-bold mb-2" style="color:#1f3864">2. ALERTES AUTOMATIQUES</h5>
<p class="small mb-2" style="text-align:justify">{{ $report['text']['alerts'] }}</p>
<table class="rep-table rep-controls mb-4" style="max-width:820px">
    <thead><tr><th style="text-align:left">Contrôle</th><th>Valeur</th><th>Statut</th></tr></thead>
    <tbody>
        @foreach($report['controls'] as $c)
        <tr class="{{ $c['status'] === 'OK' ? 'rep-row-ok' : ($c['status'] === 'EN COURS' ? 'rep-row-watch' : '') }}">
            <td>{{ $c['label'] }}</td>
            <td>{{ $c['value'] }}</td>
            <td class="{{ $alertClass[$c['status']] }}">{{ $c['status'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Évolution du stock --}}
<h5 class="fw-bold mb-2" style="color:#1f3864">3. ÉVOLUTION DU STOCK</h5>
<p class="small mb-2" style="text-align:justify">{{ $report['text']['stock'] }}</p>
<div class="mb-2">
    <div class="rep-chart-title">Évolution du stock de {{ $pct(round($report['initial'] / 1000, 1)) }} tonnes au fil des plannings</div>
    <div style="height:{{ $print ? 230 : 110 }}px;position:relative">
        <canvas id="repStock"></canvas>
    </div>
    <div class="rep-caption">Courbe d'évolution du stock, planning après planning, jusqu'à la projection après reste à servir.</div>
</div>

<script>
(function () {
    function initReportCharts() {
        if (typeof Chart === 'undefined') { setTimeout(initReportCharts, 100); return; }
        const navy = '#1f3864', navyLight = '#8ea9db', orange = '#e69138', red = '#c00000', green = '#2e7d32';
        const rows = @json($report['rows']);
        const names = rows.map(r => r.name);
        const wrap = s => s.length > 22 ? s.match(/.{1,22}(\s|$)/g).map(x => x.trim()) : s;
        const anim = {{ $print ? 'false' : 'true' }};

        const d = @json($report['donut']);
        const total = d.executed + d.covered + d.overrun || 1;
        new Chart(document.getElementById('repDonut'), {
            type: 'doughnut',
            data: {
                labels: ['Exécuté / servi (' + d.executed.toLocaleString('fr-FR') + ' kg)', 'Couvert par le stock restant (' + d.covered.toLocaleString('fr-FR') + ' kg)', 'Dépassement du stock (' + d.overrun.toLocaleString('fr-FR') + ' kg)'],
                datasets: [{ data: [d.executed, d.covered, d.overrun], backgroundColor: [navy, orange, red], borderWidth: 2 }]
            },
            options: { animation: anim, responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 14, font: { size: 11 } } },
                tooltip: { callbacks: { label: c => ' ' + (c.raw / total * 100).toFixed(1) + ' %' } } } }
        });

        new Chart(document.getElementById('repBars'), {
            type: 'bar',
            data: { labels: names.map(wrap), datasets: [
                { label: 'Quota planifié (kg)', data: rows.map(r => r.planned), backgroundColor: navyLight },
                { label: 'Exécuté (kg)', data: rows.map(r => r.executed), backgroundColor: navy }
            ] },
            options: { animation: anim, indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { x: { title: { display: true, text: 'Kilogrammes' }, ticks: { callback: v => v.toLocaleString('fr-FR') } } } }
        });

        new Chart(document.getElementById('repRates'), {
            type: 'bar',
            data: { labels: names.map(wrap), datasets: [{ label: "Taux d'exécution (%)", data: rows.map(r => r.rate),
                backgroundColor: rows.map(r => r.rate >= 100 ? green : (r.rate >= 80 ? orange : red)) }] },
            options: { animation: anim, indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, annotation: undefined },
                scales: { x: { min: 0, max: 100, ticks: { callback: v => v + ' %' }, grid: { color: ctx => ctx.tick.value === 80 ? red : '#e5e5e5', lineWidth: ctx => ctx.tick.value === 80 ? 2 : 1 } } } }
        });

        const evo = @json($report['stock_evolution']);
        const last = evo.length - 1;
        new Chart(document.getElementById('repStock'), {
            type: 'line',
            data: { labels: evo.map(e => wrap(e.label)), datasets: [{ label: 'Stock restant (kg)', data: evo.map(e => e.value),
                borderColor: navy, backgroundColor: 'rgba(31,56,100,0.15)', fill: true, tension: 0.15,
                pointBackgroundColor: evo.map((e, i) => i === last && e.value < 0 ? red : navy), pointRadius: 5 }] },
            options: { animation: anim, responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + c.raw.toLocaleString('fr-FR') + ' kg' } } },
                scales: { y: { title: { display: true, text: 'Stock restant (kg)' }, ticks: { callback: v => v.toLocaleString('fr-FR') }, grid: { color: ctx => ctx.tick.value === 0 ? '#000' : '#e5e5e5' } } } }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initReportCharts); else initReportCharts();
})();
</script>
