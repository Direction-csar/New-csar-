<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration de mouvement du travailleur</title>
    <style>
        @page { margin: 12mm; size: A4 portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; color: #000; line-height: 1.25; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .border { border: 1px solid #000; }
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .border-left { border-left: 1px solid #000; }
        .border-right { border-right: 1px solid #000; }
        .p-1 { padding: 2px 4px; }
        .p-2 { padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .upper { text-transform: uppercase; }
        .small { font-size: 7.5pt; }
        .title { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 11pt; margin-bottom: 3px; }
        .subtitle { font-size: 8pt; margin-bottom: 6px; }
        .checkbox { display: inline-block; width: 9px; height: 9px; border: 1px solid #000; margin-right: 3px; position: relative; top: 1px; }
        .checked { background: #000; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 8.5pt; margin-top: 8px; margin-bottom: 3px; }
        .field { border-bottom: 1px solid #000; min-height: 12px; display: inline-block; }
        .mt-1 { margin-top: 3px; }
        .mt-2 { margin-top: 6px; }
        .mb-1 { margin-bottom: 3px; }
        .signature { height: 30px; border-bottom: 1px solid #000; }
        .box { width: 22px; height: 22px; border: 1px solid #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; }
        .box-label { text-align: center; font-weight: bold; font-size: 9pt; }
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
            <td style="width:58%;" class="border p-2">
                <div class="title">Déclaration de mouvement du travailleur</div>
                <div class="small">N° ............................. Du .............................</div>
                <div class="small mt-1"><span class="fw-bold">REFERENCES</span><br>
                    Article 193 (Aliénas 2,3,4) du Code du travail (loi n° 61-34 du 15 Juin 1961).<br>
                    Arrêté ministériel n° 7301 du 17 Mai 1963 déterminant les modalités<br>
                    Des déclarations de mouvements de travailleurs (J.O. 22 Juin 1963, p.825).</div>
                <div class="small mt-1">N° d'immatriculation du travailleur au fichier Central de la main d'œuvre : <span class="field" style="width:160px;">{{ $val('numero_immatriculation') }}</span></div>
            </td>
            <td class="border p-2 text-center" style="width:42%;">
                <div class="small"><em>Cachet et raison social de<br>L'employeur</em></div>
                <div style="min-height:60px;"></div>
            </td>
        </tr>
    </table>

    <table class="mt-2">
        <tr>
            <td style="width:50%;" class="p-1">
                <div class="small">N° d'immatriculation du travailleur au fichier Central de la main d'œuvre</div>
            </td>
            <td style="width:50%;" class="p-1">
                <table style="width:100%;">
                    <tr>
                        @foreach(['a','b','c','d','e','f'] as $col)
                        <td class="box">{{ strtoupper($val('main_oeuvre_'.$col)) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach(['a','b','c','d','e','f'] as $col)
                        <td class="box-label">{{ strtoupper($col) }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="mt-2 border">
        <tr>
            <td class="border p-1" style="width:48%;">
                <div class="section-title" style="margin-top:0;">Objet de la présente déclaration</div>
                <div class="small">- Embauche – licenciement</div>
                <div class="small">- Expiration normale du contrat</div>
                <div class="small">- Démission – Mutation</div>
                <div class="small">- Changement de catégorie professionnelle</div>
                <div class="small">- Modification du contrat de travail</div>
                <div class="small">- Changement de situation de famille</div>
                <div class="small">- Changement de résidence habituelle mention</div>
                <div class="small">- Changement d'emploi – Décès</div>
            </td>
            <td class="border p-1" style="width:52%;">
                <div class="small text-center">(Rayer les mentions inutiles et encadrer la mention valable<br>À compléter éventuellement dans le blanc ci-dessous)</div>
                <div class="border p-2 text-center mt-1" style="font-size:12pt; font-weight:bold;">IMMATRICULATION</div>
                <div class="small text-center mt-1">Si le travailleur n'a pas encore été immatriculé, la mention<br>« Immatriculation » devra être portée dans le blanc<br>Ci-dessous</div>
                <div class="border p-1 mt-1" style="min-height:35px;">{{ $val('immatriculation_text') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Concernant le travailleur</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:18%;">Nom : <span class="field">{{ $val('nom') }}</span></td>
            <td class="border p-1" style="width:30%;">Prénoms : <span class="field">{{ $val('prenoms') }}</span></td>
            <td class="border p-1" style="width:12%;">Sexe : <span class="field">{{ $val('sexe') }}</span></td>
            <td class="border p-1" style="width:20%;">Né le : <span class="field">{{ $formatDate($val('date_naissance')) }}</span></td>
            <td class="border p-1" style="width:20%;">à : <span class="field">{{ $val('lieu_naissance') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Pays : <span class="field">{{ $val('pays') }}</span></td>
            <td class="border p-1">Nationalité : <span class="field">{{ $val('nationalite') }}</span></td>
            <td class="border p-1" colspan="2">Fils de (père) : <span class="field">{{ $val('pere') }}</span></td>
            <td class="border p-1">Et de (mère) : <span class="field">{{ $val('mere') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Groupe ethnique : <span class="field">{{ $val('groupe_ethnique') }}</span></td>
            <td class="border p-1" colspan="3">Adresse (très précise) : <span class="field">{{ $val('adresse') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Carte d'identité n° : <span class="field">{{ $val('carte_identite_numero') }}</span></td>
            <td class="border p-1" colspan="2">délivrée à Dakar : <span class="field">{{ $val('carte_identite_delivree_a') }}</span></td>
            <td class="border p-1">Par : <span class="field">{{ $val('carte_identite_par') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="3">N° d'immatriculation à la C.C.P.F. - A.T. : <span class="field">{{ $val('numero_cfp_at') }}</span></td>
            <td class="border p-1" colspan="2">N° d'immatriculation à l'A.G.R.O.M. : <span class="field">{{ $val('numero_agrom') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="3">Situation de famille (marié, divorcé, veuf) : <span class="field">{{ $val('situation_famille') }}</span></td>
            <td class="border p-1">Nombre d'épouses : <span class="field">{{ $val('nombre_epouses') }}</span></td>
            <td class="border p-1">Nom des épouses : <span class="field">{{ $val('noms_epouses') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Nombre d'enfants à charge : <span class="field">{{ $val('nombre_enfants') }}</span></td>
            <td class="border p-1" colspan="3">Noms des enfants à charge : <span class="field">{{ $val('noms_enfants') }}</span></td>
        </tr>
    </table>

    <div class="section-title">Emploi dans l'entreprise</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:30%;">Date d'entrée à l'établissement : <span class="field">{{ $formatDate($val('date_entree')) }}</span></td>
            <td class="border p-1" colspan="2">N° et date de la déclaration d'embauche effectuée lors de l'engagement : <span class="field">{{ $val('date_declaration_embauche') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Profession : <span class="field">{{ $val('profession') }}</span></td>
            <td class="border p-1">Emploi dans l'entreprise : <span class="field">{{ $val('emploi_dans_entreprise') }}</span></td>
            <td class="border p-1">Catégorie : <span class="field">{{ $val('categorie') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="3">Convention collective : <span class="field">{{ $val('convention_collective') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Eventuellement date du contrat : <span class="field">{{ $formatDate($val('date_contrat')) }}</span></td>
            <td class="border p-1" colspan="2">N° et date du visa d'approbation par l'inspection du travail et de la sécurité sociale : <span class="field">{{ $val('visa_approbation_numero_date') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="3">N° et date du visa d'enregistrement à la section locale de : <span class="field">{{ $val('visa_enregistrement_numero_date') }}</span> du service de la main-d'œuvre</td>
        </tr>
        <tr>
            <td class="border p-1" colspan="3">Raison sociale et adresse précise de l'établissement de l'employeur : <span class="field">{{ $val('raison_sociale_employeur') }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="3">Activité de l'établissement : <span class="field">{{ $val('activite_etablissement') }}</span></td>
        </tr>
    </table>

    <table class="mt-2 border">
        <tr>
            <td class="border p-1" style="width:50%;">
                <div class="fw-bold">Durée du contrat :</div>
                <div class="small">- à durée déterminée : <span class="field">{{ $is('duree_contrat_type', 'determinee') ? '✓' : '' }}</span></div>
                <div class="small">- ou à durée indéterminée : <span class="field">{{ $is('duree_contrat_type', 'indeterminee') ? '✓' : '' }}</span></div>
                <div class="small mt-1">(Rayer la mention inutile et encadrer la mention correcte)</div>
            </td>
            <td class="border p-1">Chantier de : <span class="field">{{ $val('chantier') }}</span></td>
        </tr>
    </table>

    <table class="mt-2 border">
        <tr>
            <td class="border p-1" style="width:50%;">
                <div class="fw-bold">Nom et adresse du précédent employeur :</div>
                <div class="small">{{ $val('precedent_employeur') }}</div>
                <div class="small mt-1">Si le travailleur bénéficie de l'article 107 du code du travail : <span class="field">{{ $val('article_107') }}</span></div>
                <div class="small mt-1">- Lieu de résidence habituelle du travailleur : <span class="field">{{ $val('lieu_residence_habituelle') }}</span></div>
            </td>
            <td class="border p-1 text-right" style="vertical-align: bottom;">
                <div class="small">Date d'entrée au Sénégal : <span class="field">{{ $formatDate($val('date_entree_senegal')) }}</span></div>
            </td>
        </tr>
    </table>
</div>

<div class="page">
    <div class="title">Statut militaire</div>
    <div class="small text-center">(Rayer les mentions inutiles)</div>
    <table class="mt-2">
        <tr>
            <td class="p-1">- Classe de recrutement : <span class="field">{{ $val('classe_recruement') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">- L'intéressé a-t-il effectué son service militaire ? <span class="field">{{ $val('service_militaire') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">- Armée d'appartenance : terre - mer - air : <span class="field">{{ $val('arme_appartenance') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">- Grade dans la réserve : Officier - Sous-officier - Troupe : <span class="field">{{ $val('grade_reserve') }}</span></td>
        </tr>
    </table>

    <div class="section-title">Dispositions particulières concernant l'engagement :</div>
    <div class="small">(Auxquelles les parties ont expressément souscrit)</div>
    <table class="border mt-1">
        <tr>
            <td class="border p-1" style="height:80px;">{{ $val('dispositions_particulieres') }}</td>
        </tr>
    </table>

    <div class="mt-2">
        <div class="small">1) - Le salaire du travailleur sera celui fixé pour la même catégorie de la Convention Collective Du Commerce 9<sup>ème</sup> A en fonction d'un horaire de travail hebdomadaire de :</div>
        <table class="mt-1" style="width:60%;">
            <tr>
                @foreach(['40','42','44','48','60'] as $h)
                <td class="p-1"><span class="checkbox {{ $is('horaire_hebdomadaire', $h) ? 'checked' : '' }}"></span> {{ $h }} Heures</td>
                @endforeach
            </tr>
        </table>
    </div>

    <table class="mt-2">
        <tr>
            <td class="p-1" style="width:50%;">Soit : Salaire de base,</td>
            <td class="p-1 text-right">= <span class="field">{{ $val('salaire_base') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">2) Sursalaire</td>
            <td class="p-1 text-right">= <span class="field">{{ $val('sursalaire') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">3) Indemnité de transport</td>
            <td class="p-1 text-right">= <span class="field">{{ $val('indemnite_transport') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">4) Indemnité de fonction</td>
            <td class="p-1 text-right">= <span class="field">{{ $val('indemnite_fonction') }}</span></td>
        </tr>
        <tr>
            <td class="p-1">4) Salaire brut global</td>
            <td class="p-1 text-right">= <span class="field">{{ $val('salaire_brut_global') }}</span></td>
        </tr>
    </table>

    <div class="mt-2">
        <table>
            <tr>
                <td class="border p-2" style="width:50%;">
                    <div class="small">Signature du travailleur :</div>
                    <div class="small mt-1">(Précédée de la mention manuscrite : pour accord)</div>
                    <div class="signature mt-1"></div>
                    <div class="small mt-1">{{ $val('signature_travailleur') }}</div>
                </td>
                <td class="border p-2" style="vertical-align: bottom;">
                    <div class="small">Signature de l'employeur :</div>
                    <div class="signature mt-1"></div>
                    <div class="small mt-1">{{ $val('signature_employeur') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="small mt-2">
        P.S. - N.B. : 1) La déclaration de mouvement de travailleur est à établir en trois exemplaires dûment signés par l'employeur et le travailleur.<br>
        Un exemplaire est remis au travailleur. Le second est conservé par l'employeur. Le troisième est déposé :<br>
        dans l'intérieur : à la Section locale du Service de la Main-d'œuvre, à l'Inspection régionale du Travail et de la Sécurité sociale du ressort ;<br>
        à Dakar : au Bureau central du Service de la Main-d'œuvre.<br>
        2) L'employeur doit OBLIGATOIREMENT délivrer une ampliation de la déclaration du mouvement au travailleur.<br>
        3) sont provisoirement exemptés de la déclaration de mouvement :<br>
        - les travailleurs journaliers effectivement payés tous les jours,<br>
        - les manœuvres ordinaires, dans toutes les branches d'activité.
    </div>
</div>

</body>
</html>
