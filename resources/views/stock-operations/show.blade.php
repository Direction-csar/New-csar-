@extends('layouts.direction-portal')

@section('title', $operation->reference . ' - Opération de stock')
@section('page-title', 'Opération ' . $operation->reference)

@section('content')
@php
    $role = auth()->user()->role;
    $isDg = in_array($role, ['dg','admin','super_admin']);
    $isDtl = $role === 'dtl';
    $isInsp = $role === 'inspecteur';
    $isMag = $role === 'magasinier';
    $base = $direction . '.stock-operations';
    $badge = match($operation->status){ 'verifiee'=>'bg-success','rejetee','annulee'=>'bg-danger','anomalie'=>'bg-warning text-dark','executee'=>'bg-info text-dark','soumise'=>'bg-secondary', default=>'bg-primary' };
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <a href="{{ route($base . '.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    <span class="badge {{ $badge }} fs-6">{{ $operation->status_label }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Détails de l'opération</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Type</dt><dd class="col-sm-8">{{ $operation->type_label }}</dd>
                    <dt class="col-sm-4">Magasin</dt><dd class="col-sm-8">{{ $operation->warehouse?->name }}{{ $operation->destinationWarehouse ? ' → '.$operation->destinationWarehouse->name : '' }}</dd>
                    <dt class="col-sm-4">Produit</dt><dd class="col-sm-8">{{ $operation->produit ?? 'Non précisé' }}{{ $operation->format ? ' ['.$operation->format.($operation->unite ?? '').']' : '' }}</dd>
                    <dt class="col-sm-4">Quantité demandée</dt><dd class="col-sm-8 fw-bold">{{ rtrim(rtrim(number_format((float)$operation->quantity_requested,2,'.',''),'0'),'.') }}{{ $operation->unite ? ' '.$operation->unite : ' contenants' }}</dd>
                    @if($operation->quantity_requested_kg) <dt class="col-sm-4">Soit</dt><dd class="col-sm-8">{{ rtrim(rtrim(number_format((float)$operation->quantity_requested_kg,2,'.',''),'0'),'.') }} {{ $operation->unite ?? 'kg' }}</dd>@endif
                    <dt class="col-sm-4">Quantité exécutée</dt><dd class="col-sm-8 fw-bold">{{ $operation->quantity_executed !== null ? rtrim(rtrim(number_format((float)$operation->quantity_executed,2,'.',''),'0'),'.') : '—' }}</dd>
                    @if($operation->instructions)<dt class="col-sm-4">Instructions DG</dt><dd class="col-sm-8">{{ $operation->instructions }}</dd>@endif
                    @if($operation->dtl_comment)<dt class="col-sm-4">Commentaire DTL</dt><dd class="col-sm-8">{{ $operation->dtl_comment }}</dd>@endif
                    @if($operation->inspector_comment)<dt class="col-sm-4">Consignes inspecteur</dt><dd class="col-sm-8">{{ $operation->inspector_comment }}</dd>@endif
                    @if($operation->anomaly_report)<dt class="col-sm-4 text-warning">Anomalie</dt><dd class="col-sm-8 text-warning">{{ $operation->anomaly_report }}</dd>@endif
                    @if($operation->rejection_reason)<dt class="col-sm-4 text-danger">Motif rejet/annulation</dt><dd class="col-sm-8 text-danger">{{ $operation->rejection_reason }}</dd>@endif
                </dl>
            </div>
        </div>

        {{-- Circuit : qui a fait quoi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Circuit de validation</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-user-tie me-2 text-primary"></i>Instruction créée par</span>
                        <span>{{ $operation->creator?->name }} <small class="text-muted">{{ $operation->created_at?->format('d/m/Y H:i') }}</small></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-sitemap me-2 text-info"></i>DTL → Inspecteur</span>
                        <span>{{ $operation->inspector?->name ?? '—' }} @if($operation->dtl_assigned_at)<small class="text-muted">{{ $operation->dtl_assigned_at->format('d/m/Y H:i') }}</small>@endif</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-truck me-2 text-warning"></i>Inspecteur → Magasinier</span>
                        <span>{{ $operation->magasinier?->name ?? '—' }} @if($operation->inspector_transmitted_at)<small class="text-muted">{{ $operation->inspector_transmitted_at->format('d/m/Y H:i') }}</small>@endif</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-boxes me-2 text-success"></i>Exécutée par</span>
                        <span>{{ $operation->executor?->name ?? '—' }} @if($operation->executed_at)<small class="text-muted">{{ $operation->executed_at->format('d/m/Y H:i') }}</small>@endif</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-check-double me-2 text-success"></i>Vérifiée par</span>
                        <span>{{ $operation->verifier?->name ?? '—' }} @if($operation->verified_at)<small class="text-muted">{{ $operation->verified_at->format('d/m/Y H:i') }}</small>@endif</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Mouvements liés --}}
        @if($operation->movements->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Mouvements enregistrés</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Référence</th><th>Type</th><th>Magasin</th><th class="text-end">Qté</th><th>Statut</th><th>Par</th></tr></thead>
                    <tbody>
                    @foreach($operation->movements as $m)
                        <tr>
                            <td>{{ $m->reference }}</td>
                            <td>{{ $m->type }}</td>
                            <td>{{ $m->warehouse_id === $operation->warehouse_id ? $operation->warehouse?->name : $operation->destinationWarehouse?->name }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format((float)$m->quantity,2,'.',''),'0'),'.') }}</td>
                            <td><span class="badge {{ $m->status==='verifie' ? 'bg-success' : ($m->status==='rejetee' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $m->status }}</span></td>
                            <td>{{ $m->creator?->name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        {{-- Actions selon le rôle et le statut --}}
        @if($isDtl && $operation->status === 'soumise')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-info"><i class="fas fa-user-check me-2"></i>DTL — Affecter un inspecteur</div>
            <div class="card-body">
                <form method="POST" action="{{ route('dtl.stock-operations.assign-inspector', $operation->id) }}">
                    @csrf
                    <label class="form-label">Inspecteur *</label>
                    <select name="assigned_inspector_id" class="form-select mb-2" required>
                        <option value="">— Choisir —</option>
                        @foreach($inspectors as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach
                    </select>
                    <label class="form-label">Commentaire</label>
                    <textarea name="dtl_comment" class="form-control mb-2" rows="2"></textarea>
                    <button class="btn btn-info w-100"><i class="fas fa-paper-plane me-1"></i>Affecter</button>
                </form>
            </div>
        </div>
        @endif

        @if($isInsp && $operation->status === 'affectee_inspecteur')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-primary"><i class="fas fa-truck me-2"></i>Inspecteur — Transmettre au magasinier</div>
            <div class="card-body">
                <form method="POST" action="{{ route('inspecteur.stock-operations.transmit', $operation->id) }}">
                    @csrf
                    <label class="form-label">Magasinier exécutant *</label>
                    <select name="assigned_magasinier_id" class="form-select mb-2" required>
                        <option value="">— Choisir —</option>
                        @foreach($magasiniers as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                    <label class="form-label">Consignes opérationnelles</label>
                    <textarea name="inspector_comment" class="form-control mb-2" rows="3" placeholder="Quantités à contrôler, documents à exiger, zone de stockage…"></textarea>
                    <button class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i>Transmettre</button>
                </form>
            </div>
        </div>
        @endif

        @if($isMag && $operation->status === 'transmise_magasinier')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-success"><i class="fas fa-boxes me-2"></i>Magasinier — Enregistrer l'opération réelle</div>
            <div class="card-body">
                <div class="alert alert-info py-2 small">
                    Demandé : <strong>{{ rtrim(rtrim(number_format((float)$operation->quantity_requested,2,'.',''),'0'),'.') }}</strong> contenants.
                    Le mouvement restera <strong>en attente</strong> jusqu'à vérification de l'inspecteur.
                </div>
                <form method="POST" action="{{ route('magasinier.stock-operations.execute', $operation->id) }}">
                    @csrf
                    <label class="form-label">Quantité réellement exécutée (contenants) *</label>
                    <input type="number" name="quantity_executed" class="form-control mb-2" step="0.01" min="0.01" required>
                    <label class="form-label">Équivalent kg / L</label>
                    <input type="number" name="quantity_executed_kg" class="form-control mb-2" step="0.01" min="0">
                    <label class="form-label">Position / emplacement</label>
                    <input type="text" name="position" class="form-control mb-2" placeholder="Ex : Case A3">
                    <label class="form-label">Observation</label>
                    <textarea name="observation" class="form-control mb-2" rows="2"></textarea>
                    <button class="btn btn-success w-100"><i class="fas fa-check me-1"></i>Enregistrer (en attente de vérification)</button>
                </form>
            </div>
        </div>
        @endif

        @if($isInsp && in_array($operation->status, ['executee','anomalie']))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-warning"><i class="fas fa-clipboard-check me-2"></i>Inspecteur — Vérification</div>
            <div class="card-body">
                <form method="POST" action="{{ route('inspecteur.stock-operations.verify', $operation->id) }}" class="mb-3">
                    @csrf
                    <label class="form-label">Commentaire de vérification</label>
                    <textarea name="inspector_comment" class="form-control mb-2" rows="2"></textarea>
                    <button class="btn btn-success w-100"><i class="fas fa-check-double me-1"></i>Vérifier et intégrer au stock</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('inspecteur.stock-operations.anomaly', $operation->id) }}">
                    @csrf
                    <label class="form-label">Signaler une anomalie</label>
                    <textarea name="anomaly_report" class="form-control mb-2" rows="2" required placeholder="Écart constaté, justificatif manquant…"></textarea>
                    <button class="btn btn-warning w-100"><i class="fas fa-exclamation-triangle me-1"></i>Signaler l'anomalie</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('inspecteur.stock-operations.reject', $operation->id) }}" onsubmit="return confirm('Rejeter définitivement cette opération ?')">
                    @csrf
                    <label class="form-label">Rejeter l'opération</label>
                    <input type="text" name="rejection_reason" class="form-control mb-2" required placeholder="Motif du rejet">
                    <button class="btn btn-outline-danger w-100"><i class="fas fa-times me-1"></i>Rejeter</button>
                </form>
            </div>
        </div>
        @endif

        @if($isDg && !in_array($operation->status, ['verifiee','rejetee','annulee']))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold text-danger"><i class="fas fa-ban me-2"></i>Direction — Annulation</div>
            <div class="card-body">
                <form method="POST" action="{{ route('dg.stock-operations.cancel', $operation->id) }}" onsubmit="return confirm('Annuler cette opération ?')">
                    @csrf
                    <input type="text" name="reason" class="form-control mb-2" placeholder="Motif (optionnel)">
                    <button class="btn btn-outline-danger w-100"><i class="fas fa-ban me-1"></i>Annuler l'opération</button>
                </form>
            </div>
        </div>
        @endif

        @if(($isDtl || $isDg) && in_array($operation->status, ['soumise','affectee_inspecteur','anomalie']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold text-danger"><i class="fas fa-times me-2"></i>Rejet</div>
            <div class="card-body">
                <form method="POST" action="{{ route($direction . '.stock-operations.reject', $operation->id) }}" onsubmit="return confirm('Rejeter cette opération ?')">
                    @csrf
                    <input type="text" name="rejection_reason" class="form-control mb-2" required placeholder="Motif du rejet">
                    <button class="btn btn-outline-danger w-100">Rejeter</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
