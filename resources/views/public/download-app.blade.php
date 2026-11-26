<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications mobiles — CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 50%, #388E3C 100%); min-height: 100vh; }
        .app-card { border-radius: 18px; box-shadow: 0 12px 40px rgba(0,0,0,0.22); transition: transform .2s; }
        .app-card:hover { transform: translateY(-4px); }
        .app-card.unavailable { opacity: .75; }
        .logo-box { width: 72px; height: 72px; background: #E8F5E9; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .badge-version { background: #E8F5E9; color: #1B5E20; font-size: 12px; border-radius: 20px; padding: 4px 12px; }
        .badge-ok { background: #C8E6C9; color: #1B5E20; font-size: 12px; border-radius: 20px; padding: 4px 12px; }
        .badge-ko { background: #FFF3E0; color: #E65100; font-size: 12px; border-radius: 20px; padding: 4px 12px; }
        .btn-download { background: linear-gradient(135deg, #1B5E20, #2E7D32); border: none; border-radius: 12px; padding: 12px 20px; font-weight: 600; letter-spacing: 0.4px; transition: all 0.3s; }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(27,94,32,0.4); }
        .info-box { background: rgba(255,248,225,0.95); border-left: 4px solid #FFC107; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #795548; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center text-white mb-5">
            <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="width:84px; height:84px; object-fit:contain;" class="mb-3">
            <h1 class="fw-bold mb-1">Applications mobiles CSAR</h1>
            <p class="mb-0" style="opacity:.85;">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($applications as $app)
                <div class="col-md-6 col-lg-4">
                    <div class="card app-card h-100 p-4 {{ $app['available'] ? '' : 'unavailable' }}">
                        <div class="text-center">
                            <div class="logo-box">
                                <i class="fab fa-android fa-2x" style="color:#2E7D32;"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-1">{{ $app['name'] }}</h4>
                            <p class="text-muted mb-2" style="font-size:14px; min-height:42px;">{{ $app['role'] }}</p>
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <span class="badge-version">v{{ $app['version'] }}</span>
                                @if ($app['available'])
                                    <span class="badge-ok"><i class="fas fa-check-circle me-1"></i>Disponible</span>
                                @else
                                    <span class="badge-ko"><i class="fas fa-clock me-1"></i>Bientôt disponible</span>
                                @endif
                            </div>
                        </div>

                        @if ($app['available'])
                            <a href="{{ route($app['route']) }}" class="btn btn-download btn-success text-white w-100 mt-auto">
                                <i class="fas fa-download me-2"></i>Télécharger l'APK
                                <small class="d-block" style="font-size:11px; opacity:.8; font-weight:400;">{{ $app['size'] }} — Android 5.0+</small>
                            </a>
                        @else
                            <button class="btn btn-secondary w-100 mt-auto" disabled>
                                <i class="fas fa-download me-2"></i>Téléchargement indisponible
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="info-box">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Installation :</strong> activez <em>« Sources inconnues »</em> dans les paramètres Android avant d'installer l'APK.
                    Si une version antérieure est déjà installée, désinstallez-la d'abord.
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="text-white text-decoration-none" style="font-size:13px; opacity:.85;">
                <i class="fas fa-arrow-left me-1"></i>Retour au site CSAR
            </a>
        </div>
    </div>
</body>
</html>
