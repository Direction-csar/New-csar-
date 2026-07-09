<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration de mouvement du travailleur</title>
    <style>
        @page { margin: 15mm; size: A4 portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #000; line-height: 1.3; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .border { border: 1px solid #000; }
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .border-left { border-left: 1px solid #000; }
        .border-right { border-right: 1px solid #000; }
        .p-1 { padding: 3px; }
        .p-2 { padding: 6px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .small { font-size: 8pt; }
        .title { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 12pt; margin-bottom: 5px; }
        .subtitle { text-align: center; font-size: 9pt; margin-bottom: 10px; }
        .checkbox { display: inline-block; width: 10px; height: 10px; border: 1px solid #000; margin-right: 3px; }
        .checked { background: #000; }
        .section-title { font-weight: bold; text-transform: uppercase; background: #f0f0f0; padding: 4px; border: 1px solid #000; margin-top: 8px; }
        .field { border-bottom: 1px solid #000; min-height: 14px; display: inline-block; }
        .w-100 { width: 100%; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mb-1 { margin-bottom: 4px; }
        .signature { height: 40px; border-bottom: 1px solid #000; }
    </style>
</head>
<body>

@php
$val = fn($k) => $data[$k] ?? '';
$is = fn($k, $v) => ($data[$k] ?? '') == $v;
$formatDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '';
@endphp

<div class="page">
    <table>
        <tr>
            <td style="width:60%;" class="border p-2">
                <div class="title">Déclaration de mouvement du travailleur</div>
                <div class="subtitle">Article 39, alinéa 2, Code du travail (loi n° 97-04 du 15 Jan 1997)<br>Arrêté ministériel n° 7311 du 17 Mai 1963</div>
                <div class="small">N° d'immatriculation du travailleur en forme de :
                    <span class="field" style="width:180px;">{{ $val('numero_immatriculation') }}</span>
                </div>
            </td>
            <td class="border p-2 text-center" style="width:40%;">
                <div class="fw-bold">Cachet et numéro de l'employeur</div>
                <div class="mt-2" style="min-height:40px;"></div>
            </td>
        </tr>
    </table>

    <table class="mt-2">
        <tr>
            <td class="border p-1">
                <div class="fw-bold">Numéro de la main-d'œuvre</div>
                <table style="width:60%; margin-top:4px;">
                    <tr>
                        @foreach(['a','b','c','d','e','f'] as $col)
                        <td class="border text-center" style="width:16.66%; height:24px; font-weight:bold;">{{ strtoupper($val('main_oeuvre_'.$col)) }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
            <td class="border p-1 text-right" style="width:35%;">
                <div>N° déclaration : <span class="field" style="width:80px;">{{ $val('numero') }}</span></div>
                <div class="mt-1">Date : <span class="field" style="width:80px;">{{ $formatDate($val('date')) }}</span></div>
            </td>
        </tr>
    </table>

    <div class="section-title">Objet de la présente déclaration</div>
    <table class="border">
        <tr>
            @php
            $objets = [
                'embauche' => 'Embauche',
                'rupture_contrat' => 'Rupture de contrat',
                'mutation' => 'Mutation',
                'demission' => 'Démission',
                'fin_cdd' => 'Fin de contrat à durée déterminée',
            ];
            @endphp
            @foreach($objets as $key => $label)
            <td class="border p-1" style="width:20%;">
                <span class="checkbox {{ $is('objet', $key) ? 'checked' : '' }}"></span> {{ $label }}
            </td>
            @endforeach
        </tr>
        <tr>
            @php
            $objets2 = [
                'modification_contrat' => 'Modification du contrat de travail',
                'modification_categorie' => 'Modification de catégorie professionnelle',
                'modification_convention' => 'Modification de convention collective',
                'changement_fonction' => 'Changement de fonction dans l\'entreprise',
                'autres' => 'Autres :',
            ];
            @endphp
            @foreach($objets2 as $key => $label)
            <td class="border p-1" style="width:20%;">
                <span class="checkbox {{ $is('objet', $key) ? 'checked' : '' }}"></span> {{ $label }}
                @if($key === 'autres') <span class="field" style="width:60px;">{{ $val('autres_precision') }}</span> @endif
            </td>
            @endforeach
        </tr>
    </table>

    <div class="section-title">Immatriculation</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:50%;">N° d'immatriculation : <span class="field">{{ $val('numero_immatriculation') }}</span></td>
            <td class="border p-1">N° de la main-d'œuvre : <span class="field">{{ strtoupper($val('main_oeuvre_a').$val('main_oeuvre_b').$val('main_oeuvre_c').$val('main_oeuvre_d').$val('main_oeuvre_e').$val('main_oeuvre_f')) }}</span></td>
        </tr>
    </table>

    <div class="section-title">Concernant le travailleur</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:25%;">Nom : <span class="field">{{ $val('nom') }}</span></td>
            <td class="border p-1" style="width:35%;">Prénoms : <span class="field">{{ $val('prenoms') }}</span></td>
            <td class="border p-1" style="width:10%;">Sexe : <span class="field">{{ $val('sexe') }}</span></td>
            <td class="border p-1" style="width:30%;">Date et lieu de naissance : <span class="field">{{ $formatDate($val('date_naissance')) }} {{ $val('lieu_naissance') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Pays : <span class="field">{{ $val('pays') }}</span></td>
            <td class="border p-1">Nationalité : <span class="field">{{ $val('nationalite') }}</span></td>
            <td class="border p-1" colspan="2">État civil : <span class="field">{{ $val('etat_civil') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Groupe ethnique : <span class="field">{{ $val('groupe_ethnique') }}</span></td>
            <td class="border p-1" colspan="2">N° d'identification à la C.F.P. / A.T. : <span class="field">{{ $val('numero_cfp_at') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">N° d'immatriculation à l'I.N.S.S. / O.M. : <span class="field">{{ $val('numero_inss_om') }}</span></td>
            <td class="border p-1" colspan="2">Situation de famille : <span class="field">{{ $val('situation_famille') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Nombre d'enfants à charge : <span class="field">{{ $val('nombre_enfants') }}</span></td>
            <td class="border p-1" colspan="2">Nom et prénoms du conjoint : <span class="field">{{ $val('conjoint') }}</span></td>
            <td class="border p-1">N° extrait naissance : <span class="field">{{ $val('numero_extrait_naissance') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="4">Adresse du travailleur : <span class="field">{{ $val('adresse') }}</span></td>
        </tr>
    </table>

    <div class="section-title">Emploi dans l'entreprise</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:25%;">Date d'entrée : <span class="field">{{ $formatDate($val('date_entree')) }}</span></td>
            <td class="border p-1" style="width:25%;">Profession : <span class="field">{{ $val('profession') }}</span></td>
            <td class="border p-1" style="width:25%;">Catégorie : <span class="field">{{ $val('categorie') }}</span></td>
            <td class="border p-1" style="width:25%;">Type de contrat : <span class="field">{{ $val('type_contrat') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Durée du contrat : <span class="field">{{ $val('duree_contrat') }}</span></td>
            <td class="border p-1">Période d'essai : <span class="field">{{ $val('periode_essai') }}</span></td>
            <td class="border p-1" colspan="2">Date de début : <span class="field">{{ $formatDate($val('date_debut')) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="4">Nom et adresse de l'établissement : <span class="field">{{ $val('etablissement') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Emploi occupé dans l'entreprise : <span class="field">{{ $val('emploi_occupe') }}</span></td>
            <td class="border p-1" colspan="2">Moyen de transport : <span class="field">{{ $val('moyen_transport') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="4">Nom et adresse du précédent employeur : <span class="field">{{ $val('precedent_employeur') }}</span></td>
        </tr>
    </table>
</div>

<div class="page">
    <div class="title">Statut militaire</div>
    <div class="subtitle">(Rayer les mentions inutiles)</div>
    <table class="border mt-2">
        <tr>
            @php
            $militaire = ['exempte' => 'Exempté', 'sursitaire' => 'Sursitaire', 'inapte' => 'Inapte', 'apte' => 'Apte', 'sous_drapeaux' => 'Sous les drapeaux', 'libere' => 'Libéré'];
            @endphp
            @foreach($militaire as $key => $label)
            <td class="border p-1 text-center" style="width:16.66%;">
                <span class="checkbox {{ $is('statut_militaire', $key) ? 'checked' : '' }}"></span><br>{{ $label }}
            </td>
            @endforeach
        </tr>
    </table>

    <div class="section-title">Informations particulières concernant l'engagement</div>
    <table class="border">
        <tr>
            <td class="border p-2" style="height:120px;">{{ $val('infos_engagement') }}</td>
        </tr>
    </table>

    <div class="section-title">Mentions complémentaires</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:50%; height:60px;">Date du contrat : <span class="field">{{ $formatDate($val('date_debut')) }}</span></td>
            <td class="border p-1">Ancienneté dans l'emploi : <span class="field">{{ $val('duree_contrat') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">
                Relevé du contrat : <span class="field">{{ $val('type_contrat') }}</span>
            </td>
        </tr>
    </table>

    <div class="mt-2">
        <table>
            <tr>
                <td class="border p-2" style="width:50%;">
                    <div class="fw-bold">Signature du travailleur</div>
                    <div class="signature mt-1"></div>
                    <div class="small mt-1">{{ $val('signature_travailleur') }}</div>
                </td>
                <td class="border p-2">
                    <div class="fw-bold">Signature de l'employeur</div>
                    <div class="signature mt-1"></div>
                    <div class="small mt-1">{{ $val('signature_employeur') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="small mt-2">
        N.B. : Cette déclaration doit être adressée dans les huit jours à l'organisme compétent (CNSS/CAISSE) et à l'inspection du travail.
    </div>
</div>

</body>
</html>
