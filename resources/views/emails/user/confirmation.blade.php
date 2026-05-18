<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - CSAR Platform</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .confirmation-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #51cf66;
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            color: #51cf66;
            margin-bottom: 15px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
        }
        .info-box {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="text-align:center;">
            <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="height:70px;margin-bottom:10px;background:#fff;padding:6px;border-radius:8px;">
            <h1 style="margin:8px 0;">✅ Confirmation reçue</h1>
            <p style="margin:0;">CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>
        
        <div class="content">
            <p>Bonjour,</p>
            
            @if($type === 'contact')
                <div class="confirmation-box">
                    <div class="success-icon">📬</div>
                    <h3 style="margin: 0 0 10px 0; color: #51cf66;">Message transmis avec succès</h3>
                    <p style="margin: 0;">Votre message a bien été transmis à l'équipe du CSAR.</p>
                </div>
                
                <p>Nous avons bien reçu votre message et nous vous remercions de nous avoir contactés. Notre équipe examinera votre demande et vous répondra dans les plus brefs délais.</p>
                
                <div class="info-box">
                    <h4 style="margin: 0 0 10px 0; color: #1976d2;">📋 Détails de votre message</h4>
                    @if(isset($data['subject']))
                        <p><strong>Sujet :</strong> {{ $data['subject'] }}</p>
                    @endif
                    @if(isset($data['name']))
                        <p><strong>Nom :</strong> {{ $data['name'] }}</p>
                    @endif
                    <p><strong>Date d'envoi :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
                </div>
                
            @elseif($type === 'newsletter')
                <div class="confirmation-box">
                    <div class="success-icon">📧</div>
                    <h3 style="margin: 0 0 10px 0; color: #51cf66;">Abonnement confirmé</h3>
                    <p style="margin: 0;">Votre abonnement à la newsletter du CSAR a été enregistré.</p>
                </div>
                
                <p>Merci de vous être abonné à notre newsletter ! Vous recevrez désormais nos dernières actualités, rapports et informations importantes du Commissariat à la Sécurité Alimentaire et à la Résilience.</p>
                
                <div class="info-box">
                    <h4 style="margin: 0 0 10px 0; color: #1976d2;">📊 À propos de notre newsletter</h4>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Actualités et événements du CSAR</li>
                        <li>Rapports d'analyse des risques</li>
                        <li>Alertes et recommandations</li>
                        <li>Publications et ressources</li>
                    </ul>
                </div>
                
            @elseif($type === 'request')
                <div class="confirmation-box">
                    <div class="success-icon">🚨</div>
                    <h3 style="margin: 0 0 10px 0; color: #51cf66;">Demande enregistrée</h3>
                    <p style="margin: 0;">Votre demande a bien été enregistrée.</p>
                </div>

                <p>Bonjour {{ $data['name'] ?? '' }},</p>

                <p>Nous avons bien reçu votre demande. Notre équipe spécialisée examinera votre situation et vous contactera pour vous apporter l'assistance nécessaire.</p>

                @if(isset($data['tracking_code']) && $data['tracking_code'])
                <div class="confirmation-box" style="background: #ecfdf5; border: 2px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                    <div class="success-icon" style="font-size: 36px; margin-bottom: 10px;">🔍</div>
                    <h3 style="margin: 0 0 10px 0; color: #059669;">Code de suivi</h3>
                    <p style="font-size: 28px; font-weight: bold; color: #059669; margin: 0; letter-spacing: 2px; font-family: monospace;">{{ $data['tracking_code'] }}</p>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #6b7280;">Conservez ce code pour suivre l'évolution de votre demande</p>
                </div>
                @endif

                <div class="info-box">
                    <h4 style="margin: 0 0 10px 0; color: #1976d2;">📋 Détails de votre demande</h4>
                    @if(isset($data['objet']))
                        <p><strong>Objet :</strong> {{ $data['objet'] }}</p>
                    @endif
                    @if(isset($data['type']))
                        <p><strong>Type :</strong> {{ is_string($data['type']) ? ucfirst(str_replace('_', ' ', $data['type'])) : 'Demande' }}</p>
                    @endif
                    @if(isset($data['name']))
                        <p><strong>Nom :</strong> {{ $data['name'] }}</p>
                    @endif
                    <p><strong>Date d'envoi :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
                </div>

                <div style="background: #fef3c7; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px 0; color: #92400e;">📞 Contact d'urgence</h3>
                    <p style="margin: 0;">Pour toute question urgente concernant votre demande :</p>
                    <p style="margin: 8px 0 0 0;"><strong>Email :</strong> contact@csar.sn<br>
                    <strong>Téléphone :</strong> +221 33 123 45 67</p>
                </div>
                
            @else
                <div class="confirmation-box">
                    <div class="success-icon">✅</div>
                    <h3 style="margin: 0 0 10px 0; color: #51cf66;">Action confirmée</h3>
                    <p style="margin: 0;">Votre demande a bien été traitée.</p>
                </div>
            @endif
            
            <p><strong>Prochaines étapes :</strong></p>
            <ul>
                <li>Notre équipe examinera votre demande</li>
                <li>Vous recevrez une réponse dans les plus brefs délais</li>
                <li>Pour toute urgence, contactez-nous directement</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{ url('/') }}" class="btn">
                    🏠 Retour à l'accueil
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>CSAR Platform</strong> - Commissariat à la Sécurité Alimentaire et à la Résilience</p>
            <p>📧 Email : contact@csar.sn | 📞 Téléphone : +221 XX XXX XX XX</p>
            <p>🌐 Site web : <a href="{{ url('/') }}" style="color: #51cf66;">www.csar.sn</a></p>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                Cet email a été envoyé automatiquement. Ne pas répondre à cet email.<br>
                Pour toute question, utilisez notre formulaire de contact.
            </p>
        </div>
    </div>
</body>
</html>
