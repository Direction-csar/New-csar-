<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Convention de Stage</title>
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
        .page-footer {
            position: fixed;
            bottom: -1.7cm;
            left: 0; right: 0;
            text-align: center;
            font-size: 7.3pt;
            line-height: 1.3;
            color: #555;
            border-top: 1px solid #1d6b3a;
            padding-top: 4px;
        }
        .page-footer strong { color: #1d6b3a; }

        .rep-header { text-align: center; margin-bottom: 3px; }
        .rep-header .rep {
            font-size: 10pt; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .rep-header .devise {
            font-size: 7.5pt; font-style: italic; color: #444; margin-top: 1px;
        }
        .rep-header .ministere {
            font-size: 8pt; font-weight: bold; text-transform: uppercase;
            margin-top: 3px; color: #1d6b3a;
        }
        .rep-header .org { font-size: 8.2pt; margin-top: 1px; }
        .sep { border: none; border-top: 1.5px solid #1d6b3a; margin: 5px 0 6px 0; }

        .doc-title { text-align: center; margin: 4px 0 8px 0; }
        .doc-title h1 {
            font-size: 12pt; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;
        }
        .doc-title .ref { font-size: 8pt; color: #666; margin-top: 2px; }

        .intro { text-align: justify; margin-bottom: 4px; }

        .info-table {
            width: 100%; border-collapse: collapse;
            margin: 3px 0 5px 0; font-size: 9pt;
        }
        .info-table td { border: 1px solid #cfcfcf; padding: 2px 5px; vertical-align: top; }
        .info-table td.label {
            background: #f1f6f2; font-weight: bold; width: 32%; color: #1d6b3a;
        }

        .parties-title {
            font-weight: bold; font-size: 9.5pt; text-transform: uppercase;
            color: #1d6b3a; margin: 5px 0 2px 0;
        }

        .article { margin: 3px 0; text-align: justify; }
        .article .art-title {
            font-weight: bold; text-transform: uppercase; font-size: 9.5pt; color: #14532d;
        }

        .signatures { width: 100%; margin-top: 14px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; }
        .signatures .sig-role { font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        .signatures .sig-note { font-size: 8pt; color: #666; margin-top: 2px; }
        .signatures .sig-line {
            margin-top: 32px; border-top: 1px solid #333; padding-top: 3px; font-size: 8.5pt;
        }
        .lieu-date { margin-top: 10px; font-size: 9pt; }
    </style>
</head>
<body>
    <div class="page-footer">
        <strong>COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE (CSAR)</strong><br>
        2 Rue Amadou Assane NDIAYE X Bretagne Ferrand, Dakar — SÉNÉGAL<br>
        Tél. : (+221) 33 832 01 70 — Email : contact@csar.sn — RCCM : SN DKR 2014 B 22224 — NINEA : 005047162 2G
    </div>

    <div class="rep-header">
        <div class="rep">République du Sénégal</div>
        <div class="devise">Un Peuple — Un But — Une Foi</div>
        <div class="ministere">Ministère de l'Agriculture, de la Souveraineté Alimentaire et de l'Élevage</div>
        <div class="org">Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</div>
    </div>
    <hr class="sep">

    <div class="doc-title">
        <h1>Convention de Stage</h1>
        <div class="ref">Réf. : {{ $reference ?? 'STG-' . ($personnel->matricule ?? '____') . '-' . now()->format('Y') }}</div>
    </div>

    <p class="intro">Entre les soussignés :</p>

    <div class="parties-title">I. L'Organisme d'accueil</div>
    <p class="intro" style="margin-bottom:4px;">
        Le <strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong>, établissement public
        sis 2 Rue Amadou Assane Ndiaye X Bretagne Ferrand à Dakar, représenté par son Directeur Général,
        ci-après dénommé <strong>« l'Organisme d'accueil »</strong>,
    </p>
    <p class="intro" style="margin-bottom:4px;">D'une part,</p>

    <div class="parties-title">II. Le Stagiaire</div>
    <table class="info-table">
        <tr><td class="label">Prénom(s) et Nom</td><td>{{ $personnel->prenoms_nom ?? '' }}</td></tr>
        <tr><td class="label">Date et lieu de naissance</td><td>{{ optional($personnel->date_naissance)->format('d/m/Y') ?? '____' }} à {{ $personnel->lieu_naissance ?? '____' }}</td></tr>
        <tr><td class="label">Sexe / Nationalité</td><td>{{ $personnel->sexe ?? '—' }} / {{ $personnel->nationalite ?? 'Sénégalaise' }}</td></tr>
        <tr><td class="label">N° CNI</td><td>{{ $personnel->cni ?? $personnel->numero_cni ?? '____' }}</td></tr>
        <tr><td class="label">Adresse</td><td>{{ $personnel->adresse ?? '____' }}</td></tr>
        <tr><td class="label">Téléphone / Email</td><td>{{ $personnel->telephone ?? '—' }} / {{ $personnel->email ?? '—' }}</td></tr>
        <tr><td class="label">Établissement / Filière</td><td>{{ $etablissement ?? '____' }} / {{ $filiere ?? '____' }}</td></tr>
        <tr><td class="label">Niveau d'études</td><td>{{ $niveau_etudes ?? '____' }}</td></tr>
    </table>
    <p class="intro" style="margin-bottom:4px;">Ci-après dénommé(e) <strong>« le Stagiaire »</strong>, d'autre part.</p>

    <p class="intro">Il a été convenu et arrêté ce qui suit :</p>

    <div class="article">
        <span class="art-title">Article 1 — Objet.</span>
        La présente convention a pour objet de définir les conditions du stage au sein du CSAR. Elle ne constitue
        en aucun cas un contrat de travail et le Stagiaire ne pourra prétendre à aucun salaire.
    </div>

    <div class="article">
        <span class="art-title">Article 2 — Durée du stage.</span>
        Le stage débutera le <strong>{{ optional($date_debut)->format('d/m/Y') ?? '____' }}</strong>
        et prendra fin le <strong>{{ optional($date_fin)->format('d/m/Y') ?? '____' }}</strong>,
        soit une durée de <strong>{{ $duree ?? '____' }}</strong>.
    </div>

    <div class="article">
        <span class="art-title">Article 3 — Missions.</span>
        Le Stagiaire sera affecté à la <strong>{{ $personnel->direction_service ?? '____' }}</strong>
        et exercera les missions suivantes : {{ $missions ?? 'missions conformes au programme de stage' }}.
    </div>

    <div class="article">
        <span class="art-title">Article 4 — Gratification.</span>
        En contrepartie de sa présence, le Stagiaire percevra une gratification mensuelle de
        <strong>{{ isset($gratification) ? number_format($gratification, 0, ',', ' ') . ' FCFA' : '____ FCFA' }}</strong>,
        si la durée du stage est supérieure à deux (02) mois, conformément à la législation en vigueur.
    </div>

    <div style="page-break-before: always;"></div>

    <div class="article">
        <span class="art-title">Article 5 — Horaires et assiduité.</span>
        Le Stagiaire est tenu de respecter l'horaire de l'établissement (40 h hebdomadaire) et de notifier
        toute absence à son tuteur.
    </div>

    <div class="article">
        <span class="art-title">Article 6 — Confidentialité.</span>
        Le Stagiaire s'engage à observer une stricte confidentialité sur les informations et données auxquelles
        il/elle aura accès durant le stage.
    </div>

    <div class="article">
        <span class="art-title">Article 7 — Assurance.</span>
        Le Stagiaire doit être couvert(e) par une assurance responsabilité civile et accident de travail
        souscrite par son établissement d'origine ou à titre personnel.
    </div>

    <div class="article">
        <span class="art-title">Article 8 — Rapport de stage.</span>
        À l'issue du stage, le Stagiaire devra remettre un rapport dans un délai de <strong>{{ $delai_rapport ?? 'quinze (15) jours' }}</strong>.
        Une attestation de stage lui sera délivrée sur demande.
    </div>

    <div class="article">
        <span class="art-title">Article 9 — Rupture.</span>
        Le stage peut être interrompu à l'initiative de l'une ou l'autre des parties, moyennant un préavis
        de <strong>{{ $preavis ?? 'une (01) semaine' }}</strong>.
    </div>

    <div class="article">
        <span class="art-title">Article 10 — Litiges.</span>
        Tout différend sera réglé à l'amiable ; à défaut, il sera soumis à la juridiction compétente de Dakar.
    </div>

    <div class="lieu-date">Fait à Dakar, en deux (02) exemplaires originaux, le {{ now()->format('d/m/Y') }}.</div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-role">Le Stagiaire</div>
                <div class="sig-note">(Lu et approuvé)</div>
                <div class="sig-line">{{ $personnel->prenoms_nom ?? '' }}</div>
            </td>
            <td>
                <div class="sig-role">L'Organisme d'accueil</div>
                <div class="sig-note">Le Directeur Général du CSAR</div>
                <div class="sig-line">{{ $directeur_general ?? '' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
