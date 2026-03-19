@extends('layouts.public')

@section('title', __('pages.map'))

@section('content')
<!-- Hero Section -->
<section class="hero fade-in" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); min-height: 50vh; display: flex; align-items: center; justify-content: center; padding: 80px 0; position: relative; overflow: hidden;">
    <!-- Motifs décoratifs animés -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div class="floating-circle" style="position: absolute; top: 10%; left: 10%; width: 80px; height: 80px; background: #fff; border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
        <div class="floating-circle" style="position: absolute; top: 20%; right: 15%; width: 60px; height: 60px; background: #fff; border-radius: 50%; animation: float 8s ease-in-out infinite reverse;"></div>
        <div class="floating-circle" style="position: absolute; bottom: 30%; left: 20%; width: 100px; height: 100px; background: #fff; border-radius: 50%; animation: float 7s ease-in-out infinite;"></div>
    </div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
        <h1 class="main-title animated-title" style="font-size: 3.2rem; font-weight: 800; color: #fff; margin-bottom: 20px; letter-spacing: -1px; line-height: 1.2; text-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <span class="title-word title-word-1">Carte</span>
            <span class="title-word title-word-2">Interactive</span>
        </h1>
        <p class="main-subtitle animated-subtitle" style="font-size: 1.3rem; color: #e5e7eb; max-width: 700px; margin: 0 auto; line-height: 1.6; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            Découvrez nos entrepôts et zones d'intervention à travers le Sénégal
        </p>
    </div>
</section>

<!-- Map Section -->
<section class="section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
            <div class="stat-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; text-align: center; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="fas fa-warehouse" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0 0 0.5rem;">{{ $stats['total_warehouses'] }}</h3>
                <p style="color: #6b7280; margin: 0; font-weight: 500;">Entrepôts Actifs</p>
            </div>
            
            <div class="stat-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; text-align: center; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="fas fa-map-marker-alt" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0 0 0.5rem;">14</h3>
                <p style="color: #6b7280; margin: 0; font-weight: 500;">Régions Couvertes</p>
            </div>
            
            <div class="stat-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; text-align: center; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <i class="fas fa-truck" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0 0 0.5rem;">24/7</h3>
                <p style="color: #6b7280; margin: 0; font-weight: 500;">Disponibilité</p>
            </div>
        </div>

        <!-- Map Container -->
        <div class="map-container zoom-hover" style="background: #fff; border-radius: 20px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Nos Entrepôts et Zones d'Intervention</h2>
                <p style="color: #6b7280; font-size: 1.1rem;">Cliquez sur les marqueurs pour plus d'informations</p>
                
                <!-- Légende -->
                <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 20px; height: 20px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-size: 8px; font-weight: bold;">CSAR</span>
                        </div>
                        <span style="color: #6b7280; font-size: 0.9rem;">Entrepôts CSAR</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 20px; height: 20px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-marker-alt" style="color: white; font-size: 10px;"></i>
                        </div>
                        <span style="color: #6b7280; font-size: 0.9rem;">Régions du Sénégal</span>
                    </div>
                </div>
            </div>
            
            <div id="publicMap" style="width: 100%; height: 500px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
        </div>
    </div>
</section>

