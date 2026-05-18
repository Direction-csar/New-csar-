<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation de votre demande - CSAR</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #059669; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8fafc; }
        .tracking-box { background: #ecfdf5; border: 2px solid #10b981; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .footer { background: #64748b; color: white; padding: 15px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align:center;">
            <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="height:70px;margin-bottom:10px;background:#fff;padding:6px;border-radius:8px;">
            <h1 style="margin:8px 0;">✅ DEMANDE ENREGISTRÉE</h1>
            <p style="margin:0; font-size: 14px;">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>
        
        <div class="content">
            <h2>Bonjour {{ $name }},</h2>
            
            <p>Votre demande de type <strong>{{ $type }}</strong> a été enregistrée avec succès le <strong>{{ $date }}</strong>.</p>
            
            @if($tracking_code)
            <div class="tracking-box">
                <h3>🔍 Code de suivi</h3>
                <p style="font-size: 24px; font-weight: bold; color: #059669;">{{ $tracking_code }}</p>
                <p>Conservez ce code pour suivre l'évolution de votre demande</p>
            </div>
            @endif
            
            <div style="background: #e0f2fe; padding: 15px; border-left: 4px solid #0284c7; margin: 20px 0;">
                <h3>📋 Prochaines étapes :</h3>
                <ul>
                    <li>✅ Votre demande a été transmise à l'équipe concernée</li>
                    <li>📞 Vous recevrez un appel de confirmation sous 24h</li>
                    <li>📧 Un email de suivi vous sera envoyé régulièrement</li>
                    <li>⏰ Délai de traitement : 3 à 5 jours ouvrés</li>
                </ul>
            </div>
            
            <div style="background: #fef3c7; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3>📞 Contact d'urgence</h3>
                <p>Pour toute question urgente concernant votre demande :</p>
                <p><strong>📧 Email :</strong> contact@csar.sn<br>
                <strong>📞 Téléphone :</strong> +221 33 123 45 67</p>
            </div>
            
            <p>Nous vous remercions de votre confiance et nous nous engageons à traiter votre demande dans les meilleurs délais.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe du CSAR</strong></p>
        </div>
        
        <div class="footer">
            <p>CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience<br>
            📧 contact@csar.sn | 📞 +221 33 123 45 67<br>
            🌐 www.csar.sn</p>
        </div>
    </div>
</body>
</html>

