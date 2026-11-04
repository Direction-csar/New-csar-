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
        .warn { background: #fff3cd; border: 1px solid #e0a800; padding: 6px 8px; font-size: 10px; margin-bottom: 10px; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
        .sign { margin-top: 40px; width: 100%; }
        .sign td { border: none; padding-top: 30px; font-size: 10px; }
    </style>
</head>
<body>
    <h1>CSAR — Demande de remboursement carburant</h1>
    <div class="sub">Comptable des matières — Document généré le {{ now()->format('d/m/Y H:i') }}</div>

    @if($item->duplicate_of_ref)
    <div class="warn">Attention : doublon potentiel avec la demande {{ $item->duplicate_of_ref }} — vérification requise avant approbation.</div>
    @endif

    <table>
        <tr><th>Référence</th><td class="ref">{{ $item->reference }}</td></tr>
        <tr><th>Statut</th><td><span class="status">{{ strtoupper($item->status) }}</span></td></tr>
        <tr><th>Agent demandeur</th><td>{{ $item->agent_name }} {{ $item->service ? '— ' . $item->service : '' }}</td></tr>
        <tr><th>Date de la dépense</th><td>{{ $item->expense_date?->format('d/m/Y') }}</td></tr>
        <tr><th>Motif</th><td>{{ $item->purpose }}</td></tr>
        <tr><th>Activité</th><td>{{ $item->activity_type ?? '-' }} {{ $item->activity_ref ? '(' . $item->activity_ref . ')' : '' }}</td></tr>
        <tr><th>Véhicule</th><td>{{ $item->vehicle_label ?? '-' }} {{ $item->vehicle_plate ? '(' . $item->vehicle_plate . ')' : '' }}</td></tr>
        <tr><th>N° reçu / facture</th><td>{{ $item->receipt_ref ?? '-' }}</td></tr>
        @if($item->quantity_liters)<tr><th>Litres</th><td>{{ number_format($item->quantity_liters, 2, ',', ' ') }} L</td></tr>@endif
        <tr><th>Montant déclaré</th><td>{{ number_format($item->amount_claimed, 0, ',', ' ') }} FCFA</td></tr>
        <tr><th>Montant demandé</th><td><strong>{{ number_format($item->amount_requested, 0, ',', ' ') }} FCFA</strong></td></tr>
        <tr><th>Montant approuvé</th><td>{{ $item->amount_approved !== null ? number_format($item->amount_approved, 0, ',', ' ') . ' FCFA' : '—' }}</td></tr>
        <tr><th>Montant payé</th><td>{{ $item->amount_paid !== null ? number_format($item->amount_paid, 0, ',', ' ') . ' FCFA' : '—' }}
            @if($item->payment_ref)(réf. {{ $item->payment_ref }}, le {{ $item->payment_date?->format('d/m/Y') }})@endif</td></tr>
        @if($item->advance_amount !== null)
        <tr><th>Avance consentie</th><td>{{ number_format($item->advance_amount, 0, ',', ' ') }} FCFA</td></tr>
        <tr><th>Montant justifié</th><td>{{ number_format($item->justified_amount ?? 0, 0, ',', ' ') }} FCFA</td></tr>
        @if($item->complement_amount !== null)
        <tr><th>Complément</th><td>{{ number_format($item->complement_amount, 0, ',', ' ') }} FCFA {{ $item->complement_amount < 0 ? '(reliquat restitué)' : '(complément payé)' }}</td></tr>
        @endif
        @endif
        @if($item->review_comment)<tr><th>Observation</th><td>{{ $item->review_comment }}</td></tr>@endif
        @if($item->rejection_reason)<tr><th>Motif de rejet</th><td>{{ $item->rejection_reason }}</td></tr>@endif
        <tr><th>Enregistré par</th><td>{{ $item->creator?->name }} — {{ $item->created_at?->format('d/m/Y H:i') }}</td></tr>
        @if($item->reviewer)<tr><th>Instruit par</th><td>{{ $item->reviewer->name }}</td></tr>@endif
        @if($item->approver)<tr><th>Approuvé par</th><td>{{ $item->approver->name }} — {{ $item->approved_at?->format('d/m/Y H:i') }}</td></tr>@endif
        @if($item->payer)<tr><th>Payé par</th><td>{{ $item->payer->name }} — {{ $item->paid_at?->format('d/m/Y H:i') }}</td></tr>@endif
    </table>

    <table class="sign">
        <tr>
            <td>L'Agent demandeur<br><br>____________________</td>
            <td>Le Comptable des matières<br><br>____________________</td>
            <td>L'Autorité d'approbation<br><br>____________________</td>
        </tr>
    </table>

    <div class="footer">Pièces jointes : {{ $item->attachments->count() }} — Document interne CSAR, généré par le système de gestion des stocks.</div>
</body>
</html>
