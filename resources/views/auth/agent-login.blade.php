<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Agent - CSAR</title>
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
        
        .login-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: color 0.3s ease;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9fafb;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #059669;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .form-control:focus + i {
            color: #059669;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #059669;
            cursor: pointer;
        }
        
        .remember-me label {
            color: #6b7280;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
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
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(5, 150, 105, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert i {
            font-size: 18px;
        }
        
        .login-footer {
            text-align: center;
            padding: 30px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        .login-footer p {
            color: #6b7280;
            font-size: 14px;
        }
        
        /* Agent specific styling */
        .agent-icon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 12px;
        }
        
        .agent-icon i {
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }
            
            .login-body {
                flex: 1;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-header p {
                font-size: 14px;
            }
        }
        
        /* Loading state */
        .btn-login.loading {
            color: transparent;
        }
        
        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.8s linear infinite;
        }
        
        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Error shake animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR Logo">
            </div>
            <h1>Connexion Agent</h1>
            <p>Accédez à votre espace de travail</p>
            <div class="agent-icon">
                <i class="fas fa-user-tie"></i>
                <span>ESPACE AGENT</span>
                </div>
            </div>
            
        <div class="login-body">
            <form method="POST" action="{{ route('agent.login.submit', [], false) }}" id="loginForm">
                @csrf
                
                @if (session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <div class="input-wrapper">
                        <input type="email" 
                               class="form-control @error('email') shake @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', 'agent1@csar.sn') }}" 
                               placeholder="agent@csar.sn"
                               required 
                               autofocus>
                        <i class="fas fa-envelope"></i>
                    </div>
                    @error('email')
                        <span class="text-danger" style="font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" 
                               class="form-control @error('password') shake @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••"
                               required>
                        <i class="fas fa-lock"></i>
                    </div>
                    @error('password')
                        <span class="text-danger" style="font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="forgot-link">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span>Se connecter</span>
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            <p>© {{ date('Y') }} CSAR – Tous droits réservés</p>
        </div>
    </div>
    
    <script>
        // Add loading state to button on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-violet-100 flex items-center justify-center login-bg">
    <div class="w-full max-w-xs">
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-4 border border-purple-100/50 login-card">
            <div class="text-center mb-4">
                <img src="/images/logo-csar.png" alt="CSAR Logo" class="h-8 mx-auto mb-2">
                <h1 class="text-lg font-bold text-gray-800 mb-1">Connexion Agent</h1>
                <p class="text-gray-500 text-xs">Accédez à votre espace personnel</p>
            </div>
            <form method="POST" action="{{ route('agent.login.submit', [], false) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                    <input id="email" type="email" name="email" class="w-full rounded-lg border border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-100 px-3 py-2 text-xs" required autofocus>
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-600 mb-1">Mot de passe</label>
                    <input id="password" type="password" name="password" class="w-full rounded-lg border border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-100 px-3 py-2 text-xs" required>
                </div>
                
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold py-2 rounded-lg shadow transition-all">Se connecter</button>
            </form>
        </div>
    </div>
</div>
    <div class="w-full max-w-xs">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-xl p-2 border border-white/20 login-card">
            <!-- Logo and Title Section -->
            <div class="text-center mb-6">
                <div class="flex justify-center mb-3">
                    <img src="{{ asset('images/logos/csar PNG logo_Plan de travail 1.png') }}" 
                         alt="CSAR Logo" 
                         class="h-2 w-auto object-contain login-logo">
                </div>
                <h1 class="text-base font-bold text-gray-900 mb-1 login-title">Connexion Agent</h1>
                <p class="text-gray-600 text-xs login-subtitle">Accédez à votre espace personnel</p>
            </div>
            
            <!-- Login Form -->
            <form action="{{ route('agent.login.submit', [], false) }}" method="POST" class="space-y-4">
                @csrf
                
                @if (session('error'))
                    <div class="bg-red-50/80 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-xs flex items-center error-message animate-bounce">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-50/80 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-xs flex items-center">
                        <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                
                <!-- Email Field -->
                <div class="space-y-1">
                    <label for="email" class="block text-xs font-medium text-gray-700">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                            <svg class="h-2 w-2 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" required 
                               class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 login-input bg-white/50 backdrop-blur-sm"
                               placeholder="agent@csar.sn"
                               value="{{ old('email', 'agent@csar.sn') }}">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 flex items-center">
                            <svg class="w-1.5 h-1.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                
                <!-- Password Field -->
                <div class="space-y-1">
                    <label for="password" class="block text-xs font-medium text-gray-700">Mot de passe</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                            <svg class="h-2 w-2 text-gray-400 group-focus-within:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" required 
                               class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 login-input bg-white/50 backdrop-blur-sm"
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 flex items-center">
                            <svg class="w-1.5 h-1.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Forgot Password Link -->
                <div class="text-right">
                    <a href="{{ route('password.request') }}" class="text-xs text-purple-600 hover:text-purple-500 font-medium transition duration-200 hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg login-button">
                    <span class="flex items-center justify-center">
                        <svg class="w-1.5 h-1.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Se connecter
                    </span>
                </button>
            </form>
            
            <!-- Test Credentials -->
        </div>
    </div>
</div>
{{-- Section identifiants de test masquée --}}
@endsection 