<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion CTC - CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .ctc-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 3px solid white;
        }
        .ctc-icon i {
            font-size: 2.5rem;
        }
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .login-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin: 0;
        }
        .ctc-badge {
            background: rgba(255,255,255,0.25);
            border: 2px solid white;
            border-radius: 25px;
            padding: 6px 18px;
            display: inline-block;
            margin-top: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .login-body { padding: 35px 30px; }
        .form-group { margin-bottom: 22px; }
        .form-label { font-weight: 600; color: #2c3e50; margin-bottom: 8px; display: block; }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            border-radius: 12px;
            padding: 14px 30px;
            font-size: 1.05rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.35);
            color: white;
        }
        .alert { border-radius: 12px; border: none; padding: 14px 18px; margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="ctc-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <h1>Conseil Technique Communication</h1>
            <p>Gestion des publications & contenu public</p>
            <div class="ctc-badge">Espace CTC</div>
        </div>

        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    @if (str_contains($errors->first(), 'Trop de tentatives'))
                        <p class="mb-0 mt-2 small">
                            <a href="{{ route('ctc.login.reset-rate-limit') }}">Réinitialiser la limite et réessayer</a>
                        </p>
                    @endif
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('ctc.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" placeholder="••••••••" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
