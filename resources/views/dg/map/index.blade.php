@extends('layouts.dg')

@section('title', 'Carte Interactive - Direction Générale')

@section('content')
<div class="dg-interface">
    <div class="dg-page-title">
        <h1><i class="fas fa-map-marked-alt"></i> Carte Interactive</h1>
        <p>Visualisation géographique des entrepôts et demandes CSAR</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="dg-stat-card">
            <div class="dg-stat-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="dg-stat-content">
                <h3>{{ $stats['total_warehouses'] }}</h3>
                <p>Entrepôts Actifs</p>
            </div>
        </div>
        
        <div class="dg-stat-card">
            <div class="dg-stat-icon info">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="dg-stat-content">
                <h3>{{ $stats['total_requests'] }}</h3>
                <p>Total Demandes</p>
            </div>
        </div>
        
        <div class="dg-stat-card">
            <div class="dg-stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="dg-stat-content">
                <h3>{{ $stats['pending_requests'] }}</h3>
                <p>En Attente</p>
            </div>
        </div>
        
        <div class="dg-stat-card">
            <div class="dg-stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="dg-stat-content">
                <h3>{{ $stats['approved_requests'] }}</h3>
                <p>Approuvées</p>
            </div>
        </div>
    </div>

    <!-- Statistiques des demandes par région (zones à forte demande) -->
    <div class="dg-card mb-6">
        <div class="dg-card-header">
            <h3><i class="fas fa-chart-bar"></i> Demandes d'aide alimentaire par région</h3>
        </div>
        <div class="dg-card-body overflow-x-auto">
            <table class="dg-table w-full">
                <thead>
                    <tr>
                        <th>Région</th>
                        <th>Total demandes</th>
                        <th>En attente</th>
                        <th>Approuvées</th>
                        <th>Rejetées</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandesByRegion ?? [] as $r)
                    <tr>
                        <td class="font-medium">{{ $r['region'] }}</td>
                        <td>{{ $r['total'] }}</td>
                        <td>{{ $r['pending'] }}</td>
                        <td>{{ $r['approved'] }}</td>
                        <td>{{ $r['rejected'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-500">Aucune donnée par région pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Contrôles de la carte -->
    <div class="dg-card mb-6">
        <div class="dg-card-header">
            <h3><i class="fas fa-map"></i> Contrôles de la Carte</h3>
        </div>
        <div class="dg-card-body">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="show-warehouses" checked class="dg-form-checkbox">
                    <label for="show-warehouses" class="dg-form-label">Afficher les entrepôts</label>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="show-zones" checked class="dg-form-checkbox">
                    <label for="show-zones" class="dg-form-label">Zones par région (demandes)</label>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="show-requests" checked class="dg-form-checkbox">
                    <label for="show-requests" class="dg-form-label">Afficher les demandes (points)</label>
                </div>
                <div class="flex items-center space-x-2">
                    <select id="filter-status" class="dg-form-input">
                        <option value="">Tous les statuts</option>
                        <option value="pending">En attente</option>
                        <option value="approved">Approuvées</option>
                        <option value="rejected">Rejetées</option>
                    </select>
                </div>
                <button id="refresh-map" class="dg-btn dg-btn-primary">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
            </div>
        </div>
    </div>

    <!-- Carte -->
    <div class="dg-card">
        <div class="dg-card-header">
            <h3><i class="fas fa-globe"></i> Carte des demandes et entrepôts — zones à forte demande</h3>
        </div>
        <div class="dg-card-body">
            <div id="map" style="height: 600px; width: 100%; border-radius: 8px;"></div>
        </div>
    </div>

    <!-- Légende -->
    <div class="dg-card mt-6">
        <div class="dg-card-header">
            <h3><i class="fas fa-info-circle"></i> Légende</h3>
        </div>
        <div class="dg-card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-semibold mb-3">Entrepôts</h4>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <span class="text-sm">Entrepôt actif</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                            <span class="text-sm">Entrepôt inactif</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Demandes</h4>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm">En attente</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <span class="text-sm">Approuvée</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                            <span class="text-sm">Rejetée</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Zones (par région)</h4>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                            <span class="text-sm">Région avec demandes (total / approuvées)</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Informations</h4>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span class="text-sm">Cliquez sur un marqueur pour plus d'infos</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-expand-arrows-alt text-blue-500"></i>
                            <span class="text-sm">Zoom pour voir les détails</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la carte
    const map = L.map('map').setView([14.6928, -17.4467], 8); // Centre sur Dakar
    
    // Ajouter la couche de tuiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    let markers = [];
    let warehouses = [];
    let requests = [];
    let demandesByRegion = [];
    
    // Charger les données
    loadMapData();
    
    // Contrôles
    document.getElementById('show-warehouses').addEventListener('change', function() { updateMap(); });
    document.getElementById('show-zones').addEventListener('change', function() { updateMap(); });
    document.getElementById('show-requests').addEventListener('change', function() { updateMap(); });
    document.getElementById('filter-status').addEventListener('change', function() { updateMap(); });
    document.getElementById('refresh-map').addEventListener('click', function() { loadMapData(); });
    
    function loadMapData() {
        fetch('{{ route("dg.map.data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    warehouses = data.warehouses || [];
                    requests = data.recent_requests || [];
                    demandesByRegion = data.demandes_by_region || [];
                    updateMap();
                } else {
                    console.error('Erreur lors du chargement des données:', data.error);
                }
            })
            .catch(error => console.error('Erreur:', error));
    }
    
    function updateMap() {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        
        // Marqueurs par région (zones à forte demande)
        if (document.getElementById('show-zones').checked && demandesByRegion.length) {
            demandesByRegion.forEach(function(r) {
                if (r.lat == null || r.lng == null) return;
                const radius = Math.min(14, 6 + (r.total || 0));
                const marker = L.circleMarker([r.lat, r.lng], {
                    color: '#2563eb',
                    fillColor: '#2563eb',
                    fillOpacity: 0.6,
                    radius: radius,
                    weight: 2
                }).addTo(map);
                marker.bindPopup(`
                    <div class="p-2 min-w-[180px]">
                        <h4 class="font-semibold">${r.region}</h4>
                        <p><strong>Total demandes:</strong> ${r.total}</p>
                        <p><strong>Approuvées:</strong> ${r.approved}</p>
                        <p><strong>En attente:</strong> ${r.pending}</p>
                        <p><strong>Rejetées:</strong> ${r.rejected}</p>
                    </div>
                `);
                markers.push(marker);
            });
        }
        
        // Entrepôts
        if (document.getElementById('show-warehouses').checked) {
            warehouses.forEach(warehouse => {
                const color = warehouse.status === 'success' ? 'green' : 'red';
                const marker = L.circleMarker([warehouse.latitude, warehouse.longitude], {
                    color: color, fillColor: color, fillOpacity: 0.7, radius: 8
                }).addTo(map);
                marker.bindPopup(`
                    <div class="p-2">
                        <h4 class="font-semibold">${warehouse.name}</h4>
                        <p><strong>Région:</strong> ${warehouse.region}</p>
                        <p><strong>Adresse:</strong> ${warehouse.address || 'N/A'}</p>
                        <p><strong>Demandes:</strong> ${warehouse.requests_count}</p>
                        <p><strong>En attente:</strong> ${warehouse.pending_requests}</p>
                    </div>
                `);
                markers.push(marker);
            });
        }
        
        // Demandes (points individuels)
        if (document.getElementById('show-requests').checked) {
            const statusFilter = document.getElementById('filter-status').value;
            const filtered = statusFilter ? requests.filter(req => req.status === statusFilter) : requests;
            filtered.forEach(request => {
                if (request.latitude && request.longitude) {
                    const color = getStatusColor(request.status);
                    const marker = L.circleMarker([request.latitude, request.longitude], {
                        color: color, fillColor: color, fillOpacity: 0.6, radius: 6
                    }).addTo(map);
                    marker.bindPopup(`
                        <div class="p-2">
                            <h4 class="font-semibold">Demande #${request.id}</h4>
                            <p><strong>Type:</strong> ${request.type}</p>
                            <p><strong>Statut:</strong> ${request.status}</p>
                            <p><strong>Demandeur:</strong> ${request.user_name}</p>
                            <p><strong>Région:</strong> ${request.region || '—'}</p>
                            <p><strong>Date:</strong> ${request.created_at}</p>
                        </div>
                    `);
                    markers.push(marker);
                }
            });
        }
    }
    
    function getStatusColor(status) {
        switch(status) {
            case 'pending': return 'yellow';
            case 'approved': return 'green';
            case 'rejected': return 'red';
            default: return 'blue';
        }
    }
});
</script>
@endpush
@endsection

