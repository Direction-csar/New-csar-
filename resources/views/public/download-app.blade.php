<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger SIM Collecte — CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 50%, #388E3C 100%); min-height: 100vh; display: flex; align-items: center; }
        .card-download { border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 480px; margin: auto; }
        .logo-box { width: 100px; height: 100px; background: #fff; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; padding: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .badge-version { background: #E8F5E9; color: #1B5E20; font-size: 12px; border-radius: 20px; padding: 4px 12px; }
        .btn-download { background: linear-gradient(135deg, #1B5E20, #2E7D32); border: none; border-radius: 12px; padding: 14px 32px; font-size: 16px; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s; }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(27,94,32,0.4); }
        .feature-item { background: #F1F8E9; border-radius: 10px; padding: 10px 14px; margin-bottom: 8px; font-size: 14px; }
        .feature-item i { color: #2E7D32; width: 20px; }
        .info-box { background: #FFF8E1; border-left: 4px solid #FFC107; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #795548; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card card-download p-5">
            <div class="text-center">
                <div class="logo-box">
                    <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="width:80px; height:80px; object-fit:contain;">
                </div>
                <h2 class="fw-bold text-success mb-1">SIM Collecte</h2>
                <p class="text-muted mb-1" style="font-size:13px;">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
                <span class="badge-version">Version 1.0.0</span>
            </div>

            <hr class="my-4">

            <h6 class="fw-semibold text-muted mb-3"><i class="fas fa-star text-warning me-2"></i>Fonctionnalités</h6>
            <div class="feature-item"><i class="fas fa-store me-2"></i>Collecte hebdomadaire sur les marchés</div>
            <div class="feature-item"><i class="fas fa-boxes me-2"></i>50 produits groupés par catégorie alimentaire</div>
            <div class="feature-item"><i class="fas fa-tags me-2"></i>Prix producteur, détail et ½ gros</div>
            <div class="feature-item"><i class="fas fa-map-marker-alt me-2"></i>Géolocalisation automatique</div>
            <div class="feature-item"><i class="fas fa-wifi me-2"></i>Synchronisation hors-ligne</div>

            <div class="text-center mt-4">
                <a href="{{ route('app.download.apk') }}" class="btn btn-download btn-success text-white w-100">
                    <i class="fab fa-android me-2"></i>Télécharger l'APK Android
                    <small class="d-block" style="font-size:11px; opacity:0.8; font-weight:400;">Environ 49 MB — Android 5.0+</small>
                </a>
            </div>

            <div class="info-box mt-3">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Installation :</strong> Activez <em>« Sources inconnues »</em> dans vos paramètres Android avant d'installer l'APK.
            </div>

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="text-muted text-decoration-none" style="font-size:12px;">
                    <i class="fas fa-arrow-left me-1"></i>Retour au site CSAR
                </a>
            </div>
        </div>
    </div>
</body>
</html>
