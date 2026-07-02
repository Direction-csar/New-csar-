<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation DG requise</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 1.3rem; }
        .content { padding: 30px; }
        .info-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .btn { display: inline-block; padding: 12px 24px; background: #f59e0b; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 0.85rem; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Validation DG requise</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{ $dgName }}</strong>,</p>

            <p>Une demande est en attente de validation par la Direction Générale.</p>

            <div class="info-box">
                <strong>Code de suivi :</strong> {{ $request->tracking_code }}<br>
                <strong>Demandeur :</strong> {{ $request->full_name }}<br>
                <strong>Objet :</strong> {{ $request->subject }}<br>
                <strong>Type :</strong> {{ ucfirst($request->type) }}<br>
                <strong>Soumis le :</strong> {{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y à H:i') }}
            </div>

            @if($request->description)
            <p><strong>Description :</strong></p>
            <div style="background: #f8fafc; padding: 12px; border-radius: 4px; font-size: 0.9rem;">
                {{ $request->description }}
            </div>
            @endif

            <p style="text-align: center;">
                <a href="{{ $approvalUrl }}" class="btn">Valider la demande</a>
            </p>
        </div>
        <div class="footer">
            <p><strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong></p>
            <p>Plateforme de Gestion des Demandes</p>
        </div>
    </div>
</body>
</html>
