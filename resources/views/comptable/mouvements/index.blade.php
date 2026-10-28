@extends('layouts.direction-portal')

@section('title', 'Validation des mouvements - Comptable des matières - CSAR')
@section('page-title', 'Validation comptable des mouvements de stock')

@section('content')
@if(!$validationEnabled)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Les colonnes de validation (<code>status</code>, <code>validated_by</code>, <code>validated_at</code>) sont absentes de
    <code>stock_movements</code>. Exécutez les migrations pour activer la validation comptable.
</div>
@endif

@if($validationEnabled)
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-warning">{{ $stats['draft'] }}</h3>
            <small class="text-muted">En attente de validation</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-success">{{ $stats['validated'] }}</h3>
            <small class="text-muted">Validés</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-danger">{{ $stats['rejected'] }}</h3>
            <small class="text-muted">Rejetés</small>
        </div></div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Statut</label>
            <select name="status" class="form-select form-select-sm">
                <option value="draft" {{ request('status', 'draft') === 'draft' ? 'selected' : '' }}>En attente</option>
                <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validés</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Magasin</label>
            <select name="warehouse_id" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($warehouses as $w)
                <option value="{{ $w->id }}" {{ (string) request('warehouse_id') === (string) $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Type</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Tous</option>
                <option value="entry" {{ request('type') === 'entry' ? 'selected' : '' }}>Entrées</option>
                <option value="exit" {{ request('type') === 'exit' ? 'selected' : '' }}>Sorties</option>
                <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transferts</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Ajustements</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Lien</label>
            <select name="linked" class="form-select form-select-sm">
                <option value="">Tous</option>
                <option value="mission" {{ request('linked') === 'mission' ? 'selected' : '' }}>Liés à une mission</option>
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
        </div>
    </form>
    <div class="text-end mt-2">
        <a href="{{ route('comptable.mouvements.export', request()->only(['status','warehouse_id','type','linked','from','to','q'])) }}"
           class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-csv me-1"></i> Exporter en CSV
        </a>
    </div>
</div></div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Référence</th>
                        <th>Magasin</th>
                        <th>Type</th>
                        <th class="text-end">Quantité</th>
                        <th>Saisi par</th>
                        <th>Mission</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mouvements as $m)
                    @php
                        $meta = json_decode($m->metadata ?? '{}', true);
                        $sacs = $meta['entry_sacs'] ?? $meta['exit_sacs'] ?? $m->quantity;
                        $kg   = $meta['entry_kg'] ?? $meta['exit_kg'] ?? null;
                        $statusLabel = match($m->status ?? 'draft') {
                            'validated' => ['bg-success', 'Validé'],
                            'rejected'  => ['bg-danger', 'Rejeté'],
                            default     => ['bg-warning text-dark', 'En attente'],
                        };
                    @endphp
                    <tr>
                        <td><code>{{ $m->reference }}</code></td>
                        <td>{{ $m->warehouse?->name ?? '-' }}</td>
                        <td>{{ $m->type_formatted ?? ucfirst($m->type) }}</td>
                        <td class="text-end">{{ number_format((float) $sacs, 0, ',', ' ') }}@if($kg) <small class="text-muted">/ {{ number_format((float) $kg, 0, ',', ' ') }} kg</small>@endif</td>
                        <td>{{ $m->creator?->name ?? '-' }}</td>
                        <td>
                            @if($m->request_mission_id)
                                <span class="badge bg-info"><i class="fas fa-link me-1"></i>#{{ $m->request_mission_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        <td><span class="badge {{ $statusLabel[0] }}">{{ $statusLabel[1] }}</span></td>
                        <td>
                            <a href="{{ route('comptable.mouvements.show', $m->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-exchange-alt fa-2x d-block mb-2 opacity-50"></i>
                            <strong>Aucun mouvement à afficher</strong><br>
                            <small>Modifiez les filtres ou attendez de nouvelles écritures de stock.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $mouvements->links() }}</div>
    </div>
</div>
@endsection
