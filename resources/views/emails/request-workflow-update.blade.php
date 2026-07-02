<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de votre demande CSAR</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 1.4rem; }
        .content { padding: 30px; }
        .tracking-code { background: #f0fdf4; border: 2px dashed #22c55e; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .tracking-code .code { font-size: 1.5rem; font-weight: bold; color: #15803d; letter-spacing: 2px; }
        .status-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .btn { display: inline-block; padding: 12px 24px; background: #22c55e; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 0.85rem; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📨 Mise à jour de votre demande</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{ $request->full_name }}</strong>,</p>

            <p>Votre demande soumise au <strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong> a été mise à jour.</p>

            <div class="tracking-code">
                <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 8px;">Code de suivi</div>
                <div class="code">{{ $request->tracking_code }}</div>
            </div>

            <div class="status-box">
                <strong>Statut actuel :</strong> Votre demande est maintenant <strong style="color: #1d4ed8;">{{ $label }}</strong>.
            </div>

            @if($request->admin_comment)
            <div style="background: #fefce8; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong>Commentaire de l'administration :</strong><br>
                {{ $request->admin_comment }}
            </div>
            @endif

            <p style="text-align: center;">
                <a href="{{ $trackingUrl }}" class="btn">Suivre ma demande</a>
            </p>

            <p style="font-size: 0.9rem; color: #6b7280; margin-top: 20px;">
                Vous pouvez suivre l'avancement de votre demande à tout moment avec votre code de suivi.
            </p>
        </div>
        <div class="footer">
            <p><strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong></p>
            <p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
