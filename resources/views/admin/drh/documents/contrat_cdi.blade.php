<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de Travail à Durée Indéterminée</title>
    <style>
        @page {
            size: A4;
            margin: 1cm 1.2cm 2cm 1.2cm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.22;
            color: #1a1a1a;
            margin: 0;
        }
        /* ── Pied de page répété sur CHAQUE page ── */
        .page-footer {
            position: fixed;
            bottom: -1.7cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.3pt;
            line-height: 1.3;
            color: #555;
            border-top: 1px solid #1d6b3a;
            padding-top: 4px;
        }
        .page-footer strong { color: #1d6b3a; }

        /* ── En-tête République ── */
        .rep-header {
            text-align: center;
            margin-bottom: 3px;
        }
        .rep-header .rep {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .rep-header .devise {
            font-size: 7.5pt;
            font-style: italic;
            color: #444;
            margin-top: 1px;
        }
        .rep-header .ministere {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 3px;
            color: #1d6b3a;
        }
        .rep-header .org {
            font-size: 8.2pt;
            margin-top: 1px;
        }
        .sep {
            border: none;
            border-top: 1.5px solid #1d6b3a;
            margin: 5px 0 6px 0;
        }

        /* ── Titre ── */
        .doc-title {
            text-align: center;
            margin: 4px 0 8px 0;
        }
        .doc-title h1 {
            font-size: 12pt;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .doc-title .ref {
            font-size: 8pt;
            color: #666;
            margin-top: 2px;
        }

        .intro { text-align: justify; margin-bottom: 4px; }

        /* ── Tableau état civil ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 5px 0;
            font-size: 9pt;
        }
        .info-table td {
            border: 1px solid #cfcfcf;
            padding: 2px 5px;
            vertical-align: top;
        }
        .info-table td.label {
            background: #f1f6f2;
            font-weight: bold;
            width: 32%;
            color: #1d6b3a;
        }

        .parties-title {
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            color: #1d6b3a;
            margin: 5px 0 2px 0;
        }

        /* ── Articles ── */
        .article { margin: 3px 0; text-align: justify; }
        .article .art-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
            color: #14532d;
        }

        /* ── Signatures ── */
        .signatures {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .signatures .sig-role {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .signatures .sig-note {
            font-size: 8pt;
            color: #666;
            margin-top: 2px;
        }
        .signatures .sig-line {
            margin-top: 32px;
            border-top: 1px solid #333;
            padding-top: 3px;
            font-size: 8.5pt;
        }
        .lieu-date { margin-top: 10px; font-size: 9pt; }
    </style>
</head>
<body>
    {{-- Pied de page obligatoire — répété automatiquement sur chaque page --}}
    <div class="page-footer">
        <strong>COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE (CSAR)</strong><br>
        2 Rue Amadou Assane NDIAYE X Bretagne Ferrand, Dakar — SÉNÉGAL<br>
        Tél. : (+221) 33 832 01 70 — Email : contact@csar.sn — RCCM : SN DKR 2014 B 22224 — NINEA : 005047162 2G
    </div>

    {{-- ───────────────────────── PAGE 1 ───────────────────────── --}}
    <div class="rep-header">
        <div class="rep">République du Sénégal</div>
        <div class="devise">Un Peuple — Un But — Une Foi</div>
        <div class="ministere">Ministère de l'Agriculture, de la Souveraineté Alimentaire et de l'Élevage</div>
        <div class="org">Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</div>
    </div>
    <hr class="sep">

    <div class="doc-title">
        <h1>Contrat de Travail à Durée Indéterminée</h1>
        <div class="ref">Réf. : {{ $reference ?? 'CTI-' . ($personnel->matricule ?? '____') . '-' . now()->format('Y') }}</div>
    </div>

    <p class="intro">Entre les soussignés :</p>

    <div class="parties-title">I. L'Employeur</div>
    <p class="intro" style="margin-bottom:6px;">
        Le <strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong>, établissement public
        sis 2 Rue Amadou Assane Ndiaye X Bretagne Ferrand à Dakar, représenté par son Directeur Général,
        ci-après dénommé <strong>« l'Employeur »</strong>,
    </p>
    <p class="intro" style="margin-bottom:6px;">D'une part,</p>

    <div class="parties-title">II. L'Employé(e)</div>
    <table class="info-table">
        <tr>
            <td class="label">Prénom(s) et Nom</td>
            <td>{{ $personnel->prenoms_nom ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date et lieu de naissance</td>
            <td>{{ optional($personnel->date_naissance)->format('d/m/Y') ?? '____' }} à {{ $personnel->lieu_naissance ?? '____' }}</td>
        </tr>
        <tr>
            <td class="label">Sexe / Situation matrimoniale</td>
            <td>{{ $personnel->sexe ?? '—' }} / {{ $personnel->situation_matrimoniale ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Nationalité</td>
            <td>{{ $personnel->nationalite ?? 'Sénégalaise' }}</td>
        </tr>
        <tr>
            <td class="label">N° CNI / NINEA</td>
            <td>{{ $personnel->cni ?? $personnel->numero_cni ?? '____' }}</td>
        </tr>
        <tr>
            <td class="label">Adresse</td>
            <td>{{ $personnel->adresse ?? '____' }}</td>
        </tr>
        <tr>
            <td class="label">Téléphone / Email</td>
            <td>{{ $personnel->telephone ?? '—' }} / {{ $personnel->email ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Matricule</td>
            <td>{{ $personnel->matricule ?? '____' }}</td>
        </tr>
    </table>
    <p class="intro" style="margin-bottom:6px;">Ci-après dénommé(e) <strong>« l'Employé(e) »</strong>, d'autre part.</p>

    <p class="intro">Il a été convenu et arrêté ce qui suit :</p>

    <div class="article">
        <span class="art-title">Article 1 — Engagement.</span>
        L'Employeur engage l'Employé(e), qui accepte, dans le cadre d'un contrat de travail à durée
        <strong>indéterminée</strong> régi par le Code du Travail du Sénégal et la Convention Collective applicable.
    </div>

    <div class="article">
        <span class="art-title">Article 2 — Fonctions.</span>
        L'Employé(e) est engagé(e) en qualité de <strong>{{ $personnel->poste_actuel ?? '____' }}</strong>,
        affecté(e) à la <strong>{{ $personnel->direction_service ?? '____' }}</strong>
        (lieu : {{ $personnel->localisation_region ?? 'Dakar' }}). Il/elle exécutera toutes tâches liées à cette fonction
        et pourra être affecté(e) à d'autres postes selon les nécessités de service.
    </div>

    <div class="article">
        <span class="art-title">Article 3 — Prise de service.</span>
        Le présent contrat prend effet à compter du
        <strong>{{ optional($personnel->date_prise_service_csar ?? $personnel->date_recrutement_csar)->format('d/m/Y') ?? '____' }}</strong>.
    </div>

    <div class="article">
        <span class="art-title">Article 4 — Période d'essai.</span>
        L'engagement devient définitif à l'issue d'une période d'essai de <strong>{{ $periode_essai ?? 'trois (03) mois' }}</strong>,
        renouvelable une fois, durant laquelle chaque partie peut rompre le contrat sans préavis ni indemnité.
    </div>

    {{-- ───────────────────────── PAGE 2 ───────────────────────── --}}
    <div style="page-break-before: always;"></div>

    <div class="article">
        <span class="art-title">Article 5 — Rémunération.</span>
        En contrepartie de ses services, l'Employé(e) percevra une rémunération mensuelle brute de
        <strong>{{ isset($salaire_brut) ? number_format($salaire_brut, 0, ',', ' ') . ' FCFA' : '____ FCFA' }}</strong>,
        payable à terme échu, outre les primes et indemnités prévues par les textes en vigueur.
    </div>

    <div class="article">
        <span class="art-title">Article 6 — Durée du travail.</span>
        La durée hebdomadaire de travail est fixée conformément à la législation en vigueur, soit
        <strong>quarante (40) heures</strong>, réparties selon l'horaire de l'établissement.
    </div>

    <div class="article">
        <span class="art-title">Article 7 — Congés.</span>
        L'Employé(e) bénéficie d'un congé payé dans les conditions prévues par le Code du Travail,
        soit deux (02) jours ouvrables par mois de service effectif.
    </div>

    <div class="article">
        <span class="art-title">Article 8 — Obligations et discrétion.</span>
        L'Employé(e) s'engage à exécuter ses fonctions avec diligence et loyauté, à respecter le règlement
        intérieur et à observer une stricte confidentialité sur les informations dont il/elle a connaissance.
    </div>

    <div class="article">
        <span class="art-title">Article 9 — Rupture et préavis.</span>
        Le contrat peut être rompu par l'une ou l'autre des parties dans le respect des dispositions légales,
        moyennant un préavis dont la durée est fixée par la Convention Collective.
    </div>

    <div class="article">
        <span class="art-title">Article 10 — Litiges.</span>
        Tout différend relatif à l'exécution du présent contrat, à défaut de règlement amiable, sera soumis
        à la juridiction compétente du travail de Dakar.
    </div>

    <div class="lieu-date">Fait à Dakar, en deux (02) exemplaires originaux, le {{ now()->format('d/m/Y') }}.</div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-role">L'Employé(e)</div>
                <div class="sig-note">(Lu et approuvé)</div>
                <div class="sig-line">{{ $personnel->prenoms_nom ?? '' }}</div>
            </td>
            <td>
                <div class="sig-role">L'Employeur</div>
                <div class="sig-note">Le Directeur Général du CSAR</div>
                <div class="sig-line">{{ $directeur_general ?? '' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
