<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avances Tabaski 2026 — CSAR</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #065f46; padding-bottom: 15px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #065f46; }
        .header p { font-size: 10px; color: #555; margin-top: 4px; }
        .stats { display: flex; gap: 20px; margin-bottom: 16px; }
        .stat { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 16px; border-radius: 6px; text-align: center; }
        .stat strong { display: block; font-size: 16px; color: #065f46; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #065f46; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;">
        <button onclick="window.print()" style="background:#065f46;color:white;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px;">
            🖨️ Imprimer / Enregistrer en PDF
        </button>
    </div>

    <div class="header">
        <h1>TABLEAU DES AVANCES TABASKI 2026</h1>
        <p>COMMISSARIAT A LA SECURITE ALIMENTAIRE ET A LA RESILIENCE (CSAR)</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat"><strong>{{ $stats['total'] }}</strong> Inscrits</div>
        <div class="stat"><strong>{{ $stats['montant_global'] }}</strong> Total engagé</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Poste</th>
                <th>Direction</th>
                <th>Région</th>
                <th>Montant</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inscriptions as $i => $ins)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $ins->agent->prenom ?? '' }}</td>
                <td><strong>{{ $ins->agent->nom ?? '' }}</strong></td>
                <td>{{ $ins->agent->poste ?? '' }}</td>
                <td>{{ $ins->agent->direction ?? '' }}</td>
                <td>{{ $ins->agent->region ?? '' }}</td>
                <td><strong>{{ number_format((int)$ins->montant, 0, ',', ' ') }}</strong></td>
                <td>{{ $ins->date_inscription->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">CSAR — Document confidentiel — DRH</div>
</body>
</html>
