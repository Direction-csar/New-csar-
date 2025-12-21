@extends('layouts.dg-modern')

@section('title', 'Détails du Personnel')
@section('page-title', 'Détails du Personnel - Vue DG')

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
                            Détails du Personnel
                        </h1>
                        <p class="text-muted mb-0 small">
                            Consultation détaillée - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dg.personnel.index') }}" class="btn btn-outline-primary btn-modern btn-sm">
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
                            <i class="fas fa-user me-2"></i>{{ $employee->prenoms_nom ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Matricule</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-hashtag me-2"></i>{{ $employee->matricule ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Date de Naissance</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-birthday-cake me-2"></i>{{ \Carbon\Carbon::parse($employee->date_naissance)->format('d/m/Y') ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Lieu de Naissance</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-map-marker-alt me-2"></i>{{ $employee->lieu_naissance ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Sexe</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-venus-mars me-2"></i>{{ $employee->sexe ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nationalité</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-flag me-2"></i>{{ $employee->nationalite ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Situation Matrimoniale</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-heart me-2"></i>{{ $employee->situation_matrimoniale ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nombre d'Enfants</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-child me-2"></i>{{ $employee->nombre_enfants ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Adresse Complète</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-home me-2"></i>{{ $employee->adresse_complete ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-phone me-2 text-success"></i>
                    Contact
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Téléphone</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-phone me-2"></i>{{ $employee->contact_telephonique ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-envelope me-2"></i>{{ $employee->email ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Groupe Sanguin</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-tint me-2"></i>{{ $employee->groupe_sanguin ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Contact d'Urgence</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ $employee->contact_urgence_nom ?? 'N/A' }}
                        <br><small class="text-muted">{{ $employee->contact_urgence_telephone ?? 'N/A' }}</small>
                        <br><small class="text-muted">{{ $employee->contact_urgence_lien_parente ?? 'N/A' }}</small>
                    </div>
                </div>
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
                    <label class="form-label fw-bold">Poste Actuel</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-user-tie me-2"></i>{{ $employee->poste_actuel ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Direction/Service</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-building me-2"></i>{{ $employee->direction_service ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Statut</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-badge me-2"></i>{{ $employee->statut ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Localisation</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-map me-2"></i>{{ $employee->localisation_region ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Recrutement</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-plus me-2"></i>{{ \Carbon\Carbon::parse($employee->date_recrutement_csar)->format('d/m/Y') ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Prise de Service</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-check me-2"></i>{{ \Carbon\Carbon::parse($employee->date_prise_service_csar)->format('d/m/Y') ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-graduation-cap me-2 text-warning"></i>
                    Formation
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Diplôme Académique</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-certificate me-2"></i>{{ $employee->diplome_academique ?? 'N/A' }}
                    </div>
                </div>
                
                @if($employee->autres_diplomes_certifications)
                <div class="mb-3">
                    <label class="form-label fw-bold">Autres Diplômes</label>
                    <div class="p-2 bg-light rounded" style="min-height: 60px;">
                        <i class="fas fa-award me-2"></i>{{ $employee->autres_diplomes_certifications }}
                    </div>
                </div>
                @endif
                
                @if($employee->formations_professionnelles)
                <div class="mb-3">
                    <label class="form-label fw-bold">Formations Professionnelles</label>
                    <div class="p-2 bg-light rounded" style="min-height: 60px;">
                        <i class="fas fa-chalkboard-teacher me-2"></i>{{ $employee->formations_professionnelles }}
                    </div>
                </div>
                @endif
                
                @if($employee->logiciels_maitrises)
                <div class="mb-3">
                    <label class="form-label fw-bold">Logiciels Maîtrisés</label>
                    <div class="p-2 bg-light rounded" style="min-height: 60px;">
                        <i class="fas fa-laptop me-2"></i>{{ $employee->logiciels_maitrises }}
                    </div>
                </div>
                @endif
                
                @if($employee->langues_parlees)
                <div class="mb-3">
                    <label class="form-label fw-bold">Langues Parlées</label>
                    <div class="p-2 bg-light rounded" style="min-height: 60px;">
                        <i class="fas fa-language me-2"></i>{{ $employee->langues_parlees }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statut et validation -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-check-circle me-2 text-success"></i>
                    Statut et Validation
                </h6>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Statut de Validation</label>
                        <div class="p-2">
                            @if($employee->statut_validation == 'Valide')
                                <span class="badge bg-success fs-6">Validé</span>
                            @elseif($employee->statut_validation == 'En attente')
                                <span class="badge bg-warning fs-6">En Attente</span>
                            @elseif($employee->statut_validation == 'Rejete')
                                <span class="badge bg-danger fs-6">Rejeté</span>
                            @else
                                <span class="badge bg-secondary fs-6">{{ $employee->statut_validation ?? 'N/A' }}</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($employee->date_validation)
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Date de Validation</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-calendar-check me-2"></i>{{ \Carbon\Carbon::parse($employee->date_validation)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endif
                    
                    @if($employee->valide_par)
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Validé par</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-user-check me-2"></i>Utilisateur #{{ $employee->valide_par }}
                        </div>
                    </div>
                    @endif
                    
                    @if($employee->commentaire_validation)
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Commentaire de Validation</label>
                        <div class="p-2 bg-light rounded" style="min-height: 60px;">
                            <i class="fas fa-comment me-2"></i>{{ $employee->commentaire_validation }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





































