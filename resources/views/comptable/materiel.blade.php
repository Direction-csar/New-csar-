@extends('layouts.direction-portal')

@section('title', 'Matériel - Comptable des matières - CSAR')
@section('page-title', 'Affectations de matériel aux agents')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-lg-top" style="top: 80px;">
            <div class="card-header bg-white"><strong>Nouvelle affectation</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('comptable.materiel.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small mb-1">Matériel <span class="text-danger">*</span></label>
                        <input type="text" name="produit" class="form-control form-control-sm" required maxlength="255" value="{{ old('produit') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Agent bénéficiaire <span class="text-danger">*</span></label>
                        <input type="text" name="agent_name" class="form-control form-control-sm" required maxlength="255" value="{{ old('agent_name') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Magasin</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ (string) old('warehouse_id') === (string) $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <label class="form-label small mb-1">Unité</label>
                            <input type="text" name="unite" class="form-control form-control-sm" maxlength="20" placeholder="pièce" value="{{ old('unite') }}">
                        </div>
                        <div class="col-5">
                            <label class="form-label small mb-1">Quantité</label>
                            <input type="number" name="quantity" step="0.01" min="0.01" class="form-control form-control-sm" value="{{ old('quantity', 1) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small mb-1">N° bon (optionnel)</label>
                        <input type="text" name="numero_bon" class="form-control form-control-sm" maxlength="100" value="{{ old('numero_bon') }}">
                    </div>
                    <button class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Affecter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Statut</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="affectee" {{ request('status') === 'affectee' ? 'selected' : '' }}>Actives</option>
                        <option value="retournee" {{ request('status') === 'retournee' ? 'selected' : '' }}>Retournées</option>
                        <option value="perdue" {{ request('status') === 'perdue' ? 'selected' : '' }}>Perdues</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Recherche</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Agent, matériel, n° bon…" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Matériel</th>
                            <th>Agent</th>
                            <th>Magasin</th>
                            <th>Affecté le</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $a)
                        @php
                            $badge = match($a->status) {
                                'retournee' => ['bg-success', 'Retournée'],
                                'perdue' => ['bg-danger', 'Perdue'],
                                default => ['bg-primary', 'Affectée'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $a->produit }}</strong> ×{{ number_format((float) $a->quantity, 0, ',', ' ') }}
                                @if($a->numero_bon)<br><small class="text-muted">Bon {{ $a->numero_bon }}</small>@endif
                            </td>
                            <td>{{ $a->agent_name }}</td>
                            <td>{{ $a->warehouse?->name ?? '-' }}</td>
                            <td>
                                {{ $a->assigned_at?->format('d/m/Y') }}
                                <br><small class="text-muted">par {{ $a->assigner?->name ?? '-' }}</small>
                                @if($a->return_note)<br><small class="text-muted">{{ $a->return_note }}</small>@endif
                            </td>
                            <td><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td>
                                @if($a->status === 'affectee')
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('comptable.materiel.cloturer', $a->id) }}"
                                          onsubmit="return confirm('Enregistrer le retour de « {{ addslashes($a->produit) }} » ?')">
                                        @csrf
                                        <input type="hidden" name="outcome" value="retournee">
                                        <button class="btn btn-sm btn-outline-success" title="Retour"><i class="fas fa-undo"></i></button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger loss-btn" title="Déclarer perte"
                                            data-id="{{ $a->id }}" data-produit="{{ $a->produit }}">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-hand-holding fa-2x d-block mb-2 opacity-50"></i>
                            <strong>Aucune affectation pour ces filtres</strong>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $assignments->links() }}</div>
        </div></div>
    </div>
</div>

{{-- Modale déclaration de perte --}}
<div class="modal fade" id="lossModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" id="lossForm">
            @csrf
            <input type="hidden" name="outcome" value="perdue">
            <div class="modal-header"><h6 class="modal-title">Déclarer une perte — <span id="lossProduit"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small">Motif de la perte <span class="text-danger">*</span></label>
                <textarea name="return_note" class="form-control" rows="3" required maxlength="1000"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-danger btn-sm">Déclarer la perte</button>
            </div>
        </form>
    </div></div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.loss-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('lossProduit').textContent = btn.dataset.produit;
        document.getElementById('lossForm').action = '{{ url('/comptable/materiel') }}/' + btn.dataset.id + '/cloturer';
        new bootstrap.Modal(document.getElementById('lossModal')).show();
    });
});
</script>
@endsection
