@extends('layouts.drh-portal')

@section('title', 'Ajouter du Personnel - DRH')

@section('page-title', 'Ajouter du Personnel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Enregistrement du Personnel CSAR</h5>
                    <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                    </a>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.drh.personnel.store') }}" method="POST" enctype="multipart/form-data" id="personnelForm">
                        @csrf
                        
                        <!-- I. Informations personnelles -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-user me-2"></i>I. Informations personnelles
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Prénoms et Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="prenoms_nom" value="{{ old('prenoms_nom') }}" 
                                           class="form-control @error('prenoms_nom') is-invalid @enderror" required>
                                    @error('prenoms_nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                    <input type="date" name="date_naissance" value="{{ old('date_naissance') }}" 
                                           class="form-control @error('date_naissance') is-invalid @enderror" required>
                                    @error('date_naissance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Lieu de naissance <span class="text-danger">*</span></label>
                                    <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance') }}" 
                                           class="form-control @error('lieu_naissance') is-invalid @enderror" required>
                                    @error('lieu_naissance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Tranche d'âge (18 - 60 ans) <span class="text-danger">*</span></label>
                                    <select name="tranche_age" class="form-select @error('tranche_age') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="18-25" {{ old('tranche_age') == '18-25' ? 'selected' : '' }}>18 - 25 ans</option>
                                        <option value="26-35" {{ old('tranche_age') == '26-35' ? 'selected' : '' }}>26 - 35 ans</option>
                                        <option value="36-45" {{ old('tranche_age') == '36-45' ? 'selected' : '' }}>36 - 45 ans</option>
                                        <option value="46-55" {{ old('tranche_age') == '46-55' ? 'selected' : '' }}>46 - 55 ans</option>
                                        <option value="56-60" {{ old('tranche_age') == '56-60' ? 'selected' : '' }}>56 - 60 ans</option>
                                    </select>
                                    @error('tranche_age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Nationalité <span class="text-danger">*</span></label>
                                    <input type="text" name="nationalite" value="{{ old('nationalite', 'Sénégalaise') }}" 
                                           class="form-control @error('nationalite') is-invalid @enderror" required>
                                    @error('nationalite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Numéro carte d'identité nationale <span class="text-danger">*</span></label>
                                    <input type="text" name="numero_cni" value="{{ old('numero_cni') }}" 
                                           class="form-control @error('numero_cni') is-invalid @enderror" required>
                                    @error('numero_cni')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Sexe <span class="text-danger">*</span></label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sexe" id="sexeM" value="Masculin" 
                                                   {{ old('sexe') == 'Masculin' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="sexeM">Masculin</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sexe" id="sexeF" value="Féminin" 
                                                   {{ old('sexe') == 'Féminin' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="sexeF">Féminin</label>
                                        </div>
                                    </div>
                                    @error('sexe')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Situation matrimoniale <span class="text-danger">*</span></label>
                                    <select name="situation_matrimoniale" class="form-select @error('situation_matrimoniale') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Célibataire" {{ old('situation_matrimoniale') == 'Célibataire' ? 'selected' : '' }}>Célibataire</option>
                                        <option value="Marié (e)" {{ old('situation_matrimoniale') == 'Marié (e)' ? 'selected' : '' }}>Marié (e)</option>
                                        <option value="Divorcé (e)" {{ old('situation_matrimoniale') == 'Divorcé (e)' ? 'selected' : '' }}>Divorcé (e)</option>
                                        <option value="Veuf" {{ old('situation_matrimoniale') == 'Veuf' ? 'selected' : '' }}>Veuf</option>
                                        <option value="Veuve" {{ old('situation_matrimoniale') == 'Veuve' ? 'selected' : '' }}>Veuve</option>
                                    </select>
                                    @error('situation_matrimoniale')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Nombre d'enfants <span class="text-danger">*</span></label>
                                    <select name="nombre_enfants" class="form-select @error('nombre_enfants') is-invalid @enderror" required>
                                        @for ($i = 0; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ old('nombre_enfants') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('nombre_enfants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Contact téléphonique <span class="text-danger">*</span></label>
                                    <input type="tel" name="contact_telephonique" value="{{ old('contact_telephonique') }}" 
                                           class="form-control @error('contact_telephonique') is-invalid @enderror" required>
                                    @error('contact_telephonique')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" 
                                           class="form-control @error('email') is-invalid @enderror" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Groupe sanguin <span class="text-danger">*</span></label>
                                    <select name="groupe_sanguin" class="form-select @error('groupe_sanguin') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="A+" {{ old('groupe_sanguin') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('groupe_sanguin') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('groupe_sanguin') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('groupe_sanguin') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('groupe_sanguin') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('groupe_sanguin') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('groupe_sanguin') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('groupe_sanguin') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('groupe_sanguin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Adresse complète <span class="text-danger">*</span></label>
                                    <textarea name="adresse_complete" rows="2" 
                                              class="form-control @error('adresse_complete') is-invalid @enderror" required>{{ old('adresse_complete') }}</textarea>
                                    @error('adresse_complete')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- II. Situation administrative -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-building me-2"></i>II. Situation administrative
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Matricule <span class="text-danger">*</span></label>
                                    <input type="text" name="matricule" value="{{ old('matricule') }}" 
                                           class="form-control @error('matricule') is-invalid @enderror" 
                                           placeholder="Auto-généré si vide">
                                    <small class="text-muted">Laissez vide pour génération automatique</small>
                                    @error('matricule')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Date de recrutement au CSAR <span class="text-danger">*</span></label>
                                    <input type="date" name="date_recrutement_csar" value="{{ old('date_recrutement_csar') }}" 
                                           class="form-control @error('date_recrutement_csar') is-invalid @enderror" required>
                                    @error('date_recrutement_csar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Date prise de service au CSAR <span class="text-danger">*</span></label>
                                    <input type="date" name="date_prise_service_csar" value="{{ old('date_prise_service_csar') }}" 
                                           class="form-control @error('date_prise_service_csar') is-invalid @enderror" required>
                                    @error('date_prise_service_csar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select name="statut" id="statutSelect" class="form-select @error('statut') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Fonctionnaire" {{ old('statut') == 'Fonctionnaire' ? 'selected' : '' }}>Fonctionnaire</option>
                                        <option value="Contractuel" {{ old('statut') == 'Contractuel' ? 'selected' : '' }}>Contractuel</option>
                                        <option value="Stagiaire" {{ old('statut') == 'Stagiaire' ? 'selected' : '' }}>Stagiaire</option>
                                        <option value="Journalier" {{ old('statut') == 'Journalier' ? 'selected' : '' }}>Journalier</option>
                                        <option value="Autre" {{ old('statut') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    <input type="text" name="statut_autre" id="statutAutre" 
                                           class="form-control mt-2 d-none" placeholder="Précisez le statut">
                                    @error('statut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Poste actuel <span class="text-danger">*</span></label>
                                    <select name="poste_actuel" id="posteSelect" class="form-select @error('poste_actuel') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Directeur Général">Directeur Général</option>
                                        <option value="Secrétaire général">Secrétaire général</option>
                                        <option value="Directeur">Directeur</option>
                                        <option value="Agent Comptable">Agent Comptable</option>
                                        <option value="Conseiller technique">Conseiller technique</option>
                                        <option value="Chef de cellule">Chef de cellule</option>
                                        <option value="Inspecteur régional">Inspecteur régional</option>
                                        <option value="Chef de division">Chef de division</option>
                                        <option value="Adjoint inspecteur régional">Adjoint inspecteur régional</option>
                                        <option value="Comptable">Comptable</option>
                                        <option value="Comptable des Matières">Comptable des Matières</option>
                                        <option value="Chef de bureau">Chef de bureau</option>
                                        <option value="Agent technique">Agent technique</option>
                                        <option value="Agent administratif">Agent administratif</option>
                                        <option value="Assistante de direction">Assistante de direction</option>
                                        <option value="Assistant administratif">Assistant administratif</option>
                                        <option value="Secrétaire">Secrétaire</option>
                                        <option value="Magasinier">Magasinier</option>
                                        <option value="Gérant de complexe">Gérant de complexe</option>
                                        <option value="Technicien supérieur">Technicien supérieur</option>
                                        <option value="Chauffeur">Chauffeur</option>
                                        <option value="Chef de parc">Chef de parc</option>
                                        <option value="Manœuvre">Manœuvre</option>
                                        <option value="Planton">Planton</option>
                                        <option value="Coursier">Coursier</option>
                                        <option value="Technicien de surface">Technicien de surface</option>
                                        <option value="Gardien">Gardien</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                    <input type="text" name="poste_autre" id="posteAutre" 
                                           class="form-control mt-2 d-none" placeholder="Précisez le poste">
                                    @error('poste_actuel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Direction / Service d'affectation <span class="text-danger">*</span></label>
                                    <select name="direction_service" class="form-select @error('direction_service') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Conseil d'administration">Conseil d'administration</option>
                                        <option value="Direction Générale">Direction Générale</option>
                                        <option value="Secrétariat général">Secrétariat général</option>
                                        <option value="DSAR">DSAR</option>
                                        <option value="DFC">DFC</option>
                                        <option value="DPSE">DPSE</option>
                                        <option value="DRH">DRH</option>
                                        <option value="DTL">DTL</option>
                                        <option value="CCG">CCG</option>
                                        <option value="CPM">CPM</option>
                                        <option value="CI">CI</option>
                                        <option value="CIA">CIA</option>
                                        <option value="AC">AC</option>
                                        <option value="IR">IR</option>
                                    </select>
                                    @error('direction_service')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Localisation (si en région) <span class="text-danger">*</span></label>
                                    <select name="localisation_region" class="form-select @error('localisation_region') is-invalid @enderror">
                                        <option value="">Sélectionner...</option>
                                        <option value="Dakar">Dakar</option>
                                        <option value="Thies">Thies</option>
                                        <option value="Diourbel">Diourbel</option>
                                        <option value="Fatick">Fatick</option>
                                        <option value="Kaffrine">Kaffrine</option>
                                        <option value="Matam">Matam</option>
                                        <option value="Kaolack">Kaolack</option>
                                        <option value="Kedougou">Kedougou</option>
                                        <option value="Louga">Louga</option>
                                        <option value="Saint-Louis">Saint-Louis</option>
                                        <option value="Tambacounda">Tambacounda</option>
                                        <option value="Kolda / Sedhiou">Kolda / Sedhiou</option>
                                        <option value="Ziguinchor">Ziguinchor</option>
                                    </select>
                                    @error('localisation_region')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- III. Parcours professionnel -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-briefcase me-2"></i>III. Parcours professionnel (expériences professionnelles)
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Dernier poste occupé avant le CSAR <span class="text-danger">*</span></label>
                                    <textarea name="dernier_poste_avant_csar" rows="2" 
                                              class="form-control @error('dernier_poste_avant_csar') is-invalid @enderror" required>{{ old('dernier_poste_avant_csar') }}</textarea>
                                    @error('dernier_poste_avant_csar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Formations professionnelles suivies <span class="text-danger">*</span></label>
                                    <textarea name="formations_professionnelles" rows="3" 
                                              class="form-control @error('formations_professionnelles') is-invalid @enderror" required>{{ old('formations_professionnelles') }}</textarea>
                                    @error('formations_professionnelles')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Diplômes académiques <span class="text-danger">*</span></label>
                                    <select name="diplome_academique" id="diplomeSelect" class="form-select @error('diplome_academique') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="Doctorat">Doctorat</option>
                                        <option value="Master">Master</option>
                                        <option value="DESS">DESS</option>
                                        <option value="Maîtrise">Maîtrise</option>
                                        <option value="Licence">Licence</option>
                                        <option value="DEUG">DEUG</option>
                                        <option value="Baccalauréat">Baccalauréat</option>
                                        <option value="BFEM">BFEM</option>
                                        <option value="CFEE">CFEE</option>
                                        <option value="Sans diplôme">Sans diplôme</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                    <input type="text" name="diplome_autre" id="diplomeAutre" 
                                           class="form-control mt-2 d-none" placeholder="Précisez le diplôme">
                                    @error('diplome_academique')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Autres diplômes et Certifications</label>
                                    <textarea name="autres_diplomes_certifications" rows="2" 
                                              class="form-control @error('autres_diplomes_certifications') is-invalid @enderror">{{ old('autres_diplomes_certifications') }}</textarea>
                                    @error('autres_diplomes_certifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- IV. Compétences spécifiques -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-laptop-code me-2"></i>IV. Compétences spécifiques
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Logiciels ou outils maîtrisés <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="logiciels_maitrises[]" value="PowerPoint" id="logiciel1">
                                                <label class="form-check-label" for="logiciel1">PowerPoint</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="logiciels_maitrises[]" value="Excel" id="logiciel2">
                                                <label class="form-check-label" for="logiciel2">Excel</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="logiciels_maitrises[]" value="Word" id="logiciel3">
                                                <label class="form-check-label" for="logiciel3">Word</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="logiciels_maitrises[]" value="Programmation" id="logiciel4">
                                                <label class="form-check-label" for="logiciel4">Programmation (C,C+,HTML,…)</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="logiciels_maitrises[]" value="SAARI" id="logiciel5">
                                                <label class="form-check-label" for="logiciel5">SAARI</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text">Autre</span>
                                                <input type="text" class="form-control" id="logiciel_autre" placeholder="Spécifiez d'autres logiciels">
                                                <button type="button" class="btn btn-outline-secondary" onclick="ajouterLogicielAutre()">Ajouter</button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('logiciels_maitrises')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Langues parlées / écrites <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="langues_parlees[]" value="Français" id="langue1">
                                        <label class="form-check-label" for="langue1">Français</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="langues_parlees[]" value="Anglais" id="langue2">
                                        <label class="form-check-label" for="langue2">Anglais</label>
                                    </div>
                                    <div class="input-group mt-2">
                                        <span class="input-group-text">Autre</span>
                                        <input type="text" class="form-control" id="langue_autre" placeholder="Spécifiez d'autres langues">
                                        <button type="button" class="btn btn-outline-secondary" onclick="ajouterLangueAutre()">Ajouter</button>
                                    </div>
                                    @error('langues_parlees')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Autres aptitudes spécifiques</label>
                                    <textarea name="autres_aptitudes" rows="4" 
                                              class="form-control @error('autres_aptitudes') is-invalid @enderror">{{ old('autres_aptitudes') }}</textarea>
                                    @error('autres_aptitudes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- V. Aspirations professionnelles -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-chart-line me-2"></i>V. Aspirations professionnelles (plan de carrière au sein du CSAR)
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Aspirations professionnelles <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="aspirations_professionnelles" value="Formation" id="aspiration1">
                                        <label class="form-check-label" for="aspiration1">Formation</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="aspirations_professionnelles" value="Spécialisation" id="aspiration2">
                                        <label class="form-check-label" for="aspiration2">Spécialisation</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="aspirations_professionnelles" value="Mobilité" id="aspiration3">
                                        <label class="form-check-label" for="aspiration3">Mobilité</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="aspirations_professionnelles" value="Néant" id="aspiration4">
                                        <label class="form-check-label" for="aspiration4">Néant</label>
                                    </div>
                                    @error('aspirations_professionnelles')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Donnez plus de détails concernant vos aspirations professionnelles</label>
                                    <textarea name="details_aspirations" rows="3" 
                                              class="form-control @error('details_aspirations') is-invalid @enderror">{{ old('details_aspirations') }}</textarea>
                                    @error('details_aspirations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Intérêt pour de nouvelles responsabilités <span class="text-danger">*</span></label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="interet_nouvelles_responsabilites" value="Oui" id="interet1" required>
                                            <label class="form-check-label" for="interet1">Oui</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="interet_nouvelles_responsabilites" value="Non" id="interet2" required>
                                            <label class="form-check-label" for="interet2">Non</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="interet_nouvelles_responsabilites" value="Neutre" id="interet3" required>
                                            <label class="form-check-label" for="interet3">Neutre</label>
                                        </div>
                                    </div>
                                    @error('interet_nouvelles_responsabilites')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- VI. Photo personnelle -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-camera me-2"></i>VI. Photo personnelle (format photo d'identité)
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Photo <span class="text-danger">*</span></label>
                                    <input type="file" name="photo_personnelle" accept="image/*" 
                                           class="form-control @error('photo_personnelle') is-invalid @enderror" 
                                           id="photoInput" required onchange="previewPhoto(this)">
                                    <small class="text-muted">Format accepté : image (max 1 GB). Recommandé : photo d'identité</small>
                                    @error('photo_personnelle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="mt-3" id="photoPreview" style="display: none;">
                                        <img id="previewImage" src="" alt="Aperçu" style="max-width: 200px; max-height: 200px; border: 2px solid #ddd; border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VII. Taille vêtements -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-tshirt me-2"></i>VII. Taille Lacoste / T-shirts / Gilet
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Taille <span class="text-danger">*</span></label>
                                    <select name="taille_vetements" id="tailleSelect" class="form-select @error('taille_vetements') is-invalid @enderror" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                        <option value="XXXL">XXXL</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                    <input type="text" name="taille_autre" id="tailleAutre" 
                                           class="form-control mt-2 d-none" placeholder="Précisez la taille">
                                    @error('taille_vetements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- VIII. Notification d'urgence -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-phone-alt me-2"></i>VIII. Notification d'urgence
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Prénom et Nom de la personne à contacter en cas d'urgence <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_urgence_nom" value="{{ old('contact_urgence_nom') }}" 
                                           class="form-control @error('contact_urgence_nom') is-invalid @enderror" required>
                                    @error('contact_urgence_nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Numéro téléphone de la personne à contacter en cas d'urgence <span class="text-danger">*</span></label>
                                    <input type="tel" name="contact_urgence_telephone" value="{{ old('contact_urgence_telephone') }}" 
                                           class="form-control @error('contact_urgence_telephone') is-invalid @enderror" required>
                                    @error('contact_urgence_telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Lien de parenté avec la personne <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_urgence_lien_parente" value="{{ old('contact_urgence_lien_parente') }}" 
                                           class="form-control @error('contact_urgence_lien_parente') is-invalid @enderror" 
                                           placeholder="Ex: Père, Mère, Frère, etc." required>
                                    @error('contact_urgence_lien_parente')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- IX. Observations personnelles -->
                        <div class="mb-5">
                            <h4 class="border-bottom pb-2 mb-4 text-primary">
                                <i class="fas fa-comment me-2"></i>IX. Observations personnelles
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Commentaires ou précisions utiles</label>
                                    <textarea name="observations_personnelles" rows="4" 
                                              class="form-control @error('observations_personnelles') is-invalid @enderror">{{ old('observations_personnelles') }}</textarea>
                                    @error('observations_personnelles')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card-footer bg-light d-flex justify-content-between">
                            <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-1"></i>Enregistrer le personnel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Photo preview
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').style.display = 'block';
            document.getElementById('previewImage').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Show/Hide "Autre" fields
document.getElementById('statutSelect')?.addEventListener('change', function() {
    const autreField = document.getElementById('statutAutre');
    if (this.value === 'Autre') {
        autreField.classList.remove('d-none');
        autreField.required = true;
    } else {
        autreField.classList.add('d-none');
        autreField.required = false;
    }
});

document.getElementById('posteSelect')?.addEventListener('change', function() {
    const autreField = document.getElementById('posteAutre');
    if (this.value === 'Autre') {
        autreField.classList.remove('d-none');
        autreField.required = true;
    } else {
        autreField.classList.add('d-none');
        autreField.required = false;
    }
});

document.getElementById('diplomeSelect')?.addEventListener('change', function() {
    const autreField = document.getElementById('diplomeAutre');
    if (this.value === 'Autre') {
        autreField.classList.remove('d-none');
        autreField.required = true;
    } else {
        autreField.classList.add('d-none');
        autreField.required = false;
    }
});

document.getElementById('tailleSelect')?.addEventListener('change', function() {
    const autreField = document.getElementById('tailleAutre');
    if (this.value === 'Autre') {
        autreField.classList.remove('d-none');
        autreField.required = true;
    } else {
        autreField.classList.add('d-none');
        autreField.required = false;
    }
});

// Add custom software
function ajouterLogicielAutre() {
    const input = document.getElementById('logiciel_autre');
    if (input.value.trim()) {
        const container = input.closest('.col-md-6');
        const checkbox = document.createElement('div');
        checkbox.className = 'form-check mt-2';
        checkbox.innerHTML = `
            <input class="form-check-input" type="checkbox" name="logiciels_maitrises[]" value="${input.value}" checked>
            <label class="form-check-label">${input.value}</label>
        `;
        container.insertBefore(checkbox, input.parentElement);
        input.value = '';
    }
}

// Add custom language
function ajouterLangueAutre() {
    const input = document.getElementById('langue_autre');
    if (input.value.trim()) {
        const container = input.closest('.col-md-6');
        const checkbox = document.createElement('div');
        checkbox.className = 'form-check';
        checkbox.innerHTML = `
            <input class="form-check-input" type="checkbox" name="langues_parlees[]" value="${input.value}" checked>
            <label class="form-check-label">${input.value}</label>
        `;
        container.insertBefore(checkbox, input.parentElement);
        input.value = '';
    }
}

// Form validation
document.getElementById('personnelForm')?.addEventListener('submit', function(e) {
    const logiciels = document.querySelectorAll('input[name="logiciels_maitrises[]"]:checked');
    const langues = document.querySelectorAll('input[name="langues_parlees[]"]:checked');
    
    if (logiciels.length === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins un logiciel maîtrisé');
        return false;
    }
    
    if (langues.length === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins une langue parlée');
        return false;
    }
});
</script>
@endsection

