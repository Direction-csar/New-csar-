<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .sub { color: #666; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #eee; width: 32%; }
        .ref { font-family: monospace; font-size: 13px; font-weight: bold; }
        .status { display: inline-block; padding: 2px 8px; border: 1px solid #666; border-radius: 8px; font-size: 10px; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
        .sign { margin-top: 40px; width: 100%; }
        .sign td { border: none; padding-top: 30px; font-size: 10px; }
    </style>
</head>
<body>
    <h1>CSAR — Bon de sortie de carburant</h1>
    <div class="sub">Comptable des matières — Document généré le {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <tr><th>N° du bon</th><td class="ref">{{ $item->exit_number }}</td></tr>
        <tr><th>Statut</th><td><span class="status">{{ strtoupper($item->status) }}</span></td></tr>
        <tr><th>Date de l'opération</th><td>{{ $item->operation_date?->format('d/m/Y') }}</td></tr>
        <tr><th>Magasin / cuve</th><td>{{ $item->warehouse?->name ?? '-' }}</td></tr>
        <tr><th>Carburant</th><td>{{ $item->fuelLabel() }}</td></tr>
        <tr><th>Bénéficiaire / chauffeur</th><td>{{ $item->beneficiary_name }}</td></tr>
        <tr><th>Véhicule</th><td>{{ $item->vehicle_label ?? '-' }} {{ $item->vehicle_plate ? '(' . $item->vehicle_plate . ')' : '' }}</td></tr>
        <tr><th>Destination</th><td>{{ $item->destination ?? '-' }}</td></tr>
        <tr><th>Motif</th><td>{{ $item->purpose }}</td></tr>
        <tr><th>Mission</th><td>{{ $item->mission_number ?? '-' }}
            @if($item->mission_start_date) du {{ $item->mission_start_date->format('d/m/Y') }} au {{ $item->mission_end_date?->format('d/m/Y') }}@endif</td></tr>
        <tr><th>Réf. autorisation</th><td>{{ $item->authorization_ref ?? '-' }}</td></tr>
        <tr><th>Quantité demandée</th><td>{{ number_format($item->quantity_requested, 2, ',', ' ') }} L</td></tr>
        <tr><th>Quantité autorisée</th><td>{{ $item->quantity_authorized !== null ? number_format($item->quantity_authorized, 2, ',', ' ') . ' L' : '—' }}</td></tr>
        <tr><th>Quantité délivrée</th><td><strong>{{ $item->quantity_delivered !== null ? number_format($item->quantity_delivered, 2, ',', ' ') . ' L' : '—' }}</strong></td></tr>
        @if($item->odometer_start || $item->odometer_end)
        <tr><th>Compteur (km)</th><td>{{ $item->odometer_start ?? '—' }} → {{ $item->odometer_end ?? '—' }}</td></tr>
        @endif
        @if($item->delivered_by_name)<tr><th>Délivré par</th><td>{{ $item->delivered_by_name }}</td></tr>@endif
        @if($item->notes)<tr><th>Notes</th><td>{{ $item->notes }}</td></tr>@endif
        <tr><th>Enregistré par</th><td>{{ $item->creator?->name }} — {{ $item->created_at?->format('d/m/Y H:i') }}</td></tr>
        @if($item->authorizer)<tr><th>Autorisé par</th><td>{{ $item->authorizer->name }} — {{ $item->authorized_at?->format('d/m/Y H:i') }}</td></tr>@endif
        @if($item->validator)<tr><th>Livraison validée par</th><td>{{ $item->validator->name }} — {{ $item->validated_at?->format('d/m/Y H:i') }}</td></tr>@endif
        @if($item->rejection_reason)<tr><th>Motif de rejet</th><td>{{ $item->rejection_reason }}</td></tr>@endif
    </table>

    <table class="sign">
        <tr>
            <td>Le Comptable des matières<br><br>____________________</td>
            <td>Le Bénéficiaire / Chauffeur<br><br>____________________</td>
        </tr>
    </table>

    <div class="footer">Pièces jointes : {{ $item->attachments->count() }} — Document interne CSAR, généré par le système de gestion des stocks.</div>
</body>
</html>