<!-- Additional Info Section -->
<section class="section fade-in" style="background: #fff; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 4rem;">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Notre Réseau d'Entrepôts</h2>
            <p style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Le CSAR dispose d'un réseau d'entrepôts stratégiquement positionnés pour assurer une couverture optimale du territoire sénégalais
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
            <div class="info-card zoom-hover" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 20px; padding: 2.5rem; border: 2px solid #3b82f6; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #1e40af); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);">
                    <i class="fas fa-shield-alt" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Sécurité Maximale</h3>
                <p style="color: #6b7280; margin: 0 0 1rem; line-height: 1.7; font-size: 1.1rem;">Nos entrepôts sont équipés des dernières technologies de sécurité pour protéger les stocks alimentaires.</p>
                <div style="background: rgba(59, 130, 246, 0.1); border-radius: 12px; padding: 1rem; margin-top: 1rem;">
                    <ul style="color: #374151; margin: 0; padding-left: 1.2rem; font-size: 0.95rem;">
                        <li style="margin-bottom: 0.5rem;">Système de surveillance 24/7</li>
                        <li style="margin-bottom: 0.5rem;">Contrôle d'accès biométrique</li>
                        <li style="margin-bottom: 0.5rem;">Alarmes anti-intrusion</li>
                        <li>Protection contre l'incendie</li>
                    </ul>
                </div>
            </div>
            
            <div class="info-card zoom-hover" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 20px; padding: 2.5rem; border: 2px solid #22c55e; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.1);">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);">
                    <i class="fas fa-thermometer-half" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Contrôle de Température</h3>
                <p style="color: #6b7280; margin: 0 0 1rem; line-height: 1.7; font-size: 1.1rem;">Système de réfrigération et climatisation pour maintenir la qualité des produits alimentaires.</p>
                <div style="background: rgba(34, 197, 94, 0.1); border-radius: 12px; padding: 1rem; margin-top: 1rem;">
                    <ul style="color: #374151; margin: 0; padding-left: 1.2rem; font-size: 0.95rem;">
                        <li style="margin-bottom: 0.5rem;">Température contrôlée 2-8°C</li>
                        <li style="margin-bottom: 0.5rem;">Système de réfrigération industriel</li>
                        <li style="margin-bottom: 0.5rem;">Monitoring en temps réel</li>
                        <li>Alertes automatiques</li>
                    </ul>
                </div>
            </div>
            
            <div class="info-card zoom-hover" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 20px; padding: 2.5rem; border: 2px solid #f59e0b; box-shadow: 0 10px 30px rgba(245, 158, 11, 0.1);">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);">
                    <i class="fas fa-route" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Accessibilité</h3>
                <p style="color: #6b7280; margin: 0 0 1rem; line-height: 1.7; font-size: 1.1rem;">Emplacements stratégiques proches des axes routiers principaux pour faciliter la distribution.</p>
                <div style="background: rgba(245, 158, 11, 0.1); border-radius: 12px; padding: 1rem; margin-top: 1rem;">
                    <ul style="color: #374151; margin: 0; padding-left: 1.2rem; font-size: 0.95rem;">
                        <li style="margin-bottom: 0.5rem;">Proximité des autoroutes</li>
                        <li style="margin-bottom: 0.5rem;">Accès facilité aux camions</li>
                        <li style="margin-bottom: 0.5rem;">Parking dédié</li>
                        <li>Réseau de distribution optimisé</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technical Specifications Section -->
<section class="section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 4rem;">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">Spécifications Techniques</h2>
            <p style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Nos entrepôts répondent aux normes internationales de stockage alimentaire
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <div class="spec-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-left: 4px solid #3b82f6;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <i class="fas fa-cube" style="color: #3b82f6; font-size: 1.5rem; margin-right: 0.75rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin: 0;">Capacité de Stockage</h3>
                </div>
                <p style="color: #6b7280; margin: 0; line-height: 1.6;">Chaque entrepôt peut stocker jusqu'à <strong>5 000 tonnes</strong> de denrées alimentaires dans des conditions optimales.</p>
            </div>
            
            <div class="spec-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-left: 4px solid #22c55e;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <i class="fas fa-clock" style="color: #22c55e; font-size: 1.5rem; margin-right: 0.75rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin: 0;">Disponibilité</h3>
                </div>
                <p style="color: #6b7280; margin: 0; line-height: 1.6;">Nos entrepôts fonctionnent <strong>24h/24 et 7j/7</strong> pour assurer une disponibilité permanente des stocks.</p>
            </div>
            
            <div class="spec-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-left: 4px solid #f59e0b;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <i class="fas fa-truck" style="color: #f59e0b; font-size: 1.5rem; margin-right: 0.75rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin: 0;">Logistique</h3>
                </div>
                <p style="color: #6b7280; margin: 0; line-height: 1.6;">Délai de livraison moyen de <strong>2-4 heures</strong> dans un rayon de 50km autour de chaque entrepôt.</p>
            </div>
            
            <div class="spec-card zoom-hover" style="background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-left: 4px solid #8b5cf6;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <i class="fas fa-certificate" style="color: #8b5cf6; font-size: 1.5rem; margin-right: 0.75rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin: 0;">Certifications</h3>
                </div>
                <p style="color: #6b7280; margin: 0; line-height: 1.6;">Tous nos entrepôts sont certifiés <strong>ISO 22000</strong> et respectent les normes HACCP.</p>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 20px; padding: 3rem; text-align: center; color: white;">
            <h3 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Besoin d'informations sur nos entrepôts ?</h3>
            <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">Contactez notre équipe pour obtenir des détails sur nos capacités de stockage et nos services.</p>
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" style="background: rgba(255, 255, 255, 0.2); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; border: 2px solid rgba(255, 255, 255, 0.3);">
                    <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>
                    Nous contacter
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la carte centrée sur le Sénégal
    const map = L.map('publicMap').setView([14.4974, -14.4524], 7);
    
    // Ajouter la couche de tuiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Données des entrepôts
    const warehouses = @json($warehouses);
    
    // Coordonnées des 14 régions du Sénégal
    const regions = [
        { name: 'Dakar', lat: 14.7167, lng: -17.4677 },
        { name: 'Thiès', lat: 14.7894, lng: -16.9260 },
        { name: 'Diourbel', lat: 14.6550, lng: -16.2314 },
        { name: 'Fatick', lat: 14.3390, lng: -16.4111 },
        { name: 'Kaolack', lat: 14.1510, lng: -16.0756 },
        { name: 'Kolda', lat: 12.8837, lng: -14.9500 },
        { name: 'Louga', lat: 15.6186, lng: -16.2246 },
        { name: 'Matam', lat: 15.6559, lng: -13.2554 },
        { name: 'Saint-Louis', lat: 16.0190, lng: -16.4896 },
        { name: 'Tambacounda', lat: 13.7689, lng: -13.6673 },
        { name: 'Ziguinchor', lat: 12.5641, lng: -16.2635 },
        { name: 'Kaffrine', lat: 14.1059, lng: -15.5508 },
        { name: 'Kédougou', lat: 12.5556, lng: -12.1833 },
        { name: 'Sédhiou', lat: 12.7081, lng: -15.5569 }
    ];
    
    // Icônes personnalisées style CSAR exact
    const warehouseIcon = L.divIcon({
        className: 'custom-warehouse-icon',
        html: `
            <div style="position: relative; width: 50px; height: 50px;">
                <!-- Cercle rouge extérieur -->
                <div style="position: absolute; top: 0; left: 0; width: 50px; height: 50px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <!-- Cercle vert intérieur -->
                    <div style="width: 35px; height: 35px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <!-- Texte CSAR -->
                        <span style="color: white; font-size: 10px; font-weight: 900; letter-spacing: 0.5px; font-family: Arial, sans-serif;">CSAR</span>
                    </div>
                </div>
                <!-- Tige du marqueur -->
                <div style="position: absolute; top: 50px; left: 24px; width: 2px; height: 15px; background: #d1d5db;"></div>
                <!-- Base du marqueur -->
                <div style="position: absolute; top: 65px; left: 20px; width: 10px; height: 8px; background: #991b1b; border-radius: 0 0 5px 5px;"></div>
            </div>
        `,
        iconSize: [50, 80],
        iconAnchor: [25, 80]
    });
    
    // Icône pour les régions
    const regionIcon = L.divIcon({
        className: 'custom-region-icon',
        html: `
            <div style="position: relative; width: 30px; height: 30px;">
                <div style="position: absolute; top: 0; left: 0; width: 30px; height: 30px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3); border: 2px solid white;">
                    <i class="fas fa-map-marker-alt" style="color: white; font-size: 12px;"></i>
                </div>
            </div>
        `,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    
    // Ajouter les marqueurs des régions
    regions.forEach(region => {
        const marker = L.marker([region.lat, region.lng], { icon: regionIcon })
            .addTo(map);
        
        marker.bindPopup(`
            <div style="min-width: 200px; text-align: center;">
                <h4 style="margin: 0 0 8px; color: #1f2937; font-size: 1rem;">${region.name}</h4>
                <p style="margin: 0; color: #6b7280; font-size: 0.85rem;">
                    <i class="fas fa-map-marker-alt" style="color: #3b82f6; margin-right: 5px;"></i>
                    Région du Sénégal
                </p>
            </div>
        `);
    });
    
    // Ajouter les marqueurs des entrepôts
    warehouses.forEach(warehouse => {
        const marker = L.marker([warehouse.lat, warehouse.lng], { icon: warehouseIcon })
            .addTo(map);
        
        // Popup avec informations de l'entrepôt
        marker.bindPopup(`
            <div style="min-width: 250px;">
                <h3 style="margin: 0 0 10px; color: #1f2937; font-size: 1.1rem;">${warehouse.name}</h3>
                <p style="margin: 0 0 8px; color: #6b7280; font-size: 0.9rem;">
                    <i class="fas fa-map-marker-alt" style="color: #3b82f6; margin-right: 5px;"></i>
                    ${warehouse.address}
                </p>
                <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">
                    <i class="fas fa-boxes" style="color: #22c55e; margin-right: 5px;"></i>
                    Capacité: ${warehouse.capacity || 'Non spécifiée'}
                </p>
            </div>
        `);
    });
    
    // Ajuster la vue pour inclure tous les marqueurs (régions + entrepôts)
    const allMarkers = [
        ...regions.map(r => L.marker([r.lat, r.lng])),
        ...warehouses.map(w => L.marker([w.lat, w.lng]))
    ];
    
    if (allMarkers.length > 0) {
        const group = new L.featureGroup(allMarkers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});
</script>

<style>
/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.fade-in {
    animation: fadeIn 0.8s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.zoom-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.zoom-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15) !important;
}

.animated-title .title-word {
    display: inline-block;
    animation: slideInUp 0.8s ease-out forwards;
    opacity: 0;
}

.title-word-1 { animation-delay: 0.1s; }
.title-word-2 { animation-delay: 0.2s; }
.title-word-3 { animation-delay: 0.3s; }

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .main-title {
        font-size: 2.5rem !important;
    }
    
    .main-subtitle {
        font-size: 1.1rem !important;
    }
    
    #publicMap {
        height: 400px !important;
    }
}
</style>
@endsection
