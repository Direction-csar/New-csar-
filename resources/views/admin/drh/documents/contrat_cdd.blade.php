@php
    $fmt = function ($d) {
        if (empty($d)) return '..........................';
        try { return \Carbon\Carbon::parse($d)->translatedFormat('j F Y'); } catch (\Exception $e) { return $d; }
    };
    $fmtCourt = function ($d) {
        if (empty($d)) return '..........................';
        try { return \Carbon\Carbon::parse($d)->format('d/m/Y'); } catch (\Exception $e) { return $d; }
    };
    $money = function ($m) {
        return ($m !== null && $m !== '') ? number_format((float) $m, 0, ',', ' ') : '..........................';
    };
    $nomComplet = $personnel->prenoms_nom ?? '';
    $parts = preg_split('/\s+/', trim($nomComplet));
    $nomFamille = count($parts) > 1 ? array_pop($parts) : $nomComplet;
    $prenoms = implode(' ', $parts);
    $civilite = isset($personnel) && $personnel && str_starts_with($personnel->sexe ?? '', 'F') ? 'Madame' : 'Monsieur';
    $posteLibelle = $poste ?? ($personnel->poste_actuel ?? '..........................');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de travail à durée déterminée</title>
    <style>
        @page { margin: 60px 55px; }
        body { font-family: 'DejaVu Serif', serif; font-size: 11px; color: #000; line-height: 1.45; }
        .title { text-align: center; font-weight: bold; font-size: 15px; margin-bottom: 30px; }
        .article-title { background: #d9d9d9; font-weight: bold; padding: 2px 5px; margin: 16px 0 4px 0; }
        .right { text-align: right; font-weight: bold; }
        .travailleur p { margin: 1px 0; }
        table.remu { border-collapse: collapse; }
        table.remu td { padding: 1px 0; }
        table.remu td.val { padding-left: 90px; }
        ul { margin: 4px 0 4px 25px; padding: 0; }
        .page-break { page-break-before: always; }
        .signatures { margin-top: 45px; width: 100%; }
        .visa { margin-top: 90px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

    {{-- ================= PAGE 1 ================= --}}
    <div class="title">
        CONTRAT DE TRAVAIL<br>
        A DUREE DETERMINEE
    </div>

    <p><strong>Entre les Soussignés :</strong></p>

    <p><strong>EMPLOYEUR :</strong></p>

    <p style="text-align: justify;">
        <strong>Le Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR),</strong> sis au
        2 Rue Amadou Assane NDOYE X Bérenger Ferraud, Dakar représenté par
        <strong>Madame Marième Soda NDIAYE, en sa qualité de Directeur général ;</strong>
    </p>

    <p class="right">D'UNE PART</p>

    <div class="travailleur">
        <p><strong>TRAVAILLEUR :</strong></p>
        <p>Prénom : {{ $prenoms ?: '' }}</p>
        <p>Nom : {{ $nomFamille }}</p>
        <p>Date et Lieu de Naissance : {{ isset($personnel->date_naissance) ? $fmtCourt($personnel->date_naissance) : '' }} à {{ $personnel->lieu_naissance ?? '' }}</p>
        <p>Filiation : {{ $filiation ?? '' }}</p>
        <p>N° Identification Nationale : {{ $numero_identification ?? ($personnel->numero_cni ?? '') }}</p>
        <p>Domicile actuel : {{ $domicile_actuel ?? ($personnel->adresse_complete ?? '') }}</p>
        <p>Nationalité : {{ $personnel->nationalite ?? 'Sénégalaise' }}</p>
        <p>Situation de Famille : {{ $situation_famille ?? ($personnel->situation_matrimoniale ?? '') }}</p>
        <p>Nombre d'épouses : {{ $nombre_epouses ?? '' }}</p>
        <p>Nombre d'enfants : {{ $personnel->nombre_enfants ?? '' }}</p>
    </div>

    <p class="right">D'AUTRE PART</p>

    <p><strong>Il a été convenu ce qui suit :</strong></p>

    <div class="article-title">ARTICLE 1 :</div>
    <p style="text-align: justify;">
        {{ $civilite }} <strong>{{ $nomComplet }}</strong>, titulaire {{ !empty($diplome) ? 'd\'un(e) ' . $diplome : '……………………………' }},
        est engagé pour servir en qualité de {{ $posteLibelle }} au Commissariat à la Sécurité Alimentaire et à
        la Résilience pour une durée déterminée à compter du {{ $fmt($date_debut ?? null) }} au
        {{ $fmt($date_fin ?? null) }}.
    </p>
    <p>Il sera classé à la catégorie {{ $categorie ?? '………' }} conformément à la grille salariale du CSAR</p>

    <div class="article-title">ARTICLE 2 :</div>
    <p style="margin-bottom: 2px;">Rémunération :</p>
    <table class="remu">
        <tr><td>Salaire de base :</td><td class="val">{{ $money($salaire_base ?? null) }} F</td></tr>
        <tr><td>Sursalaire :</td><td class="val">{{ $money($sursalaire ?? null) }} F</td></tr>
        <tr><td>Indemnité de fonction :</td><td class="val">{{ $money($indemnite_fonction ?? null) }} F</td></tr>
        <tr><td>Indemnité de transport :</td><td class="val">{{ $money($indemnite_transport ?? null) }} F</td></tr>
        <tr><td>Salaire brut global :</td><td class="val">{{ $money($salaire_brut ?? null) }} F</td></tr>
    </table>

    <p style="text-align: justify; margin-top: 12px;">
        Toutefois, l'agent subit sur son traitement une retenue pour la constitution d'une
        retraite au titre de l'IPRES.
    </p>

    {{-- ================= PAGE 2 ================= --}}
    <div class="page-break"></div>

    <div class="article-title">ARTICLE 3 :</div>
    <p style="text-align: justify;">
        L'horaire hebdomadaire est de 40 Heures correspondant au salaire indiqué ci-dessus.
    </p>

    <div class="article-title">ARTICLE 4 :</div>
    <p style="text-align: justify;">
        Le lieu d'exécution du présent contrat de travail est situé à : Dakar, 2 Rue Amadou
        Assane NDOYE x Bérenger Ferraud
    </p>

    <div class="article-title">ARTICLE 5 :</div>
    <p style="text-align: justify;">
        {{ $civilite }} <strong>{{ $nomComplet }}</strong> est appelé à servir dans tous les services du CSAR partout où
        besoin sera.
    </p>

    <div class="article-title">ARTICLE 6 :</div>
    <p style="text-align: justify;">
        {{ $civilite }} <strong>{{ $nomComplet }}</strong> déclare formellement n'être lié actuellement à
        aucun employeur et libre de tout engagement.
    </p>

    <div class="article-title">ARTICLE 7 :</div>
    <p style="text-align: justify;">
        Référence des textes qui régissent l'ensemble des rapports de travail entre
        employeur et travailleur.
    </p>
    <ul>
        <li>Code du travail et décret d'application</li>
        <li>Convention collective nationale interprofessionnelle</li>
        <li>Convention collective du Commerce</li>
        <li>Eventuellement grille interne du CSAR</li>
    </ul>

    <div class="article-title">ARTICLE 8 :</div>
    <p style="text-align: justify;">
        Toute modification aux dispositions du présent contrat de travail fera l'objet
        d'un avenant qui sera notifié à l'intéressée.
    </p>

    <table class="signatures">
        <tr>
            <td style="width: 40%; vertical-align: top;"><strong>Le Travailleur</strong></td>
            <td style="width: 60%; text-align: center; vertical-align: top;">
                <strong>Le Directeur Général<br>
                du Commissariat à la Sécurité Alimentaire et à la Résilience<br>
                Marième Soda NDIAYE</strong>
            </td>
        </tr>
    </table>

    <div class="visa">
        Visa de l'Inspecteur du<br>
        Travail et de la Sécurité<br>
        Sociale.
    </div>

</body>
</html>
