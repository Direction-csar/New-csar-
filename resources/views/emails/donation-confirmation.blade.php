<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation de don - CSAR</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f1f5f9; }
        .container { max-width: 600px; margin: 30px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #00a86b 0%, #005a9e 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0 0 8px; font-size: 20px; }
        .header p { margin: 0; opacity: 0.85; font-size: 14px; }
        .hero { background: #f0fdf4; padding: 24px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .amount-badge { display: inline-block; background: #00a86b; color: white; font-size: 28px; font-weight: bold; padding: 12px 28px; border-radius: 50px; margin: 8px 0; }
        .content { padding: 28px; }
        .info-box { background: #f8fafc; border-left: 4px solid #00a86b; padding: 16px; border-radius: 0 8px 8px 0; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; }
        .info-value { font-weight: 600; color: #1e293b; }
        .thank-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center; }
        .footer { background: #334155; color: #cbd5e1; padding: 20px; text-align: center; font-size: 12px; }
        .footer a { color: #94a3b8; text-decoration: none; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <h1>🏛️ CSAR</h1>
            <p>Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>

        <div class="hero">
            <p style="font-size:16px; margin:0 0 8px; color:#374151;">✅ Votre don a été reçu avec succès !</p>
            <div class="amount-badge">
                {{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency }}
            </div>
            <p style="margin:8px 0 0; color:#6b7280; font-size:13px;">
                {{ $donation->donation_type === 'monthly' ? 'Don récurrent — ' . ucfirst($donation->frequency ?? 'mensuel') : 'Don unique' }}
            </p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $donation->is_anonymous ? 'Donateur anonyme' : $donation->full_name }}</strong>,</p>

            <p>Nous avons bien reçu votre don et vous en remercions chaleureusement. Votre générosité contribue directement à la sécurité alimentaire et à la résilience des communautés au Sénégal.</p>

            <div class="info-box">
                <p style="margin:0 0 12px; font-weight:700; color:#1e293b;">📋 Récapitulatif du don</p>
                <div class="info-row">
                    <span class="info-label">Référence</span>
                    <span class="info-value">#{{ $donation->id }}</span>
                </div>
                @if($donation->transaction_id)
                <div class="info-row">
                    <span class="info-label">N° de transaction</span>
                    <span class="info-value">{{ $donation->transaction_id }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Montant</span>
                    <span class="info-value">{{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mode de paiement</span>
                    <span class="info-value">
                        @php
                            $methods = ['wave'=>'Wave','orange_money'=>'Orange Money','credit_card'=>'Carte bancaire','paypal_balance'=>'PayPal','paypal_card'=>'PayPal (carte)'];
                        @endphp
                        {{ $methods[$donation->payment_method] ?? ucfirst($donation->payment_method) }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span class="info-value">{{ $donation->processed_at ? $donation->processed_at->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut</span>
                    <span class="info-value" style="color:#00a86b;">✅ Confirmé</span>
                </div>
            </div>

            @if($donation->message)
            <div style="background:#fafafa; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin:16px 0;">
                <p style="margin:0 0 6px; font-size:13px; color:#64748b;">Votre message accompagnant le don :</p>
                <p style="margin:0; font-style:italic; color:#374151;">"{{ $donation->message }}"</p>
            </div>
            @endif

            <hr class="divider">

            <div class="thank-box">
                <p style="margin:0; font-size:15px; color:#1e40af;">
                    🙏 <strong>Merci pour votre soutien au CSAR !</strong><br>
                    <span style="font-size:13px; color:#3730a3;">Votre contribution fait une différence réelle sur le terrain.</span>
                </p>
            </div>

            <p style="font-size:13px; color:#64748b;">
                Si vous avez des questions concernant votre don, n'hésitez pas à nous contacter en indiquant votre référence <strong>#{{ $donation->id }}</strong>.
            </p>

            <p>Cordialement,<br><strong>L'équipe du CSAR</strong></p>
        </div>

        <div class="footer">
            <p>CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience<br>
            📧 <a href="mailto:contact@csar.sn">contact@csar.sn</a> &nbsp;|&nbsp;
            🌐 <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
            <p style="margin-top:8px; font-size:11px; color:#94a3b8;">
                Cet email est une confirmation automatique. Conservez-le comme preuve de votre don.
            </p>
        </div>

    </div>
</body>
</html>
