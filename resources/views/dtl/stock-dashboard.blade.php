@extends('layouts.stock-portal')

@section('title', 'Pilotage stocks — DTL')
@section('page-title', 'Centre de pilotage opérationnel')
@section('breadcrumb', 'DTL › Gestion des stocks › Pilotage')

@section('sidebar-extra')
    <div class="sb-section">Circuit des stocks</div>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'inspector-assignments') ? 'active' : '' }}" href="{{ route('dtl.inspector-assignments.index') }}">
        <i class="fas fa-user-check"></i><span class="lbl">Affectations inspecteurs</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-staff') ? 'active' : '' }}" href="{{ route('dtl.stock-staff.index') }}">
        <i class="fas fa-users"></i><span class="lbl">Personnel stock</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-operations') ? 'active' : '' }}" href="{{ route('dtl.stock-operations.index') }}">
        <i class="fas fa-dolly"></i><span class="lbl">Opérations de stock</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-operations.pending') ? 'active' : '' }}" href="{{ route('dtl.stock-operations.pending') }}">
        <i class="fas fa-clock"></i><span class="lbl">Mouvements en attente</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-reports') ? 'active' : '' }}" href="{{ route('dtl.stock-reports.index') }}">
        <i class="fas fa-chart-bar"></i><span class="lbl">Rapports & statistiques</span>
    </a>
    <div class="sb-section">Autres espaces</div>
    <a class="sb-link" href="{{ route('archives.dtl.index') }}">
        <i class="fas fa-box-archive"></i><span class="lbl">Archives & dossiers</span>
    </a>
    <a class="sb-link" href="{{ route('dtl.demandes.index') }}">
        <i class="fas fa-file-alt"></i><span class="lbl">Demandes DTL</span>
    </a>
@endsection

@section('content')

