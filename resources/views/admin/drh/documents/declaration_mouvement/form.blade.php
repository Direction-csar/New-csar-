@extends('layouts.drh-portal')

@section('title', $docInfo['label'])
@section('page-title', $docInfo['label'])

@section('content')

@php
$saved = $savedData ?? [];
$defaults = $defaults ?? [];
$val = fn($key) => old($key, $saved[$key] ?? ($defaults[$key] ?? ''));
$checked = fn($key, $v) => old($key, $saved[$key] ?? '') == $v ? 'checked' : '';
$hasAgent = $agent !== null;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4><i class="fas fa-file-signature me-2 text-primary"></i>{{ $docInfo['label'] }}</h4>
        @if($hasAgent)
            <p class="text-muted mb-0">Agent : <strong>{{ $agent->prenoms_nom }}</strong></p>
        @else
            <p class="text-muted mb-0">Aucun agent sélectionné. <a href="{{ route('admin.drh.documents') }}">Choisir un agent</a></p>
        @endif
    </div>
    <a href="{{ route('admin.drh.documents') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.drh.documents.save', $type) }}" method="POST">
            @csrf
            @if($hasAgent)
                <input type="hidden" name="personnel_id" value="{{ $agent->id }}">
            @endif

            <h5 class="text-primary border-bottom pb-2 mb-3">En-tête</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label fw-semibold">N° déclaration</label><input type="text" name="numero" class="form-control" value="{{ $val('numero') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Date</label><input type="date" name="date" class="form-control" value="{{ $val('date') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° d'immatriculation du travailleur au fichier Central de la main-d'œuvre</label><input type="text" name="numero_immatriculation" class="form-control" value="{{ $val('numero_immatriculation') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">N° de la main-d'œuvre (A à F)</h5>
            <div class="row g-2">
                @foreach(['a','b','c','d','e','f'] as $col)
                <div class="col-2">
                    <label class="form-label text-uppercase fw-semibold text-center">{{ $col }}</label>
                    <input type="text" name="main_oeuvre_{{ $col }}" class="form-control text-center" maxlength="1" value="{{ $val('main_oeuvre_'.$col) }}">
                </div>
                @endforeach
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Objet de la présente déclaration</h5>
            <div class="row g-2">
                @php
                $objets = [
                    'embauche_licenciement' => 'Embauche - Licenciement',
                    'expiration_contrat' => 'Expiration normale du contrat',
                    'demission_mutation' => 'Démission - Mutation',
                    'changement_categorie' => 'Changement de catégorie professionnelle',
                    'modification_contrat' => 'Modification du contrat de travail',
                    'changement_situation_famille' => 'Changement de situation de famille',
                    'changement_residence' => 'Changement de résidence habituelle',
                    'changement_emploi_deces' => 'Changement d\'emploi - Décès',
                ];
                @endphp
                @foreach($objets as $key => $label)
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="objet" id="objet_{{ $key }}" value="{{ $key }}" {{ $checked('objet', $key) }}>
                        <label class="form-check-label" for="objet_{{ $key }}">{{ $label }}</label>
                    </div>
                </div>
                @endforeach
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Immatriculation</h5>
            <div class="mb-3">
                <label class="form-label fw-semibold">Si le travailleur n'a pas encore été immatriculé, la mention « Immatriculation » devra être portée dans le blanc ci-dessous</label>
                <textarea name="immatriculation_text" rows="2" class="form-control">{{ $val('immatriculation_text') }}</textarea>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Concernant le travailleur</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label fw-semibold">Nom</label><input type="text" name="nom" class="form-control" value="{{ $val('nom') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Prénoms</label><input type="text" name="prenoms" class="form-control" value="{{ $val('prenoms') }}"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">Sexe</label>
                    <select name="sexe" class="form-select">
                        <option value="">--</option>
                        <option value="M" {{ $val('sexe') == 'M' ? 'selected' : '' }}>M</option>
                        <option value="F" {{ $val('sexe') == 'F' ? 'selected' : '' }}>F</option>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label fw-semibold">Né(e) le</label><input type="date" name="date_naissance" class="form-control" value="{{ $val('date_naissance') }}"></div>
                <div class="col-md-2"><label class="form-label fw-semibold">à</label><input type="text" name="lieu_naissance" class="form-control" value="{{ $val('lieu_naissance') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Pays</label><input type="text" name="pays" class="form-control" value="{{ $val('pays') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Nationalité</label><input type="text" name="nationalite" class="form-control" value="{{ $val('nationalite') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Fils de (père)</label><input type="text" name="pere" class="form-control" value="{{ $val('pere') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Et de (mère)</label><input type="text" name="mere" class="form-control" value="{{ $val('mere') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Groupe ethnique</label><input type="text" name="groupe_ethnique" class="form-control" value="{{ $val('groupe_ethnique') }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Adresse (très précise)</label><textarea name="adresse" rows="2" class="form-control">{{ $val('adresse') }}</textarea></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Carte d'identité n°</label><input type="text" name="carte_identite_numero" class="form-control" value="{{ $val('carte_identite_numero') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">délivrée à</label><input type="text" name="carte_identite_delivree_a" class="form-control" value="{{ $val('carte_identite_delivree_a') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Par</label><input type="text" name="carte_identite_par" class="form-control" value="{{ $val('carte_identite_par') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° d'immatriculation à la C.C.P.F. - A.T.</label><input type="text" name="numero_cfp_at" class="form-control" value="{{ $val('numero_cfp_at') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° d'immatriculation à l'A.G.R.O.M.</label><input type="text" name="numero_agrom" class="form-control" value="{{ $val('numero_agrom') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Situation de famille (marié, divorcé, veuf)</label><input type="text" name="situation_famille" class="form-control" value="{{ $val('situation_famille') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Nombre d'épouses</label><input type="number" name="nombre_epouses" class="form-control" value="{{ $val('nombre_epouses') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Nom des épouses</label><input type="text" name="noms_epouses" class="form-control" value="{{ $val('noms_epouses') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Nombre d'enfants à charge</label><input type="number" name="nombre_enfants" class="form-control" value="{{ $val('nombre_enfants') }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Noms des enfants à charge</label><input type="text" name="noms_enfants" class="form-control" value="{{ $val('noms_enfants') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Emploi dans l'entreprise</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Date d'entrée à l'établissement</label><input type="date" name="date_entree" class="form-control" value="{{ $val('date_entree') }}"></div>
                <div class="col-md-8"><label class="form-label fw-semibold">N° et date de la déclaration d'embauche effectuée lors de l'engagement</label><input type="text" name="date_declaration_embauche" class="form-control" value="{{ $val('date_declaration_embauche') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Profession</label><input type="text" name="profession" class="form-control" value="{{ $val('profession') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Emploi dans l'entreprise</label><input type="text" name="emploi_dans_entreprise" class="form-control" value="{{ $val('emploi_dans_entreprise') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Catégorie</label><input type="text" name="categorie" class="form-control" value="{{ $val('categorie') }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Convention collective</label><input type="text" name="convention_collective" class="form-control" value="{{ $val('convention_collective') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Eventuellement date du contrat</label><input type="date" name="date_contrat" class="form-control" value="{{ $val('date_contrat') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° et date du visa d'approbation par l'inspection du travail et de la sécurité sociale</label><input type="text" name="visa_approbation_numero_date" class="form-control" value="{{ $val('visa_approbation_numero_date') }}"></div>
                <div class="col-md-12"><label class="form-label fw-semibold">N° et date du visa d'enregistrement à la section locale de ... du service de la main-d'œuvre</label><input type="text" name="visa_enregistrement_numero_date" class="form-control" value="{{ $val('visa_enregistrement_numero_date') }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Raison sociale et adresse précise de l'établissement de l'employeur</label><textarea name="raison_sociale_employeur" rows="2" class="form-control">{{ $val('raison_sociale_employeur') }}</textarea></div>
                <div class="col-12"><label class="form-label fw-semibold">Activité de l'établissement</label><input type="text" name="activite_etablissement" class="form-control" value="{{ $val('activite_etablissement') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Durée du contrat</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check"><input class="form-check-input" type="radio" name="duree_contrat_type" id="dct_determinee" value="determinee" {{ $checked('duree_contrat_type', 'determinee') }}><label class="form-check-label" for="dct_determinee">à durée déterminée</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="duree_contrat_type" id="dct_indeterminee" value="indeterminee" {{ $checked('duree_contrat_type', 'indeterminee') }}><label class="form-check-label" for="dct_indeterminee">ou à durée indéterminée</label></div>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Chantier de</label><input type="text" name="chantier" class="form-control" value="{{ $val('chantier') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Informations complémentaires</h5>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Nom et adresse du précédent employeur</label><textarea name="precedent_employeur" rows="2" class="form-control">{{ $val('precedent_employeur') }}</textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Si le travailleur bénéficie de l'article 107 du code du travail</label><input type="text" name="article_107" class="form-control" value="{{ $val('article_107') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Lieu de résidence habituelle du travailleur</label><input type="text" name="lieu_residence_habituelle" class="form-control" value="{{ $val('lieu_residence_habituelle') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Date d'entrée au Sénégal</label><input type="date" name="date_entree_senegal" class="form-control" value="{{ $val('date_entree_senegal') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Page 2 - Statut militaire</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label fw-semibold">Classe de recrutement</label><input type="text" name="classe_recruement" class="form-control" value="{{ $val('classe_recruement') }}"></div>
                <div class="col-md-5"><label class="form-label fw-semibold">L'intéressé a-t-il effectué son service militaire ?</label>
                    <select name="service_militaire" class="form-select">
                        <option value="">--</option>
                        <option value="oui" {{ $val('service_militaire') == 'oui' ? 'selected' : '' }}>Oui</option>
                        <option value="non" {{ $val('service_militaire') == 'non' ? 'selected' : '' }}>Non</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Armée d'appartenance : terre - mer - air</label><input type="text" name="arme_appartenance" class="form-control" value="{{ $val('arme_appartenance') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Grade dans la réserve (Officier - Sous-officier - Troupe)</label><input type="text" name="grade_reserve" class="form-control" value="{{ $val('grade_reserve') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Dispositions particulières concernant l'engagement</h5>
            <div class="mb-3">
                <label class="form-label fw-semibold">(Auxquelles les parties ont expressément souscrit)</label>
                <textarea name="dispositions_particulieres" rows="3" class="form-control">{{ $val('dispositions_particulieres') }}</textarea>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Salaire et horaire</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Horaire hebdomadaire</label>
                    <select name="horaire_hebdomadaire" class="form-select">
                        <option value="">--</option>
                        <option value="40" {{ $val('horaire_hebdomadaire') == '40' ? 'selected' : '' }}>40 Heures</option>
                        <option value="42" {{ $val('horaire_hebdomadaire') == '42' ? 'selected' : '' }}>42 heures</option>
                        <option value="44" {{ $val('horaire_hebdomadaire') == '44' ? 'selected' : '' }}>44 heures</option>
                        <option value="48" {{ $val('horaire_hebdomadaire') == '48' ? 'selected' : '' }}>48 heures</option>
                        <option value="60" {{ $val('horaire_hebdomadaire') == '60' ? 'selected' : '' }}>60 heures</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Salaire de base</label><input type="number" step="any" name="salaire_base" class="form-control" value="{{ $val('salaire_base') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Sursalaire</label><input type="number" step="any" name="sursalaire" class="form-control" value="{{ $val('sursalaire') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Indemnité de transport</label><input type="number" step="any" name="indemnite_transport" class="form-control" value="{{ $val('indemnite_transport') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Indemnité de fonction</label><input type="number" step="any" name="indemnite_fonction" class="form-control" value="{{ $val('indemnite_fonction') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Salaire brut global</label><input type="number" step="any" name="salaire_brut_global" class="form-control" value="{{ $val('salaire_brut_global') }}"></div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Signatures</h5>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Signature du travailleur (précédée de la mention manuscrite : pour accord)</label><input type="text" name="signature_travailleur" class="form-control" value="{{ $val('signature_travailleur') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Signature de l'employeur</label><input type="text" name="signature_employeur" class="form-control" value="{{ $val('signature_employeur') }}"></div>
            </div>

            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Enregistrer la déclaration
                </button>
                @if($savedDocument)
                    <a href="{{ route('admin.drh.documents.export', $savedDocument) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Générer le PDF
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@endsection
