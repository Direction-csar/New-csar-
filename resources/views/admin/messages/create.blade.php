@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Nouveau Message')
@section('page-title', 'Créer un Nouveau Message')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nouveau Message</h1>
    <p class="page-subtitle">Envoyer un message à un utilisateur</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>Composer un Message
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.messages.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="destinataire" class="form-label">Destinataire *</label>
                        <select class="form-select" id="destinataire" name="destinataire" required>
                            <option value="">Sélectionner un destinataire</option>
                            <option value="admin@csar.sn">Admin CSAR</option>
                            <option value="dg@csar.sn">Directeur Général</option>
                            <option value="responsable@csar.sn">Responsable Entrepôt</option>
                            <option value="agent@csar.sn">Agent Terrain</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sujet" class="form-label">Sujet *</label>
                        <input type="text" class="form-control" id="sujet" name="sujet" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contenu" class="form-label">Contenu du message *</label>
                        <textarea class="form-control" id="contenu" name="contenu" rows="8" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="urgent" name="urgent">
                            <label class="form-check-label" for="urgent">
                                Message urgent
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer
                        </button>
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary">
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
                    <strong>Destinataires disponibles :</strong>
                </p>
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-user-shield me-2"></i>Admin CSAR</li>
                    <li><i class="fas fa-user-tie me-2"></i>Directeur Général</li>
                    <li><i class="fas fa-user-cog me-2"></i>Responsable Entrepôt</li>
                    <li><i class="fas fa-user me-2"></i>Agent Terrain</li>
                </ul>
                
                <hr>
                
                <p class="text-muted">
                    <strong>Message urgent :</strong> Les messages urgents sont prioritaires et affichés en premier.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection