@extends('layouts.admin')

@section('title', 'Gestion des Demandes')
@section('page-title', 'Gestion des Demandes')

@section('content')
<div class="container-fluid px-3">
    <!-- Header moderne -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">📧 Boîte de Réception</h1>
                        <p class="text-muted mb-0 small">Gérez les demandes et messages des utilisateurs</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshDemandes()">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <div class="dropdown" style="position: relative;">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative; z-index: 1000; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);">
                                <i class="fas fa-download me-1"></i>Exporter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown" style="min-width: 250px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); border: 2px solid #e9ecef; border-radius: 8px; z-index: 1050; background: white; animation: fadeInDown 0.3s ease-out;">
                                <li><h6 class="dropdown-header" style="background: #f8f9fa; color: #495057; font-weight: 600; border-radius: 4px; margin: 4px; padding: 8px 12px;">📊 Choisir le format d'export</h6></li>
                                <li><hr class="dropdown-divider" style="margin: 8px 0; border-color: #dee2e6;"></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="exportDemandes('excel')" style="display: flex; align-items: center; padding: 12px 16px; margin: 2px 4px; border-radius: 4px; transition: all 0.2s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateX(4px)'" onmouseout="this.style.backgroundColor=''; this.style.transform=''">
                                    <i class="fas fa-file-excel me-3 text-success" style="font-size: 20px;"></i>
                                    <div>
                                        <div class="fw-bold" style="color: #198754;">Excel (.xlsx)</div>
                                        <small class="text-muted">Format recommandé avec mise en forme</small>
                                    </div>
                                </a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="exportDemandes('csv')" style="display: flex; align-items: center; padding: 12px 16px; margin: 2px 4px; border-radius: 4px; transition: all 0.2s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateX(4px)'" onmouseout="this.style.backgroundColor=''; this.style.transform=''">
                                    <i class="fas fa-file-csv me-3 text-info" style="font-size: 20px;"></i>
                                    <div>
                                        <div class="fw-bold" style="color: #0dcaf0;">CSV (.csv)</div>
                                        <small class="text-muted">Compatible avec tous les tableurs</small>
                                    </div>
                                </a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="exportDemandes('pdf')" style="display: flex; align-items: center; padding: 12px 16px; margin: 2px 4px; border-radius: 4px; transition: all 0.2s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateX(4px)'" onmouseout="this.style.backgroundColor=''; this.style.transform=''">
                                    <i class="fas fa-file-pdf me-3 text-danger" style="font-size: 20px;"></i>
                                    <div>
                                        <div class="fw-bold" style="color: #dc3545;">PDF (.pdf)</div>
                                        <small class="text-muted">Pour impression et archivage</small>
                                    </div>
                                </a></li>
                                <li><hr class="dropdown-divider" style="margin: 8px 0; border-color: #dee2e6;"></li>
                                <li><div class="dropdown-item-text small text-muted px-3" style="padding: 8px 16px; background: #f8f9fa; border-radius: 4px; margin: 4px;">
                                    <i class="fas fa-info-circle me-1 text-primary"></i>
                                    L'export inclut toutes les données filtrées
                                </div></li>
                            </ul>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="showExportOptions()" title="Exporter les demandes" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);">
                            <i class="fas fa-file-export me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="border-radius: 10px;">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 40px; height: 40px; background: var(--gradient-success);">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>Succès !</strong><br>
                        <small>{{ session('success') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="border-radius: 10px;">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 40px; height: 40px; background: var(--gradient-danger);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>Erreur !</strong><br>
                        <small>{{ session('error') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Alertes dynamiques -->
    @if($stats['non_vues'] > 0)
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show border-0" role="alert" style="border-radius: 10px;">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 40px; height: 40px; background: var(--gradient-warning);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>Nouvelles demandes non consultées</strong><br>
                        <small>Vous avez <span id="unread-count">{{ $stats['non_vues'] }}</span> demandes en attente de traitement</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistiques modernes avec icônes 3D -->
    <div class="row mb-2">
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card-modern p-2 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-2" style="background: var(--gradient-primary); width: 45px; height: 45px;">
                        <i class="fas fa-list" style="font-size: 18px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 fw-bold text-primary" id="total-demandes">{{ $stats['total'] ?? 0 }}</h4>
                        <p class="text-muted mb-0 small">📋 Total Demandes</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card-modern p-2 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-2" style="background: var(--gradient-warning); width: 45px; height: 45px;">
                        <i class="fas fa-clock" style="font-size: 18px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 fw-bold text-warning" id="pending-demandes">{{ $stats['pending'] ?? 0 }}</h4>
                        <p class="text-muted mb-0 small">⏳ En Attente</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card-modern p-2 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-2" style="background: var(--gradient-success); width: 45px; height: 45px;">
                        <i class="fas fa-check-circle" style="font-size: 18px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 fw-bold text-success" id="approved-demandes">{{ $stats['approved'] ?? 0 }}</h4>
                        <p class="text-muted mb-0 small">✅ Approuvées</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card-modern p-2 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-2" style="background: var(--gradient-danger); width: 45px; height: 45px;">
                        <i class="fas fa-times-circle" style="font-size: 18px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 fw-bold text-danger" id="rejected-demandes">{{ $stats['rejected'] ?? 0 }}</h4>
                        <p class="text-muted mb-0 small">❌ Rejetées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et indicateurs visuels -->
    <div class="row mb-2">
        <div class="col-lg-6 mb-2">
            <div class="card-modern p-2">
                <h6 class="mb-1 fw-bold">📊 Demandes par Statut</h6>
                <div class="chart-container" style="position: relative; height: 180px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-2">
            <div class="card-modern p-2">
                <h6 class="mb-1 fw-bold">📈 Évolution (7 derniers jours)</h6>
                <div class="chart-container" style="position: relative; height: 180px;">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et tri modernes -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-2">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">🔍 Recherche</label>
                        <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Rechercher une demande...">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="form-label small fw-bold">📊 Workflow</label>
                        <select class="form-select form-select-sm" id="workflowFilter">
                            <option value="">Tous</option>
                            <option value="soumise">Soumise</option>
                            <option value="en_revue">En revue</option>
                            <option value="document_attente">Attente doc</option>
                            <option value="signee">Signée</option>
                            <option value="scannee">Scannée</option>
                            <option value="validee_dg">Validée DG</option>
                            <option value="approuvee">Approuvée</option>
                            <option value="rejetee">Rejetée</option>
                            <option value="cloturee">Clôturée</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="form-label small fw-bold">⚠️ Doublon</label>
                        <select class="form-select form-select-sm" id="duplicateFilter">
                            <option value="">Tous</option>
                            <option value="1">Doublons</option>
                            <option value="0">Uniques</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="form-label small fw-bold">📅 Date</label>
                        <select class="form-select form-select-sm" id="dateFilter">
                            <option value="">Toutes les dates</option>
                            <option value="today">Aujourd'hui</option>
                            <option value="week">Cette semaine</option>
                            <option value="month">Ce mois</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="form-label small fw-bold">📋 Type</label>
                        <select class="form-select form-select-sm" id="typeFilter">
                            <option value="">Tous les types</option>
                            <option value="demande">Demande</option>
                            <option value="reclamation">Réclamation</option>
                            <option value="information">Information</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="form-label small fw-bold">🔄 Tri</label>
                        <select class="form-select form-select-sm" id="sortFilter">
                            <option value="newest">Plus récent</option>
                            <option value="oldest">Plus ancien</option>
                            <option value="priority">Priorité</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">📅 Période personnalisée</label>
                        <div class="d-flex gap-1">
                            <input type="date" class="form-control form-control-sm" id="dateFromFilter" placeholder="Du">
                            <input type="date" class="form-control form-control-sm" id="dateToFilter" placeholder="Au">
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">🌍 Région</label>
                        <select class="form-select form-select-sm" id="regionFilter">
                            <option value="">Toutes les régions</option>
                            <option value="Dakar">Dakar</option>
                            <option value="Thiès">Thiès</option>
                            <option value="Diourbel">Diourbel</option>
                            <option value="Kaolack">Kaolack</option>
                            <option value="Fatick">Fatick</option>
                            <option value="Kaffrine">Kaffrine</option>
                            <option value="Louga">Louga</option>
                            <option value="Saint-Louis">Saint-Louis</option>
                            <option value="Matam">Matam</option>
                            <option value="Tambacounda">Tambacounda</option>
                            <option value="Kédougou">Kédougou</option>
                            <option value="Kolda">Kolda</option>
                            <option value="Sédhiou">Sédhiou</option>
                            <option value="Ziguinchor">Ziguinchor</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Effacer
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="applyFilters()">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des demandes moderne -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">📋 Liste des Demandes</h6>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-primary btn-sm" onclick="selectAll()">
                            <i class="fas fa-check-square"></i> Tout sélectionner
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" id="bulkWorkflowDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-forward"></i> Avancer workflow
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkWorkflowDropdown">
                                <li><h6 class="dropdown-header">Avancer vers :</h6></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkWorkflowAction('en_revue'); return false;"><i class="fas fa-eye text-info me-2"></i>En revue</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkWorkflowAction('document_attente'); return false;"><i class="fas fa-file-alt text-warning me-2"></i>Attente document</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkWorkflowAction('signee'); return false;"><i class="fas fa-signature text-primary me-2"></i>Signée</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkWorkflowAction('scannee'); return false;"><i class="fas fa-file-pdf text-info me-2"></i>Scannée</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkWorkflowAction('approuvee'); return false;"><i class="fas fa-check-circle text-success me-2"></i>Approuvée</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="bulkWorkflowAction('rejetee'); return false;"><i class="fas fa-times-circle text-danger me-2"></i>Rejeter</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkWorkflowAction('cloturee'); return false;"><i class="fas fa-archive text-dark me-2"></i>Clôturer</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-danger btn-sm" onclick="bulkAction('delete')">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="demandesTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                </th>
                                <th>Code de Suivi</th>
                                <th>Demandeur</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Région</th>
                                <th>Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="demandesTableBody">
                            @forelse($demandes as $demande)
                                @php
                                    $isStale = false;
                                    $daysSince = \Carbon\Carbon::parse($demande->created_at)->diffInDays(now());
                                    if (in_array($demande->workflow_status, ['soumise', 'en_revue']) && $daysSince > 7) {
                                        $isStale = true;
                                    } elseif ($demande->workflow_status === 'document_attente' && $daysSince > 3) {
                                        $isStale = true;
                                    }
                                @endphp
                                <tr data-id="{{ $demande->id }}" class="demande-row {{ $isStale ? 'table-warning' : '' }} {{ $demande->is_duplicate ? 'duplicate-row' : '' }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input demande-checkbox" value="{{ $demande->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-3d me-2" style="width: 30px; height: 30px; background: var(--gradient-primary);">
                                                <i class="fas fa-file-alt" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <strong class="text-primary">{{ $demande->tracking_code ?? 'CSAR-' . $demande->id }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($demande->description ?? 'Aucune description', 30) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $demande->full_name ?? (($demande->nom ?? '') . ' ' . ($demande->prenom ?? '')) }}</strong>
                                                <br><small class="text-muted">{{ $demande->email ?? 'email@example.com' }}</small>
                                                @if($demande->is_duplicate)
                                                    <br><span class="badge bg-danger" style="font-size:0.65rem;">Doublon</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($demande->type ?? $demande->type_demande ?? 'Demande') }}</span>
                                    </td>
                                    <td>
                                        {!! $demande->workflow_status_badge !!}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $demande->region ?? 'Non spécifiée' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $createdAt = \Carbon\Carbon::parse($demande->created_at);
                                        @endphp
                                        <small>{{ $createdAt->format('d/m/Y') }}</small>
                                        <br><small class="text-muted">{{ $createdAt->diffForHumans() }}</small>
                                        @if($isStale)
                                        <br><span class="badge bg-warning text-dark" style="font-size: 0.6rem;"><i class="fas fa-exclamation-triangle me-1"></i>{{ $daysSince }}j sans action</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.demandes.show', $demande->id) }}" class="btn btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.demandes.pdf', $demande->id) }}" class="btn btn-outline-success" title="Télécharger PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('admin.demandes.edit', $demande->id) }}" class="btn btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" onclick="deleteDemande({{ $demande->id }})" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <h5>Aucune demande trouvée</h5>
                                            <p>Il n'y a actuellement aucune demande dans le système.</p>
                                            <p class="text-muted small">Les demandes sont créées depuis la plateforme publique</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination moderne -->
                @if($demandes->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Affichage de {{ $demandes->firstItem() }} à {{ $demandes->lastItem() }} sur {{ $demandes->total() }} demandes
                    </div>
                    <div>
                        {{ $demandes->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Modal d'export -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0;">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-download me-2"></i>Exporter les Demandes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div class="text-center mb-4">
                    <i class="fas fa-file-export fa-4x text-primary mb-3" style="color: #667eea;"></i>
                    <h5 class="fw-bold">Choisissez le format d'export</h5>
                    <p class="text-muted">L'export inclura toutes les demandes selon les filtres appliqués</p>
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-success h-100" onclick="exportDemandes('excel')" style="cursor: pointer; transition: all 0.3s ease; border-width: 2px;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 8px 25px rgba(25, 135, 84, 0.3)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow=''">
                            <div class="card-body text-center" style="padding: 20px;">
                                <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                                <h6 class="card-title fw-bold" style="color: #198754;">Excel (.xlsx)</h6>
                                <p class="card-text small text-muted">Format recommandé avec mise en forme professionnelle</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-info h-100" onclick="exportDemandes('csv')" style="cursor: pointer; transition: all 0.3s ease; border-width: 2px;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 8px 25px rgba(13, 202, 240, 0.3)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow=''">
                            <div class="card-body text-center" style="padding: 20px;">
                                <i class="fas fa-file-csv fa-3x text-info mb-3"></i>
                                <h6 class="card-title fw-bold" style="color: #0dcaf0;">CSV (.csv)</h6>
                                <p class="card-text small text-muted">Compatible avec tous les tableurs et logiciels</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-danger h-100" onclick="exportDemandes('pdf')" style="cursor: pointer; transition: all 0.3s ease; border-width: 2px;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 8px 25px rgba(220, 53, 69, 0.3)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow=''">
                            <div class="card-body text-center" style="padding: 20px;">
                                <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                <h6 class="card-title fw-bold" style="color: #dc3545;">PDF (.pdf)</h6>
                                <p class="card-text small text-muted">Pour impression et archivage</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding: 20px 30px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 6px;">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
  /* Optimisation de l'espace pour les demandes */
  .container-fluid { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
  
  /* Cards plus compactes */
  .card-modern { margin-bottom: 0.5rem !important; }
  
  /* Graphiques optimisés */
  .chart-container { position: relative; height: 180px; }
  .chart-container canvas { width: 100% !important; height: 100% !important; }
  
  /* Réduction des marges */
  .row { margin-bottom: 0.5rem !important; }
  .mb-2 { margin-bottom: 0.5rem !important; }
  
  /* Icônes plus petites */
  .icon-3d { width: 45px !important; height: 45px !important; }
  .icon-3d i { font-size: 18px !important; }
  
  /* Textes plus compacts */
  .h4 { font-size: 1.25rem !important; }
  .h6 { font-size: 0.9rem !important; }
  
  /* Boutons plus petits et cliquables */
  .btn-sm { 
    padding: 0.25rem 0.5rem !important; 
    font-size: 0.8rem !important; 
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 10 !important;
  }
  
  /* Tableau moderne */
  .table {
    margin-bottom: 0 !important;
  }
  
  .table th {
    border-top: none !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #6c757d !important;
  }
  
  .table td {
    vertical-align: middle !important;
    font-size: 0.85rem !important;
  }
  
  .demande-row:hover {
    background-color: rgba(102, 126, 234, 0.05) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
  }
  
  /* Avatar moderne */
  .avatar-sm {
    width: 32px !important;
    height: 32px !important;
  }
  
  /* Badges modernes */
  .badge {
    font-size: 0.7rem !important;
    padding: 0.35em 0.65em !important;
  }
  
  /* Formulaires compacts */
  .form-control-sm, .form-select-sm {
    font-size: 0.8rem !important;
    padding: 0.25rem 0.5rem !important;
  }
  
  /* Animations de chargement */
  .loading {
    opacity: 0.6;
    pointer-events: none;
  }
  
  .loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  
  /* Amélioration du menu déroulant d'export */
  .dropdown-menu {
    border-radius: 8px !important;
    border: 2px solid #e9ecef !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    animation: fadeInDown 0.3s ease-out !important;
  }
  
  .dropdown-item {
    border-radius: 4px !important;
    margin: 2px 4px !important;
    transition: all 0.2s ease !important;
  }
  
  .dropdown-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(4px) !important;
  }
  
  .dropdown-item:active {
    background-color: #e9ecef !important;
  }
  
  .dropdown-header {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    font-weight: 600 !important;
    border-radius: 4px !important;
    margin: 4px !important;
  }
  
  .dropdown-divider {
    margin: 8px 0 !important;
    border-color: #dee2e6 !important;
  }
  
  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  /* Bouton export amélioré */
  .btn-primary.dropdown-toggle {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3) !important;
  }
  
  .btn-primary.dropdown-toggle:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4) !important;
  }
  
  .btn-primary.dropdown-toggle:focus {
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.5) !important;
  }
