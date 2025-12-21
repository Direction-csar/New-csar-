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
                        @if($demande->status == 'en_attente')
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
                            @if($demande->status == 'en_attente')
                                <span class="badge bg-warning fs-6">En Attente</span>
                            @elseif($demande->status == 'approuvee')
                                <span class="badge bg-success fs-6">Approuvée</span>
                            @elseif($demande->status == 'rejetee')
                                <span class="badge bg-danger fs-6">Rejetée</span>
                            @else
                                <span class="badge bg-secondary fs-6">{{ $demande->status ?? 'N/A' }}</span>
                            @endif
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
</div>
@endsection

@section('scripts')
<script>
function approveRequest(id) {
    if (confirm('Êtes-vous sûr de vouloir approuver cette demande ?')) {
        fetch(`/dg/demandes/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: 'approuvee',
                admin_comment: 'Demande approuvée par le DG'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de l\'approbation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de l\'approbation');
        });
    }
}

function rejectRequest(id) {
    const comment = prompt('Raison du rejet (optionnel):');
    if (comment !== null) {
        fetch(`/dg/demandes/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: 'rejetee',
                admin_comment: comment || 'Demande rejetée par le DG'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du rejet');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du rejet');
        });
    }
}
</script>
@endsection





































