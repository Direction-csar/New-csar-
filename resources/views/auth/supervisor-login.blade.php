<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Superviseur SIM - CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .login-container { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); width: 100%; max-width: 400px; overflow: hidden; }
        .login-header { background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 40px 30px; text-align: center; }
        .icon-circle { width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .icon-circle i { font-size: 2.5rem; color: #0078d4; }
        .badge-role { background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3); border-radius: 25px; padding: 6px 18px; display: inline-block; margin-top: 12px; font-size: 0.85rem; font-weight: 600; }
        .login-body { padding: 35px 30px; }
        .form-control { border: 2px solid #e9ecef; border-radius: 10px; padding: 13px 18px; font-size: 1rem; transition: all 0.3s; }
        .form-control:focus { border-color: #0078d4; box-shadow: 0 0 0 0.2rem rgba(0,120,212,0.2); }
        .form-label { font-weight: 600; color: #0078d4; margin-bottom: 6px; }
        .btn-login { background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); border: none; border-radius: 10px; padding: 14px; font-size: 1.05rem; font-weight: 600; color: white; width: 100%; transition: all 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,120,212,0.35); }
        .alert { border-radius: 10px; border-left: 4px solid #dc3545; }
        .login-footer { text-align: center; padding: 18px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 0.88rem; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <div class="icon-circle"><i class="fas fa-users-cog"></i></div>
        <h2 class="mb-1 fw-bold">Superviseur SIM</h2>
        <p class="mb-0 opacity-75">Suivi des collecteurs terrain</p>
        <div class="badge-role"><i class="fas fa-map-marker-alt me-1"></i>ESPACE SUPERVISEUR</div>
    </div>
    <div class="login-body">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                @foreach ($errors->all() as $error)
                    <div><i class="fas fa-exclamation-circle me-1"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('supervisor.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Adresse Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus placeholder="superviseur@csar.sn">
            </div>
            <div class="mb-4">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       required placeholder="••••••••">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label text-muted" for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="{{ url('/') }}" class="text-muted small"><i class="fas fa-arrow-left me-1"></i>Retour au site</a>
        </div>
    </div>
    <div class="login-footer">© {{ date('Y') }} CSAR — Tous droits réservés</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