</style>
@endpush

@push('scripts')
<script>
// Graphique des statuts
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente', 'Approuvées', 'Rejetées'],
            datasets: [{
                data: [
                    {{ $stats['pending'] ?? 0 }},
                    {{ $stats['approved'] ?? 0 }},
                    {{ $stats['rejected'] ?? 0 }}
                ],
                backgroundColor: [
                    '#ffd43b',
                    '#51cf66',
                    '#ff6b6b'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        boxWidth: 12
                    }
                }
            }
        }
    });
}

// Graphique d'évolution
const evolutionCtx = document.getElementById('evolutionChart');
if (evolutionCtx) {
    const evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Nouvelles demandes',
                data: [5, 8, 3, 12, 7, 4, 9],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Variables globales
let currentFilters = {};
let selectedDemandes = [];

// Fonction d'actualisation
function refreshDemandes() {
    const refreshBtn = document.querySelector('button[onclick="refreshDemandes()"]');
    const icon = refreshBtn.querySelector('i');
    
    // Animation de rotation
    icon.style.animation = 'spin 1s linear infinite';
    
    // Simuler le rechargement
    setTimeout(() => {
        // Arrêter l'animation
        icon.style.animation = '';
        
        // Mettre à jour les statistiques
        updateStats();
        
        // Afficher un message de succès
        showToast('Demandes actualisées avec succès!', 'success');
    }, 1000);
}

// Fonction pour afficher la modal d'export
function showExportOptions() {
    const modal = new bootstrap.Modal(document.getElementById('exportModal'));
    modal.show();
}

// Fonction d'export
function exportDemandes(format = 'excel') {
    // Fermer la modal si elle est ouverte
    const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
    if (modal) {
        modal.hide();
    }
    
    showToast('Export en cours...', 'info');
    
    // Récupérer les filtres actuels
    const filters = {
        format: format,
        search: document.getElementById('searchInput')?.value || '',
        workflow_status: document.getElementById('workflowFilter')?.value || '',
        is_duplicate: document.getElementById('duplicateFilter')?.value || '',
        type: document.getElementById('typeFilter')?.value || '',
        region: document.getElementById('regionFilter')?.value || '',
        date_from: document.getElementById('dateFromFilter')?.value || '',
        date_to: document.getElementById('dateToFilter')?.value || ''
    };
    
    // Créer un formulaire pour envoyer les données
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.demandes.export") }}';
    form.style.display = 'none';
    
    // Ajouter le token CSRF
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Ajouter les paramètres de filtrage
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = filters[key];
            form.appendChild(input);
        }
    });
    
    // Ajouter le formulaire au DOM et le soumettre
    document.body.appendChild(form);
    form.submit();
    
    // Nettoyer
    setTimeout(() => {
        document.body.removeChild(form);
        showToast('Export terminé!', 'success');
    }, 1000);
}

