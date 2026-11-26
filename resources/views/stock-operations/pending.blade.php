@extends('layouts.direction-portal')

@section('title', 'Mouvements en attente - CSAR')
@section('page-title', 'Mouvements en attente de vérification')

@section('content')
@php
    $typeLabels = ['entry'=>'Entrée','exit'=>'Sortie','in'=>'Entrée','out'=>'Sortie','transfer'=>'Transfert','adjustment'=>'Ajustement','report'=>'Report'];
    $isMagasinier = auth()->user()->role === 'magasinier';
@endphp

<div class="alert alert-info py-2 small">
    <i class="fas fa-info-circle me-1"></i>
    Tout mouvement saisi (Web ou APK, même hors connexion) reste <strong>en attente</strong> et ne modifie
    pas le stock officiel tant qu'il n'est pas vérifié. Les ajustements/inventaires sont réservés à la DTL et à la DG.
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold"><i class="fas fa-clock me-2 text-warning"></i>Mouvements en attente ({{ $rows->count() }})</span>
        <form method="GET" class="d-flex gap-2">
            <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Tous les magasins</option>
                @foreach($warehouses as $w)
                <option value="{{ $w->id }}" @selected(request('warehouse_id')==$w->id)>{{ $w->name }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Tous types</option>
                @foreach($typeLabels as $k=>$v)
                <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Référence</th><th>Type</th><th>Magasin</th><th class="text-end">Quantité</th>
                        <th>Motif</th><th>Saisi par</th><th>Date</th><th>État</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    @php $m = $row['movement']; $meta = json_decode($m->metadata ?? '{}', true) ?: []; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $m->reference }}</td>
                        <td>
                            {{ $typeLabels[$m->type] ?? $m->type }}
                            @if($row['is_adjustment'])<span class="badge bg-warning text-dark ms-1">Ajustement</span>@endif
                            @if($row['is_transfer_in'])<span class="badge bg-info text-dark ms-1">Réception transfert</span>@endif
                        </td>
                        <td>{{ $m->warehouse?->name ?? '—' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format((float)$m->quantity, 2, '.', ''), '0'), '.') }}</td>
                        <td class="small">{{ \Illuminate\Support\Str::limit($m->reason, 60) }}</td>
                        <td class="small">{{ $m->creator?->name ?? '—' }}</td>
                        <td class="small">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($row['is_transfer_in'] && !$row['receipt_confirmed'])
                                <span class="badge bg-secondary">Réception à confirmer</span>
                            @else
                                <span class="badge bg-warning text-dark">En attente</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($row['can_confirm'])
                            <form method="POST" action="{{ route($direction . '.stock-operations.pending.confirm', $m->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Confirmer réception</button>
                            </form>
                            @endif
                            @if($row['can_verify'])
                            <form method="POST" action="{{ route($direction . '.stock-operations.pending.verify', $m->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-primary"><i class="fas fa-check-double me-1"></i>Vérifier</button>
                            </form>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reject{{ $m->id }}"><i class="fas fa-times"></i></button>
                            @elseif(!$isMagasinier && $row['is_transfer_in'] && !$row['receipt_confirmed'])
                            <span class="text-muted small">En attente réception magasin</span>
                            @endif
                        </td>
                    </tr>
                    @if($row['can_verify'] || (!$isMagasinier && !$row['is_adjustment']))
                    <div class="modal fade" id="reject{{ $m->id }}" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <form method="POST" action="{{ route($direction . '.stock-operations.pending.reject', $m->id) }}" class="modal-content">
                                @csrf
                                <div class="modal-header"><h6 class="modal-title">Rejeter {{ $m->reference }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="text" name="rejection_reason" class="form-control" placeholder="Motif du rejet *" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button class="btn btn-sm btn-danger">Rejeter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Aucun mouvement en attente.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
