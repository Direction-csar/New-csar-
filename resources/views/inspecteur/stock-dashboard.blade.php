@extends('layouts.stock-portal')

@section('title', 'Gestion du contrôle — Inspecteur')
@section('page-title', 'Tableau de bord du contrôle')
@section('breadcrumb', 'CSAR — Gestion du contrôle › Mon périmètre')

@section('sidebar-extra')
    <div class="sb-section">Contrôle des stocks</div>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-operations') && !str_contains($currentRoute ?? '', 'pending') ? 'active' : '' }}" href="{{ route('inspecteur.stock-operations.index') }}">
        <i class="fas fa-dolly"></i><span class="lbl">Opérations affectées</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'missions') ? 'active' : '' }}" href="{{ route('inspecteur.missions.index') }}">
        <i class="fas fa-route"></i><span class="lbl">Missions terrain</span>
    </a>
@endsection

@section('content')

<div class="alert-strip alert-info">
    <i class="fas fa-shield-alt"></i>
    <div><b>Périmètre de contrôle :</b> vous ne visualisez que les magasins qui vous sont affectés. Toute tentative d'accès à un autre périmètre est bloquée côté serveur.</div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div class="ic ic-blue"><i class="fas fa-warehouse"></i></div><div><div class="v">{{ number_format($stats['magasins'], 0, ',', ' ') }}</div><div class="l">Magasins affectés</div><div class="d" style="color:var(--green)">Mon périmètre</div></div></div>
    <div class="kpi"><div class="ic ic-night"><i class="fas fa-clipboard-list"></i></div><div><div class="v">{{ number_format($stats['ops_reçues'], 0, ',', ' ') }}</div><div class="l">Opérations reçues</div><div class="d">Cumulées</div></div></div>
    <div class="kpi"><div class="ic ic-orange"><i class="fas fa-tasks"></i></div><div><div class="v">{{ number_format($stats['a_traiter'], 0, ',', ' ') }}</div><div class="l">Contrôles à effectuer</div><div class="d" style="color:var(--orange)">En cours</div></div></div>
    <div class="kpi"><div class="ic ic-amber"><i class="fas fa-clock"></i></div><div><div class="v">{{ $mouvements->count() }}</div><div class="l">Mouvements en attente</div><div class="d" style="color:#B45309">À vérifier</div></div></div>
    <div class="kpi"><div class="ic ic-red"><i class="fas fa-exclamation-triangle"></i></div><div><div class="v">{{ number_format($stats['anomalies'], 0, ',', ' ') }}</div><div class="l">Anomalies signalées</div><div class="d" style="color:var(--red)">Sur mon périmètre</div></div></div>
    <div class="kpi"><div class="ic ic-green"><i class="fas fa-check-double"></i></div><div><div class="v">{{ number_format($stats['verifiees'], 0, ',', ' ') }}</div><div class="l">Opérations vérifiées</div><div class="d" style="color:var(--green)">Validées</div></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-warehouse"></i> Mes magasins affectés</h3><span class="badge b-blue">{{ $warehouses->count() }} magasin(s)</span></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Magasin</th><th>Région</th><th>Stock actuel</th><th>Magasinier</th></tr></thead>
                <tbody>
                @forelse($warehouses as $w)
                    @php $mag = $magasiniers->firstWhere('warehouse_id', $w->id); @endphp
                    <tr>
                        <td><b>{{ $w->name }}</b></td>
                        <td>{{ $w->region ?? '—' }}</td>
                        <td>{{ number_format((float) $w->current_stock, 0, ',', ' ') }}</td>
                        <td>{{ $mag->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-map-marker-alt"></i><p>Aucun magasin ne vous est affecté pour le moment.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-tasks"></i> Opérations à traiter</h3><a href="{{ route('inspecteur.stock-operations.index') }}" class="btn btn-ghost btn-sm">Tout voir</a></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Référence</th><th>Type</th><th>Magasin</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                @forelse($aTraiter as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>{{ $op->type_label }}</td>
                        <td>{{ $op->warehouse->name ?? '—' }}@if($op->destinationWarehouse) → {{ $op->destinationWarehouse->name }}@endif</td>
                        <td><span class="badge b-{{ $op->status === 'anomalie' ? 'red' : ($op->status === 'executee' ? 'amber' : 'blue') }}">{{ $op->status_label }}</span></td>
                        <td><a href="{{ route('inspecteur.stock-operations.show', $op->id) }}" class="btn btn-blue btn-sm">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><i class="fas fa-check-circle"></i><p>Aucune opération en attente de contrôle.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid-2e">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-clock"></i> Mouvements en attente de vérification</h3></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Date</th><th>Magasin</th><th>Type</th><th>Qté</th></tr></thead>
                <tbody>
                @forelse($mouvements as $m)
                    <tr>
                        <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $m->warehouse->name ?? '—' }}</td>
                        <td>{{ $m->type }}</td>
                        <td>{{ number_format((float) $m->quantity, 0, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-check"></i><p>Aucun mouvement en attente.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-history"></i> Historique des contrôles</h3></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Référence</th><th>Magasin</th><th>Résultat</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($historiques as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>{{ $op->warehouse->name ?? '—' }}</td>
                        <td><span class="badge b-{{ $op->status === 'verifiee' ? 'green' : 'red' }}">{{ $op->status_label }}</span></td>
                        <td>{{ $op->verified_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-inbox"></i><p>Aucun contrôle terminé.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
