<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie CSAR - Comité de Secours et d'Assistance aux Réfugiés</title>
    <meta name="description" content="Découvrez la galerie d'images du CSAR. Photos des missions, activités, infrastructures et équipes du Comité de Secours et d'Assistance aux Réfugiés.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
            --dark-bg: #2c3e50;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 300px;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }
        
        .gallery-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .gallery-category {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            height: 100%;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stats-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }
        
        .filter-btn {
            border-radius: 25px;
            padding: 8px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .footer {
            background: var(--dark-bg);
            color: white;
            padding: 40px 0;
            margin-top: 80px;
        }
        
        .real-time-badge {
            background: linear-gradient(45deg, var(--success-color), #2ecc71);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Modal pour l'image en grand */
        .modal-image {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="display-4 fw-bold mb-4">Galerie CSAR</h1>
                <p class="lead mb-4">Découvrez nos missions, activités et infrastructures en images</p>
                <div class="mt-4">
                    <span class="real-time-badge">
                        <i class="fas fa-sync-alt pulse"></i>
                        Galerie mise à jour en temps réel
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistiques -->
    <section class="py-5" style="background: var(--light-bg);">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number" id="total-images">{{ $stats['total'] }}</div>
                        <div class="stats-label">📸 Images totales</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number" id="active-images">{{ $stats['actif'] }}</div>
                        <div class="stats-label">✅ Images actives</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">{{ is_object($stats['par_categorie'] ?? null) && method_exists($stats['par_categorie'], 'count') ? $stats['par_categorie']->count() : count($stats['par_categorie'] ?? []) }}</div>
                        <div class="stats-label">🏷️ Catégories</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="stats-number">{{ number_format($stats['taille_totale'], 1) }} MB</div>
                        <div class="stats-label">💾 Taille totale</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    <section class="py-4">
        <div class="container">
            <div class="text-center mb-4">
                <h3>Filtrer par catégorie</h3>
                <div class="d-flex flex-wrap justify-content-center">
                    <button class="btn btn-outline-primary filter-btn active" data-category="all">
                        Toutes <span class="badge bg-primary ms-1">{{ $stats['total'] }}</span>
                    </button>
                    @foreach($stats['par_categorie'] as $category => $count)
                        <button class="btn btn-outline-primary filter-btn" data-category="{{ $category }}">
                            {{ ucfirst(str_replace('_', ' ', $category)) }} <span class="badge bg-primary ms-1">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Galerie -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title">Notre Galerie</h2>
            <p class="section-subtitle">Découvrez nos activités en images</p>
            
            <div class="row" id="gallery-container">
                @forelse ($images as $image)
                    <div class="col-lg-4 col-md-6 mb-4 gallery-item-wrapper" data-category="{{ $image->categorie }}">
                        <div class="gallery-item" onclick="openModal('{{ asset('images/galerie/' . $image->fichier) }}', '{{ $image->titre }}', '{{ $image->description }}')">
                            <img src="{{ asset('images/galerie/' . $image->fichier) }}" 
                                 alt="{{ $image->titre }}"
                                 onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                            <div class="gallery-overlay">
                                <div class="gallery-title">{{ $image->titre ?: 'Sans titre' }}</div>
                                <div class="gallery-category">{{ ucfirst(str_replace('_', ' ', $image->categorie)) }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune image disponible</h5>
                            <p class="text-muted">La galerie sera bientôt mise à jour avec de nouvelles images.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5>CSAR - Comité de Secours et d'Assistance aux Réfugiés</h5>
                    <p>Découvrez nos activités et missions à travers notre galerie d'images.</p>
                </div>
                <div class="col-lg-6 text-end">
                    <p><strong>Dernière mise à jour :</strong> <span id="last-update">{{ now()->format('d/m/Y H:i:s') }}</span></p>
                    <p><small>Galerie mise à jour en temps réel</small></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal pour afficher l'image en grand -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Titre de l'image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="modal-image">
                    <p id="modalDescription" class="mt-3 text-muted"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a id="downloadLink" href="" download class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Télécharger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Filtrage par catégorie
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                const category = this.dataset.category;
                const items = document.querySelectorAll('.gallery-item-wrapper');
                
                items.forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Fonction pour ouvrir le modal avec l'image
        function openModal(imageSrc, title, description) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('downloadLink').href = imageSrc;
            
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Actualisation automatique des statistiques
        function updateStats() {
            fetch('/galerie/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('total-images').textContent = data.total;
                        document.getElementById('active-images').textContent = data.actif;
                        document.getElementById('last-update').textContent = new Date(data.derniere_mise_a_jour).toLocaleString('fr-FR');
                    }
                })
                .catch(error => {
                    console.log('Erreur lors de la mise à jour des statistiques:', error);
                });
        }

        // Actualisation toutes les 30 secondes
        setInterval(updateStats, 30000);

        // Animation des images au scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        // Observer toutes les images de la galerie
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(30px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(item);
        });
    </script>
</body>
</html>



