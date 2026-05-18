<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue dans la newsletter du CSAR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #00a86b 0%, #00c851 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .welcome-box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #00a86b;
            text-align: center;
        }
        .features {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: #00a86b;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #00a86b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header" style="text-align:center;">
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="height:70px;margin-bottom:10px;background:#fff;padding:6px;border-radius:8px;">
        <h1 style="margin:8px 0;">🎉 Bienvenue dans la newsletter du CSAR !</h1>
        <p style="margin:0;">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
    </div>
    
    <div class="content">
        <div class="welcome-box">
            <h2>Merci pour votre abonnement !</h2>
            <p>Nous sommes ravis de vous compter parmi nos abonnés. Vous recevrez désormais nos dernières actualités directement dans votre boîte email.</p>
        </div>
        
        <h3>Ce que vous recevrez :</h3>
        <div class="features">
            <ul>
                <li><strong>📰 Actualités :</strong> Les dernières nouvelles du CSAR</li>
                <li><strong>📊 Rapports :</strong> Nos rapports sur la sécurité alimentaire</li>
                <li><strong>🎯 Événements :</strong> Informations sur nos événements et programmes</li>
                <li><strong>📈 Statistiques :</strong> Données sur la résilience alimentaire au Sénégal</li>
            </ul>
        </div>
        
        <p>Notre mission est de garantir l'accès à une alimentation suffisante et nutritive pour tous les Sénégalais, tout en renforçant leur capacité à faire face aux crises et aux défis climatiques.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/') }}" class="btn">Visiter notre site</a>
        </div>
        
        <div class="social-links" style="text-align: center;">
            <h4>Suivez-nous sur :</h4>
            <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/">LinkedIn</a>
            <a href="https://www.facebook.com/people/Commissariat-%C3%A0-la-S%C3%A9curit%C3%A9-Alimentaire-et-%C3%A0-la-R%C3%A9silience/">Facebook</a>
            <a href="https://x.com/csar_sn">Twitter</a>
            <a href="https://www.instagram.com/csar.sn/">Instagram</a>
        </div>
        
        <div class="footer">
            <p><strong>Commissariat à la Sécurité Alimentaire et à la Résilience</strong></p>
            <p>Si vous ne souhaitez plus recevoir nos emails, vous pouvez vous désabonner à tout moment.</p>
            <p>© {{ date('Y') }} CSAR - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>

