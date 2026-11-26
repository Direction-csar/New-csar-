@extends('layouts.stock-portal')

@section('title', 'Tableau de bord — Magasinier')
@section('page-title', 'Tableau de bord du magasin')
@section('breadcrumb', 'Portail Magasinier › Gestion quotidienne')

@section('sidebar-extra')
    <div class="sb-section">Gestion quotidienne</div>
    <a class="sb-link" href="{{ route('magasinier.warehouses.index') }}">
        <i class="fas fa-warehouse"></i><span class="lbl">Mes magasins</span>
    </a>
    <a class="sb-link" href="{{ route('magasinier.transferts.create') }}">
        <i class="fas fa-exchange-alt"></i><span class="lbl">Nouveau transfert</span>
    </a>
    <a class="sb-link" href="{{ route('magasinier.inventaires.create') }}">
        <i class="fas fa-clipboard-list"></i><span class="lbl">Nouvel inventaire</span>
    </a>
    <div class="sb-section">Circuit de contrôle</div>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-operations') && !str_contains($currentRoute ?? '', 'pending') ? 'active' : '' }}" href="{{ route('magasinier.stock-operations.index') }}">
        <i class="fas fa-dolly"></i><span class="lbl">Opérations transmises</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'stock-operations.pending') ? 'active' : '' }}" href="{{ route('magasinier.stock-operations.pending') }}">
        <i class="fas fa-sign-in-alt"></i><span class="lbl">Réceptions à confirmer</span>
    </a>
    <a class="sb-link {{ str_contains($currentRoute ?? '', 'missions') ? 'active' : '' }}" href="{{ route('magasinier.missions.index') }}">
        <i class="fas fa-route"></i><span class="lbl">Missions de sortie</span>
    </a>
@endsection

@section('content')

<div class="kpi-grid">
    <div class="kpi"><div class="ic ic-blue"><i class="fas fa-boxes"></i></div><div><div class="v">{{ number_format($stockTotal, 0, ',', ' ') }}</div><div class="l">Stock officiel (sacs)</div><div class="d">{{ $warehouses->count() }} magasin(s)</div></div></div>
    <div class="kpi"><div class="ic ic-green"><i class="fas fa-sign-in-alt"></i></div><div><div class="v">{{ number_format($stats['entries_today_sacs'], 0, ',', ' ') }}</div><div class="l">Entrées aujourd'hui</div><div class="d" style="color:var(--green)">Sacs</div></div></div>
    <div class="kpi"><div class="ic ic-red"><i class="fas fa-sign-out-alt"></i></div><div><div class="v">{{ number_format($stats['exits_today_sacs'], 0, ',', ' ') }}</div><div class="l">Sorties aujourd'hui</div><div class="d" style="color:var(--red)">Sacs</div></div></div>
    <div class="kpi"><div class="ic ic-orange"><i class="fas fa-dolly"></i></div><div><div class="v">{{ $stockOps['a_executer'] }}</div><div class="l">Opérations à exécuter</div><div class="d" style="color:var(--orange)">Transmises par l'inspecteur</div></div></div>
    <div class="kpi"><div class="ic ic-amber"><i class="fas fa-sign-in-alt"></i></div><div><div class="v">{{ $receptions->count() }}</div><div class="l">Réceptions à confirmer</div><div class="d" style="color:#B45309">Transferts entrants</div></div></div>
    <div class="kpi"><div class="ic ic-night"><i class="fas fa-clock"></i></div><div><div class="v">{{ $enAttente->count() }}</div><div class="l">Mouvements en attente</div><div class="d">Vérification inspecteur</div></div></div>
</div>

