@extends('layouts.stock-portal')

@section('title', 'Rapports et statistiques')
@section('page-title', 'Rapports et statistiques')
@section('breadcrumb', $portalLabel . ' › Rapports')

@section('sidebar-extra')
    <div class="sb-section">Rapports</div>
    <a class="sb-link active" href="{{ route($portal . '.stock-reports.index') }}">
        <i class="fas fa-chart-bar"></i><span class="lbl">Rapports & exports</span>
    </a>
@endsection

@section('content')

<div class="card">
    <div class="card-h"><h3><i class="fas fa-file-export"></i> Générer un rapport</h3><span class="badge b-blue">{{ $portal === 'dg' ? 'Portail DG' : 'Portail DTL' }}</span></div>
    <div class="card-b">
        <form method="POST" action="{{ route($portal . '.stock-reports.generate') }}">
            @csrf
            <div class="filters" style="margin-bottom:16px">
                <select name="report_type" class="f-select" required>
                    <option value="">— Choisir le type de rapport —</option>
                    @foreach($catalog as $key => $label)
                        <option value="{{ $key }}" @selected(old('report_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="format" class="f-select" required>
                    <option value="pdf" @selected(old('format','pdf')==='pdf')>PDF</option>
                    <option value="xlsx" @selected(old('format')==='xlsx')>Excel (.xlsx)</option>
                </select>
            </div>

            <div class="filters">
                <div class="field" style="margin:0"><label style="font-size:11.5px">Du</label><input type="date" name="date_from" class="f-input" value="{{ old('date_from') }}"></div>
                <div class="field" style="margin:0"><label style="font-size:11.5px">Au</label><input type="date" name="date_to" class="f-input" value="{{ old('date_to') }}"></div>
                <select name="region" class="f-select">
                    <option value="">Toutes les régions</option>
                    @foreach($regions as $r)<option value="{{ $r }}" @selected(old('region')===$r)>{{ $r }}</option>@endforeach
                </select>
                <select name="warehouse_id" class="f-select">
                    <option value="">Tous les magasins</option>
                    @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected((string)old('warehouse_id')===(string)$w->id)>{{ $w->name }} ({{ $w->region }})</option>@endforeach
                </select>
                <input type="text" name="produit" class="f-input" placeholder="Produit / motif…" value="{{ old('produit') }}" style="min-width:160px">
                <select name="statut" class="f-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $s)<option value="{{ $s }}" @selected(old('statut')===$s)>{{ $s }}</option>@endforeach
                </select>
                @if($portal === 'dtl')
                <select name="inspecteur_id" class="f-select">
                    <option value="">Tous les inspecteurs</option>
                    @foreach($inspectors as $i)<option value="{{ $i->id }}" @selected((string)old('inspecteur_id')===(string)$i->id)>{{ $i->name }}</option>@endforeach
                </select>
                @endif
                <button type="submit" class="btn btn-orange"><i class="fas fa-download"></i> Générer & télécharger</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-h"><h3><i class="fas fa-folder-open"></i> Catalogue des rapports</h3><span class="badge b-gray">{{ count($catalog) }} type(s)</span></div>
    <div class="card-b">
        <div class="report-cards">
            @foreach($catalog as $key => $label)
                <div class="rep-card" onclick="document.querySelector('select[name=report_type]').value='{{ $key }}';document.querySelector('form').scrollIntoView({behavior:'smooth'})">
                    <h4><i class="fas fa-file-alt" style="color:var(--orange)"></i>{{ $label }}</h4>
                    <div style="font-size:11.5px;color:var(--muted)">Cliquez pour pré-remplir le générateur — formats PDF et Excel disponibles.</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-h"><h3><i class="fas fa-history"></i> Historique des exports</h3></div>
    <div style="overflow-x:auto">
        <table class="tbl compact">
            <thead><tr><th>Référence</th><th>Type</th><th>Format</th><th>Généré par</th><th>Date</th><th>Période</th><th></th></tr></thead>
            <tbody>
            @forelse($history as $h)
                <tr>
                    <td><b>{{ $h->reference }}</b></td>
                    <td>{{ $catalog[$h->report_type] ?? $h->report_type }}</td>
                    <td><span class="badge b-{{ $h->format === 'pdf' ? 'red' : 'green' }}">{{ strtoupper($h->format) }}</span></td>
                    <td>{{ $h->user->name ?? '—' }}</td>
                    <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                    <td style="font-size:12px;color:var(--muted)">
                        @if($h->filters['date_from'] ?? null) {{ $h->filters['date_from'] }} → {{ $h->filters['date_to'] ?? '…' }} @else Toute période @endif
                    </td>
                    <td><a href="{{ route($portal . '.stock-reports.download', $h->id) }}" class="btn btn-ghost btn-sm"><i class="fas fa-download"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty"><i class="fas fa-folder-open"></i><p>Aucun rapport généré pour le moment.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
