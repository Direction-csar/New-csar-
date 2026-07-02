<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion DSAR - CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dsar-primary: #1e40af;
            --dsar-secondary: #3b82f6;
            --dsar-accent: #f59e0b;
        }
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
        }
        .logo-section {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-section img {
            height: 70px;
            width: auto;
            margin-bottom: 0.5rem;
        }
        .dsar-badge {
            background: linear-gradient(135deg, var(--dsar-accent), #d97706);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: inline-block;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        .login-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }
        .form-control-lg {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control-lg:focus {
            border-color: var(--dsar-secondary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .input-group-text {
            background: #f1f5f9;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #64748b;
        }
        .btn-dsar {
            background: linear-gradient(135deg, var(--dsar-primary), var(--dsar-secondary));
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.3);
        }
        .btn-dsar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        .divider span {
            padding: 0 1rem;
            color: #94a3b8;
            font-size: 0.8rem;
        }
        .back-link {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--dsar-primary);
        }
        .workflow-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px dashed #cbd5e1;
        }
        .step {
            text-align: center;
            flex: 1;
        }
        .step-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e0e7ff;
            color: var(--dsar-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.4rem;
            font-size: 0.8rem;
        }
        .step-label {
            font-size: 0.65rem;
            color: #64748b;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo & Title -->
        <div class="logo-section">
            <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR Logo" onerror="this.style.display='none'">
            <div class="dsar-badge mb-2">Interface DSAR</div>
            <h1 class="login-title">Connexion DSAR</h1>
            <p class="login-subtitle">Direction de la Sécurité Alimentaire et de la Résilience</p>
        </div>

        <!-- Messages -->
        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('dsar.login.submit') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold text-dark">Adresse email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="votre@email.com" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                <div class="text-danger small mt-1"><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-dark">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                </div>
                @error('password')
                <div class="text-danger small mt-1"><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Se souvenir de moi</label>
                </div>
                <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color: var(--dsar-primary);">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-dsar w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
            </button>
        </form>

        <div class="divider">
            <span>Workflow demandes</span>
        </div>

        <!-- Workflow steps visual -->
        <div class="workflow-steps">
            <div class="step">
                <div class="step-icon"><i class="fas fa-paper-plane"></i></div>
                <div class="step-label">SOUMISE</div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-signature"></i></div>
                <div class="step-label">SIGNEE</div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-file-pdf"></i></div>
                <div class="step-label">SCANNEE</div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-stamp"></i></div>
                <div class="step-label">VALIDEE</div>
            </div>
        </div>

        <!-- Back link -->
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="back-link">
                <i class="fas fa-arrow-left me-1"></i>Retour au site public
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
