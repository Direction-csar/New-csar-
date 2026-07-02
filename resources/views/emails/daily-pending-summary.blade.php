<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résumé quotidien des demandes</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 1.3rem; }
        .content { padding: 25px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { text-align: center; padding: 15px; background: #eff6ff; border-radius: 8px; min-width: 120px; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #1d4ed8; }
        .stat-label { font-size: 0.85rem; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f8fafc; font-weight: bold; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-secondary { background: #f3f4f6; color: #374151; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 0.85rem; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Résumé quotidien des demandes</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{ $adminName }}</strong>,</p>

            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number">{{ $count }}</div>
                    <div class="stat-label">Demandes en attente</div>
                </div>
            </div>

            <p>Voici la liste des demandes nécessitant une attention :</p>

            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Demandeur</th>
                        <th>Objet</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pending as $req)
                    <tr>
                        <td><strong>{{ $req->tracking_code }}</strong></td>
                        <td>{{ $req->full_name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($req->subject, 30) }}</td>
                        <td>
                            @php
                                $badgeClass = match($req->workflow_status) {
                                    'soumise' => 'badge-secondary',
                                    'en_revue' => 'badge-info',
                                    'document_attente' => 'badge-warning',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $req->workflow_status_label }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            <p><strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong></p>
            <p>Résumé généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>