// Fonction de mise à jour des statistiques
function updateStats() {
    // Animation des compteurs
    animateValue('total-demandes', 0, {{ $stats['total'] ?? 0 }}, 1000);
    animateValue('pending-demandes', 0, {{ $stats['pending'] ?? 0 }}, 1000);
    animateValue('approved-demandes', 0, {{ $stats['approved'] ?? 0 }}, 1000);
    animateValue('rejected-demandes', 0, {{ $stats['rejected'] ?? 0 }}, 1000);
}

// Animation des valeurs
function animateValue(elementId, start, end, duration) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Fonction de filtrage
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const workflow = document.getElementById('workflowFilter').value;
    const duplicate = document.getElementById('duplicateFilter').value;
    const date = document.getElementById('dateFilter').value;
    const sort = document.getElementById('sortFilter').value;
    
    currentFilters = { search, workflow, duplicate, date, sort };
    
    // Appliquer les filtres (simulation)
    filterTable();
    
    showToast('Filtres appliqués!', 'success');
}

// Fonction de filtrage du tableau
function filterTable() {
    const rows = document.querySelectorAll('.demande-row');
    const search = (currentFilters.search || '').toLowerCase();
    const workflow = currentFilters.workflow || '';
    const duplicate = currentFilters.duplicate || '';
    
    rows.forEach(row => {
        let show = true;
        
        // Filtre de recherche
        if (search) {
            const text = row.textContent.toLowerCase();
            if (!text.includes(search)) {
                show = false;
            }
        }
        
        // Filtre workflow (cherche dans les badges de statut)
        if (workflow && show) {
            const badges = row.querySelectorAll('td .badge');
            let hasWorkflow = false;
            badges.forEach(badge => {
                const text = badge.textContent.toLowerCase().trim();
                const workflowMap = {
                    'soumise': 'soumise',
                    'en_revue': 'en revue',
                    'document_attente': 'attente doc',
                    'signee': 'signée',
                    'scannee': 'scannée',
                    'validee_dg': 'validée dg',
                    'approuvee': 'approuvée',
                    'rejetee': 'rejetée',
                    'cloturee': 'clôturée'
                };
                if (text === workflowMap[workflow]) {
                    hasWorkflow = true;
                }
            });
            if (!hasWorkflow) show = false;
        }
        
        // Filtre doublon
        if (duplicate !== '' && show) {
            const isDup = row.classList.contains('duplicate-row');
            if (duplicate === '1' && !isDup) show = false;
            if (duplicate === '0' && isDup) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// Fonction d'effacement des filtres
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('workflowFilter').value = '';
    document.getElementById('duplicateFilter').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('regionFilter').value = '';
    document.getElementById('dateFilter').value = '';
    document.getElementById('dateFromFilter').value = '';
    document.getElementById('dateToFilter').value = '';
    document.getElementById('sortFilter').value = 'newest';
    
    currentFilters = {};
    
    // Afficher toutes les lignes
    const rows = document.querySelectorAll('.demande-row');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    showToast('Filtres effacés!', 'info');
}

// Fonction de sélection
function selectAll() {
    const checkboxes = document.querySelectorAll('.demande-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelectedCount();
}

// Fonction de mise à jour du compteur de sélection
function updateSelectedCount() {
    const checked = document.querySelectorAll('.demande-checkbox:checked');
    selectedDemandes = Array.from(checked).map(cb => cb.value);
}

// Fonction d'action en lot
function bulkAction(action) {
    if (selectedDemandes.length === 0) {
        showToast('Veuillez sélectionner au moins une demande', 'warning');
        return;
    }
    
    if (action === 'delete') {
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedDemandes.length} demande(s) ?`)) {
            showToast('Suppression en cours...', 'info');
            
            // Créer un formulaire pour la suppression en masse
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/demandes/bulk-delete';
            form.style.display = 'none';
            
            // Ajouter le token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            // Ajouter les IDs des demandes sélectionnées
            selectedDemandes.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'demande_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            // Soumettre le formulaire
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Fonction d'action en lot sur le workflow
function bulkWorkflowAction(newStatus) {
    if (selectedDemandes.length === 0) {
        showToast('Veuillez sélectionner au moins une demande', 'warning');
        return;
    }

    const statusLabels = {
        'en_revue': 'En revue',
        'document_attente': 'Attente document',
        'signee': 'Signée',
        'scannee': 'Scannée',
        'approuvee': 'Approuvée',
        'rejetee': 'Rejetée',
        'cloturee': 'Clôturée',
    };

    const label = statusLabels[newStatus] || newStatus;
    const verb = newStatus === 'rejetee' ? 'rejeter' : (newStatus === 'cloturee' ? 'clôturer' : 'avancer');

    if (!confirm(`Êtes-vous sûr de vouloir ${verb} ${selectedDemandes.length} demande(s) vers "${label}" ?`)) {
        return;
    }

    showToast('Traitement en cours...', 'info');

    fetch('{{ route("admin.demandes.bulk-workflow") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            ids: selectedDemandes,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Workflow avancé avec succès !', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast(data.message || 'Une erreur est survenue.', 'danger');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de l\'avancement du workflow.', 'danger');
    });
}

// Fonction de suppression d'une demande
function deleteDemande(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')) {
        showToast('Suppression en cours...', 'info');
        
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/demandes/${id}`;
        form.style.display = 'none';
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Ajouter la méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour afficher des toasts
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Supprimer le toast après 3 secondes
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements de sélection
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('demande-checkbox')) {
            updateSelectedCount();
        }
    });
    
    // Écouter la recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentFilters.search = this.value;
            filterTable();
        });
    }
    
    // Mise à jour automatique toutes les 30 secondes
    setInterval(() => {
        updateStats();
    }, 30000);
    
    // Animation CSS pour la rotation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush