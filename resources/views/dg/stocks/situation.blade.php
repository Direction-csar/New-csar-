@extends('layouts.dg-modern')

@section('title', 'Situation des stocks')
@section('page-title', 'Situation des stocks en temps réel')

@section('content')
<div class="container-fluid">
    <div class="card-modern p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>Situation des stocks des magasins</h1>
                <p class="text-muted mb-0 small">
                    Données saisies par les magasiniers (portail web et application mobile).
                    Dernière mise à jour : <strong id="maj">{{ $situation['generated_at'] }}</strong>
                    <span class="badge bg-success ms-2" id="live">● en direct (actualisation toutes les 20 s)</span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('dg.situation-stocks.export-csv') }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i>CSV</a>
                <a href="{{ route('dg.situation-stocks.export-pdf') }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <a href="{{ route('dg.stock-operations.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-dolly me-1"></i>Opérations</a>
                <button class="btn btn-primary-modern btn-modern btn-sm" onclick="rafraichir()"><i class="fas fa-sync-alt me-1"></i>Actualiser</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3" id="totaux"></div>

    <div class="card-modern p-3 mb-3" id="bloc-attente" style="display:none">
        <h5 class="fw-bold mb-3"><i class="fas fa-hourglass-half me-2 text-warning"></i>En attente de vérification</h5>
        <div class="d-flex gap-4 mb-2">
            <div>Opérations en cours : <strong id="ops-en-cours">0</strong></div>
            <div>Mouvements en attente : <strong id="mvmts-attente">0</strong></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm" id="table-attente">
                <thead><tr><th>Date</th><th>Magasin</th><th>Opération</th><th>Libellé</th><th class="text-end">Contenants</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card-modern p-3 mb-3">
        <h5 class="fw-bold mb-3"><i class="fas fa-warehouse me-2 text-primary"></i>Stock par magasin</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="table-magasins">
                <thead><tr><th>Magasin</th><th>Région</th><th>Produits en stock</th><th class="text-end">Total (kg)</th><th class="text-end">Total (L)</th><th>Dernier mouvement</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card-modern p-3">
        <h5 class="fw-bold mb-3"><i class="fas fa-history me-2 text-primary"></i>Derniers mouvements</h5>
        <div class="table-responsive">
            <table class="table table-sm" id="table-recents">
                <thead><tr><th>Date</th><th>Magasin</th><th>Type</th><th>Libellé</th><th class="text-end">Contenants</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const fmt = n => new Intl.NumberFormat('fr-FR', {maximumFractionDigits: 2}).format(n);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let data = @json($situation);

    function render(d) {
        document.getElementById('maj').textContent = d.generated_at;
        document.getElementById('totaux').innerHTML = d.totals.length ? d.totals.map(t =>
            `<div class="col-6 col-md-3 col-xl-2"><div class="stats-card"><h4 class="stats-number">${fmt(t.quantite)} <small>${esc(t.unit)}</small></h4><p class="stats-label">${esc(t.name)}</p></div></div>`
        ).join('') : '<div class="col-12 text-muted">Aucun stock enregistré.</div>';

        document.querySelector('#table-magasins tbody').innerHTML = d.warehouses.map(w => `<tr>
            <td class="fw-semibold">${esc(w.name)}</td><td>${esc(w.region)}</td>
            <td>${w.products.length ? w.products.map(p => `<span class="badge bg-light text-dark border me-1 mb-1">${esc(p.name)} : ${fmt(p.quantite)} ${esc(p.unit)} <small class="text-muted">(${fmt(p.contenants)} × ${p.format} ${esc(p.unit)})</small></span>`).join('') : '<span class="text-muted">Vide</span>'}</td>
            <td class="text-end">${fmt(w.total_kg)}</td><td class="text-end">${fmt(w.total_l)}</td>
            <td class="small text-muted">${esc(w.last_movement_at ?? '—')}</td></tr>`).join('');

        document.querySelector('#table-recents tbody').innerHTML = d.recent.map(m => `<tr>
            <td class="small">${esc(m.date)}</td><td>${esc(m.warehouse)}</td><td>${esc(m.type)}</td><td>${esc(m.reason)}</td><td class="text-end">${fmt(m.quantity)}</td></tr>`).join('');

        const ops = d.operations || {en_cours: 0, mouvements_en_attente: 0, derniers_en_attente: []};
        document.getElementById('ops-en-cours').textContent = ops.en_cours;
        document.getElementById('mvmts-attente').textContent = ops.mouvements_en_attente;
        const bloc = document.getElementById('bloc-attente');
        bloc.style.display = (ops.en_cours > 0 || ops.mouvements_en_attente > 0) ? '' : 'none';
        document.querySelector('#table-attente tbody').innerHTML = (ops.dernier_en_attente || ops.derniers_en_attente || []).map(m => `<tr>
            <td class="small">${esc(m.date)}</td><td>${esc(m.warehouse)}</td><td>${esc(m.operation || '—')}</td><td>${esc(m.reason)}</td><td class="text-end">${fmt(m.quantity)}</td></tr>`).join('');
    }

    window.rafraichir = function () {
        fetch(@json(route('dg.situation-stocks.data')), {headers: {'Accept': 'application/json'}, credentials: 'same-origin'})
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(d => { data = d; render(d); document.getElementById('live').className = 'badge bg-success ms-2'; })
            .catch(() => { document.getElementById('live').className = 'badge bg-warning text-dark ms-2'; });
    };
    render(data);
    setInterval(window.rafraichir, 20000);
})();
</script>
@endsection