<div class="card" style="background:linear-gradient(135deg,#0B1F3A,#16345E);border:none">
    <div class="card-b" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="color:#fff;font-weight:700;margin:0 0 4px">Priorités du jour</h3>
            <div style="color:rgba(255,255,255,.75);font-size:13.5px">Suivi en temps réel du circuit de gestion des stocks alimentaires — {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('dtl.stock-operations.index') }}" class="btn btn-orange"><i class="fas fa-list"></i> Instructions reçues</a>
            <a href="{{ route('dtl.inspector-assignments.index') }}" class="btn btn-ghost" style="border-color:rgba(255,255,255,.3);color:#fff"><i class="fas fa-user-check"></i> Affecter un inspecteur</a>
            <a href="{{ route('dtl.stock-staff.index') }}" class="btn btn-ghost" style="border-color:rgba(255,255,255,.3);color:#fff"><i class="fas fa-users"></i> Personnel stock</a>
        </div>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div class="ic ic-blue"><i class="fas fa-clipboard-list"></i></div><div><div class="v">{{ number_format($stats['instructions_reçues'], 0, ',', ' ') }}</div><div class="l">Instructions reçues</div><div class="d" style="color:var(--green)">Total cumulé</div></div></div>
    <div class="kpi"><div class="ic ic-orange"><i class="fas fa-user-plus"></i></div><div><div class="v">{{ number_format($stats['a_affecter'], 0, ',', ' ') }}</div><div class="l">À affecter</div><div class="d" style="color:var(--orange)">Priorité DTL</div></div></div>
    <div class="kpi"><div class="ic ic-night"><i class="fas fa-user-shield"></i></div><div><div class="v">{{ number_format($inspectors, 0, ',', ' ') }}</div><div class="l">Inspecteurs actifs</div><div class="d" style="color:var(--green)">Comptes actifs</div></div></div>
    <div class="kpi"><div class="ic ic-green"><i class="fas fa-warehouse"></i></div><div><div class="v">{{ number_format($magasins, 0, ',', ' ') }}</div><div class="l">Magasins actifs</div><div class="d">{{ number_format($magasinsAvecMagasinier, 0, ',', ' ') }} avec magasinier</div></div></div>
    <div class="kpi"><div class="ic ic-amber"><i class="fas fa-clock"></i></div><div><div class="v">{{ number_format($mouvements['en_attente'], 0, ',', ' ') }}</div><div class="l">Mouvements en attente</div><div class="d" style="color:#B45309">À vérifier</div></div></div>
    <div class="kpi"><div class="ic ic-orange"><i class="fas fa-truck"></i></div><div><div class="v">{{ number_format($mouvements['receptions'], 0, ',', ' ') }}</div><div class="l">Réceptions à confirmer</div><div class="d" style="color:var(--orange)">Transferts entrants</div></div></div>
    <div class="kpi"><div class="ic ic-red"><i class="fas fa-exclamation-triangle"></i></div><div><div class="v">{{ number_format($stats['anomalies'], 0, ',', ' ') }}</div><div class="l">Anomalies signalées</div><div class="d" style="color:var(--red)">Opérations bloquées</div></div></div>
    <div class="kpi"><div class="ic ic-green"><i class="fas fa-check-double"></i></div><div><div class="v">{{ number_format($stats['verifiees_mois'], 0, ',', ' ') }}</div><div class="l">Opérations terminées</div><div class="d">Ce mois-ci</div></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-user-plus"></i> Instructions à affecter</h3><a href="{{ route('dtl.stock-operations.index') }}" class="btn btn-ghost btn-sm">Tout voir</a></div>
        <div style="overflow-x:auto">
            <table class="tbl">
                <thead><tr><th>Référence</th><th>Type</th><th>Magasin</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                @forelse($aAffecter as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>{{ $op->type_label }}</td>
                        <td>{{ $op->warehouse->name ?? '—' }}@if($op->destinationWarehouse) → {{ $op->destinationWarehouse->name }}@endif</td>
                        <td><span class="badge b-orange">À affecter</span></td>
                        <td><a href="{{ route('dtl.stock-operations.show', $op->id) }}" class="btn btn-blue btn-sm">Affecter</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><i class="fas fa-check-circle"></i><p>Aucune instruction en attente d'affectation.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-tasks"></i> Circuit en cours</h3></div>
        <div style="overflow-x:auto">
            <table class="tbl">
                <thead><tr><th>Référence</th><th>Acteur</th><th>Étape</th><th>Statut</th></tr></thead>
                <tbody>
                @forelse($enCours as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>
                            @if($op->status === 'affectee_inspecteur') Inspecteur : {{ $op->inspector->name ?? '—' }}
                            @elseif($op->status === 'transmise_magasinier') Magasinier : {{ $op->magasinier->name ?? '—' }}
                            @elseif($op->status === 'executee') Exécutée — à vérifier
                            @else Anomalie
                            @endif
                        </td>
                        <td>{{ $op->type_label }}</td>
                        <td><span class="badge b-{{ $op->status === 'anomalie' ? 'red' : ($op->status === 'executee' ? 'amber' : 'blue') }}">{{ $op->status_label }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-inbox"></i><p>Aucune opération en cours.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid-2e">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-exclamation-triangle"></i> Anomalies à traiter</h3></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Référence</th><th>Magasin</th><th>Signalée par</th><th></th></tr></thead>
                <tbody>
                @forelse($anomalies as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>{{ $op->warehouse->name ?? '—' }}</td>
                        <td>{{ $op->inspector->name ?? '—' }}</td>
                        <td><a href="{{ route('dtl.stock-operations.show', $op->id) }}" class="btn btn-ghost btn-sm">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-check"></i><p>Aucune anomalie en cours.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-map-marked-alt"></i> Couverture nationale</h3></div>
        <div class="card-b">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center">
                <div><div style="font-size:26px;font-weight:800;color:var(--green)">{{ $regionsCouvertes }}</div><div style="font-size:11.5px;color:var(--muted)">Régions couvertes</div></div>
                <div><div style="font-size:26px;font-weight:800;color:#B45309">{{ $regionsIncomplete }}</div><div style="font-size:11.5px;color:var(--muted)">Couverture incomplète</div></div>
                <div><div style="font-size:26px;font-weight:800;color:var(--red)">{{ $regionsSansMagasin }}</div><div style="font-size:11.5px;color:var(--muted)">Sans magasin</div></div>
            </div>
            <div class="progress-mini" style="margin-top:16px"><span style="width:{{ $regionsCouvertes > 0 ? round($regionsCouvertes/14*100) : 0 }}%;background:var(--green)"></span></div>
            <div style="font-size:12px;color:var(--muted);margin-top:8px">{{ $regionsCouvertes }}/14 régions avec couverture inspecteur complète</div>
        </div>
    </div>
</div>

@endsection
