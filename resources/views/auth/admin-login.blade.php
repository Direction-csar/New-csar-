<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            position: relative;
        }
        .login-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        .admin-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
            border: 3px solid #ffd43b;
        }
        .admin-icon img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .admin-icon i {
            font-size: 2.5rem;
            color: #2c3e50;
        }
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            position: relative;
            z-index: 1;
        }
        .login-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        .admin-badge {
            background: rgba(255,255,255,0.9);
            border: 2px solid #28a745;
            border-radius: 25px;
            padding: 8px 20px;
            display: inline-block;
            margin-top: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
            color: #28a745;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
            background: white;
        }
        .form-control::placeholder {
            color: #6c757d;
        }
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .form-check-input {
            margin-right: 10px;
            transform: scale(1.2);
        }
        .form-check-label {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .forgot-password {
            color: #2c3e50;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .forgot-password:hover {
            color: #34495e;
            text-decoration: underline;
        }
        .btn-login {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover::before {
            left: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(44, 62, 80, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .security-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid #2c3e50;
        }
        .security-info h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .security-info p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
            line-height: 1.5;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .back-link a:hover {
            color: #2c3e50;
        }
        .floating-icons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .floating-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        .floating-icon:hover {
            transform: scale(1.1);
        }
        .floating-icon.dark {
            background: #2c3e50;
            color: white;
        }
        .floating-icon.purple {
            background: #9b59b6;
            color: white;
        }
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .login-footer {
            background: white;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .login-footer p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                border-radius: 15px;
            }
            .login-header {
                padding: 30px 20px;
            }
            .login-body {
                padding: 30px 20px;
            }
            .admin-icon {
                width: 60px;
                height: 60px;
            }
            .admin-icon i {
                font-size: 2rem;
            }
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-icons">
        <div class="floating-icon dark">
            <i class="fas fa-cog"></i>
        </div>
        <div class="floating-icon purple">
            <i class="fas fa-bolt"></i>
        </div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="admin-icon">
                <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display: none; align-items: center; justify-content: center; width: 100%; height: 100%;">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
            <h1>Connexion Admin</h1>
            <p>Accédez à votre espace d'administration</p>
            <div class="admin-badge">
                ZONE SÉCURISÉE
            </div>
        </div>

        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit', [], false) }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', 'admin@csar.sn') }}" 
                           required 
                           autofocus>
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           value="admin123"
                           required>
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Se souvenir de moi
                        </label>
                    </div>
                    <a href="#" class="forgot-password">
                        Mot de passe oublié ?
                    </a>
                </div>

                <button type="submit" class="btn btn-login">
                    Se connecter
                </button>
            </form>

        </div>

        <div class="login-footer">
            <p>© 2025 CSAR - Tous droits réservés</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation du bouton de connexion
        document.querySelector('.btn-login').addEventListener('click', function(e) {
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion...';
        });

        // Animation des icônes flottantes
        document.querySelectorAll('.floating-icon').forEach((icon, index) => {
            setTimeout(() => {
                icon.style.animation = 'float 3s ease-in-out infinite';
            }, index * 500);
        });

        // Ajouter l'animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>