<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Code de vérification CSAR</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px">
  <tr><td align="center">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:12px;overflow:hidden">
      <tr><td align="center" style="padding:24px 24px 8px">
        <img src="{{ rtrim(config('app.url'), '/') }}/images/csar-logo.png" alt="CSAR" width="72" height="72" style="display:block;border:0">
        <p style="margin:10px 0 0;font-size:13px;color:#475569">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
      </td></tr>
      <tr><td style="padding:16px 28px 8px;font-size:15px;line-height:1.5">
        <p style="margin:0 0 12px">Votre code de vérification pour consulter votre demande :</p>
        <p style="margin:0 0 16px;text-align:center;font-size:30px;font-weight:bold;letter-spacing:8px;color:#0d9488">{{ $code }}</p>
        <p style="margin:0 0 8px">Ce code expire après <strong>10 minutes</strong>. Il n'est utilisable que dans le navigateur qui a demandé la vérification.</p>
        <p style="margin:0 0 8px">Ne le communiquez à personne. Si vous n'avez pas demandé ce code, ignorez ce message.</p>
      </td></tr>
      <tr><td style="padding:16px 28px 24px;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0">
        CSAR — +221 77 645 92 42 — contact@csar.sn
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
