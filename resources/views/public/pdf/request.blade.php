<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande {{ $request->tracking_code }} - CSAR</title>
    <style>
        @page {
            margin: 15mm 20mm;
            size: A4;
            @top-center {
                content: "CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience";
                font-size: 8px;
                color: #666;
            }
            @bottom-center {
                content: "Page " counter(page) " sur " counter(pages);
                font-size: 8px;
                color: #666;
            }
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo-section {
            display: table-cell;
            vertical-align: top;
            width: 100px;
            text-align: left;
            padding-right: 15px;
        }
        
        .logo-img {
            width: 70px;
            height: auto;
            max-height: 70px;
        }
        
        .logo-placeholder {
            width: 70px;
            height: 70px;
            background: #22c55e;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            border-radius: 50%;
        }
        
        .header-content {
            display: table-cell;
            vertical-align: top;
            text-align: center;
            padding: 0 20px;
        }
        
        .republic-info {
            display: table-cell;
            vertical-align: top;
            width: 130px;
            text-align: right;
            font-size: 8px;
            color: #666;
            padding-left: 20px;
        }
        
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .subtitle {
            font-size: 13px;
            color: #22c55e;
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        
        .document-type {
            font-size: 9px;
            color: #6b7280;
            margin: 0;
        }
        
        .tracking-code {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            background: #f8fafc;
            border: 2px solid #22c55e;
            padding: 15px 25px;
            border-radius: 6px;
            margin: 25px auto;
            text-align: center;
            max-width: 400px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            background: #f1f5f9;
            border-left: 4px solid #22c55e;
            padding: 12px 15px;
            margin: 20px 0 15px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-grid {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 15px;
        }
        
        .info-row {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dotted #d1d5db;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        
        .info-value {
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 25px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
        }
        
        .status-approved {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #22c55e;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .status-completed {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }
        
        .description-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 18px;
            margin: 15px 0;
            line-height: 1.6;
            text-align: justify;
        }
        
        .description-title {
            font-weight: 600;
            color: #374151;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .description-content {
            color: #1f2937;
            font-size: 11px;
            line-height: 1.5;
        }
        
        .coordinates-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            padding: 10px;
            margin: 10px 0;
            font-size: 10px;
            text-align: center;
            color: #0369a1;
        }
        
        .footer {
            margin-top: 40px;
            padding: 20px 0;
            border-top: 2px solid #22c55e;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            background: #f8fafc;
            page-break-inside: avoid;
        }
        
        .footer-logo {
            font-weight: bold;
            color: #22c55e;
            font-size: 12px;
            margin-bottom: 6px;
        }
        
        .footer-org-name {
            font-weight: 600;
            color: #374151;
            font-size: 10px;
            margin-bottom: 8px;
        }
        
        .footer-republic {
            font-weight: 600;
            color: #374151;
            font-size: 9px;
            margin-bottom: 8px;
        }
        
        .contact-info {
            margin: 10px 0;
            font-size: 8px;
            line-height: 1.5;
            text-align: center;
        }
        
        .document-info {
            margin-top: 12px;
            font-size: 8px;
            color: #6b7280;
            font-style: italic;
        }
        
        .security-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 8px;
            margin: 10px 0;
            font-size: 8px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            @php
                $logoPath = public_path('images/logos/LOGO CSAR vectoriel-01.png');
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoBase64 = 'data:image/png;base64,' . $logoData;
                } else {
                    $logoBase64 = '';
                }
            @endphp
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo CSAR" class="logo-img">
            @else
                <div class="logo-placeholder">CSAR</div>
            @endif
        </div>
        <div class="header-content">
            <div class="title">FICHE DE DEMANDE</div>
            <div class="subtitle">Commissariat à la Sécurité Alimentaire et à la Résilience</div>
            <div class="document-type">Document officiel - Confidentiel</div>
        </div>
        <div class="republic-info">
            <div style="font-weight: bold; margin-bottom: 6px; font-size: 9px; line-height: 1.2;">RÉPUBLIQUE DU<br>SÉNÉGAL</div>
            <div style="font-style: italic; font-size: 8px; line-height: 1.3;">Un Peuple, Un But,<br>Une Foi</div>
        </div>
    </div>

    <div class="tracking-code">
        Code de suivi : {{ $request->tracking_code }}
    </div>

    @php
        $typeLabels = [
            'aide_alimentaire'    => 'Aide alimentaire',
            'demande_audience'    => "Demande d'audience",
            'information_generale'=> 'Information générale',
            'autre'               => 'Autre demande',
        ];
        $typeLabel = $typeLabels[$request->type_demande] ?? ucfirst(str_replace('_', ' ', $request->type_demande ?? 'Non spécifié'));
        $statutLabels = [
            'pending'    => 'EN ATTENTE',
            'en_cours'   => 'EN TRAITEMENT',
            'traite'     => 'TRAITÉE',
            'approuve'   => 'APPROUVÉE',
            'rejete'     => 'REJETÉE',
            'approved'   => 'APPROUVÉE',
            'rejected'   => 'REJETÉE',
        ];
        $statutLabel = $statutLabels[$request->statut] ?? strtoupper($request->statut ?? 'Non défini');
        $statutClass = in_array($request->statut, ['approved','approuve','traite']) ? 'status-approved'
            : (in_array($request->statut, ['rejected','rejete']) ? 'status-rejected'
            : (($request->statut === 'en_cours') ? 'status-completed' : 'status-pending'));
        $verifyUrl = url('/fr/verifier/' . $request->tracking_code);
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($verifyUrl);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
    @endphp

    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">TYPE DE DEMANDE</div>
                <div class="info-value">{{ $typeLabel }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">STATUT ACTUEL</div>
                <div class="info-value">
                    <span class="status-badge {{ $statutClass }}">{{ $statutLabel }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">DATE DE SOUMISSION</div>
                <div class="info-value">{{ $request->created_at ? $request->created_at->format('d/m/Y à H:i') : 'Non spécifiée' }}</div>
            </div>
            @if($request->date_traitement)
            <div class="info-row">
                <div class="info-label">DATE DE TRAITEMENT</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($request->date_traitement)->format('d/m/Y à H:i') }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="info-section">
        <div class="section-title">INFORMATIONS DU DEMANDEUR</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">NOM COMPLET</div>
                <div class="info-value">{{ trim(($request->nom ?? '') . ' ' . ($request->prenom ?? '')) ?: 'Non renseigné' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">ADRESSE EMAIL</div>
                <div class="info-value">{{ $request->email ?? 'Non renseigné' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NUMÉRO DE TÉLÉPHONE</div>
                <div class="info-value">{{ $request->telephone ?? 'Non renseigné' }}</div>
            </div>
            @if($request->adresse)
            <div class="info-row">
                <div class="info-label">ADRESSE</div>
                <div class="info-value">{{ $request->adresse }}</div>
            </div>
            @endif
            @if($request->region)
            <div class="info-row">
                <div class="info-label">RÉGION ADMINISTRATIVE</div>
                <div class="info-value">{{ $request->region }}</div>
            </div>
            @endif
        </div>
        @if($request->latitude && $request->longitude)
        <div class="coordinates-info">
            Coordonnées GPS : Lat. {{ number_format($request->latitude, 6) }}° / Lon. {{ number_format($request->longitude, 6) }}°
        </div>
        @endif
    </div>

    @if($request->description)
    <div class="info-section">
        <div class="section-title">DESCRIPTION DE LA DEMANDE</div>
        <div class="description-box">
            @if($request->objet)
            <div class="description-title">Objet : {{ $request->objet }}</div>
            @endif
            <div class="description-content">{!! nl2br(e($request->description)) !!}</div>
        </div>
    </div>
    @endif

    @if($request->commentaire_admin)
    <div class="info-section">
        <div class="section-title">Réponse / Commentaire administratif</div>
        <div class="description-box" style="background: #fef3c7; border-color: #f59e0b;">
            {!! nl2br(e($request->commentaire_admin)) !!}
        </div>
    </div>
    @endif

    <div class="info-section" style="margin-top: 20px;">
        <div style="display: table; width: 100%; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; background: #f0fdf4;">
            <div style="display: table-cell; vertical-align: middle; width: 120px; text-align: center;">
                <img src="{{ $qrBase64 }}" alt="QR Code" style="width: 100px; height: 100px;">
            </div>
            <div style="display: table-cell; vertical-align: middle; padding-left: 20px;">
                <div style="font-weight: bold; font-size: 12px; color: #1f2937; margin-bottom: 6px;">VÉRIFICATION D'AUTHENTICITÉ</div>
                <div style="font-size: 10px; color: #374151; line-height: 1.5;">
                    Scannez ce QR code pour vérifier l'authenticité de ce document sur le site officiel du CSAR.<br>
                    URL : {{ $verifyUrl }}
                </div>
            </div>
        </div>
    </div>

    <div class="security-note">
        ⚠️ <strong>Note de confidentialité :</strong> Ce document contient des informations personnelles confidentielles. 
        Il doit être traité conformément aux lois sur la protection des données personnelles du Sénégal.
    </div>

    <div class="footer">
        <div class="footer-logo">CSAR</div>
        <div class="footer-org-name">Commissariat à la Sécurité Alimentaire et à la Résilience</div>
        
        <div class="footer-republic">République du Sénégal • Un Peuple, Un But, Une Foi</div>
        
        <div class="contact-info">
            Site web : www.csar.sn • Email : contact@csar.gouv.sn • Téléphone : +221 33 123 45 67 • Dakar, Sénégal
        </div>
        
        <div class="document-info">
            Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }} • Code : {{ $request->tracking_code }}
        </div>
    </div>
</body>
</html>
