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

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Numéro de la déclaration</label>
                    <input type="text" name="numero" class="form-control" value="{{ $val('numero') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $val('date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Numéro d'immatriculation du travailleur</label>
                    <input type="text" name="numero_immatriculation" class="form-control" value="{{ $val('numero_immatriculation') }}">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="text-primary mb-3">Objet de la présente déclaration</h5>
            <div class="row g-2">
                @php
                $objets = [
                    'embauche' => 'Embauche',
                    'rupture_contrat' => 'Rupture de contrat',
                    'mutation' => 'Mutation',
                    'demission' => 'Démission',
                    'fin_cdd' => 'Fin de contrat à durée déterminée',
                    'modification_contrat' => 'Modification du contrat de travail (salaire, durée, etc.)',
                    'modification_categorie' => 'Modification de catégorie professionnelle',
                    'modification_convention' => 'Modification de convention collective',
                    'changement_fonction' => 'Changement de fonction dans l\'entreprise',
                    'autres' => 'Autres',
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
                <div class="col-12">
                    <label class="form-label fw-semibold">Préciser si autre</label>
                    <input type="text" name="autres_precision" class="form-control" value="{{ $val('autres_precision') }}">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="text-primary mb-3">Numéro de la main-d'œuvre</h5>
            <div class="row g-2">
                @foreach(['a','b','c','d','e','f'] as $col)
                <div class="col-2">
                    <label class="form-label text-uppercase fw-semibold">{{ $col }}</label>
                    <input type="text" name="main_oeuvre_{{ $col }}" class="form-control text-center" maxlength="1" value="{{ $val('main_oeuvre_'.$col) }}">
                </div>
                @endforeach
            </div>

            <hr class="my-4">
            <h5 class="text-primary mb-3">Concernant le travailleur</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Nom</label><input type="text" name="nom" class="form-control" value="{{ $val('nom') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Prénoms</label><input type="text" name="prenoms" class="form-control" value="{{ $val('prenoms') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Sexe</label>
                    <select name="sexe" class="form-select">
                        <option value="">--</option>
                        <option value="M" {{ $val('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                        <option value="F" {{ $val('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Date de naissance</label><input type="date" name="date_naissance" class="form-control" value="{{ $val('date_naissance') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control" value="{{ $val('lieu_naissance') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Pays</label><input type="text" name="pays" class="form-control" value="{{ $val('pays') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Nationalité</label><input type="text" name="nationalite" class="form-control" value="{{ $val('nationalite') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">État civil</label>
                    <select name="etat_civil" class="form-select">
                        <option value="">--</option>
                        <option value="celibataire" {{ $val('etat_civil') == 'celibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="marie" {{ $val('etat_civil') == 'marie' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="divorce" {{ $val('etat_civil') == 'divorce' ? 'selected' : '' }}>Divorcé(e)</option>
                        <option value="veuf" {{ $val('etat_civil') == 'veuf' ? 'selected' : '' }}>Veuf/Veuve</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-semibold">Groupe ethnique</label><input type="text" name="groupe_ethnique" class="form-control" value="{{ $val('groupe_ethnique') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° d'identification à la C.F.P. / A.T.</label><input type="text" name="numero_cfp_at" class="form-control" value="{{ $val('numero_cfp_at') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° d'immatriculation à l'I.N.S.S. / O.M.</label><input type="text" name="numero_inss_om" class="form-control" value="{{ $val('numero_inss_om') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Situation de famille</label><input type="text" name="situation_famille" class="form-control" value="{{ $val('situation_famille') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nombre d'enfants à charge</label><input type="number" name="nombre_enfants" class="form-control" value="{{ $val('nombre_enfants') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nom et prénoms du conjoint</label><input type="text" name="conjoint" class="form-control" value="{{ $val('conjoint') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">N° d'extrait de naissance</label><input type="text" name="numero_extrait_naissance" class="form-control" value="{{ $val('numero_extrait_naissance') }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Adresse du travailleur</label><textarea name="adresse" rows="2" class="form-control">{{ $val('adresse') }}</textarea></div>
            </div>

            <hr class="my-4">
            <h5 class="text-primary mb-3">Emploi dans l'entreprise</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Date d'entrée à l'établissement</label><input type="date" name="date_entree" class="form-control" value="{{ $val('date_entree') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Profession</label><input type="text" name="profession" class="form-control" value="{{ $val('profession') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Catégorie</label><input type="text" name="categorie" class="form-control" value="{{ $val('categorie') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Type de contrat</label><input type="text" name="type_contrat" class="form-control" value="{{ $val('type_contrat') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Durée du contrat</label><input type="text" name="duree_contrat" class="form-control" value="{{ $val('duree_contrat') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Période d'essai</label><input type="text" name="periode_essai" class="form-control" value="{{ $val('periode_essai') }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Date de début</label><input type="date" name="date_debut" class="form-control" value="{{ $val('date_debut') }}"></div>
                <div class="col-md-8"><label class="form-label fw-semibold">Nom et adresse de l'établissement</label><input type="text" name="etablissement" class="form-control" value="{{ $val('etablissement') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Emploi occupé dans l'entreprise</label><input type="text" name="emploi_occupe" class="form-control" value="{{ $val('emploi_occupe') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Moyen de transport</label><input type="text" name="moyen_transport" class="form-control" value="{{ $val('moyen_transport') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nom et adresse du précédent employeur</label><input type="text" name="precedent_employeur" class="form-control" value="{{ $val('precedent_employeur') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Si le travailleur bénéficiait d'un contrat de travail</label><input type="text" name="beneficiait_contrat" class="form-control" value="{{ $val('beneficiait_contrat') }}"></div>
            </div>

            <hr class="my-4">
            <h5 class="text-primary mb-3">Page 2 - Statut militaire et informations complémentaires</h5>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Statut militaire</label>
                    <div class="d-flex gap-4 flex-wrap">
                        @foreach(['exempte' => 'Exempté', 'sursitaire' => 'Sursitaire', 'inapte' => 'Inapte', 'apte' => 'Apte', 'sous_drapeaux' => 'Sous les drapeaux', 'libere' => 'Libéré'] as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="statut_militaire" id="sm_{{ $key }}" value="{{ $key }}" {{ $checked('statut_militaire', $key) }}>
                            <label class="form-check-label" for="sm_{{ $key }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12"><label class="form-label fw-semibold">Informations particulières concernant l'engagement</label><textarea name="infos_engagement" rows="4" class="form-control">{{ $val('infos_engagement') }}</textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Signature du travailleur / date</label><input type="text" name="signature_travailleur" class="form-control" value="{{ $val('signature_travailleur') }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Signature de l'employeur / date</label><input type="text" name="signature_employeur" class="form-control" value="{{ $val('signature_employeur') }}"></div>
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
