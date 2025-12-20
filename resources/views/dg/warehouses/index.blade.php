@extends('layouts.dg-modern')

@section('title', 'Consultation des Entrepôts')
@section('page-title', 'Entrepôts CSAR - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-warehouse me-2 text-primary"></i>
                            Consultation des Entrepôts
                        </h1>
                        <p class="text-muted mb-0 small">
                            Vue d'ensemble des entrepôts CSAR - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary-modern btn-modern btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-success-modern btn-modern btn-sm" onclick="exportWarehouses()">
                            <i class="fas fa-file-excel me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des entrepôts -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 50px; height: 50px;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['total'] ?? 0 }}</h3>
                        <p class="stats-label">🏢 Total Entrepôts</p>
                        <small class="text-muted">Tous types</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-success); width: 50px; height: 50px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['active'] ?? 0 }}</h3>
                        <p class="stats-label">✅ Actifs</p>
                        <small class="text-success">Opérationnels</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-info); width: 50px; height: 50px;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['capacity'] ?? 0 }}</h3>
                        <p class="stats-label">📦 Capacité Totale</p>
                        <small class="text-info">En tonnes</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="background: var(--gradient-warning); width: 50px; height: 50px;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="stats-number">{{ $stats['regions'] ?? 0 }}</h3>
                        <p class="stats-label">🗺️ Régions</p>
                        <small class="text-warning">Couvertes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des entrepôts -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Liste des Entrepôts
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">{{ $warehouses->count() }} entrepôt(s)</span>
                    </div>
                </div>
                
                @if($warehouses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Entrepôt</th>
                                    <th>Type</th>
                                    <th>Capacité</th>
                                    <th>Localisation</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehouses as $warehouse)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                                <i class="fas fa-warehouse" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $warehouse->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $warehouse->type ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $warehouse->type ?? 'Standard' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $warehouse->capacity ?? 0 }} tonnes</div>
                                        <small class="text-muted">Capacité max</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $warehouse->location ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $warehouse->region ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($warehouse->is_active ?? true)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('dg.warehouses.show', $warehouse->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="icon-3d mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-secondary);">
                            <i class="fas fa-warehouse" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="text-muted">Aucun entrepôt trouvé</h5>
                        <p class="text-muted">Aucun entrepôt n'est actuellement enregistré</p>
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

    function exportWarehouses() {
        alert('Export des entrepôts en cours...');
    }
</script>
@endsection



































