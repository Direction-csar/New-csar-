<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande reçue - CSAR</title>
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
            background: #10b981;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .info-box {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .value {
            color: #6b7280;
            margin-bottom: 10px;
        }
        .footer {
            background: #f3f4f6;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
            color: #6b7280;
        }
        .tracking-code {
            background: #1f2937;
            color: white;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header" style="text-align:center;">
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="height:70px;margin-bottom:10px;background:#fff;padding:6px;border-radius:8px;">
        <h1 style="margin:8px 0;">🔔 Nouvelle demande reçue</h1>
        <p style="margin:0; font-size: 14px;">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
    </div>
    
    <div class="content">
        <h2>Une nouvelle demande a été soumise sur la plateforme CSAR</h2>
        
        <div class="info-box">
            <h3>📋 Informations de la demande</h3>
            
            <div class="label">Nom complet :</div>
            <div class="value">{{ $name }}</div>
            
            <div class="label">Email :</div>
            <div class="value">{{ $email }}</div>
            
            @if($phone)
            <div class="label">Téléphone :</div>
            <div class="value">{{ $phone }}</div>
            @endif
            
            <div class="label">Type de demande :</div>
            <div class="value">{{ is_string($type) ? ucfirst(str_replace('_', ' ', $type)) : 'Demande' }}</div>
            
            <div class="label">Date de soumission :</div>
            <div class="value">{{ $date }}</div>
            
            @if($tracking_code)
            <div class="label">Code de suivi :</div>
            <div class="tracking-code">{{ $tracking_code }}</div>
            @endif
        </div>
        
        @if($message)
        <div class="info-box">
            <h3>📝 Message du demandeur</h3>
            <p>{{ $message }}</p>
        </div>
        @endif
        
        <div class="info-box">
            <h3>⚡ Actions recommandées</h3>
            <ul>
                <li>Consulter la demande dans l'interface d'administration</li>
                <li>Vérifier les informations fournies</li>
                <li>Contacter le demandeur si nécessaire</li>
                @if(is_string($type) && $type === 'aide_alimentaire')
                <li><strong>Priorité élevée :</strong> Demande d'aide alimentaire</li>
                @endif
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <p>Ce message a été envoyé automatiquement par le système CSAR.</p>
        <p>© {{ date('Y') }} CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience</p>
    </div>
</body>
</html>
