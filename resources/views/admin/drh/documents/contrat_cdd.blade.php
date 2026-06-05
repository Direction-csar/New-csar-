<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de Travail à Durée Déterminée</title>
    <style>
        @page { size: A4; margin: 0.5cm 0.8cm 1.3cm 0.8cm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 8.3pt;
            line-height: 1.1;
            color: #000;
            margin: 0;
        }

        /* Footer répété sur chaque page */
        .page-footer {
            position: fixed;
            bottom: -1.2cm;
            left: 0; right: 0;
            text-align: center;
            font-size: 6.8pt;
            line-height: 1.2;
            color: #333;
            border-top: 1px solid #000;
            padding-top: 2px;
        }

        /* Header officiel */
        .head { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: middle; }
        .head .logo-cell { width: 48px; text-align: center; }
        .head .logo-cell img { max-width: 42px; max-height: 42px; }
        .head .logo-ph {
            width: 42px; height: 42px; border: 1px dashed #999;
            font-size: 6.5pt; color: #999; text-align: center; line-height: 42px;
        }
        .head .title-cell { text-align: center; }
        .head .rep { font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .head .devise { font-size: 6.8pt; font-style: italic; margin-top: 1px; }
        .head .min { font-size: 7.2pt; font-weight: bold; text-transform: uppercase; margin-top: 2px; }
        .head .org { font-size: 7.2pt; font-weight: bold; text-transform: uppercase; margin-top: 1px; }

        .sep { border: none; border-top: 1.5px solid #000; margin: 2px 0 3px 0; }

        /* Ref / date */
        .ref-row { width: 100%; font-size: 7.8pt; margin-bottom: 3px; }
        .ref-row .ref { text-align: left; }
        .ref-row .date { text-align: right; }

        /* Titre */
        .doc-title { text-align: center; margin: 2px 0 3px 0; }
        .doc-title h1 { font-size: 10.5pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; margin: 0; }
        .doc-title h2 { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; margin: 1px 0 0 0; }

        .section-title { font-weight: bold; margin: 2px 0 1px 0; }

        /* Tableaux parties */
        .party-table {
            width: 100%; border-collapse: collapse; margin: 1px 0;
            font-size: 8.3pt;
        }
        .party-table th {
            border: 1px solid #000; padding: 1px 3px;
            text-align: center; font-weight: bold;
            background: #e8e8e8; text-transform: uppercase;
            font-size: 8.3pt;
        }
        .party-table td {
            border: 1px solid #000; padding: 1px 3px;
            vertical-align: top;
        }
        .party-table td.label { font-weight: bold; width: 38%; }

        .part-label {
            text-align: center; font-weight: bold;
            border: 1px solid #000; padding: 1px;
            background: #e8e8e8; font-size: 8.3pt;
            margin: 1px 0;
        }

        /* Articles */
        .article { margin: 1px 0; text-align: justify; }
        .article .art-title { font-weight: bold; text-transform: uppercase; font-size: 8.3pt; }

        /* Tableau rémunération */
        .salary-table {
            width: 100%; border-collapse: collapse; margin: 1px 0;
            font-size: 8.3pt;
        }
        .salary-table td {
            border: 1px solid #000; padding: 1px 3px;
        }
        .salary-table td.label { font-weight: bold; width: 62%; }
        .salary-table td.amount { text-align: right; font-weight: bold; width: 38%; }

        /* Signatures */
        .signatures {
            width: 100%; margin-top: 4px;
            border-collapse: collapse; font-size: 8.3pt;
        }
        .signatures td {
            width: 33%; text-align: center;
            vertical-align: top; padding: 1px;
        }
        .signatures .role {
            font-weight: bold; text-transform: uppercase;
            font-size: 8pt;
        }
        .signatures .space { height: 18px; }
    </style>
</head>
<body>
    <div class="page-footer">
        <strong>COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE (CSAR)</strong><br>
        2 Rue Amadou Assane NDIAYE X Bretagne Ferrand, Dakar — SÉNÉGAL — Tél. : (+221) 33 832 01 70 — RCCM : SN DKR 2014 B 22224 — NINEA : 005047162 2G
    </div>

    {{-- Header officiel --}}
    <table class="head">
        <tr>
            <td class="logo-cell">
                @php $logoPath = public_path('images/csar-logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="CSAR">
                @else
                    <div class="logo-ph">LOGO</div>
                @endif
            </td>
            <td class="title-cell">
                <div class="rep">RÉPUBLIQUE DU SÉNÉGAL</div>
                <div class="devise">Un Peuple — Un But — Une Foi</div>
                <div class="min">MINISTÈRE DE LA FAMILLE, DE L'ACTION SOCIALE ET DES SOLIDARITÉS</div>
                <div class="org">COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>
    <hr class="sep">

    {{-- Réf / Date --}}
    <table class="ref-row">
        <tr>
            <td class="ref">N° {{ $reference ?? '_________' }}/MIFASS/CSAR/SG/DRH</td>
            <td class="date">Dakar, le {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    {{-- Titre --}}
    <div class="doc-title">
        <h1>CONTRAT DE TRAVAIL</h1>
        <h2>A DUREE DETERMINEE</h2>
    </div>

    <p class="section-title">Entre les Soussignés :</p>

    {{-- Employeur --}}
    <table class="party-table">
        <tr><th colspan="2">EMPLOYEUR</th></tr>
        <tr><td colspan="2">La Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR), sis au 2 Rue Amadou Assane NDIAYE X Bretagne Ferrand, Dakar représenté par Madame <strong>Marième Soda NDIAYE</strong>, en sa qualité de Directeur général ;</td></tr>
    </table>

    <div class="part-label">D'UNE PART</div>

    {{-- Travailleur --}}
    <table class="party-table">
        <tr><th colspan="2">TRAVAILLEUR</th></tr>
        <tr><td class="label">Prénom :</td><td>{{ $personnel?->prenom ?? ($personnel?->prenoms ?? '____') }}</td></tr>
        <tr><td class="label">Nom :</td><td>{{ $personnel?->nom ?? '____' }}</td></tr>
        <tr><td class="label">Date et Lieu de Naissance :</td><td>{{ optional($personnel?->date_naissance)->format('d/m/Y') ?? '__/__/____' }} à {{ $personnel?->lieu_naissance ?? '____' }}</td></tr>
        <tr><td class="label">Filiation :</td><td>{{ $personnel?->filiation ?? '____' }}</td></tr>
        <tr><td class="label">N° Identification Nationale :</td><td>{{ $personnel?->cni ?? $personnel?->numero_cni ?? '____' }} du {{ $personnel?->date_delivrance_cni ?? '____' }}</td></tr>
        <tr><td class="label">Domicile actuel :</td><td>{{ $personnel?->adresse ?? '____' }}</td></tr>
        <tr><td class="label">Nationalité :</td><td>{{ $personnel?->nationalite ?? 'Sénégalaise' }}</td></tr>
        <tr><td class="label">Situation de Famille :</td><td>{{ $personnel?->situation_matrimoniale ?? '____' }}</td></tr>
        <tr><td class="label">Nombre d'épouses :</td><td>{{ $personnel?->nombre_epouses ?? '0' }}</td></tr>
        <tr><td class="label">Nombre d'enfants :</td><td>{{ $personnel?->nombre_enfants ?? '0' }}</td></tr>
    </table>

    <div class="part-label">D'AUTRE PART</div>

    <p style="text-align:center; font-weight:bold; margin:2px 0;">Il a été convenu et arrêté ce qui suit :</p>

    <div class="article">
        <span class="art-title">ARTICLE 1 :</span>
        Monsieur <strong>{{ $personnel?->prenoms_nom ?? '____' }}</strong>, titulaire d'un <strong>{{ $diplome ?? '____________________' }}</strong>, est engagé pour servir en qualité de <strong>{{ $personnel?->poste_actuel ?? '____' }}</strong> à la Direction générale du Commissariat à la Sécurité Alimentaire et à la Résilience pour une durée déterminée à compter du <strong>{{ optional($date_debut ?? $personnel?->date_prise_service_csar)->format('d/m/Y') ?? '__/__/____' }}</strong>. Il sera classé à la catégorie <strong>{{ $categorie ?? '____' }}</strong> C conformément à la convention collective du commerce.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 2 :</span>
        <strong>Rémunération :</strong>
        <table class="salary-table">
            <tr><td class="label">Salaire de base, complément spécial de solde et indemnité résidence :</td><td class="amount">{{ isset($salaire_base) ? number_format((float)$salaire_base, 0, ',', ' ') . ' F' : '____ F' }}</td></tr>
            <tr><td class="label">Sur-salaire :</td><td class="amount">{{ isset($sur_salaire) ? number_format((float)$sur_salaire, 0, ',', ' ') . ' F' : '____ F' }}</td></tr>
            <tr><td class="label">Indemnité de transport :</td><td class="amount">{{ isset($indemnite_transport) ? number_format((float)$indemnite_transport, 0, ',', ' ') . ' F' : '____ F' }}</td></tr>
            <tr><td class="label">Salaire brut global :</td><td class="amount">{{ isset($salaire_brut_global) ? number_format((float)$salaire_brut_global, 0, ',', ' ') . ' F' : '____ F' }}</td></tr>
        </table>
        Toutefois, l'agent subira sur traitement une retenue pour la constitution d'une retraite au titre de l'IPRES.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 3 :</span>
        Le présent contrat est conclu pour une durée déterminée allant du <strong>{{ optional($date_debut ?? $personnel?->date_prise_service_csar)->format('d/m/Y') ?? '__/__/____' }}</strong> au <strong>{{ optional($date_fin)->format('d/m/Y') ?? '__/__/____' }}</strong> (soit <strong>{{ $duree ?? '____' }}</strong>). Il prend fin de plein droit à son terme, sans préavis ni indemnité de rupture.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 4 :</span>
        Le contrat comporte une période d'essai de <strong>{{ $periode_essai ?? 'un (01) mois' }}</strong>, durant laquelle chaque partie peut y mettre fin sans préavis ni indemnité.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 5 :</span>
        L'horaire hebdomadaire est de <strong>40 Heures</strong> correspondant au salaire indiqué ci-dessus.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 6 :</span>
        Le lieu d'exécution du présent contrat de travail est situé à : <strong>Dakar, 22 Rue Amadou Assane NDIAYE X Bretagne Ferrand</strong>
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 7 :</span>
        Monsieur <strong>{{ $personnel?->prenoms_nom ?? '____' }}</strong> est appelé à servir dans tous les services du CSAR partout où besoin sera.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 8 :</span>
        Monsieur <strong>{{ $personnel?->prenoms_nom ?? '____' }}</strong> déclare formellement n'être actuellement à aucun employeur et libre de tout engagement.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 9 :</span>
        Toute rupture avant le terme convenu, en dehors de la faute lourde ou de la force majeure, ouvre droit à des dommages et intérêts conformément au Code du Travail.
    </div>

    <div class="article">
        <span class="art-title">ARTICLE 10 :</span>
        Référence des textes qui régissent l'ensemble des rapports de travail : Code du travail et décret d'application ; Convention collective nationale interprofessionnelle ; Convention collective du commerce ; Eventuellement grille interne du CSAR.
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="role">Le Travailleur</div>
                <div class="space"></div>
                <div>{{ $personnel?->prenoms_nom ?? '' }}</div>
            </td>
            <td>
                <div class="role">Le Directeur Général<br>du Commissariat à la Sécurité<br>Alimentaire et à la Résilience</div>
                <div class="space"></div>
                <div><strong>Marième Soda NDIAYE</strong></div>
            </td>
            <td>
                <div class="role">Visa de l'Inspecteur du<br>Travail et de la Sécurité<br>Sociale</div>
                <div class="space"></div>
                <div></div>
            </td>
        </tr>
    </table>
</body>
</html>
