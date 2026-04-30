@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Détails du Message')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-envelope me-2"></i>Détails du Message
                    </h1>
                    <p class="text-muted mb-0">Informations complètes du message</p>
                </div>
                <div>
                    <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary-modern btn-modern">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Détails du message -->
            <div class="card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informations du Message
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Expéditeur</label>
                                <p class="form-control-plaintext">{{ $message->expediteur }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="form-control-plaintext">{{ $message->email_expediteur }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Téléphone</label>
                                <p class="form-control-plaintext">{{ $message->telephone_expediteur ?? 'Non renseigné' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Date d'envoi</label>
                                <p class="form-control-plaintext">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Sujet</label>
                        <p class="form-control-plaintext">{{ $message->sujet }}</p>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Contenu</label>
                        <div class="border rounded p-3 bg-light">
                            {{ $message->contenu }}
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Statut</label>
                        <div>
                            @if($message->lu)
                                <span class="badge bg-success">Lu</span>
                            @else
                                <span class="badge bg-warning">Non lu</span>
                            @endif
                            
                            @if($message->reponse)
                                <span class="badge bg-info ms-2">Répondu</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Réponse existante -->
            @if($message->reponse)
                <div class="card-modern mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-reply me-2"></i>Réponse
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="border rounded p-3 bg-light">
                            {{ $message->reponse }}
                        </div>
                        <small class="text-muted">
                            Répondu le {{ $message->reponse_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            <div class="card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if(!$message->lu)
                        <form method="POST" action="{{ route('admin.messages.mark-read') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="id" value="{{ $message->id }}">
                            <button type="submit" class="btn btn-success-modern btn-modern w-100 mb-2">
                                <i class="fas fa-check me-2"></i>Marquer comme lu
                            </button>
                        </form>
                    @endif

                    @if(!$message->reponse)
                        <button class="btn btn-primary-modern btn-modern w-100 mb-2" data-bs-toggle="modal" data-bs-target="#replyModal">
                            <i class="fas fa-reply me-2"></i>Répondre
                        </button>
                    @endif

                    <form method="POST" action="{{ route('admin.messages.destroy', $message->id) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger-modern btn-modern w-100">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card-modern mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="mb-1">{{ $message->lu ? 'Oui' : 'Non' }}</h4>
                                <small class="text-muted">Lu</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-1">{{ $message->reponse ? 'Oui' : 'Non' }}</h4>
                            <small class="text-muted">Répondu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de réponse -->
@if(!$message->reponse)
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">
                    <i class="fas fa-reply me-2"></i>Répondre au message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.messages.reply', $message->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="reponse" class="form-label">Réponse <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reponse" name="reponse" rows="5" required placeholder="Tapez votre réponse ici..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer la réponse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection