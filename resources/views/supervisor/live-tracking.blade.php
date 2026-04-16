<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi en Temps Réel | CSAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --csar-green: #00a86b;
            --csar-blue: #0078d4;
        }
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        .nav-supervisor {
            background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-supervisor .navbar-brand { color: white !important; font-weight: 700; }
        #map {
            height: calc(100vh - 120px);
            width: 100%;
        }
        .collector-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
        }
        .collector-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .collector-card.active {
            border-left: 4px solid #00a86b;
        }
        .collector-card.collecting {
            border-left: 4px solid #0078d4;
        }
        .collector-card.paused {
            border-left: 4px solid #f59e0b;
        }
        .collector-card.offline {
            border-left: 4px solid #6b7280;
            opacity: 0.6;
        }
        .sidebar {
            height: calc(100vh - 120px);
            overflow-y: auto;
            background: #f8fafc;
            padding: 15px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-collecting { background: #dbeafe; color: #1e40af; }
        .status-paused { background: #fef3c7; color: #92400e; }
        .status-offline { background: #e5e7eb; color: #374151; }
        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-supervisor">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('supervisor.dashboard') }}">
                <i class="fas fa-map-marked-alt me-2"></i>Suivi en Temps Réel
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-tie me-1"></i>{{ auth('supervisor')->user()->name }}
                </span>
                <a href="{{ route('supervisor.dashboard') }}" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
                <form method="POST" action="{{ route('supervisor.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-3 sidebar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Collecteurs Actifs</h6>
                    <span class="badge bg-primary" id="activeCount">0</span>
                </div>
                <div id="collectorsList"></div>
            </div>
            <div class="col-md-9">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg refresh-btn" onclick="loadCollectors()">
        <i class="fas fa-sync-alt"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = {};
        let selectedCollector = null;

        // Initialiser la carte
        function initMap() {
            map = L.map('map').setView([14.6928, -17.4467], 12); // Dakar par défaut
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
        }

        // Charger les collecteurs
        async function loadCollectors() {
            try {
                const response = await fetch('/api/mobile/collectors/locations');
                const data = await response.json();
                
                if (data.success) {
                    updateCollectorsList(data.data);
                    updateMap(data.data);
                    document.getElementById('activeCount').textContent = data.data.length;
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Mettre à jour la liste
        function updateCollectorsList(collectors) {
            const list = document.getElementById('collectorsList');
            list.innerHTML = '';
            
            collectors.forEach(collector => {
                const card = document.createElement('div');
                card.className = `collector-card ${collector.status}`;
                card.onclick = () => focusCollector(collector);
                
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>${collector.collector_name}</strong>
                        <span class="status-badge status-${collector.status}">
                            ${getStatusLabel(collector.status)}
                        </span>
                    </div>
                    <div class="small text-muted">
                        <div><i class="fas fa-map-marker-alt me-1"></i>${collector.current_market || 'Non spécifié'}</div>
                        <div><i class="fas fa-database me-1"></i>${collector.collections_today} collectes</div>
                        <div><i class="fas fa-clock me-1"></i>${collector.last_activity}</div>
                    </div>
                `;
                
                list.appendChild(card);
            });
        }

        // Mettre à jour la carte
        function updateMap(collectors) {
            // Supprimer les anciens marqueurs
            Object.values(markers).forEach(marker => map.removeLayer(marker));
            markers = {};
            
            // Ajouter les nouveaux marqueurs
            collectors.forEach(collector => {
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background: ${getStatusColor(collector.status)}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        <i class="fas fa-user" style="font-size: 14px;"></i>
                    </div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                const marker = L.marker([collector.latitude, collector.longitude], { icon })
                    .addTo(map)
                    .bindPopup(`
                        <strong>${collector.collector_name}</strong><br>
                        Statut: ${getStatusLabel(collector.status)}<br>
                        Marché: ${collector.current_market || 'N/A'}<br>
                        Collectes: ${collector.collections_today}<br>
                        Dernière activité: ${collector.last_activity}
                    `);
                
                markers[collector.collector_id] = marker;
            });
            
            // Ajuster la vue pour afficher tous les marqueurs
            if (collectors.length > 0) {
                const bounds = L.latLngBounds(collectors.map(c => [c.latitude, c.longitude]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        // Centrer sur un collecteur
        function focusCollector(collector) {
            map.setView([collector.latitude, collector.longitude], 16);
            markers[collector.collector_id].openPopup();
        }

        // Obtenir la couleur du statut
        function getStatusColor(status) {
            const colors = {
                'active': '#00a86b',
                'collecting': '#0078d4',
                'paused': '#f59e0b',
                'offline': '#6b7280'
            };
            return colors[status] || '#6b7280';
        }

        // Obtenir le label du statut
        function getStatusLabel(status) {
            const labels = {
                'active': 'Actif',
                'collecting': 'En collecte',
                'paused': 'En pause',
                'offline': 'Hors ligne'
            };
            return labels[status] || status;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            loadCollectors();
            
            // Actualiser toutes les 10 secondes
            setInterval(loadCollectors, 10000);
        });
    </script>
</body>
</html>
