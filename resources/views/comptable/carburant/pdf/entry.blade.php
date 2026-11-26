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
    <h1>CSAR — Bon d'entrée de carburant</h1>
    <div class="sub">Comptable des matières — Document généré le {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <tr><th>Référence</th><td class="ref">{{ $item->reference }}</td></tr>
        <tr><th>Statut</th><td><span class="status">{{ strtoupper($item->status) }}</span></td></tr>
        <tr><th>Date de l'opération</th><td>{{ $item->operation_date?->format('d/m/Y') }}</td></tr>
        <tr><th>Magasin / cuve</th><td>{{ $item->warehouse?->name ?? '-' }}</td></tr>
        <tr><th>Fournisseur</th><td>{{ $item->supplier }}</td></tr>
        <tr><th>Carburant</th><td>{{ $item->fuelLabel() }}</td></tr>
        <tr><th>Quantité</th><td><strong>{{ number_format($item->quantity, 2, ',', ' ') }} litres</strong></td></tr>
        <tr><th>Prix unitaire</th><td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA / L</td></tr>
        <tr><th>Montant total</th><td><strong>{{ number_format($item->total_amount, 0, ',', ' ') }} FCFA</strong></td></tr>
        <tr><th>N° facture</th><td>{{ $item->invoice_number ?? '-' }} {{ $item->invoice_date ? '(' . $item->invoice_date->format('d/m/Y') . ')' : '' }}</td></tr>
        <tr><th>Bon de livraison</th><td>{{ $item->delivery_note_ref ?? '-' }}</td></tr>
        <tr><th>Source de financement</th><td>{{ $item->funding_source ?? '-' }}</td></tr>
        @if($item->notes)<tr><th>Notes</th><td>{{ $item->notes }}</td></tr>@endif
        <tr><th>Enregistré par</th><td>{{ $item->creator?->name }} — {{ $item->created_at?->format('d/m/Y H:i') }}</td></tr>
        @if($item->validator)<tr><th>Validé par</th><td>{{ $item->validator->name }} — {{ $item->validated_at?->format('d/m/Y H:i') }}</td></tr>@endif
        @if($item->rejection_reason)<tr><th>Motif de rejet</th><td>{{ $item->rejection_reason }}</td></tr>@endif
    </table>

    <table class="sign">
        <tr>
            <td>Le Comptable des matières<br><br>____________________</td>
            <td>Le Fournisseur / Livreur<br><br>____________________</td>
        </tr>
    </table>

    <div class="footer">Pièces jointes : {{ $item->attachments->count() }} — Document interne CSAR, généré par le système de gestion des stocks.</div>
</body>
</html>
