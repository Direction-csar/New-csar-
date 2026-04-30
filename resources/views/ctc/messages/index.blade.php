@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Gestion des Messages')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-envelope me-2"></i>Gestion des Messages
                    </h1>
                    <p class="text-muted mb-0">Gérez les messages reçus et envoyés</p>
                </div>
                <div>
                    <button class="btn btn-info-modern btn-modern" onclick="markAllAsRead()">
                        <i class="fas fa-check-double me-2"></i>Tout Marquer Lu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-primary);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="mb-1" id="total-messages">{{ \App\Models\Message::count() ?? 0 }}</h3>
                    <p class="text-muted mb-0">Total Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-warning);">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                    <h3 class="mb-1" id="unread-messages">{{ \App\Models\Message::where('lu', false)->count() ?? 0 }}</h3>
                    <p class="text-muted mb-0">Non Lus</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-success);">
                        <i class="fas fa-reply"></i>
                    </div>
                    <h3 class="mb-1" id="replied-messages">{{ \App\Models\Message::whereNotNull('reponse')->count() ?? 0 }}</h3>
                    <p class="text-muted mb-0">Répondus</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-info);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1" id="pending-replies">{{ \App\Models\Message::where('lu', true)->whereNull('reponse')->count() ?? 0 }}</h3>
                    <p class="text-muted mb-0">En Attente</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search-input" placeholder="Expéditeur, sujet, contenu...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select class="form-select" id="status-filter">
                                    <option value="">Tous</option>
                                    <option value="unread">Non lus</option>
                                    <option value="read">Lus</option>
                                    <option value="replied">Répondus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select class="form-select" id="type-filter">
                                    <option value="">Tous</option>
                                    <option value="contact">Contact</option>
                                    <option value="support">Support</option>
                                    <option value="general">Général</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="date-filter">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary-modern btn-modern" onclick="applyFilters()">
                                        <i class="fas fa-search me-2"></i>Filtrer
                                    </button>
                                    <button class="btn btn-secondary-modern btn-modern" onclick="clearFilters()">
                                        <i class="fas fa-times me-2"></i>Effacer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des messages -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Liste des Messages
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                <i class="fas fa-check-square me-1"></i>Tout sélectionner
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="markSelectedAsRead()">
                                <i class="fas fa-check me-1"></i>Marquer lu
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="replySelected()">
                                <i class="fas fa-reply me-1"></i>Répondre
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSelected()">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="messages-list">
                        <!-- Liste vide par défaut -->
                        <div class="text-center py-5">
                            <div class="icon-3d mb-3" style="background: var(--gradient-secondary); width: 80px; height: 80px; margin: 0 auto;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h5 class="text-muted">Aucun message</h5>
                            <p class="text-muted">Aucun message reçu pour le moment. Les messages sont générés automatiquement depuis le formulaire de contact public.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal Réponse -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">
                    <i class="fas fa-reply me-2"></i>Répondre au Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="replyForm">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="reply_subject" class="form-label">Sujet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reply_subject" name="reply_subject" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="reply_content" class="form-label">Réponse <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reply_content" name="reply_content" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer la Réponse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
    animateCounters();
    
    // Les messages sont générés automatiquement depuis la plateforme publique
    
    // Gestion du formulaire de réponse
    document.getElementById('replyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendReply();
    });
    
    // Gestion des filtres
    document.getElementById('search-input').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('type-filter').addEventListener('change', applyFilters);
    document.getElementById('date-filter').addEventListener('change', applyFilters);
});

function animateCounters() {
    // Animation des compteurs (pour l'instant à 0)
    animateValue(document.getElementById('total-messages'), 0, 0, 1000);
    animateValue(document.getElementById('unread-messages'), 0, 0, 1000);
    animateValue(document.getElementById('replied-messages'), 0, 0, 1000);
    animateValue(document.getElementById('pending-replies'), 0, 0, 1000);
}

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Les messages sont créés automatiquement depuis la plateforme publique

function sendReply() {
    const formData = new FormData(document.getElementById('replyForm'));
    
    // Simulation d'envoi de réponse (à remplacer par une vraie requête AJAX)
    showToast('Réponse envoyée avec succès !', 'success');
    document.getElementById('replyForm').reset();
    bootstrap.Modal.getInstance(document.getElementById('replyModal')).hide();
    
    // Mettre à jour la liste
    loadMessages();
}

function applyFilters() {
    // Simulation de filtrage (à remplacer par une vraie requête AJAX)
    showToast('Filtres appliqués', 'info');
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('type-filter').value = '';
    document.getElementById('date-filter').value = '';
    applyFilters();
}

function loadMessages() {
    // Simulation de chargement (à remplacer par une vraie requête AJAX)
    // Pour l'instant, la liste reste vide
}

function selectAll() {
    showToast('Tous les messages sélectionnés', 'info');
}

function markSelectedAsRead() {
    showToast('Messages marqués comme lus', 'success');
}

function replySelected() {
    // Ouvrir le modal de réponse
    const replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
    replyModal.show();
}

function deleteSelected() {
    if (confirm('Êtes-vous sûr de vouloir supprimer les messages sélectionnés ?')) {
        showToast('Messages supprimés', 'success');
    }
}

function markAllAsRead() {
    if (confirm('Marquer tous les messages comme lus ?')) {
        showToast('Tous les messages ont été marqués comme lus', 'success');
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endpush