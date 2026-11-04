<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - CSAR</title>

    <!-- Meta tags pour SEO -->
    <meta name="description" content="Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR) - Plateforme officielle pour la gestion alimentaire au Sénégal.">
    <meta name="keywords" content="CSAR, sécurité alimentaire, résilience, Sénégal, alimentation, crises climatiques, gestion alimentaire">
    <meta name="author" content="CSAR Sénégal">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://csar.sn/">
    <meta property="og:title" content="CSAR - Sécurité Alimentaire et Résilience au Sénégal">
    <meta property="og:description" content="Le Commissariat à la Sécurité Alimentaire et à la Résilience œuvre pour garantir l'accès à une alimentation suffisante et nutritive pour tous les Sénégalais.">
    <meta property="og:image" content="https://csar.sn/images/csar-og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://csar.sn/">
    <meta property="twitter:title" content="CSAR - Sécurité Alimentaire et Résilience au Sénégal">
    <meta property="twitter:description" content="Le Commissariat à la Sécurité Alimentaire et à la Résilience œuvre pour garantir l'accès à une alimentation suffisante et nutritive pour tous les Sénégalais.">
    <meta property="twitter:image" content="https://csar.sn/images/csar-twitter-image.jpg">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}">

    <!-- Styles CSS optimisés -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Schema.org Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)",
      "url": "https://csar.sn",
      "logo": "https://csar.sn/images/logos/LOGO CSAR vectoriel-01.png",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+221-33-123-4567",
        "contactType": "Customer Service"
      },
      "sameAs": [
        "https://facebook.com/csar.sn",
        "https://twitter.com/csar_sn",
        "https://linkedin.com/company/csar-sn"
      ]
    }
    </script>
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="{{ route('home', ['locale' => 'fr']) }}">
                <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="Logo CSAR" class="logo">
            </a>
            <button class="menu-toggle" aria-label="Toggle navigation">☰</button>
            <nav class="nav">
                <a href="{{ route('home', ['locale' => 'fr']) }}">Accueil</a>
                <a href="{{ route('about') }}">À propos</a>
                <a href="{{ route('institution') }}">Institution</a>
                <a href="{{ route('news') }}">Actualités</a>
                <a href="{{ route('sim.index') }}">SIM</a>
                <a href="{{ route('reports') }}">Rapports</a>
                <a href="{{ route('contact.simple') }}">Contact</a>
                <input type="text" id="search-input" placeholder="Rechercher..." class="form-control">
                <div id="search-results"></div>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" style="background-image: url('{{ $backgroundImage ?? asset('img/1.jpg') }}');">
            <div class="container">
                <!-- Slider dynamique si disponible -->
                @if(isset($backgroundSlider) && count($backgroundSlider) > 0)
                    @foreach($backgroundSlider as $slide)
                        <div class="background-slide" style="background-image: url('{{ $slide['image'] }}');"></div>
                    @endforeach
                @endif
                <h1>Le Commissariat à la Sécurité Alimentaire et à la Résilience</h1>
                <p>Œuvre pour garantir l'accès à une alimentation suffisante et nutritive pour tous les Sénégalais, tout en renforçant leur capacité à faire face aux crises et aux défis climatiques.</p>
                <a href="{{ route('demande.create') }}" class="cta-button">Effectuer une demande</a>
                <a href="{{ route('about') }}" class="cta-button btn-secondary">Découvrir le CSAR</a>
            </div>
        </section>

        <section class="section" id="actualites">
            <div class="container">
                <h2>Actualités & Informations</h2>
                <div class="cards">
                    @if(isset($latestNews) && $latestNews->count() > 0)
                        @foreach($latestNews as $news)
                            <div class="card news-card">
                                @if($news->image)
                                    <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}" loading="lazy">
                                @endif
                                <h3>{{ $news->title }}</h3>
                                <p>{{ $news->excerpt }}</p>
                                <span class="date">{{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}</span>
                                <a href="{{ route('news.show', $news->id) }}" class="btn btn-secondary mt-3">Lire plus</a>
                            </div>
                        @endforeach
                    @else
                        <!-- Message professionnel si aucune actualité -->
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Aucune actualité disponible pour le moment</h4>
                                <p class="text-muted">Les actualités du CSAR seront publiées ici dès qu'elles seront disponibles.</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('news') }}" class="cta-button">Voir toutes les actualités</a>
                </div>
            </div>
        </section>

        <section class="section bg-light-gray" id="galerie">
            <div class="container">
                <h2>Galerie de missions</h2>
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <img src="{{ asset('storage/gallery/distributions.jpg') }}" alt="Distributions alimentaires" loading="lazy">
                    </div>
                    <div class="gallery-item">
                        <img src="{{ asset('storage/gallery/magasins.jpg') }}" alt="Magasins de stockage CSAR" loading="lazy">
                    </div>
                    <div class="gallery-item">
                        <img src="{{ asset('storage/gallery/operations.jpg') }}" alt="Opérations terrain" loading="lazy">
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('gallery.index') }}" class="cta-button">Découvrir nos actions</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div>
                <h4>CSAR</h4>
                <p>© 2025 CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience. Tous droits réservés.</p>
            </div>
            <div>
                <h4>Liens rapides</h4>
                <a href="{{ route('home', ['locale' => 'fr']) }}">Accueil</a>
                <a href="{{ route('about') }}">À propos</a>
                <a href="{{ route('institution') }}">Institution</a>
                <a href="{{ route('news') }}">Actualités</a>
                <a href="{{ route('sim.index') }}">SIM</a>
                <a href="{{ route('reports') }}">Rapports</a>
            </div>
            <div>
                <h4>Partenaires institutionnels</h4>
                <a href="https://www.famille.gouv.sn/" target="_blank" rel="noopener noreferrer">Ministère de la Famille et des Solidarités</a>
                <a href="https://www.primature.sn/" target="_blank" rel="noopener noreferrer">Primature</a>
                <a href="https://www.presidence.sn/" target="_blank" rel="noopener noreferrer">Présidence de la République</a>
                <a href="#">Nos partenaires</a>
            </div>
            <div>
                <h4>Contact</h4>
                <a href="{{ route('demande.create') }}">Effectuer une demande</a>
                <a href="{{ route('contact.simple') }}">Nous contacter</a>
                <p>contact@csar.sn</p>
                <p>+221 77 645 92 42</p>
                <p>Dakar, Sénégal</p>
            </div>
        </div>
    </footer>

    <!-- Scripts JS optimisés -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>

