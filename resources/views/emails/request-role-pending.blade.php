<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1e3a5f, #2c5282); color: #fff; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; }
        .badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-signature { background: #dbeafe; color: #1e40af; }
        .badge-scan { background: #cffafe; color: #155e75; }
        .badge-dg { background: #d1fae5; color: #065f46; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 10px 5px; }
        .btn-primary { background: #1e3a5f; color: #fff; }
        .btn-success { background: #059669; color: #fff; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
        .info-box { background: #f8fafc; border-left: 4px solid #1e3a5f; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CSAR - Action requise</h1>
        </div>
        <div class="content">
            <p>Bonjour {{ $userName ?? 'utilisateur' }},</p>

            <p>Une demande est en attente de votre intervention en tant que <strong>{{ $role ?? 'acteur' }}</strong>.</p>

            <div class="info-box">
                <p><strong>Code de suivi :</strong> {{ $request->tracking_code }}</p>
                <p><strong>Demandeur :</strong> {{ $request->full_name }}</p>
                <p><strong>Objet :</strong> {{ $request->subject }}</p>
                <p><strong>Action requise :</strong> {{ $actionLabel ?? 'traiter' }}</p>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $actionUrl }}" class="btn btn-primary">
                    {{ ucfirst($actionLabel ?? 'traiter') }} la demande
                </a>
                <a href="{{ $dashboardUrl }}" class="btn btn-success">
                    Voir le tableau de bord
                </a>
            </div>

            <p style="font-size: 0.9rem; color: #6b7280;">
                Cet email a été envoyé automatiquement par le système CSAR. Merci de ne pas y répondre.
            </p>
        </div>
        <div class="footer">
            <p>Centre de Solidarité et d'Action Sociale (CSAR) - Sénégal</p>
        </div>
    </div>
</body>
</html>
