@extends('layouts.admin')

@section('title', 'Détail de la Demande')
@section('page-title', 'Détail de la Demande')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">📋 Détail de la Demande</h1>
                        <p class="text-muted mb-0 small">
                            Code de suivi: {{ $demande->tracking_code ?? 'CSAR-' . $demande->id }}
                            @if($demande->is_duplicate)
                                <span class="badge bg-danger ms-2">Doublon</span>
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.demandes.edit', $demande->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Traiter
                        </a>
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Status Banner -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3 bg-light">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <small class="text-muted">Statut workflow</small>
                            <div>{!! $demande->workflow_status_badge !!}</div>
                        </div>
                        @if($demande->courier_reference)
                        <div>
                            <small class="text-muted">Référence courrier</small>
                            <div class="fw-bold text-primary">{{ $demande->courier_reference }}</div>
                        </div>
                        @endif
                        @if($demande->courier_date)
                        <div>
                            <small class="text-muted">Date courrier</small>
                            <div class="fw-bold">{{ \Carbon\Carbon::parse($demande->courier_date)->format('d/m/Y') }}</div>
                        </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @if($demande->dg_signature_file)
                        <a href="{{ asset('storage/' . $demande->dg_signature_file) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-signature me-1"></i>Signé
                        </a>
                        @endif
                        @if($demande->scan_file)
                        <a href="{{ asset('storage/' . $demande->scan_file) }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Scan
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations principales -->
    <div class="row mb-2">
        <div class="col-lg-8">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">📋 Informations de la Demande</h6>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Demandeur</label>
                            <p class="mb-0">{{ $demande->full_name }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <p class="mb-0">{{ $demande->email }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Téléphone</label>
                            <p class="mb-0">{{ $demande->phone }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Objet</label>
                            <p class="mb-0">{{ $demande->subject }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Type de demande</label>
                            <p class="mb-0">
                                <span class="badge bg-info">{{ ucfirst($demande->type ?? 'Non spécifié') }}</span>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Statut</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $demande->status === 'approved' ? 'success' : ($demande->status === 'rejected' ? 'danger' : ($demande->status === 'completed' ? 'info' : 'warning')) }}">
                                    {{ ucfirst($demande->status) }}
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Date de demande</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y à H:i') }}</p>
                        </div>
                        
                        @if($demande->processed_date)
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Date de traitement</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($demande->processed_date)->format('d/m/Y à H:i') }}</p>
                        </div>
                        @endif
                        
                        @if($demande->tracking_code)
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Code de suivi</label>
                            <p class="mb-0"><span class="badge bg-primary">{{ $demande->tracking_code }}</span></p>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($demande->description)
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Description</label>
                    <div class="bg-light p-3 rounded">
                        {{ $demande->description }}
                    </div>
                </div>
                @endif
                
                @if($demande->admin_comment)
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Réponse administrateur</label>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        {{ $demande->admin_comment }}
                    </div>
                </div>
                @endif

                @if($demande->document_notes)
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Notes documents</label>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        {{ $demande->document_notes }}
                    </div>
                </div>
                @endif

                @if($demande->is_duplicate && $demande->originalRequest)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Doublon détecté</strong> — Cette demande est fusionnée avec 
                    <a href="{{ route('admin.demandes.show', $demande->originalRequest->id) }}" class="alert-link">
                        {{ $demande->originalRequest->tracking_code }}
                    </a>
                </div>
                @endif
            </div>

            <!-- Timeline du workflow -->
            <div class="card-modern p-3 mt-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-stream me-2 text-primary"></i>Timeline du Workflow</h6>
                @php
                    $steps = [
                        'soumise' => ['label' => 'Soumise', 'icon' => 'fa-paper-plane', 'desc' => 'Demande reçue'],
                        'en_revue' => ['label' => 'En revue', 'icon' => 'fa-eye', 'desc' => 'Examen par DSAR'],
                        'document_attente' => ['label' => 'Attente document', 'icon' => 'fa-file-alt', 'desc' => 'Attente courrier'],
                        'signee' => ['label' => 'Signée', 'icon' => 'fa-signature', 'desc' => 'Document signé'],
                        'scannee' => ['label' => 'Scannée', 'icon' => 'fa-file-pdf', 'desc' => 'Document scanné'],
                        'validee_dg' => ['label' => 'Validée DG', 'icon' => 'fa-stamp', 'desc' => 'Validation DG'],
                        'approuvee' => ['label' => 'Approuvée', 'icon' => 'fa-check-circle', 'desc' => 'Demande approuvée'],
                    ];
                    $currentStep = $demande->workflow_status ?? 'soumise';
                    $rejected = $currentStep === 'rejetee';
                    $passed = true;
                @endphp
                <div class="timeline">
                    @foreach($steps as $key => $step)
                        @php
                            $isCurrent = $currentStep === $key;
                            $isCompleted = !$rejected && $passed && !$isCurrent;
                            if ($isCurrent) $passed = false;
                        @endphp
                        <div class="timeline-item {{ $isCompleted ? 'completed' : ($isCurrent ? 'current' : '') }} {{ $rejected && !$isCompleted ? 'pending' : '' }}">
                            <div class="timeline-icon">
                                @if($isCompleted)
                                    <i class="fas fa-check"></i>
                                @elseif($isCurrent)
                                    <i class="fas {{ $step['icon'] }}"></i>
                                @else
                                    <i class="fas fa-circle" style="font-size:0.6rem;"></i>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-0 fw-bold">{{ $step['label'] }}</h6>
                                <small class="text-muted">{{ $step['desc'] }}</small>
                            </div>
                        </div>
                    @endforeach
                    @if($rejected)
                    <div class="timeline-item rejected">
                        <div class="timeline-icon"><i class="fas fa-times"></i></div>
                        <div class="timeline-content">
                            <h6 class="mb-0 fw-bold">Rejetée</h6>
                            <small class="text-muted">{{ $demande->admin_comment ?? 'Sans commentaire' }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historique des actions -->
            @if(!empty($demande->workflow_history))
            <div class="card-modern p-3 mt-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-history me-2 text-info"></i>Historique des actions</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Commentaire</th>
                                <th>Par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_reverse($demande->workflow_history) as $entry)
                            <tr>
                                <td class="small">{{ \Carbon\Carbon::parse($entry['timestamp'] ?? now())->format('d/m/Y H:i') }}</td>
                                <td class="small fw-bold">{{ $entry['action'] ?? '-' }}</td>
                                <td class="small">{{ $entry['comment'] ?? '-' }}</td>
                                <td class="small text-muted">{{ $entry['user_id'] ? \App\Models\User::find($entry['user_id'])?->name ?? 'Utilisateur '.$entry['user_id'] : 'Système' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar avec actions -->
        <div class="col-lg-4">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">⚡ Actions Rapides</h6>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.demandes.edit', $demande->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Traiter la demande
                    </a>
                    
                    @if($demande->status === 'approved')
                    <div class="alert alert-success mb-2">
                        <i class="fas fa-check-circle me-2"></i>Demande approuvée
                    </div>
                    @elseif($demande->status === 'rejected')
                    <div class="alert alert-danger mb-2">
                        <i class="fas fa-times-circle me-2"></i>Demande rejetée
                    </div>
                    @else
                    <div class="alert alert-warning mb-2">
                        <i class="fas fa-clock me-2"></i>En attente de traitement
                    </div>
                    @endif

                    <!-- Marquer comme doublon -->
                    @if(!$demande->is_duplicate)
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#duplicateModal">
                        <i class="fas fa-clone me-1"></i>Marquer comme doublon
                    </button>
                    @endif
                </div>
            </div>
            
            <!-- Informations de suivi -->
            <div class="card-modern p-3 mt-2">
                <h6 class="fw-bold mb-3">📊 Informations de Suivi</h6>
                
                @if($demande->tracking_code)
                <div class="mb-2">
                    <small class="text-muted">Code de suivi:</small>
                    <p class="mb-0">
                        <span class="badge bg-primary">{{ $demande->tracking_code }}</span>
                    </p>
                </div>
                @endif
                
                <div class="mb-2">
                    <small class="text-muted">Date de création:</small>
                    <p class="mb-0 small">{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y à H:i') }}</p>
                </div>
                
                @if($demande->processed_date)
                <div class="mb-2">
                    <small class="text-muted">Date de traitement:</small>
                    <p class="mb-0 small">{{ \Carbon\Carbon::parse($demande->processed_date)->format('d/m/Y à H:i') }}</p>
                </div>
                @endif
                
                @if($demande->assignedTo)
                <div class="mb-2">
                    <small class="text-muted">Assigné à:</small>
                    <p class="mb-0 small">{{ $demande->assignedTo->name }}</p>
                </div>
                @endif

                @if($demande->processor)
                <div class="mb-2">
                    <small class="text-muted">Traité par:</small>
                    <p class="mb-0 small">{{ $demande->processor->name }}</p>
                </div>
                @endif

                @if($demande->dgApprover)
                <div class="mb-2">
                    <small class="text-muted">Validée DG par:</small>
                    <p class="mb-0 small">{{ $demande->dgApprover->name }}</p>
                </div>
                @endif

                @if($demande->dg_approved_at)
                <div class="mb-2">
                    <small class="text-muted">Date validation DG:</small>
                    <p class="mb-0 small">{{ \Carbon\Carbon::parse($demande->dg_approved_at)->format('d/m/Y à H:i') }}</p>
                </div>
                @endif
            </div>

            <!-- Documents -->
            @if($demande->dg_signature_file || $demande->scan_file)
            <div class="card-modern p-3 mt-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-folder-open me-2 text-warning"></i>Documents</h6>
                @if($demande->dg_signature_file)
                <div class="mb-2">
                    <a href="{{ asset('storage/' . $demande->dg_signature_file) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-signature me-1"></i>Voir le document signé
                    </a>
                </div>
                @endif
                @if($demande->scan_file)
                <div class="mb-2">
                    <a href="{{ asset('storage/' . $demande->scan_file) }}" target="_blank" class="btn btn-outline-info btn-sm w-100">
                        <i class="fas fa-file-pdf me-1"></i>Voir le scan
                    </a>
                </div>
                @endif
            </div>
            @endif

            <!-- Doublons potentiels -->
            @if(!$demande->is_duplicate && $demande->requester_id)
                @php
                    $potentialDuplicates = \App\Models\PublicRequest::where('requester_id', $demande->requester_id)
                        ->where('id', '!=', $demande->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                @if($potentialDuplicates->count() > 0)
                <div class="card-modern p-3 mt-2 border-warning">
                    <h6 class="fw-bold mb-3 text-warning"><i class="fas fa-clone me-2"></i>Demandes similaires</h6>
                    @foreach($potentialDuplicates as $dup)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                        <div>
                            <small class="fw-bold">{{ $dup->tracking_code }}</small>
                            <div class="small text-muted">{{ $dup->subject }}</div>
                        </div>
                        <a href="{{ route('admin.demandes.show', $dup->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Modal Doublon -->
@if(!$demande->is_duplicate)
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.demandes.duplicate', $demande->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Marquer comme doublon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ID de la demande originale</label>
                        <input type="number" name="original_id" class="form-control" required placeholder="ID de la demande originale">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Raison du marquage doublon..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le doublon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.timeline { position: relative; padding-left: 20px; }
.timeline::before { content: ''; position: absolute; left: 8px; top: 4px; bottom: 4px; width: 2px; background: #e5e7eb; }
.timeline-item { position: relative; padding-bottom: 16px; display: flex; align-items: flex-start; gap: 12px; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-icon { width: 18px; height: 18px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; color: #6b7280; flex-shrink: 0; margin-top: 2px; }
.timeline-item.completed .timeline-icon { background: #22c55e; color: #fff; font-size: 0.5rem; }
.timeline-item.current .timeline-icon { background: #3b82f6; color: #fff; font-size: 0.55rem; }
.timeline-item.rejected .timeline-icon { background: #ef4444; color: #fff; font-size: 0.55rem; }
.timeline-content h6 { font-size: 0.9rem; }
</style>
@endpush