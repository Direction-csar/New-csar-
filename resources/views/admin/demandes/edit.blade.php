@extends('layouts.admin')

@section('title', 'Traiter la Demande')
@section('page-title', 'Traiter la Demande')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">✏️ Traiter la Demande</h1>
                        <p class="text-muted mb-0 small">Code de suivi: {{ $demande->tracking_code ?? 'CSAR-' . $demande->id }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.demandes.show', $demande->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Voir
                        </a>
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de la demande -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">📋 Informations de la Demande</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Demandeur:</strong> {{ $demande->full_name }}</p>
                        <p><strong>Email:</strong> {{ $demande->email }}</p>
                        <p><strong>Téléphone:</strong> {{ $demande->phone }}</p>
                        <p><strong>Région:</strong> {{ $demande->region }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Type:</strong> {{ ucfirst($demande->type) }}</p>
                        <p><strong>Date de demande:</strong> {{ optional($demande->request_date)->format('d/m/Y') ?? \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</p>
                        <p><strong>Statut actuel:</strong> 
                            <span class="badge bg-{{ $demande->status === 'approved' ? 'success' : ($demande->status === 'rejected' ? 'danger' : ($demande->status === 'completed' ? 'info' : 'warning')) }}">
                                {{ ucfirst($demande->status) }}
                            </span>
                        </p>
                        @if($demande->address)
                        <p><strong>Adresse:</strong> {{ $demande->address }}</p>
                        @endif
                    </div>
                </div>
                @if($demande->description)
                <div class="mt-3">
                    <p><strong>Description:</strong></p>
                    <div class="bg-light p-3 rounded">
                        {{ $demande->description }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Formulaire de traitement -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.demandes.update', $demande->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <h6 class="fw-bold mb-3">⚙️ Traitement de la Demande</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label small fw-bold">Statut *</label>
                            <select class="form-select form-select-sm" id="status" name="status" required>
                                <option value="pending" {{ $demande->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="approved" {{ $demande->status === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                <option value="rejected" {{ $demande->status === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                <option value="completed" {{ $demande->status === 'completed' ? 'selected' : '' }}>Terminée</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label small fw-bold">Assigner à</label>
                            <select class="form-select form-select-sm" id="assigned_to" name="assigned_to">
                                <option value="">Non assigné</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $demande->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_comment" class="form-label small fw-bold">Commentaire administrateur</label>
                        <textarea class="form-control form-control-sm" id="admin_comment" name="admin_comment" rows="4" 
                                  placeholder="Ajoutez un commentaire sur le traitement de cette demande...">{{ $demande->admin_comment ?? '' }}</textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Mettre à jour
                        </button>
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection