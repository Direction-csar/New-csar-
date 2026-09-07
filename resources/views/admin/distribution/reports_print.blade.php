<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte rendu — {{ $event->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        @page { size: A4; margin: 14mm 12mm 16mm 12mm; }
        body { font-family: 'Times New Roman', Georgia, serif; color: #111; font-size: 13px; background: #eee; }
        .sheet { background: #fff; max-width: 210mm; margin: 12px auto; padding: 14mm 12mm; box-shadow: 0 0 8px rgba(0,0,0,.15); }
        .letterhead { text-align: center; line-height: 1.35; font-size: 12px; border-bottom: 2px solid #1f3864; padding-bottom: 8px; margin-bottom: 14px; }
        .letterhead .rep { font-weight: 700; letter-spacing: .5px; }
        .letterhead .sep { color: #1f3864; }
        .letterhead img { height: 62px; margin-bottom: 4px; }
        .doc-title { text-align: center; font-weight: 800; font-size: 17px; color: #1f3864; text-transform: uppercase; margin: 8px 0 2px; }
        .doc-sub { text-align: center; font-size: 12px; color: #555; margin-bottom: 14px; }
        h5 { font-family: Arial, Helvetica, sans-serif; font-size: 13.5px; text-transform: uppercase; margin-top: 10px; }
        p { text-align: justify; line-height: 1.5; }
        .page-break { page-break-before: always; }
        .toolbar { position: fixed; top: 10px; right: 10px; z-index: 10; }
        .footer-note { border-top: 1px solid #ccc; margin-top: 18px; padding-top: 6px; font-size: 11px; color: #666; display: flex; justify-content: space-between; }
        .rep-table { font-size: 11.5px !important; }
        @media print {
            body { background: #fff; }
            .sheet { box-shadow: none; margin: 0; padding: 0; max-width: none; }
            .toolbar { display: none; }
            canvas { max-width: 100% !important; }
        }
    </style>
</head>
<body>
<div class="toolbar d-flex gap-2">
    <button class="btn btn-primary btn-sm" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.distribution.reports', ['event_id' => $event->id]) }}">← Retour</a>
</div>

<div class="sheet">
    <div class="letterhead">
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR"><br>
        <span class="rep">REPUBLIQUE DU SENEGAL</span><br>
        <em>Un Peuple – Un But – Une Foi</em> <span class="sep">------------------*-------------------</span><br>
        Ministère de la Famille, de l'Action Sociale et des Solidarités <span class="sep">------------------*-------------------</span><br>
        <strong>Commissariat à la Sécurité Alimentaire et à la Résilience</strong>
    </div>

    <div class="doc-title">Compte rendu consolidé des opérations de distribution</div>
    <div class="doc-sub">{{ $event->name }}@if($event->location) — {{ $event->location }}@endif · Situation arrêtée au {{ $report['generated_at']->format('d/m/Y à H:i') }}</div>

    <p>{{ $report['text']['summary'] }}</p>

    @include('admin.distribution._report_widgets', ['report' => $report, 'event' => $event, 'print' => true])

    <div class="page-break"></div>
    <h5 class="fw-bold" style="color:#1f3864">4. CONCLUSION</h5>
    <p>{{ $report['text']['conclusion'] }}</p>

    <div class="row mt-4">
        <div class="col-6 small">
            <strong>Bénéficiaires recensés :</strong> {{ number_format($report['total_beneficiaries'], 0, ',', ' ') }}<br>
            <strong>Tickets délivrés :</strong> {{ number_format($report['total_tickets'], 0, ',', ' ') }}<br>
            <strong>Dons effectivement récupérés :</strong> {{ number_format($report['total_collected'], 0, ',', ' ') }}<br>
            <strong>Tickets non récupérés :</strong> {{ number_format($report['ticket_not_collected'], 0, ',', ' ') }}
        </div>
        <div class="col-6 text-end small">
            Fait à Dakar, le {{ $report['generated_at']->format('d/m/Y') }}<br><br><br>
            <strong>Le Commissaire à la Sécurité Alimentaire et à la Résilience</strong>
        </div>
    </div>

    <div class="footer-note">
        <span>CSAR — Système de gestion des distributions · Rapport généré automatiquement à partir des données de la plateforme</span>
        <span>{{ $report['generated_at']->format('d/m/Y H:i') }}</span>
    </div>
</div>
</body>
</html>
