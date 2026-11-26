<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSAR — Gestion du contrôle</title>
    <link rel="stylesheet" href="{{ asset('css/stock-portal.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-hero-side{display:flex;flex-direction:column;justify-content:center;padding:56px 48px;color:#fff;position:relative;overflow:hidden}
        .login-hero-side::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 85% 15%,rgba(249,115,22,.25),transparent 55%)}
        .login-hero-side .inst{font-size:12px;letter-spacing:1.6px;text-transform:uppercase;opacity:.65;margin-bottom:18px}
        .login-hero-side h1{font-size:30px;font-weight:800;line-height:1.2;margin-bottom:14px}
        .login-hero-side h1 span{color:#FB923C}
        .login-hero-side p{font-size:14px;opacity:.78;line-height:1.7;max-width:420px}
        .login-hero-side .points{margin-top:26px;display:flex;flex-direction:column;gap:12px}
        .login-hero-side .points div{display:flex;align-items:center;gap:12px;font-size:13.5px;opacity:.9}
        .login-hero-side .points i{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:14px}
        .login-wrap-split{min-height:100vh;display:grid;grid-template-columns:1.15fr 1fr}
        @media(max-width:900px){.login-wrap-split{grid-template-columns:1fr}.login-hero-side{display:none}}
        .login-form-side{display:flex;align-items:center;justify-content:center;padding:40px 24px;background:#F4F6FA}
        .login-card-wide{width:100%;max-width:430px}
        .login-head{text-align:left;padding:24px 28px 10px;background:transparent;color:var(--night)}
        .login-head h2{font-size:22px;font-weight:800;color:var(--night)}
        .login-head p{color:var(--muted);font-size:13px;margin-top:4px}
    </style>
</head>
<body>
<div class="login-wrap-split">
    <div class="login-hero-side" style="background:linear-gradient(135deg,#0B1F3A 0%,#16345E 55%,#1D4ED8 100%)">
        <div class="inst">République du Sénégal</div>
        <h1>CSAR —<br><span>Gestion du contrôle</span></h1>
        <p>Commissariat à la Sécurité Alimentaire et à la Résilience. Espace dédié aux inspecteurs pour le contrôle des stocks alimentaires sur le terrain.</p>
        <div class="points">
            <div><i class="fas fa-user-check"></i> Contrôle de votre périmètre de magasins affectés</div>
            <div><i class="fas fa-dolly"></i> Transmission des instructions aux magasiniers</div>
            <div><i class="fas fa-clipboard-check"></i> Vérification des mouvements et signalement d'anomalies</div>
            <div><i class="fas fa-camera"></i> Photos justificatives et traçabilité complète</div>
        </div>
    </div>

    <div class="login-form-side">
        <div class="login-card-wide">
            <div class="login-head">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                    <div style="width:46px;height:46px;background:var(--night);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:14px">CSAR</div>
                    <div><div style="font-weight:800;color:var(--night);font-size:15px">Connexion inspecteur</div><div style="font-size:12px;color:var(--muted)">Gestion du contrôle — stocks alimentaires</div></div>
                </div>
                <h2>Bienvenue</h2>
                <p>Connectez-vous avec votre compte inspecteur autorisé.</p>
            </div>

            <div class="login-body" style="padding:20px 28px 28px">
                @if(session('success'))
                    <div class="alert-strip alert-info" style="margin-bottom:14px"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
                @endif
                @if($errors->any())
                    <div class="err"><i class="fas fa-exclamation-circle"></i><div>{{ $errors->first() }}</div></div>
                @endif

                <form method="POST" action="{{ route('inspecteur.login.submit') }}">
                    @csrf
                    <div class="field">
                        <label>Adresse e-mail</label>
                        <div class="in">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="inspecteur@csar.sn" required autofocus>
                        </div>
                    </div>
                    <div class="field">
                        <label>Mot de passe</label>
                        <div class="in">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                            <button type="button" class="pw-eye" onclick="togglePwd()" aria-label="Afficher le mot de passe"><i class="fas fa-eye" id="togglePwdIcon"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange" style="width:100%;justify-content:center;padding:13px;font-size:14.5px">
                        <i class="fas fa-sign-in-alt"></i> Se connecter à la gestion du contrôle
                    </button>
                </form>

                <div style="text-align:center;font-size:12px;color:var(--muted);margin-top:18px">
                    <i class="fas fa-lock" style="font-size:10px"></i> Connexion sécurisée — accès strictement réservé aux comptes inspecteurs
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function togglePwd(){
    const p=document.getElementById('password'),i=document.getElementById('togglePwdIcon');
    const show=p.type==='password';
    p.type=show?'text':'password';
    i.classList.toggle('fa-eye',!show);i.classList.toggle('fa-eye-slash',show);
}
</script>
</body>
</html>
