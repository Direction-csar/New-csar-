<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déclaration de mouvement du travailleur</title>
    <style>
        @page { margin: 12mm; size: A4 portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8pt; color: #000; line-height: 1.18; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        tr { page-break-inside: avoid; }
        .border { border: 1px solid #000; }
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .border-left { border-left: 1px solid #000; }
        .border-right { border-right: 1px solid #000; }
        .p-1 { padding: 0px 3px; }
        .p-2 { padding: 4px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .upper { text-transform: uppercase; }
        .small { font-size: 7.5pt; }
        .title { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 11pt; margin-bottom: 3px; }
        .subtitle { font-size: 8pt; margin-bottom: 6px; }
        .checkbox { display: inline-block; width: 9px; height: 9px; border: 1px solid #000; margin-right: 3px; position: relative; top: 1px; }
        .checked { background: #000; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 8pt; margin-top: 5px; margin-bottom: 2px; }
        .field { min-height: 12px; display: inline-block; font-weight: bold; font-size: 9pt; }
        .mt-1 { margin-top: 3px; }
        .mt-2 { margin-top: 5px; }
        .mb-1 { margin-bottom: 3px; }
        .signature { height: 30px; border-bottom: 1px solid #000; }
        .box { width: 22px; height: 22px; border: 1px solid #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; }
        .box-label { text-align: center; font-weight: bold; font-size: 9pt; }
        .keep { page-break-inside: avoid; }
    </style>
</head>
<body>

@php
$val = fn($k) => $data[$k] ?? '';
$lim = fn($v, $n = 100) => \Illuminate\Support\Str::limit((string) $v, $n, '…');
$is = fn($k, $v) => ($data[$k] ?? '') == $v;
$formatDate = function ($d) {
    if (!$d) { return ''; }
    try { return \Carbon\Carbon::parse($d)->format('d/m/Y'); }
    catch (\Throwable $e) { return \Illuminate\Support\Str::limit((string) $d, 20, '…'); }
};
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
                <div class="small mt-1">N° d'immatriculation du travailleur au fichier Central de la main d'œuvre : <span class="field" style="width:160px;">{{ $lim($val('numero_immatriculation'), 60) }}</span></div>
            </td>
            <td class="border p-2 text-center" style="width:42%;">
                <div class="small"><em>Cachet et raison social de<br>L'employeur</em></div>
                <div style="min-height:42px;"></div>
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
                <div class="border p-1 mt-1" style="min-height:24px;">{{ $lim($val('immatriculation_text'), 200) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Concernant le travailleur</div>
    <table class="border">
        <tr>
            <td class="border p-1" style="width:25%;">Nom : <span class="field">{{ $lim($val('nom'), 60) }}</span></td>
            <td class="border p-1" style="width:50%;">Prénoms : <span class="field">{{ $lim($val('prenoms'), 80) }}</span></td>
            <td class="border p-1" style="width:25%;">Sexe : <span class="field">{{ $lim($val('sexe'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Né le : <span class="field">{{ $formatDate($val('date_naissance')) }}</span></td>
            <td class="border p-1">à : <span class="field">{{ $lim($val('lieu_naissance'), 60) }}</span></td>
            <td class="border p-1">Pays : <span class="field">{{ $lim($val('pays'), 60) }}</span></td>
            <td class="border p-1">Nationalité : <span class="field">{{ $lim($val('nationalite'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">Fils de (père) : <span class="field">{{ $lim($val('pere'), 70) }}</span></td>
            <td class="border p-1">Et de (mère) : <span class="field">{{ $lim($val('mere'), 70) }}</span></td>
            <td class="border p-1" colspan="2">Groupe ethnique : <span class="field">{{ $lim($val('groupe_ethnique'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="4">Adresse (très précise) : <span class="field">{{ $lim($val('adresse'), 90) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Carte d'identité n° : <span class="field">{{ $lim($val('carte_identite_numero'), 40) }}</span></td>
            <td class="border p-1" colspan="2">délivrée à Dakar : <span class="field">{{ $lim($val('carte_identite_delivree_a'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="4">Par : <span class="field">{{ $lim($val('carte_identite_par'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">N° d'immatriculation à la C.C.P.F. - A.T. : <span class="field">{{ $lim($val('numero_cfp_at'), 40) }}</span></td>
            <td class="border p-1" colspan="2">N° d'immatriculation à l'A.G.R.O.M. : <span class="field">{{ $lim($val('numero_agrom'), 40) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Situation de famille (marié, divorcé, veuf) : <span class="field">{{ $lim($val('situation_famille'), 40) }}</span></td>
            <td class="border p-1" colspan="2">Nombre d'épouses : <span class="field">{{ $lim($val('nombre_epouses'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="4">Nom des épouses : <span class="field">{{ $lim($val('noms_epouses'), 90) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Nombre d'enfants à charge : <span class="field">{{ $lim($val('nombre_enfants'), 60) }}</span></td>
            <td class="border p-1" colspan="2">Noms des enfants à charge : <span class="field">{{ $lim($val('noms_enfants'), 70) }}</span></td>
        </tr>
    </table>

    <div class="section-title">Emploi dans l'entreprise</div>
    <table class="border">
        <tr>
            <td class="border p-1" colspan="2">Date d'entrée à l'établissement : <span class="field">{{ $formatDate($val('date_entree')) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">N° et date de la déclaration d'embauche effectuée lors de l'engagement : <span class="field">{{ $lim($val('date_declaration_embauche'), 80) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" style="width:50%;">Profession : <span class="field">{{ $lim($val('profession'), 60) }}</span></td>
            <td class="border p-1">Emploi dans l'entreprise : <span class="field">{{ $lim($val('emploi_dans_entreprise'), 60) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" style="width:60%;">Convention collective : <span class="field">{{ $val('convention_collective') !== '' ? $lim($val('convention_collective'), 50) : 'du commerce et grille salariale CSAR' }}</span></td>
            <td class="border p-1">Catégorie : <span class="field">{{ $lim($val('categorie'), 40) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Eventuellement date du contrat : <span class="field">{{ $formatDate($val('date_contrat')) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">N° et date du visa d'approbation par l'inspection du travail et de la sécurité sociale : <span class="field">{{ $lim($val('visa_approbation_numero_date'), 70) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1">N° et date du visa d'enregistrement à la section locale de : <span class="field">{{ $lim($val('visa_enregistrement_numero_date'), 40) }}</span></td>
            <td class="border p-1">du service de la main-d'œuvre</td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Raison sociale et adresse précise de l'établissement de l'employeur : <span class="field">{{ $lim($val('raison_sociale_employeur'), 100) }}</span></td>
        </tr>
        <tr>
            <td class="border p-1" colspan="2">Activité de l'établissement : <span class="field">{{ $lim($val('activite_etablissement'), 90) }}</span></td>
        </tr>
    </table>

    <table class="mt-2 border">
        <tr>
            <td class="border p-1" style="width:50%;">
                <div class="fw-bold">Durée du contrat :</div>
                <div class="small">- à durée déterminée : <span class="field">{{ $is('duree_contrat_type', 'determinee') ? 'x' : '' }}</span></div>
                <div class="small">- ou à durée indéterminée : <span class="field">{{ $is('duree_contrat_type', 'indeterminee') ? 'x' : '' }}</span></div>
                <div class="small mt-1">(Rayer la mention inutile et encadrer la mention correcte)</div>
            </td>
            <td class="border p-1">Chantier de : <span class="field">{{ $lim($val('chantier'), 70) }}</span></td>
        </tr>
    </table>

    <table class="mt-2 border">
        <tr>
            <td class="border p-1" style="width:50%;">
                <div class="fw-bold">Nom et adresse du précédent employeur :</div>
                <div class="small">{{ $lim($val('precedent_employeur'), 70) }}</div>
                <div class="small mt-1">Si le travailleur bénéficie de l'article 107 du code du travail : <span class="field">{{ $lim($val('article_107'), 70) }}</span></div>
                <div class="small mt-1">- Lieu de résidence habituelle du travailleur : <span class="field">{{ $lim($val('lieu_residence_habituelle'), 70) }}</span></div>
            </td>
            <td class="border p-1 text-right" style="vertical-align: bottom;">
                <div class="small">Date d'entrée au Sénégal : <span class="field">{{ $formatDate($val('date_entree_senegal')) }}</span></div>
            </td>
        </tr>
    </table>
</div>

<div class="page" style="font-size: 9.5pt; line-height: 1.35;">
    <div class="title" style="font-size: 12pt; margin-top: 2px;">Statut militaire</div>
    <div class="text-center" style="font-size: 8.5pt; margin-bottom: 10px;">(Rayer les mentions inutiles)</div>
    <table>
        <tr>
            <td style="padding: 4px 4px;">- Classe de recrutement : <span class="field">{{ $lim($val('classe_recruement'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">- L'intéressé a-t-il effectué son service militaire ? <span class="field">{{ $lim($val('service_militaire'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">- Armée d'appartenance : terre - mer - air : <span class="field">{{ $lim($val('arme_appartenance'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">- Grade dans la réserve : Officier - Sous-officier - Troupe : <span class="field">{{ $lim($val('grade_reserve'), 60) }}</span></td>
        </tr>
    </table>

    <div class="section-title" style="font-size: 9.5pt; margin-top: 8px;">Dispositions particulières concernant l'engagement :</div>
    <div style="font-size: 8.5pt;">(Auxquelles les parties ont expressément souscrit)</div>
    <table class="border keep" style="margin-top: 3px;">
        <tr>
            <td class="border" style="height: 70px; padding: 4px;">{{ $lim($val('dispositions_particulieres'), 350) }}</td>
        </tr>
    </table>

    <div class="keep" style="margin-top: 8px;">
        <div>1) - Le salaire du travailleur sera celui fixé pour la même catégorie de la Convention Collective Du Commerce 9<sup>ème</sup> A<br>
        en fonction d'un horaire de travail hebdomadaire de :</div>
        <div style="margin-top: 3px; margin-left: 30px;">
            @foreach(['40' => '40 Heures', '41' => '41 heures', '44' => '44 heures', '48' => '48 heures', '60' => '60 heures'] as $h => $label)
            <div style="padding: 1px 6px;">{{ $label }} {!! $is('horaire_hebdomadaire', $h) ? '<strong>x</strong>' : '' !!}</div>
            @endforeach
            @if(!in_array($data['horaire_hebdomadaire'] ?? '', ['', '40', '41', '44', '48', '60'], true))
            <div style="padding: 1px 6px;">{{ $lim($val('horaire_hebdomadaire'), 10) }} heures <strong>x</strong></div>
            @endif
        </div>
    </div>

    <table style="margin-top: 4px;">
        <tr>
            <td style="width:50%; padding: 4px 4px;">Soit : Salaire de base,</td>
            <td class="text-right" style="padding: 4px 4px;">= <span class="field">{{ $lim($val('salaire_base'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">2) Sursalaire</td>
            <td class="text-right" style="padding: 4px 4px;">= <span class="field">{{ $lim($val('sursalaire'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">3) Indemnité de transport</td>
            <td class="text-right" style="padding: 4px 4px;">= <span class="field">{{ $lim($val('indemnite_transport'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">4) Indemnité de fonction</td>
            <td class="text-right" style="padding: 4px 4px;">= <span class="field">{{ $lim($val('indemnite_fonction'), 60) }}</span></td>
        </tr>
        <tr>
            <td style="padding: 4px 4px;">5) Salaire brut global</td>
            <td class="text-right" style="padding: 4px 4px;">= <span class="field">{{ $lim($val('salaire_brut_global'), 60) }}</span></td>
        </tr>
    </table>

    <div class="keep" style="margin-top: 8px;">
        <table>
            <tr>
                <td style="width:50%; padding: 4px; vertical-align: top;">
                    <div style="font-size: 9pt;">Signature du travailleur :</div>
                    <div style="font-size: 8pt; margin-top: 3px;">(Précédée de la mention manuscrite : pour accord)</div>
                    <div style="height: 45px; margin-top: 4px;"></div>
                    <div style="font-size: 8.5pt; margin-top: 3px;">{{ $lim($val('signature_travailleur'), 60) }}</div>
                </td>
                <td style="padding: 4px; vertical-align: top; text-align: right;">
                    <div style="font-size: 9pt;">Signature de l'employeur :</div>
                    <div style="height: 45px; margin-top: 4px;"></div>
                    <div style="font-size: 8.5pt; margin-top: 3px; text-align: left;">{{ $lim($val('signature_employeur'), 60) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="keep" style="font-size: 8pt; margin-top: 8px; line-height: 1.3; border-top: 1px solid #000; padding-top: 6px;">
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
