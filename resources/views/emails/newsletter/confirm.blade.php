<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmez votre abonnement - CSAR</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f7f6; font-family: Arial, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #2d7d46, #4caf70); padding: 30px 40px; text-align: center; }
        .header img { height: 50px; margin-bottom: 12px; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,.85); margin: 6px 0 0; font-size: 13px; }
        .body { padding: 36px 40px; }
        .body p { line-height: 1.7; font-size: 15px; color: #444; }
        .confirm-box { background: #f0fdf4; border: 2px solid #4caf70; border-radius: 8px; padding: 24px; text-align: center; margin: 28px 0; }
        .confirm-box p { margin: 0 0 18px; font-size: 15px; color: #1a5c30; font-weight: 600; }
        .btn { display: inline-block; background: #2d7d46; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: 700; letter-spacing: .5px; }
        .btn:hover { background: #1e5c33; }
        .url-fallback { background: #f8f8f8; border: 1px solid #ddd; border-radius: 4px; padding: 10px 14px; font-size: 12px; color: #666; word-break: break-all; margin-top: 16px; }
        .notice { font-size: 13px; color: #888; margin-top: 24px; padding-top: 20px; border-top: 1px solid #eee; }
        .footer { background: #2d7d46; padding: 20px 40px; text-align: center; }
        .footer p { margin: 4px 0; font-size: 12px; color: rgba(255,255,255,.75); }
        .footer a { color: rgba(255,255,255,.9); text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📧 CSAR Newsletter</h1>
            <p>Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>

        <div class="body">
            <p>Bonjour,</p>
            <p>Vous avez demandé à vous abonner à la newsletter du <strong>CSAR</strong>. Pour finaliser votre inscription et commencer à recevoir nos actualités, veuillez cliquer sur le bouton ci-dessous :</p>

            <div class="confirm-box">
                <p>Confirmez votre abonnement en un clic</p>
                <a href="{{ $confirmUrl }}" class="btn">✅ Confirmer mon abonnement</a>
                <div class="url-fallback">
                    <strong>Lien alternatif :</strong><br>
                    {{ $confirmUrl }}
                </div>
            </div>

            <p>Une fois confirmé, vous recevrez :</p>
            <ul style="line-height:2; color:#444; font-size:14px;">
                <li>📊 Les <strong>bulletins SIM</strong> (Système d'Information sur les Marchés)</li>
                <li>📰 Les <strong>actualités</strong> du CSAR</li>
                <li>📋 Les <strong>rapports et analyses</strong> sur la résilience alimentaire</li>
                <li>📅 Les <strong>événements</strong> et annonces importantes</li>
            </ul>

            <p class="notice">
                ⚠️ Si vous n'avez pas demandé cet abonnement, ignorez simplement cet email — aucune action ne sera effectuée.<br><br>
                Ce lien est valide pour <strong>72 heures</strong>.
            </p>
        </div>

        <div class="footer">
            <p><strong>CSAR</strong> — Commissariat à la Sécurité Alimentaire et à la Résilience</p>
            <p><a href="{{ url('/') }}">www.csar.sn</a> | <a href="{{ url('/newsletter/unsubscribe') }}">Se désabonner</a></p>
            <p style="margin-top:10px; font-size:11px; color:rgba(255,255,255,.5);">© {{ date('Y') }} CSAR Sénégal. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
