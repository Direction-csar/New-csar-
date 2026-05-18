<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande reçue - CSAR</title>
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
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #f8fafb;
            border: 1px solid #e5e7eb;
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
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
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
    <div class="container">
        <div class="header" style="text-align:center;">
            <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="height:70px;margin-bottom:10px;background:#fff;padding:6px;border-radius:8px;">
            <h1 style="margin:8px 0;">🔔 Nouvelle demande reçue</h1>
            <p style="margin:0;">CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>

        <div class="content">
            <h2>Une nouvelle demande a été soumise sur la plateforme CSAR</h2>

            <div class="info-box">
                <h3>📋 Informations de la demande</h3>

                <div class="label">Nom complet :</div>
                <div class="value">{{ $request->full_name ?? $request->name ?? 'Non renseigné' }}</div>

                <div class="label">Email :</div>
                <div class="value">{{ $request->email }}</div>

                @if($request->phone)
                <div class="label">Téléphone :</div>
                <div class="value">{{ $request->phone }}</div>
                @endif

                <div class="label">Type de demande :</div>
                <div class="value">{{ is_string($request->type) ? ucfirst(str_replace('_', ' ', $request->type)) : 'Demande' }}</div>

                <div class="label">Objet :</div>
                <div class="value">{{ $request->subject ?? 'Non renseigné' }}</div>

                <div class="label">Date de soumission :</div>
                <div class="value">{{ now()->format('d/m/Y à H:i') }}</div>

                @if($request->tracking_code)
                <div class="label">Code de suivi :</div>
                <div class="tracking-code">{{ $request->tracking_code }}</div>
                @endif
            </div>

            @if($request->description)
            <div class="info-box">
                <h3>📝 Description de la demande</h3>
                <p>{{ $request->description }}</p>
            </div>
            @endif

            <div class="info-box">
                <h3>⚡ Actions recommandées</h3>
                <ul>
                    <li>Consulter la demande dans l'interface d'administration</li>
                    <li>Vérifier les informations fournies</li>
                    <li>Contacter le demandeur si nécessaire</li>
                    @if(is_string($request->type) && $request->type === 'aide_alimentaire')
                    <li><strong>Priorité élevée :</strong> Demande d'aide alimentaire</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>CSAR Platform</strong> - Commissariat à la Sécurité Alimentaire et à la Résilience</p>
            <p>📧 Email : contact@csar.sn | 📞 Téléphone : +221 XX XXX XX XX</p>
            <p>🌐 Site web : <a href="{{ url('/') }}" style="color: #059669;">www.csar.sn</a></p>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                Cet email a été envoyé automatiquement. Ne pas répondre à cet email.<br>
                Pour toute question, utilisez notre formulaire de contact.
            </p>
        </div>
    </div>
</body>
</html>
