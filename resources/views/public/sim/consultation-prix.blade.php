@extends('layouts.public')

@section('title', 'Consultation des prix & Export | SIM CSAR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sim-fluid-modern.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4 sim-consult-fluid">
    {{-- Titre style SIMA Niger --}}
    <div class="sim-consult-banner text-center py-3 mb-4">
        <h1 class="sim-consult-title mb-0">CONSULTATION DES PRIX & EXPORT DE DONNÉES</h1>
    </div>

    {{-- Onglets par catégorie (BDD) --}}
    <ul class="nav nav-tabs sim-tabs mb-3" role="tablist">
        @foreach($categories as $cat)
        <li class="nav-item">
            @php
                $params = request()->only(['year', 'month', 'region', 'search', 'sort', 'dir', 'per_page']);
                $params['category'] = $cat['id'];
                $active = ($categoryId === $cat['id']) || ($categoryId === null && $cat['id'] === null);
            @endphp
            <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route('sim.consultation-prix', array_merge(['locale' => app()->getLocale()], $params)) }}">{{ strtoupper($cat['name']) }}</a>
        </li>
        @endforeach
    </ul>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            {{-- Filtres + Afficher X éléments + Rechercher + Export --}}
            <form method="GET" action="{{ route('sim.consultation-prix', ['locale' => app()->getLocale()]) }}" id="filterForm" class="row g-2 align-items-center mb-3 flex-wrap">
                <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
                <input type="hidden" name="category" value="{{ $categoryId }}">
                <input type="hidden" name="sort" value="{{ $sortCol }}">
                <input type="hidden" name="dir" value="{{ $sortDir }}">
                <div class="col-auto">
                    <label class="col-form-label small">Année</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2020" max="2030" style="width:90px">
                </div>
                <div class="col-auto">
                    <label class="col-form-label small">Mois</label>
                    <select name="month" class="form-select form-select-sm" style="width:120px">
                        <option value="">Tous</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="col-form-label small">Région</label>
                    <select name="region" class="form-select form-select-sm" style="width:140px">
                        <option value="">Toutes</option>
                        @foreach($regions as $rName => $rLabel)
                            <option value="{{ $rName }}" {{ $region == $rName ? 'selected' : '' }}>{{ $rLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="col-form-label small">Afficher</label>
                    <select name="per_page" class="form-select form-select-sm" style="width:80px" onchange="document.getElementById('filterForm').submit()">
                        @foreach([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    <span class="small text-muted">éléments</span>
                </div>
                <div class="col-auto ms-auto">
                    <label class="col-form-label small">Rechercher&nbsp;:</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Produit, marché, région..." style="width:220px">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success btn-sm">Filtrer</button>
                </div>
            </form>

            {{-- Boutons Export --}}
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Imprimer</button>
                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportTable('excel')"><i class="fas fa-file-excel me-1"></i>Excel</button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="exportTable('csv')"><i class="fas fa-file-csv me-1"></i>CSV</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyTable()"><i class="fas fa-copy me-1"></i>Copier</button>
            </div>

            {{-- Tableau triable (données BDD) --}}
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="priceTable">
                    <thead class="table-light">
                        <tr>
                            @php
                                $baseParams = array_merge(request()->only(['year', 'month', 'region', 'category', 'search', 'per_page']), ['locale' => app()->getLocale()]);
                                $sortLink = function($col) use ($baseParams, $sortCol, $sortDir) {
                                    $dir = ($sortCol === $col && $sortDir === 'asc') ? 'desc' : 'asc';
                                    return route('sim.consultation-prix', array_merge($baseParams, ['sort' => $col, 'dir' => $dir]));
                                };
                            @endphp
                            <th><a href="{{ $sortLink('year') }}" class="text-dark text-decoration-none">ANNÉE ↑↓</a></th>
                            <th><a href="{{ $sortLink('month') }}" class="text-dark text-decoration-none">MOIS ↑↓</a></th>
                            <th><a href="{{ $sortLink('region_name') }}" class="text-dark text-decoration-none">RÉGION ↑↓</a></th>
                            <th><a href="{{ $sortLink('market_name') }}" class="text-dark text-decoration-none">MARCHÉS ↑↓</a></th>
                            <th><a href="{{ $sortLink('product_name') }}" class="text-dark text-decoration-none">PRODUIT ↑↓</a></th>
                            <th class="text-end"><a href="{{ $sortLink('price') }}" class="text-dark text-decoration-none">PRIX ↑↓</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $row->year }}</td>
                            <td>{{ $row->month ? \Carbon\Carbon::createFromDate(2000, $row->month, 1)->translatedFormat('M') : '—' }}</td>
                            <td>{{ $row->region_name ?? '—' }}</td>
                            <td>{{ $row->market_name ?? '—' }}</td>
                            <td>{{ $row->product_name ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row->price ?? 0, 0, ',', ' ') }} F</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune donnée pour les filtres sélectionnés. Les données proviennent de la base de données SIM.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($paginator) && $paginator && method_exists($paginator, 'links'))
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <div class="small text-muted">
                        Affichage de l'élément {{ $paginator->firstItem() ?? 0 }} à {{ $paginator->lastItem() ?? 0 }} sur {{ $paginator->total() }} éléments
                    </div>
                    <div>{{ $paginator->withQueryString()->links() }}</div>
                </div>
            @else
                <div class="small text-muted mt-2">Total : {{ $total }} enregistrement(s)</div>
            @endif
        </div>
    </div>
