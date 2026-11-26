@extends('layouts.direction-portal')

@section('title', 'Remboursement ' . $reimb->reference . ' - Comptable - CSAR')
@section('page-title', 'Demande ' . $reimb->reference)

@section('content')
@include('comptable.carburant._nav', ['tab' => 'reimbs'])

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger py-2">@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $reimb->reference }}</strong>
                @include('comptable.carburant._status', ['status' => $reimb->status])
            </div>
            <div class="card-body">
                @if($reimb->duplicate_of_ref)
                <div class="alert alert-warning py-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Doublon potentiel avec la demande <strong>{{ $reimb->duplicate_of_ref }}</strong> — vérifier avant approbation.
                </div>
                @endif
                <dl class="row mb-0">
                    <dt class="col-sm-4">Agent</dt><dd class="col-sm-8">{{ $reimb->agent_name }} {{ $reimb->service ? '— ' . $reimb->service : '' }}</dd>
                    <dt class="col-sm-4">Date de dépense</dt><dd class="col-sm-8">{{ $reimb->expense_date?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Motif</dt><dd class="col-sm-8">{{ $reimb->purpose }}</dd>
                    <dt class="col-sm-4">Activité</dt><dd class="col-sm-8">{{ $reimb->activity_type ?? '-' }} {{ $reimb->activity_ref ? '(' . $reimb->activity_ref . ')' : '' }}</dd>
                    <dt class="col-sm-4">Véhicule</dt><dd class="col-sm-8">{{ $reimb->vehicle_label ?? '-' }} {{ $reimb->vehicle_plate ? '(' . $reimb->vehicle_plate . ')' : '' }}</dd>
                    <dt class="col-sm-4">Reçu</dt><dd class="col-sm-8">{{ $reimb->receipt_ref ?? '-' }}</dd>
                    @if($reimb->quantity_liters)<dt class="col-sm-4">Litres</dt><dd class="col-sm-8">{{ number_format($reimb->quantity_liters, 2, ',', ' ') }} L</dd>@endif
                    <dt class="col-sm-4">Montant déclaré</dt><dd class="col-sm-8">{{ number_format($reimb->amount_claimed, 0, ',', ' ') }} FCFA</dd>
                    <dt class="col-sm-4">Montant demandé</dt><dd class="col-sm-8"><strong>{{ number_format($reimb->amount_requested, 0, ',', ' ') }} FCFA</strong></dd>
                    <dt class="col-sm-4">Montant approuvé</dt><dd class="col-sm-8">{{ $reimb->amount_approved !== null ? number_format($reimb->amount_approved, 0, ',', ' ') . ' FCFA' : '—' }}</dd>
                    <dt class="col-sm-4">Montant payé</dt><dd class="col-sm-8">{{ $reimb->amount_paid !== null ? number_format($reimb->amount_paid, 0, ',', ' ') . ' FCFA' : '—' }}
                        @if($reimb->payment_ref)({{ $reimb->payment_ref }}, {{ $reimb->payment_date?->format('d/m/Y') }})@endif</dd>
                    @if($reimb->advance_amount !== null)
                    <dt class="col-sm-4">Avance</dt><dd class="col-sm-8">{{ number_format($reimb->advance_amount, 0, ',', ' ') }} FCFA</dd>
                    <dt class="col-sm-4">Justifié</dt><dd class="col-sm-8">{{ number_format($reimb->justified_amount ?? 0, 0, ',', ' ') }} FCFA
                        (reste : {{ number_format($reimb->remainingToJustify(), 0, ',', ' ') }} FCFA)</dd>
                    <dt class="col-sm-4">Complément</dt><dd class="col-sm-8">
                        @if($reimb->complement_amount !== null)
                            {{ number_format($reimb->complement_amount, 0, ',', ' ') }} FCFA
                            {{ $reimb->complement_amount < 0 ? '(reliquat restitué)' : '(complément payé)' }}
                        @else — @endif</dd>
                    @endif
                    @if($reimb->review_comment)<dt class="col-sm-4">Observation</dt><dd class="col-sm-8">{{ $reimb->review_comment }}</dd>@endif
                    @if($reimb->rejection_reason)<dt class="col-sm-4">Motif du rejet</dt><dd class="col-sm-8 text-danger">{{ $reimb->rejection_reason }}</dd>@endif
                    <dt class="col-sm-4">Créé par</dt><dd class="col-sm-8">{{ $reimb->creator?->name }} — {{ $reimb->created_at?->format('d/m/Y H:i') }}</dd>
                    @if($reimb->reviewer)<dt class="col-sm-4">Instruit par</dt><dd class="col-sm-8">{{ $reimb->reviewer->name }}</dd>@endif
                    @if($reimb->approver)<dt class="col-sm-4">Approuvé par</dt><dd class="col-sm-8">{{ $reimb->approver->name }} — {{ $reimb->approved_at?->format('d/m/Y H:i') }}</dd>@endif
                    @if($reimb->payer)<dt class="col-sm-4">Payé par</dt><dd class="col-sm-8">{{ $reimb->payer->name }} — {{ $reimb->paid_at?->format('d/m/Y H:i') }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><strong>Pièces justificatives</strong></div>
            <div class="card-body">
                @if(in_array($reimb->status, ['draft', 'submitted']))
                <form method="POST" action="{{ route('comptable.carburant.attachments.store', ['type' => 'reimb', 'id' => $reimb->id]) }}" enctype="multipart/form-data" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-5"><input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <div class="col-md-4">
                        <select name="kind" class="form-select form-select-sm">
                            <option value="recu">Reçu</option>
                            <option value="facture">Facture</option>
                            <option value="ordre_mission">Ordre de mission</option>
                            <option value="justificatif">Justificatif</option>
                        </select>
                    </div>
                    <div class="col-md-3"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-upload me-1"></i>Ajouter</button></div>
                </form>
                @endif
                <ul class="list-group list-group-flush">
                    @forelse($reimb->attachments as $a)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas {{ $a->isImage() ? 'fa-image' : 'fa-file-pdf' }} me-1"></i>{{ $a->original_name }}
                            <small class="text-muted">({{ $a->sizeHuman() }}, {{ $a->uploader?->name }})</small></span>
                        <span class="d-flex gap-1">
                            <a href="{{ route('comptable.carburant.attachments.download', $a->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i></a>
                            @if(in_array($reimb->status, ['draft', 'submitted']))
                            <form method="POST" action="{{ route('comptable.carburant.attachments.delete', $a->id) }}" onsubmit="return confirm('Supprimer cette pièce ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted px-0">Aucune pièce jointe.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><strong>Historique</strong></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($reimb->statusLogs as $log)
                    <li class="list-group-item px-0 small">
                        <i class="fas fa-history me-1 text-muted"></i>
                        {{ $log->created_at?->format('d/m/Y H:i') }} — <strong>{{ $log->action }}</strong>
                        ({{ $log->from_status ?? '—' }} → {{ $log->to_status }})
                        @if($log->reason)<br><span class="text-muted">Motif : {{ $log->reason }}</span>@endif
                    </li>
                    @empty
                    <li class="list-group-item text-muted px-0">Aucun événement.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Actions</strong></div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('comptable.carburant.pdf', ['type' => 'reimb', 'id' => $reimb->id]) }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf me-1"></i>PDF de la demande</a>

                @if($reimb->status === 'draft')
                <form method="POST" action="{{ route('comptable.carburant.reimbs.submit', $reimb->id) }}">
                    @csrf
                    <button class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i>Soumettre pour instruction</button>
                </form>
                @endif

                @if($reimb->status === 'submitted')
                <form method="POST" action="{{ route('comptable.carburant.reimbs.review', $reimb->id) }}">
                    @csrf
                    <input type="text" name="comment" class="form-control form-control-sm mb-1" placeholder="Observation (optionnel)">
                    <button class="btn btn-warning w-100"><i class="fas fa-search me-1"></i>Prendre en instruction</button>
                </form>
                @endif

                @if(in_array($reimb->status, ['submitted', 'in_review']))
                    @if($canDecide)
                    <form method="POST" action="{{ route('comptable.carburant.reimbs.approve', $reimb->id) }}">
                        @csrf
                        <label class="form-label small mb-1">Montant approuvé (FCFA)</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $reimb->amount_requested }}" name="amount_approved" class="form-control form-control-sm mb-1" value="{{ $reimb->amount_requested }}" required>
                        <button class="btn btn-success w-100"><i class="fas fa-check me-1"></i>Approuver</button>
                    </form>
                    @else
                    <div class="alert alert-light border small mb-0 py-1">
                        L'approbation financière est réservée au rôle <strong>admin</strong>.
                    </div>
                    @endif
                    <form method="POST" action="{{ route('comptable.carburant.reimbs.reject', $reimb->id) }}">
                        @csrf
                        <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Motif du rejet (obligatoire)" required>
                        <button class="btn btn-outline-danger w-100"><i class="fas fa-times me-1"></i>Rejeter</button>
                    </form>
                @endif

                @if($reimb->status === 'approved')
                    @if($canDecide)
                    <form method="POST" action="{{ route('comptable.carburant.reimbs.pay', $reimb->id) }}">
                        @csrf
                        <label class="form-label small mb-1">Référence de paiement</label>
                        <input type="text" name="payment_ref" class="form-control form-control-sm mb-1" placeholder="N° virement / caisse" required>
                        <label class="form-label small mb-1">Date de paiement</label>
                        <input type="date" name="payment_date" class="form-control form-control-sm mb-1" value="{{ now()->format('Y-m-d') }}" required>
                        <button class="btn btn-success w-100"><i class="fas fa-money-bill me-1"></i>Enregistrer le paiement</button>
                    </form>
                    @else
                    <div class="alert alert-light border small mb-0 py-1">
                        Enregistrement du paiement réservé au rôle <strong>admin</strong>.
                    </div>
                    @endif
                @endif

                @if($reimb->status === 'paid')
                <form method="POST" action="{{ route('comptable.carburant.reimbs.close', $reimb->id) }}">
                    @csrf
                    <button class="btn btn-dark w-100"><i class="fas fa-archive me-1"></i>Clôturer le dossier</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
