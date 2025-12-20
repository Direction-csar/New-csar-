<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion Entrepôt - CSAR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, -20px) rotate(180deg); }
        }
        
        .logo {
            position: relative;
            z-index: 2;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-icon i {
            font-size: 24px;
            color: white;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .space-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 25px;
            margin-top: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .space-badge i {
            font-size: 14px;
        }
        
        .space-badge span {
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-container {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 16px;
            z-index: 2;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #FAFAFA;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #059669;
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .form-input::placeholder {
            color: #9CA3AF;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox {
            width: 18px;
            height: 18px;
            accent-color: #059669;
            cursor: pointer;
        }
        
        .checkbox-label {
            font-size: 14px;
            color: #6B7280;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #059669;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-link:hover {
            color: #047857;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(5, 150, 105, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .footer {
            text-align: center;
            padding: 20px 30px;
            color: #6B7280;
            font-size: 14px;
            border-top: 1px solid #F3F4F6;
        }
        
        .error-message {
            color: #DC2626;
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .success-message {
            color: #059669;
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .test-credentials {
            background: #F3F4F6;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .test-credentials h4 {
            color: #374151;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .test-credentials p {
            color: #6B7280;
            margin: 5px 0;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .form-container {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="logo-text">CSAR</div>
            </div>
            <h1>Connexion Entrepôt</h1>
            <p>Accédez à votre espace de gestion des stocks</p>
            <div class="space-badge">
                <i class="fas fa-boxes"></i>
                <span>ESPACE ENTREPÔT</span>
            </div>
        </div>
        
        <div class="form-container">
            <form method="POST" action="{{ request()->is('entrepot/*') ? '/entrepot/login' : '/responsable/login' }}" id="loginForm">
                @csrf
                
                @if ($errors->any())
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                
                @if (session('success'))
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               placeholder="entrepot@csar.sn"
                               value="{{ old('email', 'entrepot@csar.sn') }}"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="••••••••"
                               required>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember" class="checkbox">
                        <label for="remember" class="checkbox-label">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">
                    <div class="spinner" id="spinner" style="display: none;"></div>
                    <i class="fas fa-sign-in-alt" id="loginIcon"></i>
                    <span id="loginText">Se connecter</span>
                </button>
            </form>
            
        </div>
        
        <div class="footer">
            © 2025 CSAR - Tous droits réservés
        </div>
    </div>

    <script>
        // Rafraîchir le token CSRF si nécessaire
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier si le token CSRF existe
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                // Créer un meta tag pour le token CSRF
                const meta = document.createElement('meta');
                meta.name = 'csrf-token';
                meta.content = document.querySelector('input[name="_token"]')?.value || '';
                document.head.appendChild(meta);
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const spinner = document.getElementById('spinner');
            const icon = document.getElementById('loginIcon');
            const text = document.getElementById('loginText');
            
            // Vérifier que le token CSRF est présent
            const tokenInput = document.querySelector('input[name="_token"]');
            if (!tokenInput || !tokenInput.value) {
                e.preventDefault();
                alert('Erreur: Token CSRF manquant. Veuillez rafraîchir la page.');
                location.reload();
                return;
            }
            
            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display = 'none';
            text.textContent = 'Connexion...';
        });
    </script>
</body>
</html>