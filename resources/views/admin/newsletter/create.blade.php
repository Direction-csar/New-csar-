@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Nouvelle Newsletter')
@section('page-title', 'Créer une Newsletter')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nouvelle Newsletter</h1>
    <p class="page-subtitle">Créer et envoyer une newsletter</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>Créer une Newsletter
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.newsletter.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre de la Newsletter *</label>
                        <input type="text" class="form-control" id="titre" name="titre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sujet" class="form-label">Sujet de l'email *</label>
                        <input type="text" class="form-control" id="sujet" name="sujet" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contenu" class="form-label">Contenu de la Newsletter *</label>
                        <textarea class="form-control" id="contenu" name="contenu" rows="12" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="destinataires" class="form-label">Destinataires *</label>
                            <select class="form-select" id="destinataires" name="destinataires" required>
                                <option value="">Sélectionner les destinataires</option>
                                <option value="all">Tous les abonnés (1,250)</option>
                                <option value="admin">Administrateurs seulement</option>
                                <option value="personnel">Personnel CSAR</option>
                                <option value="partenaires">Partenaires</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="date_envoi" class="form-label">Date d'envoi</label>
                            <input type="datetime-local" class="form-control" id="date_envoi" name="date_envoi">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="envoi_immediat" name="envoi_immediat">
                            <label class="form-check-label" for="envoi_immediat">
                                Envoyer immédiatement
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <button type="submit" name="action" value="send" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer
                        </button>
                        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informations
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong>Abonnés disponibles :</strong>
                </p>
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-users me-2"></i>Tous les abonnés: 1,250</li>
                    <li><i class="fas fa-user-shield me-2"></i>Administrateurs: 5</li>
                    <li><i class="fas fa-user-tie me-2"></i>Personnel CSAR: 45</li>
                    <li><i class="fas fa-handshake me-2"></i>Partenaires: 12</li>
                </ul>
                
                <hr>
                
                <p class="text-muted">
                    <strong>Conseils :</strong>
                </p>
                <ul class="list-unstyled text-muted small">
                    <li>• Utilisez un titre accrocheur</li>
                    <li>• Gardez le contenu concis</li>
                    <li>• Testez avant l'envoi</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection