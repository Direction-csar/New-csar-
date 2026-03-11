<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface désactivée - CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f1f5f9; }
        .card { border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .card-title { color: #1e293b; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 p-md-5 text-center">
                    <div class="mb-3">
                        <span style="font-size: 3rem;">🔒</span>
                    </div>
                    <h1 class="card-title h4 mb-2">Interface temporairement désactivée</h1>
                    <p class="text-muted mb-4">Cette interface n'est pas disponible pour le moment. Seules les interfaces Admin et DG sont actives.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