<div class="card">
    <div class="card-h"><h3><i class="fas fa-bolt"></i> Actions rapides</h3></div>
    <div class="card-b" style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="{{ route('magasinier.warehouses.index') }}" class="btn btn-orange"><i class="fas fa-plus"></i> Enregistrer une entrée / sortie</a>
        <a href="{{ route('magasinier.transferts.create') }}" class="btn btn-blue"><i class="fas fa-exchange-alt"></i> Effectuer un transfert</a>
        <a href="{{ route('magasinier.inventaires.create') }}" class="btn btn-ghost"><i class="fas fa-clipboard-list"></i> Réaliser un inventaire</a>
        <a href="{{ route('magasinier.stock-operations.index') }}" class="btn btn-ghost"><i class="fas fa-dolly"></i> Consulter les opérations</a>
        <a href="{{ route('magasinier.stock-operations.pending') }}" class="btn btn-ghost"><i class="fas fa-sign-in-alt"></i> Confirmer une réception</a>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-warehouse"></i> Stock par magasin</h3></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Magasin</th><th>Région</th><th>Sacs</th><th>Kg</th><th>Capacité</th></tr></thead>
                <tbody>
                @forelse($balances as $b)
                    @php $w = $b['warehouse']; $bal = $b['balance']; @endphp
                    <tr>
                        <td><b>{{ $w->name }}</b></td>
                        <td>{{ $w->region ?? '—' }}</td>
                        <td>{{ number_format($bal['sacs'], 0, ',', ' ') }}</td>
                        <td>{{ number_format($bal['kg'], 0, ',', ' ') }}</td>
                        <td>
                            @php $pct = $w->capacity > 0 ? round($bal['sacs'] / $w->capacity * 100) : 0; @endphp
                            <div class="progress-mini"><span style="width:{{ min(100, $pct) }}%;background:{{ $pct <= 10 ? 'var(--red)' : ($pct <= 25 ? '#B45309' : 'var(--green)') }}"></span></div>
                            <span style="font-size:11px;color:var(--muted)">{{ $pct }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><i class="fas fa-warehouse"></i><p>Aucun magasin ne vous est rattaché.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-exclamation-triangle"></i> Alertes de stock</h3><span class="badge b-{{ count($alerts) > 0 ? 'red' : 'green' }}">{{ count($alerts) }}</span></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Magasin</th><th>Stock</th><th>Niveau</th></tr></thead>
                <tbody>
                @forelse($alerts as $a)
                    <tr>
                        <td><b>{{ $a['warehouse_name'] }}</b><br><span style="font-size:11px;color:var(--muted)">{{ $a['region'] ?? '' }}</span></td>
                        <td>{{ number_format($a['current_stock'], 0, ',', ' ') }} / {{ number_format($a['capacity'], 0, ',', ' ') }}</td>
                        <td><span class="badge b-{{ $a['severity'] === 'critical' ? 'red' : 'amber' }}">{{ $a['percentage'] }}%</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="empty"><i class="fas fa-check-circle"></i><p>Aucune alerte — tous les stocks sont au-dessus du seuil.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-h"><h3><i class="fas fa-dolly"></i> Opérations transmises par l'inspecteur</h3><a href="{{ route('magasinier.stock-operations.index') }}" class="btn btn-ghost btn-sm">Tout voir</a></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Référence</th><th>Type</th><th>Magasin</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                @forelse($operations as $op)
                    <tr>
                        <td><b>{{ $op->reference ?? ('#'.$op->id) }}</b></td>
                        <td>{{ $op->type_label }}</td>
                        <td>{{ $op->warehouse->name ?? '—' }}@if($op->destinationWarehouse) → {{ $op->destinationWarehouse->name }}@endif</td>
                        <td><span class="badge b-{{ $op->status === 'executee' ? 'amber' : 'orange' }}">{{ $op->status_label }}</span></td>
                        <td><a href="{{ route('magasinier.stock-operations.show', $op->id) }}" class="btn btn-blue btn-sm">{{ $op->status === 'transmise_magasinier' ? 'Exécuter' : 'Voir' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><i class="fas fa-inbox"></i><p>Aucune opération transmise pour le moment.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3><i class="fas fa-sign-in-alt"></i> Réceptions de transferts à confirmer</h3><a href="{{ route('magasinier.stock-operations.pending') }}" class="btn btn-ghost btn-sm">Tout voir</a></div>
        <div style="overflow-x:auto">
            <table class="tbl compact">
                <thead><tr><th>Date</th><th>Magasin</th><th>Qté (sacs)</th><th></th></tr></thead>
                <tbody>
                @forelse($receptions as $m)
                    <tr>
                        <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $m->warehouse->name ?? '—' }}</td>
                        <td>{{ number_format((float) $m->quantity, 0, ',', ' ') }}</td>
                        <td><a href="{{ route('magasinier.stock-operations.pending') }}" class="btn btn-orange btn-sm">Confirmer</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty"><i class="fas fa-check"></i><p>Aucune réception en attente.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-h"><h3><i class="fas fa-history"></i> Derniers mouvements</h3></div>
    <div style="overflow-x:auto">
        <table class="tbl compact">
            <thead><tr><th>Date</th><th>Magasin</th><th>Type</th><th>Quantité</th><th>Motif</th></tr></thead>
            <tbody>
            @forelse($recent as $m)
                <tr>
                    <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->warehouse->name ?? '—' }}</td>
                    <td>{{ $m->type }}</td>
                    <td>{{ number_format((float) $m->quantity, 0, ',', ' ') }}</td>
                    <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $m->reason ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty"><i class="fas fa-inbox"></i><p>Aucun mouvement enregistré.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
