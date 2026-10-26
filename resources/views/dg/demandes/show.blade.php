@extends('layouts.dg-modern')

@section('title', 'Détails de la Demande')
@section('page-title', 'Détails de la Demande - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-eye me-2 text-primary"></i>
                            Détails de la Demande
                        </h1>
                        <p class="text-muted mb-0 small">
                            Consultation détaillée - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dg.demandes.index') }}" class="btn btn-outline-primary btn-modern btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                        @if($demande->workflow_status === 'scannee')
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="approveRequest({{ $demande->id }})">
                            <i class="fas fa-check me-1"></i>Approuver
                        </button>
                        <button class="btn btn-danger-modern btn-modern btn-sm" onclick="rejectRequest({{ $demande->id }})">
                            <i class="fas fa-times me-1"></i>Rejeter
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de la demande -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Informations Générales
                </h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Code de Suivi</label>
                        <div class="p-2 bg-light rounded">
                            <span class="badge bg-primary fs-6">{{ $demande->tracking_code ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Statut</label>
                        <div class="p-2">
                            {!! $demande->workflow_status_badge !!}
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Type de Demande</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-tag me-2"></i>{{ ucfirst(str_replace('_', ' ', $demande->type ?? 'N/A')) }}
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Urgence</label>
                        <div class="p-2">
                            @if($demande->urgency == 'haute')
                                <span class="badge bg-danger fs-6">Haute</span>
                            @elseif($demande->urgency == 'moyenne')
                                <span class="badge bg-warning fs-6">Moyenne</span>
                            @elseif($demande->urgency == 'basse')
                                <span class="badge bg-success fs-6">Basse</span>
                            @else
                                <span class="badge bg-secondary fs-6">{{ $demande->urgency ?? 'N/A' }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Objet</label>
                        <div class="p-2 bg-light rounded">
                            {{ $demande->subject ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <div class="p-2 bg-light rounded" style="min-height: 100px;">
                            {{ $demande->description ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-user me-2 text-success"></i>
                    Informations du Demandeur
                </h6>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nom Complet</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-user me-2"></i>{{ $demande->full_name ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-envelope me-2"></i>{{ $demande->email ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Téléphone</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-phone me-2"></i>{{ $demande->phone ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Adresse</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-map-marker-alt me-2"></i>{{ $demande->address ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Région</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-globe me-2"></i>{{ $demande->region ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Contact Préféré</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-comments me-2"></i>{{ ucfirst($demande->preferred_contact ?? 'N/A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates et Traitement -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-calendar me-2 text-info"></i>
                    Dates
                </h6>

                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Demande</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-plus me-2"></i>{{ \Carbon\Carbon::parse($demande->request_date ?? $demande->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>

                @if($demande->processed_date)
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Traitement</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-check me-2"></i>{{ \Carbon\Carbon::parse($demande->processed_date)->format('d/m/Y H:i') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-cogs me-2 text-warning"></i>
                    Traitement
                </h6>

                @if($demande->assigned_to)
                <div class="mb-3">
                    <label class="form-label fw-bold">Assigné à</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-user-tie me-2"></i>{{ $demande->user->name ?? 'Utilisateur #' . $demande->assigned_to }}
                    </div>
                </div>
                @endif

                @if($demande->admin_comment)
                <div class="mb-3">
                    <label class="form-label fw-bold">Commentaire Admin</label>
                    <div class="p-2 bg-light rounded" style="min-height: 80px;">
                        <i class="fas fa-comment me-2"></i>{{ $demande->admin_comment }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Documents : signature et scan -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-file-signature me-2 text-primary"></i>
                    Documents
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded mb-2">
                            <strong>Document signé :</strong>
                            @if($demande->dg_signature_file)
                                <span class="badge bg-success ms-1">Déposé</span>
                            @else
                                <span class="badge bg-secondary ms-1">Non déposé</span>
                            @endif
                        </div>
                        @if(!in_array($demande->workflow_status, ['signee', 'scannee', 'validee_dg', 'approuvee', 'rejetee', 'cloturee'], true))
                        <form method="POST" action="{{ route('dg.demandes.signature', $demande->id) }}" enctype="multipart/form-data" class="doc-form">
                            @csrf
                            <label for="signature_file" class="form-label fw-bold">Déposer le document signé</label>
                            <input type="file" id="signature_file" name="signature_file" class="form-control mb-2" accept=".pdf,.png,.jpg,.jpeg" required>
                            <button type="submit" class="btn btn-primary-modern btn-modern btn-sm">
                                <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                                Enregistrer la signature
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded mb-2">
                            <strong>Scan :</strong>
                            @if($demande->scan_file)
                                <span class="badge bg-success ms-1">Déposé</span>
                            @else
                                <span class="badge bg-secondary ms-1">Non déposé</span>
                            @endif
                        </div>
                        @if($demande->workflow_status === 'signee')
                        <form method="POST" action="{{ route('dg.demandes.scan', $demande->id) }}" enctype="multipart/form-data" class="doc-form">
                            @csrf
                            <label for="scan_file" class="form-label fw-bold">Déposer le scan</label>
                            <input type="file" id="scan_file" name="scan_file" class="form-control mb-2" accept=".pdf,.png,.jpg,.jpeg" required>
                            <button type="submit" class="btn btn-primary-modern btn-modern btn-sm">
                                <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                                Enregistrer le scan
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modale de rejet -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Rejeter la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <label for="rejectReason" class="form-label fw-bold">Motif du rejet <span class="text-danger">*</span></label>
                    <textarea id="rejectReason" class="form-control" rows="4" maxlength="1000" required aria-describedby="rejectReasonHelp"></textarea>
                    <div id="rejectReasonHelp" class="form-text">Minimum 10 caractères. Le motif est conservé dans l'historique du dossier.</div>
                    <div class="invalid-feedback" id="rejectReasonError">Le motif est obligatoire (10 caractères minimum).</div>
                    <div id="rejectServerError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmit">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true" id="rejectSpinner"></span>
                        Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modale d'approbation -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approuver la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <label for="approveComment" class="form-label fw-bold">Commentaire d'approbation <span class="text-muted fw-normal">(optionnel)</span></label>
                    <textarea id="approveComment" class="form-control" rows="4" maxlength="1000" aria-describedby="approveCommentHelp"></textarea>
                    <div id="approveCommentHelp" class="form-text">Si vide, le commentaire par défaut « Demande approuvée par la Directrice » sera enregistré.</div>
                    <div id="approveServerError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" id="approveSubmit">
                        <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true" id="approveSpinner"></span>
                        Confirmer l'approbation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const approveModalEl = document.getElementById('approveModal');
const approveModal = bootstrap.Modal.getOrCreateInstance(approveModalEl);
const approveForm = document.getElementById('approveForm');
const approveComment = document.getElementById('approveComment');
const approveSubmit = document.getElementById('approveSubmit');
const approveSpinner = document.getElementById('approveSpinner');
const approveServerError = document.getElementById('approveServerError');
let approveTargetId = null;
let approveSubmitting = false;

function setApproveBusy(busy) {
    approveSubmitting = busy;
    approveSubmit.disabled = busy;
    approveComment.disabled = busy;
    approveSpinner.classList.toggle('d-none', !busy);
}

function approveRequest(id) {
    approveTargetId = id;
    approveComment.value = '';
    approveServerError.classList.add('d-none');
    setApproveBusy(false);
    approveModal.show();
    approveModalEl.addEventListener('shown.bs.modal', () => approveComment.focus(), { once: true });
}

approveForm.addEventListener('submit', function (event) {
    event.preventDefault();
    if (approveSubmitting) return;

    const comment = approveComment.value.trim();
    approveServerError.classList.add('d-none');
    setApproveBusy(true);

    fetch(`/dg/demandes/${approveTargetId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            statut: 'approuvee',
            commentaire_admin: comment || 'Demande approuvée par la Directrice'
        })
    })
    .then(response => response.json().catch(() => ({})).then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            location.reload();
            return;
        }
        approveServerError.textContent = data.message || 'L\'approbation n\'a pas pu être enregistrée.';
        approveServerError.classList.remove('d-none');
        setApproveBusy(false);
    })
    .catch(() => {
        approveServerError.textContent = 'Erreur de connexion. Veuillez réessayer.';
        approveServerError.classList.remove('d-none');
        setApproveBusy(false);
    });
});

const MIN_REJECT_LENGTH = 10;
const rejectModalEl = document.getElementById('rejectModal');
const rejectModal = bootstrap.Modal.getOrCreateInstance(rejectModalEl);
const rejectForm = document.getElementById('rejectForm');
const rejectReason = document.getElementById('rejectReason');
const rejectSubmit = document.getElementById('rejectSubmit');
const rejectSpinner = document.getElementById('rejectSpinner');
const rejectServerError = document.getElementById('rejectServerError');
let rejectTargetId = null;
let rejectSubmitting = false;

function setRejectBusy(busy) {
    rejectSubmitting = busy;
    rejectSubmit.disabled = busy;
    rejectReason.disabled = busy;
    rejectSpinner.classList.toggle('d-none', !busy);
}

function rejectRequest(id) {
    rejectTargetId = id;
    rejectReason.value = '';
    rejectReason.classList.remove('is-invalid');
    rejectServerError.classList.add('d-none');
    setRejectBusy(false);
    rejectModal.show();
    rejectModalEl.addEventListener('shown.bs.modal', () => rejectReason.focus(), { once: true });
}

rejectForm.addEventListener('submit', function (event) {
    event.preventDefault();
    if (rejectSubmitting) return;

    const reason = rejectReason.value.trim();
    if (reason.length < MIN_REJECT_LENGTH) {
        rejectReason.classList.add('is-invalid');
        rejectReason.focus();
        return;
    }
    rejectReason.classList.remove('is-invalid');
    rejectServerError.classList.add('d-none');
    setRejectBusy(true);

    fetch(`/dg/demandes/${rejectTargetId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ statut: 'rejetee', commentaire_admin: reason })
    })
    .then(response => response.json().catch(() => ({})).then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            location.reload();
            return;
        }
        rejectServerError.textContent = data.message || 'Le rejet n\'a pas pu être enregistré.';
        rejectServerError.classList.remove('d-none');
        setRejectBusy(false);
    })
    .catch(() => {
        rejectServerError.textContent = 'Erreur de connexion. Veuillez réessayer.';
        rejectServerError.classList.remove('d-none');
        setRejectBusy(false);
    });
});

document.querySelectorAll('form.doc-form').forEach(form => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.querySelector('.spinner-border').classList.remove('d-none');
    });
});
</script>
@endsection








































