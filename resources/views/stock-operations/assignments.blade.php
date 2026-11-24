@extends('layouts.direction-portal')

@section('title', 'Affectations inspecteurs - CSAR')
@section('page-title', 'Affectations inspecteurs ↔ magasins')

@section('content')
<div class="alert alert-info py-2 small">
    <i class="fas fa-info-circle me-1"></i>
    Chaque inspecteur ne peut consulter et vérifier que les mouvements et opérations des magasins qui lui sont affectés.
    Les révocations sont historisées — aucune affectation n'est supprimée.
</div>

{{-- Matrice de gestion --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i>Gérer les affectations</div>
    <div class="card-body">
        @foreach($inspectors as $insp)
        @php $current = $matrix[$insp->id] ?? []; @endphp
        <form method="POST" action="{{ route($portal . '.inspector-assignments.store') }}" class="border rounded p-3 mb-3">
            @csrf
            <input type="hidden" name="inspector_id" value="{{ $insp->id }}">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <div>
                    <strong>{{ $insp->name }}</strong>
                    <span class="text-muted small ms-2">{{ $insp->email }}</span>
                    <span class="badge bg-secondary ms-2">{{ count($current) }} magasin(s)</span>
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
            </div>
            <div class="row g-2">
                @foreach($warehouses as $w)
                <div class="col-md-4 col-lg-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="warehouse_ids[]"
                               value="{{ $w->id }}" id="w_{{ $insp->id }}_{{ $w->id }}"
                               {{ in_array($w->id, $current) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="w_{{ $insp->id }}_{{ $w->id }}">
                            {{ $w->name }} <span class="text-muted">({{ $w->region }})</span>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </form>
        @endforeach
        @if($inspectors->isEmpty())
            <p class="text-muted mb-0">Aucun inspecteur actif.</p>
        @endif
    </div>
</div>

{{-- Couverture des 14 régions --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="fas fa-map-location-dot me-2 text-primary"></i>Couverture des 14 régions</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Région</th><th>Magasin</th><th>Magasinier(s)</th><th>Inspecteur(s)</th><th>État</th></tr></thead>
            <tbody>
            @foreach($coverage as $reg)
                @php($ok = $reg['status'] === 'COUVERT')
                @forelse($reg['warehouses'] as $w)
                    <tr>
                        @if($loop->first)<td rowspan="{{ count($reg['warehouses']) }}" class="fw-semibold">{{ $reg['region'] }}</td>@endif
                        <td>{{ $w['name'] }}</td>
                        <td>{{ collect($w['magasiniers'])->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td>{{ collect($w['inspecteurs'])->pluck('name')->implode(', ') ?: '—' }}</td>
                        @if($loop->first)<td rowspan="{{ count($reg['warehouses']) }}"><span class="badge bg-{{ $ok ? 'success' : 'warning text-dark' }}">{{ $reg['status'] }}</span></td>@endif
                    </tr>
                @empty
                    <tr><td class="fw-semibold">{{ $reg['region'] }}</td><td colspan="3" class="text-muted">Aucun magasin enregistré</td><td><span class="badge bg-danger">{{ $reg['status'] }}</span></td></tr>
                @endforelse
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Affectations actives --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="fas fa-list-check me-2 text-success"></i>Affectations actives</div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr>
                <th>Inspecteur</th><th>Magasin</th><th>Attribuée par</th><th>Depuis le</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($active ?? [] as $a)
                <tr>
                    <td>{{ $a->inspector?->name }}</td>
                    <td>{{ $a->warehouse?->name }}</td>
                    <td>{{ $a->assignedBy?->name ?? '—' }}</td>
                    <td>{{ $a->created_at?->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route($portal . '.inspector-assignments.revoke', $a->id) }}"
                              onsubmit="return confirm('Révoquer cette affectation ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Révoquer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-3">Aucune affectation active.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Historique des révocations --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold"><i class="fas fa-clock-rotate-left me-2 text-warning"></i>Historique des révocations (50 dernières)</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr>
                <th>Inspecteur</th><th>Magasin</th><th>Révoquée par</th><th>Le</th>
            </tr></thead>
            <tbody>
            @forelse($history as $h)
                <tr>
                    <td>{{ $h->inspector?->name }}</td>
                    <td>{{ $h->warehouse?->name }}</td>
                    <td>{{ $h->revokedBy?->name ?? '—' }}</td>
                    <td>{{ $h->revoked_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-3">Aucune révocation.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
