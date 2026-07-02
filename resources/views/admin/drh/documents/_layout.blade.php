<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('doc-title', 'Document CSAR')</title>
    <style>
        @page { size: A4; margin: 0.9cm 1.2cm 1.8cm 1.2cm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #1a1a1a;
            margin: 0;
        }

        /* ── Pied de page obligatoire (bas absolu, chaque page) ── */
        .page-footer {
            position: fixed;
            bottom: -1.5cm;
            left: 0; right: 0;
            text-align: center;
            font-size: 7.3pt;
            line-height: 1.3;
            color: #555;
            border-top: 1px solid #1d6b3a;
            padding-top: 4px;
        }
        .page-footer strong { color: #1d6b3a; }

        /* ── En-tête (logo + bloc officiel) ── */
        .head { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: middle; }
        .head .logo-cell { width: 70px; text-align: center; }
        .head .logo-cell img { max-width: 60px; max-height: 60px; }
        .head .logo-ph {
            width: 60px; height: 60px; border: 1px dashed #aaa;
            font-size: 7pt; color: #999; text-align: center; line-height: 60px;
        }
        .head .title-cell { text-align: center; }
        .head .rep { font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        .head .devise { font-size: 7.5pt; font-style: italic; color: #444; margin-top: 1px; }
        .head .min { font-size: 7.8pt; font-weight: bold; text-transform: uppercase; margin-top: 3px; }
        .head .org { font-size: 7.8pt; font-weight: bold; text-transform: uppercase; color: #1d6b3a; margin-top: 1px; }

        .sep { border: none; border-top: 1.4px solid #1d6b3a; margin: 5px 0 4px 0; }

        /* ── Références / date ── */
        .ref-row { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 8px; }
        .ref-row .ref { text-align: left; }
        .ref-row .date { text-align: right; }

        /* ── Titre du document ── */
        .doc-h1 {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 4px 0 10px 0;
            letter-spacing: 0.5px;
        }

        /* ── Corps ── */
        .corps { text-align: justify; }
        .corps p { margin: 0 0 4px 0; }
        .corps strong { font-weight: bold; }

        /* ── Signature ── */
        .sign-block {
            margin-top: 14px;
            text-align: right;
        }
        .sign-block .role { font-weight: bold; }
        .sign-block .space { height: 35px; }
        .sign-block .name { font-weight: bold; }
    </style>
</head>
<body>
    {{-- Pied de page obligatoire --}}
    <div class="page-footer">
        <strong>COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE (CSAR)</strong><br>
        22 Rue Amadou Assane NDOYE X Bérenger Féraud, Dakar — SÉNÉGAL<br>
        Tél. : (+221) 33 832 01 70 — Email : contact@csar.sn — RCCM : SN DKR 2014 B 22224 — NINEA : 005047162 2G
    </div>

    {{-- En-tête officiel --}}
    <table class="head">
        <tr>
            <td class="logo-cell">
                @php $logoPath = public_path('images/csar-logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="CSAR">
                @else
                    <div class="logo-ph">LOGO CSAR</div>
                @endif
            </td>
            <td class="title-cell">
                <div class="rep">République du Sénégal</div>
                <div class="devise">Un Peuple — Un But — Une Foi</div>
                <div class="min">Ministère de la Famille, de l'Action Sociale et des Solidarités</div>
                <div class="org">Commissariat à la Sécurité Alimentaire et à la Résilience</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>
    <hr class="sep">

    {{-- Références / Date --}}
    <table class="ref-row">
        <tr>
            <td class="ref">N° @yield('reference', '_________') /MIFASS/CSAR/SG/DRH</td>
            <td class="date">Dakar, le {{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    {{-- Titre --}}
    <div class="doc-h1">@yield('titre')</div>

    {{-- Corps --}}
    <div class="corps">
        @yield('corps')
    </div>

    {{-- Signature --}}
    <div class="sign-block">
        <div class="role">@yield('signataire-role', 'Le Directeur Général')</div>
        <div class="space"></div>
        <div class="name">@yield('signataire-nom', $directeur_general ?? '')</div>
    </div>
</body>
</html>
