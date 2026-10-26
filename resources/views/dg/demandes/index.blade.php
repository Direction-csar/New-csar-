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

    <!-- Filtres et recherche (côté serveur) -->
    <form method="GET" action="{{ route('dg.demandes.index') }}" class="card-modern p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small fw-bold" for="q">Rechercher</label>
                <input type="text" class="form-control form-control-sm" id="q" name="q"
                       value="{{ $filters['q'] }}" placeholder="Code, nom, email, objet...">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-bold" for="workflow_status">Étape</label>
                <select class="form-select form-select-sm" id="workflow_status" name="workflow_status">
                    <option value="">Toutes les étapes</option>
                    @foreach(['soumise' => 'Soumise', 'en_revue' => 'En revue', 'document_attente' => 'Attente document', 'signee' => 'Signée', 'scannee' => 'Scannée (à décider)', 'validee_dg' => 'Validée DG', 'approuvee' => 'Approuvée', 'rejetee' => 'Rejetée', 'cloturee' => 'Clôturée'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['workflow_status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-bold" for="type">Type</label>
                <select class="form-select form-select-sm" id="type" name="type">
                    <option value="">Tous les types</option>
                    <option value="aide_alimentaire" @selected($filters['type'] === 'aide_alimentaire')>Aide alimentaire</option>
                    <option value="aide_urgence" @selected($filters['type'] === 'aide_urgence')>Aide urgence</option>
                    <option value="autre" @selected($filters['type'] === 'autre')>Autre</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-bold" for="urgency">Urgence</label>
                <select class="form-select form-select-sm" id="urgency" name="urgency">
                    <option value="">Toutes</option>
                    <option value="faible" @selected($filters['urgency'] === 'faible')>Faible</option>
                    <option value="moyenne" @selected($filters['urgency'] === 'moyenne')>Moyenne</option>
                    <option value="haute" @selected($filters['urgency'] === 'haute')>Haute</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary-modern btn-modern btn-sm flex-fill">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
                <a href="{{ route('dg.demandes.index') }}" class="btn btn-outline-secondary btn-sm" title="Effacer">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>

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

                {{-- Corps de la liste DG : tableau desktop + cartes mobile, états workflow réels --}}
                @php
                    $stepBadges = [
                        'soumise' => 'bg-secondary',
                        'en_revue' => 'bg-info',
                        'document_attente' => 'bg-warning text-dark',
                        'signee' => 'bg-primary',
                        'scannee' => 'bg-primary',
                        'validee_dg' => 'bg-success',
                        'approuvee' => 'bg-success',
                        'rejetee' => 'bg-danger',
                        'cloturee' => 'bg-dark',
                    ];
                    $urgencyBadges = ['haute' => 'bg-danger', 'moyenne' => 'bg-warning text-dark', 'faible' => 'bg-success'];
                @endphp

                @if($demandes->count() > 0)
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Demandeur</th>
                                    <th>Type</th>
                                    <th>Objet</th>
                                    <th>Urgence</th>
                                    <th>Étape</th>
                                    <th>Reçue le</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($demandes as $demande)
                                <tr>
                                    <td><span class="badge bg-info text-dark">{{ $demande->tracking_code }}</span></td>
                                    <td>
                                        <div class="fw-bold">{{ $demande->full_name }}</div>
                                        <small class="text-muted">{{ $demande->email }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $demande->type ?? '—')) }}</span></td>
                                    <td><div class="text-truncate" style="max-width: 220px;" title="{{ $demande->subject }}">{{ $demande->subject }}</div></td>
                                    <td><span class="badge {{ $urgencyBadges[$demande->urgency] ?? 'bg-secondary' }}">{{ ucfirst($demande->urgency ?? '—') }}</span></td>
                                    <td><span class="badge {{ $stepBadges[$demande->workflow_status] ?? 'bg-secondary' }}">{{ $demande->workflow_status_label }}</span></td>
                                    <td><small>{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</small></td>
                                    <td class="text-end">
                                        <a href="{{ route('dg.demandes.show', $demande->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Ouvrir
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-md-none">
                        @foreach($demandes as $demande)
                        <div class="border rounded p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold">{{ $demande->full_name }}</div>
                                    <small class="text-muted">{{ $demande->tracking_code }}</small>
                                </div>
                                <span class="badge {{ $stepBadges[$demande->workflow_status] ?? 'bg-secondary' }}">{{ $demande->workflow_status_label }}</span>
                            </div>
                            <p class="small mt-2 mb-2">{{ $demande->subject }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge {{ $urgencyBadges[$demande->urgency] ?? 'bg-secondary' }}">{{ ucfirst($demande->urgency ?? '—') }}</span>
                                <a href="{{ route('dg.demandes.show', $demande->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Ouvrir
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $demandes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <h5 class="text-muted">Aucune demande trouvée</h5>
                        <p class="text-muted mb-0">Aucune demande ne correspond aux critères de recherche.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function refreshData() {
        location.reload();
    }
</script>
@endsection
