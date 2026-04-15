<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace DRH — CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #10b981 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, #065f46 0%, #10b981 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .drh-icon {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .drh-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .login-header h2 { font-size: 1.4rem; font-weight: 700; margin-bottom: 4px; }
        .login-header p  { font-size: 0.85rem; opacity: 0.85; margin: 0; }
        .login-body { padding: 30px; }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        .btn-drh {
            background: linear-gradient(135deg, #065f46, #10b981);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 600;
            font-size: 0.95rem;
            width: 100%;
            color: white;
            transition: opacity 0.2s;
        }
        .btn-drh:hover { opacity: 0.9; color: white; }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 2px solid #e5e7eb;
            border-right: none;
            background: #f9fafb;
            color: #6b7280;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; }
        .badge-secure {
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <div class="drh-icon">
            <img src="{{ asset('img/Tabaski logo.jpeg') }}" alt="Tabaski" onerror="this.style.display='none';this.parentNode.innerHTML='<i class=\'fas fa-users\' style=\'font-size:2.5rem;color:#065f46\'></i>'">
        </div>
        <h2>Espace DRH</h2>
        <p>Direction des Ressources Humaines — CSAR</p>
        <span class="badge-secure"><i class="fas fa-lock me-1"></i> Accès sécurisé</span>
    </div>

    <div class="login-body">

        @if(session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 py-2">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4 py-2">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('drh.login.submit') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary small">Adresse email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="votre@email.sn" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-drh">
                <i class="fas fa-sign-in-alt me-2"></i> Accéder à l'espace DRH
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ url('/avance-tabaski') }}" class="text-success text-decoration-none small">
                <i class="fas fa-external-link-alt me-1"></i> Voir le formulaire agent
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
