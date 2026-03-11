@extends('layouts.dg-modern')

@section('title', 'Détails de l\'Utilisateur')
@section('page-title', 'Détails de l\'Utilisateur - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Détails de l'Utilisateur
                        </h1>
                        <p class="text-muted mb-0 small">
                            Consultation détaillée - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dg.users.index') }}" class="btn btn-outline-primary btn-modern btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations personnelles -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-id-card me-2 text-primary"></i>
                    Informations Personnelles
                </h6>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nom Complet</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-user me-2"></i>{{ $user->name ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-envelope me-2"></i>{{ $user->email ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Téléphone</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-phone me-2"></i>{{ $user->phone ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Adresse</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-map-marker-alt me-2"></i>{{ $user->address ?? 'N/A' }}
                        </div>
                    </div>
                    
                    @if($user->avatar)
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Photo de Profil</label>
                        <div class="p-2 bg-light rounded">
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-shield-alt me-2 text-success"></i>
                    Informations de Compte
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Rôle</label>
                    <div class="p-2">
                        @if($user->role == 'admin')
                            <span class="badge bg-danger fs-6">Administrateur</span>
                        @elseif($user->role == 'dg')
                            <span class="badge bg-primary fs-6">Directeur Général</span>
                        @elseif($user->role == 'responsable')
                            <span class="badge bg-warning fs-6">Responsable</span>
                        @elseif($user->role == 'agent')
                            <span class="badge bg-info fs-6">Agent</span>
                        @else
                            <span class="badge bg-secondary fs-6">{{ $user->role ?? 'N/A' }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Statut</label>
                    <div class="p-2">
                        @if($user->is_active)
                            <span class="badge bg-success fs-6">Actif</span>
                        @else
                            <span class="badge bg-danger fs-6">Inactif</span>
                        @endif
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Vérifié</label>
                    <div class="p-2">
                        @if($user->email_verified_at)
                            <span class="badge bg-success fs-6">Vérifié</span>
                        @else
                            <span class="badge bg-warning fs-6">Non Vérifié</span>
                        @endif
                    </div>
                </div>
                
                @if($user->two_factor_enabled)
                <div class="mb-3">
                    <label class="form-label fw-bold">Authentification 2FA</label>
                    <div class="p-2">
                        <span class="badge bg-info fs-6">Activée</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Informations professionnelles -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-briefcase me-2 text-info"></i>
                    Informations Professionnelles
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Poste</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-user-tie me-2"></i>{{ $user->position ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Département</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-building me-2"></i>{{ $user->department ?? 'N/A' }}
                    </div>
                </div>
                
                @if($user->warehouse_id)
                <div class="mb-3">
                    <label class="form-label fw-bold">Entrepôt Assigné</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-warehouse me-2"></i>Entrepôt #{{ $user->warehouse_id }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-calendar me-2 text-warning"></i>
                    Dates et Activité
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Création</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-plus me-2"></i>{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Dernière Mise à Jour</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-edit me-2"></i>{{ \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                
                @if($user->last_login_at)
                <div class="mb-3">
                    <label class="form-label fw-bold">Dernière Connexion</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-sign-in-alt me-2"></i>{{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}
                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}</small>
                    </div>
                </div>
                @else
                <div class="mb-3">
                    <label class="form-label fw-bold">Dernière Connexion</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-times me-2"></i>Jamais connecté
                    </div>
                </div>
                @endif
                
                @if($user->email_verified_at)
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Vérifié le</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-check-circle me-2"></i>{{ \Carbon\Carbon::parse($user->email_verified_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Activité Récente
                </h6>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                            <h5 class="mb-1">{{ \Carbon\Carbon::parse($user->created_at)->diffInDays(now()) }}</h5>
                            <small class="text-muted">Jours depuis l'inscription</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-clock fa-2x text-info mb-2"></i>
                            <h5 class="mb-1">
                                @if($user->last_login_at)
                                    {{ \Carbon\Carbon::parse($user->last_login_at)->diffInDays(now()) }}
                                @else
                                    N/A
                                @endif
                            </h5>
                            <small class="text-muted">Jours depuis dernière connexion</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                            <h5 class="mb-1">
                                @if($user->two_factor_enabled)
                                    2FA
                                @else
                                    Standard
                                @endif
                            </h5>
                            <small class="text-muted">Type d'authentification</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








































