@extends('layouts.dg-modern')

@section('title', 'Gestion des Demandes')
@section('page-title', 'Demandes CSAR - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-clipboard-list me-2 text-primary"></i>
                            Gestion des Demandes
                        </h1>
                        <p class="text-muted mb-0 small">
                            Système unifié de gestion des demandes - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary-modern btn-modern btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="exportDemandes()">
                            <i class="fas fa-file-excel me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des demandes -->
    <div class="stats-responsive">
        <div class="stats-card-responsive">
            <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-list"></i>
            </div>
            <h3 class="stats-number">{{ $stats['total'] ?? 0 }}</h3>
            <p class="stats-label">📋 Total Demandes</p>
            <small class="text-muted">Toutes catégories</small>
        </div>

        <div class="stats-card-responsive">
            <div class="stats-icon" style="background: linear-gradient(135deg, #ffd43b 0%, #ff8c00 100%);">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="stats-number">{{ $stats['en_attente'] ?? 0 }}</h3>
            <p class="stats-label">⏳ En Attente</p>
            <small class="text-warning">Nécessite attention</small>
        </div>

        <div class="stats-card-responsive">
            <div class="stats-icon" style="background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="stats-number">{{ $stats['approuvees'] ?? 0 }}</h3>
            <p class="stats-label">✅ Approuvées</p>
            <small class="text-success">Traitées</small>
        </div>

        <div class="stats-card-responsive">
            <div class="stats-icon" style="background: linear-gradient(135deg, #ff6b6b 0%, #e55353 100%);">
                <i class="fas fa-times-circle"></i>
            </div>
            <h3 class="stats-number">{{ $stats['rejetees'] ?? 0 }}</h3>
            <p class="stats-label">❌ Rejetées</p>
            <small class="text-danger">Non éligibles</small>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="responsive-filters">
        <!-- Version Desktop -->
        <div class="filters-desktop">
            <div class="row w-100">
                <div class="col-lg-3 mb-2">
                    <label class="form-label small fw-bold">Rechercher</label>
                    <input type="text" class="form-control form-control-sm" placeholder="Nom, email, objet..." id="searchInput">
                </div>
                <div class="col-lg-2 mb-2">
                    <label class="form-label small fw-bold">Statut</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours</option>
                        <option value="approuvee">Approuvée</option>
                        <option value="rejetee">Rejetée</option>
                        <option value="terminee">Terminée</option>
                    </select>
                </div>
                <div class="col-lg-2 mb-2">
                    <label class="form-label small fw-bold">Type</label>
                    <select class="form-select form-select-sm" id="typeFilter">
                        <option value="">Tous les types</option>
                        <option value="aide_alimentaire">Aide Alimentaire</option>
                        <option value="aide_urgence">Aide Urgence</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="col-lg-2 mb-2">
                    <label class="form-label small fw-bold">Urgence</label>
                    <select class="form-select form-select-sm" id="urgencyFilter">
                        <option value="">Toutes</option>
                        <option value="faible">Faible</option>
                        <option value="moyenne">Moyenne</option>
                        <option value="haute">Haute</option>
                    </select>
                </div>
                <div class="col-lg-3 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary-modern btn-modern btn-sm me-2" onclick="applyFilters()">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times me-1"></i>Effacer
                    </button>
                </div>
            </div>
        </div>

        <!-- Version Mobile/Tablet -->
        <div class="filters-mobile">
            <div class="form-group">
                <label class="form-label">Rechercher</label>
                <input type="text" class="form-control" placeholder="Nom, email, objet..." id="searchInputMobile">
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select class="form-select" id="statusFilterMobile">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="approuvee">Approuvée</option>
                            <option value="rejetee">Rejetée</option>
                            <option value="terminee">Terminée</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-select" id="typeFilterMobile">
                            <option value="">Tous les types</option>
                            <option value="aide_alimentaire">Aide Alimentaire</option>
                            <option value="aide_urgence">Aide Urgence</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Urgence</label>
                        <select class="form-select" id="urgencyFilterMobile">
                            <option value="">Toutes</option>
                            <option value="faible">Faible</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <div class="btn-group">
                            <button class="btn btn-primary-modern btn-modern" onclick="applyFiltersMobile()">
                                <i class="fas fa-filter me-1"></i>Filtrer
                            </button>
                            <button class="btn btn-outline-secondary" onclick="clearFiltersMobile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des demandes -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Liste des Demandes
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">{{ $demandes->count() }} résultat(s)</span>
                    </div>
                </div>

                @if($demandes->count() > 0)
                    <div class="responsive-table-container">
                        <!-- Version Desktop -->
                        <div class="responsive-table-desktop">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Demandeur</th>
                                            <th>Type</th>
                                            <th>Objet</th>
                                            <th>Urgence</th>
                                            <th>Statut</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($demandes as $demande)
                                        <tr>
                                            <td>
                                                <span class="badge bg-info small">{{ $demande->tracking_code }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                                        <i class="fas fa-user" style="font-size: 14px;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $demande->full_name }}</div>
                                                        <small class="text-muted">{{ $demande->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $demande->type_demande)) }}</span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $demande->objet }}">
                                                    {{ $demande->objet }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($demande->urgence == 'haute')
                                                    <span class="badge bg-danger">Haute</span>
                                                @elseif($demande->urgence == 'moyenne')
                                                    <span class="badge bg-warning">Moyenne</span>
                                                @else
                                                    <span class="badge bg-success">Faible</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($demande->statut == 'en_attente')
                                                    <span class="badge bg-warning">En attente</span>
                                                @elseif($demande->statut == 'en_cours')
                                                    <span class="badge bg-info">En cours</span>
                                                @elseif($demande->statut == 'approuvee')
                                                    <span class="badge bg-success">Approuvée</span>
                                                @elseif($demande->statut == 'rejetee')
                                                    <span class="badge bg-danger">Rejetée</span>
                                                @else
                                                    <span class="badge bg-secondary">Terminée</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('dg.demandes.show', $demande->id) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($demande->statut == 'en_attente')
                                                        <button class="btn btn-outline-success" onclick="updateStatus({{ $demande->id }}, 'approuvee')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="updateStatus({{ $demande->id }}, 'rejetee')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Version Mobile/Tablet -->
                        <div class="responsive-table-mobile">
                            @foreach($demandes as $demande)
                            <div class="mobile-card">
                                <div class="mobile-card-header">
                                    <div class="mobile-card-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="mobile-card-title">
                                        <h6>{{ $demande->full_name }}</h6>
                                        <small>{{ $demande->email }}</small>
                                    </div>
                                </div>

                                <div class="mobile-card-badges">
                                    <span class="badge bg-info mobile-card-badge">{{ $demande->tracking_code }}</span>
                                    <span class="badge bg-info mobile-card-badge">{{ ucfirst(str_replace('_', ' ', $demande->type_demande)) }}</span>
                                </div>

                                <div class="mobile-card-content">
                                    <p title="{{ $demande->objet }}">{{ $demande->objet }}</p>
                                </div>

                                <div class="mobile-card-badges">
                                    @if($demande->urgence == 'haute')
                                        <span class="badge bg-danger mobile-card-badge">Haute</span>
                                    @elseif($demande->urgence == 'moyenne')
                                        <span class="badge bg-warning mobile-card-badge">Moyenne</span>
                                    @else
                                        <span class="badge bg-success mobile-card-badge">Faible</span>
                                    @endif

                                    @if($demande->statut == 'en_attente')
                                        <span class="badge bg-warning mobile-card-badge">En attente</span>
                                    @elseif($demande->statut == 'en_cours')
                                        <span class="badge bg-info mobile-card-badge">En cours</span>
                                    @elseif($demande->statut == 'approuvee')
                                        <span class="badge bg-success mobile-card-badge">Approuvée</span>
                                    @elseif($demande->statut == 'rejetee')
                                        <span class="badge bg-danger mobile-card-badge">Rejetée</span>
                                    @else
                                        <span class="badge bg-secondary mobile-card-badge">Terminée</span>
                                    @endif

                                    <small class="text-muted">{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</small>
                                </div>

                                <div class="mobile-card-actions">
                                    <a href="{{ route('dg.demandes.show', $demande->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($demande->statut == 'en_attente')
                                        <button class="btn btn-outline-success" onclick="updateStatus({{ $demande->id }}, 'approuvee')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="updateStatus({{ $demande->id }}, 'rejetee')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $demandes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="icon-3d mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-secondary);">
                            <i class="fas fa-inbox" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="text-muted">Aucune demande trouvée</h5>
                        <p class="text-muted">Aucune demande ne correspond aux critères de recherche</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de mise à jour -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mettre à jour la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm">
                <div class="modal-body">
                    <input type="hidden" id="demandeId">
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="approuvee">Approuvée</option>
                            <option value="rejetee">Rejetée</option>
                            <option value="terminee">Terminée</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea class="form-control" id="commentaire" rows="3" placeholder="Commentaire sur la décision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function refreshData() {
        location.reload();
    }

    function exportDemandes() {
        alert('Export des demandes en cours...');
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const type = document.getElementById('typeFilter').value;
        const urgency = document.getElementById('urgencyFilter').value;

        // Ici vous pouvez ajouter la logique de filtrage
        console.log('Filtres appliqués:', { search, status, type, urgency });
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('urgencyFilter').value = '';
    }

    function applyFiltersMobile() {
        const search = document.getElementById('searchInputMobile').value;
        const status = document.getElementById('statusFilterMobile').value;
        const type = document.getElementById('typeFilterMobile').value;
        const urgency = document.getElementById('urgencyFilterMobile').value;

        // Synchroniser avec les filtres desktop
        document.getElementById('searchInput').value = search;
        document.getElementById('statusFilter').value = status;
        document.getElementById('typeFilter').value = type;
        document.getElementById('urgencyFilter').value = urgency;

        // Appliquer les filtres
        applyFilters();
    }

    function clearFiltersMobile() {
        document.getElementById('searchInputMobile').value = '';
        document.getElementById('statusFilterMobile').value = '';
        document.getElementById('typeFilterMobile').value = '';
        document.getElementById('urgencyFilterMobile').value = '';

        // Synchroniser avec les filtres desktop
        clearFilters();
    }

    function updateStatus(demandeId, status) {
        document.getElementById('demandeId').value = demandeId;
        document.getElementById('newStatus').value = status;

        const modal = new bootstrap.Modal(document.getElementById('updateModal'));
        modal.show();
    }

    document.getElementById('updateForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const demandeId = document.getElementById('demandeId').value;
        const status = document.getElementById('newStatus').value;
        const commentaire = document.getElementById('commentaire').value;

        // Envoyer la requête AJAX
        fetch(`/dg/demandes/${demandeId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                statut: status,
                commentaire_admin: commentaire
            })
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erreur lors de la mise à jour');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la mise à jour');
        });
    });
</script>
@endsection
