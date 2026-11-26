<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenue à la Newsletter CSAR</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #7c3aed; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8fafc; }
        .welcome-box { background: #f0f9ff; border: 2px solid #0ea5e9; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .footer { background: #64748b; color: white; padding: 15px; text-align: center; font-size: 12px; }
        .btn { display: inline-block; background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📰 BIENVENUE À LA NEWSLETTER CSAR</h1>
        </div>
        
        <div class="content">
            <div class="welcome-box">
                <h2>🎉 Félicitations !</h2>
                <p>Vous êtes maintenant abonné(e) à la Newsletter du CSAR</p>
                <p><strong>📧 Email :</strong> {{ $email }}</p>
                <p><strong>📅 Date d'inscription :</strong> {{ $date }}</p>
            </div>
            
            <h2>📋 Que recevrez-vous ?</h2>
            
            <div style="background: #ecfdf5; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h3>📊 Informations régulières :</h3>
                <ul>
                    <li>📈 Rapports mensuels sur la sécurité alimentaire</li>
                    <li>🏛️ Actualités institutionnelles du CSAR</li>
                    <li>🌾 Informations sur les programmes d'aide</li>
                    <li>📅 Calendrier des distributions</li>
                    <li>🎯 Alertes importantes pour les bénéficiaires</li>
                </ul>
            </div>
            
            <div style="background: #fef3c7; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h3>🔔 Fréquence d'envoi :</h3>
                <p>Vous recevrez nos communications <strong>2 fois par mois</strong> maximum, uniquement avec des informations importantes et utiles.</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/') }}" class="btn">🌐 Visiter notre site</a>
                <a href="{{ url('/about') }}" class="btn">ℹ️ En savoir plus sur le CSAR</a>
            </div>
            
            <div style="background: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3>🔒 Protection de vos données</h3>
                <p>Votre adresse email est protégée et ne sera jamais partagée avec des tiers. Vous pouvez vous désabonner à tout moment en cliquant sur le lien en bas de nos emails.</p>
            </div>
            
            <p>Merci de votre intérêt pour les activités du CSAR et bienvenue dans notre communauté !</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe Communication du CSAR</strong></p>
        </div>
        
        <div class="footer">
            <p>CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience<br>
            📧 contact@csar.sn | 📞 +221 77 645 92 42<br>
            🌐 www.csar.sn</p>
            <p style="margin-top: 10px;">
                <a href="{{ url('/newsletter/unsubscribe?email=' . $email) }}" style="color: #94a3b8; text-decoration: underline;">
                    Se désabonner
                </a>
            </p>
        </div>
    </div>
</body>
</html>

