@extends('layouts.drh-portal')

@section('title', 'Détails du Personnel - Administration')

@section('page-title', 'Fiche Personnel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Détails du Personnel</h5>
                    <div>
                        <a href="{{ route('admin.drh.personnel.edit', $personnel->id) }}" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Photo Section -->
                        <div class="col-md-3 text-center mb-4">
                            @if($personnel->photo_personnelle)
                                <img src="{{ $personnel->photo_url }}" alt="{{ $personnel->prenoms_nom }}" 
                                     class="img-fluid rounded shadow" style="max-width: 250px;">
                            @else
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded shadow mx-auto" 
                                     style="width: 250px; height: 300px; font-size: 5rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            
                            <div class="mt-3">
                                @if($personnel->statut_validation === 'Valide')
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle me-1"></i>Validé
                                    </span>
                                @elseif($personnel->statut_validation === 'En attente')
                                    <span class="badge bg-warning fs-6">
                                        <i class="fas fa-clock me-1"></i>En attente
                                    </span>
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-times-circle me-1"></i>Rejeté
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Details Section -->
                        <div class="col-md-9">
                            <!-- I. Informations personnelles -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-user me-2"></i>Informations personnelles
                            </h5>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Nom & Prénoms</label>
                                    <p class="fw-bold">{{ $personnel->prenoms_nom }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Date de naissance</label>
                                    <p class="fw-bold">{{ $personnel->date_naissance ? $personnel->date_naissance->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Lieu de naissance</label>
                                    <p class="fw-bold">{{ $personnel->lieu_naissance ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Tranche d'âge</label>
                                    <p class="fw-bold">{{ $personnel->tranche_age ?? '-' }} ans</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Nationalité</label>
                                    <p class="fw-bold">{{ $personnel->nationalite ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">N° CNI</label>
                                    <p class="fw-bold">{{ $personnel->numero_cni ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Sexe</label>
                                    <p class="fw-bold">{{ $personnel->sexe ?? '-' }}</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Situation matrimoniale</label>
                                    <p class="fw-bold">{{ $personnel->situation_matrimoniale ?? '-' }}</p>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="text-muted small">Enfants</label>
                                    <p class="fw-bold">{{ $personnel->nombre_enfants ?? 0 }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Téléphone</label>
                                    <p class="fw-bold">{{ $personnel->contact_telephonique ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Email</label>
                                    <p class="fw-bold">{{ $personnel->email ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Groupe sanguin</label>
                                    <p class="fw-bold">{{ $personnel->groupe_sanguin ?? '-' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Adresse complète</label>
                                    <p class="fw-bold">{{ $personnel->adresse_complete ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- II. Situation administrative -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-building me-2"></i>Situation administrative
                            </h5>
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Matricule</label>
                                    <p class="fw-bold">{{ $personnel->matricule ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Date de recrutement</label>
                                    <p class="fw-bold">{{ $personnel->date_recrutement_csar ? $personnel->date_recrutement_csar->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Date prise de service</label>
                                    <p class="fw-bold">{{ $personnel->date_prise_service_csar ? $personnel->date_prise_service_csar->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Statut</label>
                                    <p class="fw-bold">{{ $personnel->statut ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Poste actuel</label>
                                    <p class="fw-bold"><span class="badge bg-info fs-6">{{ $personnel->poste_actuel ?? '-' }}</span></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Direction / Service</label>
                                    <p class="fw-bold">{{ $personnel->direction_service ?? '-' }}</p>
                                </div>
                                @if($personnel->localisation_region)
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Localisation</label>
                                    <p class="fw-bold">{{ $personnel->localisation_region }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- III. Parcours professionnel -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-briefcase me-2"></i>Parcours professionnel
                            </h5>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Dernier poste avant le CSAR</label>
                                    <p class="fw-bold">{{ $personnel->dernier_poste_avant_csar ?? '-' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Formations professionnelles</label>
                                    <p class="fw-bold">{{ $personnel->formations_professionnelles ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Diplôme académique</label>
                                    <p class="fw-bold">{{ $personnel->diplome_academique ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Autres diplômes et certifications</label>
                                    <p class="fw-bold">{{ $personnel->autres_diplomes_certifications ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- IV. Compétences spécifiques -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-laptop-code me-2"></i>Compétences spécifiques
                            </h5>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Logiciels maîtrisés</label>
                                    @php
                                        $logiciels = $personnel->logiciels_maitrises;
                                        if (is_string($logiciels)) {
                                            $logiciels = json_decode($logiciels, true) ?? [];
                                        }
                                        $logiciels = $logiciels ?? [];
                                    @endphp
                                    @if(!empty($logiciels) && is_array($logiciels))
                                        @foreach($logiciels as $logiciel)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $logiciel }}</span>
                                        @endforeach
                                    @else
                                        <p class="fw-bold">-</p>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Langues parlées</label>
                                    @php
                                        $langues = $personnel->langues_parlees;
                                        if (is_string($langues)) {
                                            $langues = json_decode($langues, true) ?? [];
                                        }
                                        $langues = $langues ?? [];
                                    @endphp
                                    @if(!empty($langues) && is_array($langues))
                                        @foreach($langues as $langue)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $langue }}</span>
                                        @endforeach
                                    @else
                                        <p class="fw-bold">-</p>
                                    @endif
                                </div>
                                @if($personnel->autres_aptitudes)
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Autres aptitudes</label>
                                    <p class="fw-bold">{{ $personnel->autres_aptitudes }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- V. Aspirations professionnelles -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-chart-line me-2"></i>Aspirations professionnelles
                            </h5>
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Aspirations</label>
                                    <p class="fw-bold">{{ $personnel->aspirations_professionnelles ?? '-' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Intérêt pour de nouvelles responsabilités</label>
                                    <p class="fw-bold">
                                        @if($personnel->interet_nouvelles_responsabilites === 'Oui')
                                            <span class="badge bg-success">Oui</span>
                                        @elseif($personnel->interet_nouvelles_responsabilites === 'Non')
                                            <span class="badge bg-danger">Non</span>
                                        @else
                                            <span class="badge bg-secondary">Neutre</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- VI. Autres informations -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="fas fa-info-circle me-2"></i>Autres informations
                            </h5>
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Taille vêtements</label>
                                    <p class="fw-bold">{{ $personnel->taille_vetements ?? '-' }}</p>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Contact d'urgence</label>
                                    <p class="fw-bold">{{ $personnel->contact_urgence_nom ?? '-' }}</p>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Téléphone urgence</label>
                                    <p class="fw-bold">{{ $personnel->contact_urgence_telephone ?? '-' }}</p>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="text-muted small">Lien de parenté</label>
                                    <p class="fw-bold">{{ $personnel->contact_urgence_lien_parente ?? '-' }}</p>
                                </div>
                                @if($personnel->observations_personnelles)
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted small">Observations personnelles</label>
                                    <p class="fw-bold">{{ $personnel->observations_personnelles }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                    </a>
                    <div>
                        <a href="{{ route('admin.drh.personnel.edit', $personnel->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <form action="{{ route('admin.personnel.destroy', $personnel->id) }}" 
                              method="POST" class="d-inline" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce personnel ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

