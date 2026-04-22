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
                        <p class="text-muted mb-0 small">Code de suivi: {{ $demande->tracking_code ?? 'CSAR-' . $demande->id }}</p>
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
                
            </div>
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
            </div>
        </div>
    </div>
</div>
@endsection