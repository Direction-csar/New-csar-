<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('doc-title', 'Document CSAR')</title>
    <style>
        @page { size: A4; margin: 1.3cm 1.7cm 2.4cm 1.7cm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #1a1a1a;
            margin: 0;
        }

        /* ── Pied de page obligatoire (bas absolu, chaque page) ── */
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

        /* ── En-tête (logo + bloc officiel) ── */
        .head { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: middle; }
        .head .logo-cell { width: 95px; text-align: center; }
        .head .logo-cell img { max-width: 85px; max-height: 85px; }
        .head .logo-ph {
            width: 80px; height: 80px; border: 1px dashed #aaa;
            font-size: 7pt; color: #999; text-align: center; line-height: 80px;
        }
        .head .title-cell { text-align: center; }
        .head .rep { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; }
        .head .devise { font-size: 8.5pt; font-style: italic; color: #444; margin-top: 1px; }
        .head .min { font-size: 8.6pt; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
        .head .org { font-size: 8.6pt; font-weight: bold; text-transform: uppercase; color: #1d6b3a; margin-top: 1px; }

        .sep { border: none; border-top: 1.4px solid #1d6b3a; margin: 8px 0 6px 0; }

        /* ── Références / date ── */
        .ref-row { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 14px; }
        .ref-row .ref { text-align: left; }
        .ref-row .date { text-align: right; }

        /* ── Titre du document ── */
        .doc-h1 {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 6px 0 18px 0;
            letter-spacing: 0.5px;
        }

        /* ── Corps ── */
        .corps { text-align: justify; }
        .corps p { margin: 0 0 9px 0; }
        .corps strong { font-weight: bold; }

        /* ── Signature ── */
        .sign-block {
            margin-top: 26px;
            text-align: right;
        }
        .sign-block .role { font-weight: bold; }
        .sign-block .space { height: 60px; }
        .sign-block .name { font-weight: bold; }
    </style>
</head>
<body>
    {{-- Pied de page obligatoire --}}
    <div class="page-footer">
        <strong>COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE (CSAR)</strong><br>
        2 Rue Amadou Assane NDIAYE X Bretagne Ferrand, Dakar — SÉNÉGAL<br>
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