</div>

<style>
.sim-consult-banner { background: linear-gradient(135deg, #c9a227 0%, #b8860b 50%, #daa520 100%); color: #fff; }
.sim-consult-title { font-size: 1.25rem; font-weight: 700; letter-spacing: 0.03em; }
.sim-tabs .nav-link { font-weight: 600; color: #333; }
.sim-tabs .nav-link.active { background: #28a745; color: #fff; border-color: #28a745; }
@media print { .sim-consult-banner, .sim-tabs, .btn, form, .pagination, .d-flex.gap-2 { display: none !important; } }
</style>
<script>
function getTableData() {
    var rows = [];
    document.querySelectorAll('#priceTable tbody tr').forEach(function(tr) {
        var cells = tr.querySelectorAll('td');
        if (cells.length >= 6) rows.push([cells[0].innerText, cells[1].innerText, cells[2].innerText, cells[3].innerText, cells[4].innerText, cells[5].innerText]);
    });
    return rows;
}
function exportTable(format) {
    var rows = getTableData();
    if (rows.length === 0) { alert('Aucune donnée à exporter.'); return; }
    var header = ['ANNÉE', 'MOIS', 'RÉGION', 'MARCHÉS', 'PRODUIT', 'PRIX'];
    var sep = format === 'csv' ? ';' : '\t';
    var content = header.join(sep) + '\n' + rows.map(function(r) { return r.join(sep); }).join('\n');
    var blob = new Blob(['\ufeff' + content], { type: format === 'csv' ? 'text/csv;charset=utf-8' : 'application/vnd.ms-excel' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'sim_prix_' + new Date().toISOString().slice(0,10) + (format === 'csv' ? '.csv' : '.xls');
    a.click();
    URL.revokeObjectURL(a.href);
}
function copyTable() {
    var rows = getTableData();
    if (rows.length === 0) { alert('Aucune donnée à copier.'); return; }
    var header = ['ANNÉE', 'MOIS', 'RÉGION', 'MARCHÉS', 'PRODUIT', 'PRIX'];
    var text = header.join('\t') + '\n' + rows.map(function(r) { return r.join('\t'); }).join('\n');
    navigator.clipboard.writeText(text).then(function() { alert('Tableau copié dans le presse-papiers.'); }).catch(function() { alert('Copie impossible.'); });
}
</script>
@endsection
