<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site en maintenance | CSAR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logos/' . rawurlencode('LOGO CSAR vectoriel-01.png')) }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            color: #fff;
        }

        /* Fond : image 1.jpg, overlay un peu sombre */
        .bg-maintenance {
            position: fixed;
            inset: 0;
            background-color: #2a2a2a;
            background-image: url("{{ asset('img/1.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -2;
        }
        /* Overlay léger pour lisibilité (un peu sombre) */
        .bg-maintenance::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            pointer-events: none;
            z-index: 0;
        }
        .bg-maintenance::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 50% at 50% 50%, rgba(0,0,0,0.25) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        @keyframes bgShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.97; }
        }
        .bg-maintenance {
            animation: bgShift 8s ease-in-out infinite;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 90vw;
        }

        /* Logo CSAR — plus grand + halo flou pour bien le détacher du fond */
        .logo-wrap {
            margin-bottom: 2.5rem;
            animation: fadeInDown 0.8s ease-out;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .logo-wrap img {
            max-height: 120px;
            width: auto;
            max-width: 340px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)) drop-shadow(0 8px 24px rgba(0,0,0,0.4)) drop-shadow(0 0 40px rgba(0,0,0,0.3));
            transition: transform 0.3s ease;
            display: block;
        }
        .logo-wrap img:hover {
            transform: scale(1.03);
        }
        .logo-fallback {
            display: none;
            flex-direction: column;
            align-items: center;
            padding: 1rem 1.5rem;
            background: rgba(30, 58, 138, 0.9);
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 1.75rem;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .logo-fallback small {
            font-size: 0.65rem;
            font-weight: 400;
            margin-top: 0.25rem;
            opacity: 0.95;
        }

        /* Titre principal */
        .title {
            font-size: clamp(1.75rem, 5vw, 2.75rem);
            font-weight: 700;
            letter-spacing: 0.02em;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
            margin-bottom: 0.75rem;
            animation: fadeIn 1s ease-out 0.2s both, pulse 2.5s ease-in-out 1.2s infinite;
        }

        /* Sous-titre */
        .subtitle {
            font-size: clamp(0.95rem, 2.5vw, 1.15rem);
            font-weight: 400;
            opacity: 0.95;
            text-shadow: 0 1px 10px rgba(0,0,0,0.25);
            margin-bottom: 2rem;
            animation: fadeIn 1s ease-out 0.4s both;
        }

        /* Barre de progression / chargement */
        .loader-wrap {
            width: 200px;
            margin: 0 auto 2rem;
            animation: fadeIn 1s ease-out 0.6s both;
        }
        .loader-bar {
            height: 4px;
            background: rgba(255,255,255,0.25);
            border-radius: 4px;
            overflow: hidden;
        }
        .loader-fill {
            height: 100%;
            width: 35%;
            background: #fff;
            border-radius: 4px;
            animation: loading 1.5s ease-in-out infinite;
            box-shadow: 0 0 12px rgba(255,255,255,0.6);
        }
        @keyframes loading {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(250%); }
            100% { transform: translateX(-100%); }
        }

        /* Points animés optionnels */
        .dots {
            display: inline-flex;
            gap: 6px;
            margin-top: 0.5rem;
        }
        .dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: dotPulse 1.2s ease-in-out infinite;
            box-shadow: 0 0 8px rgba(255,255,255,0.5);
        }
        .dots span:nth-child(2) { animation-delay: 0.2s; }
        .dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotPulse {
            0%, 100% { opacity: 0.4; transform: scale(0.9); }
            50% { opacity: 1; transform: scale(1.1); }
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 1.5rem;
            left: 0;
            right: 0;
            font-size: 0.9rem;
            opacity: 0.9;
            animation: fadeIn 1s ease-out 0.8s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.92; }
        }

        @media (max-width: 480px) {
            .logo-wrap img { max-height: 95px; }
            .logo-wrap { padding: 0.75rem 1rem; }
            .content { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="bg-maintenance" aria-hidden="true"></div>

    <main class="content">
        <div class="logo-wrap">
            <img src="{{ asset('images/logos/' . rawurlencode('LOGO CSAR vectoriel-01.png')) }}" alt="CSAR" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="logo-fallback">CSAR<br><small>Commissariat à la Sécurité Alimentaire et à la Résilience</small></div>
        </div>
        <h1 class="title">Site en Maintenance</h1>
        <p class="subtitle">Le site est actuellement en maintenance, merci de patienter !</p>
        <div class="loader-wrap">
            <div class="loader-bar">
                <div class="loader-fill"></div>
            </div>
            <div class="dots" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
        </div>
    </main>

    <footer class="footer">© CSAR {{ date('Y') }}</footer>
</body>
</html>
