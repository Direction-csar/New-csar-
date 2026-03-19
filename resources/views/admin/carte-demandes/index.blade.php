@extends('layouts.admin')

@section('title', 'Carte des demandes d\'aide')
@section('page-title', 'Carte des demandes d\'aide géolocalisées')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    #carte-demandes-map { height: calc(100vh - 180px); min-height: 500px; border-radius: 12px; }
    .carte-sidebar { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .zone-marker { background: #dc3545; color: white; border-radius: 50%; min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
    .demande-marker { background: #0d6efd; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
    .stat-card { border-left: 4px solid #0d6efd; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar filtres (style SmartWork) -->
        <div class="col-lg-4 col-xl-3 mb-3">
            <div class="carte-sidebar p-4">
                <h5 class="mb-3 fw-bold text-primary">
                    <i class="fas fa-map-marker-alt me-2"></i>Je veux voir les demandes...
                </h5>
                
                <div class="mb-3">
                    <label class="form-label small fw-600">Rechercher une adresse</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchAddress" class="form-control" placeholder="Ex: Dakar, Thiès...">
                        <button class="btn btn-primary" type="button" id="btnSearchAddress">
                            <i class="fas fa-location-arrow"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-600">Préciser la région</label>
                    <select id="filterRegion" class="form-select form-select-sm">
                        <option value="">Toutes les régions</option>
                        @foreach(config('regions_senegal.names', []) as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-600">Type de demande</label>
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        <option value="aide_alimentaire">Aide alimentaire</option>
                        <option value="aide">Aide générale</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-600">Statut</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="pending">En attente</option>
                        <option value="approved">Approuvées</option>
                        <option value="rejected">Rejetées</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-600">Année</label>
                        <select id="filterYear" class="form-select form-select-sm">
                            <option value="">Toutes</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-600">Mois</label>
                        <select id="filterMonth" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            @foreach(['1'=>'Jan','2'=>'Fév','3'=>'Mar','4'=>'Avr','5'=>'Mai','6'=>'Juin','7'=>'Juil','8'=>'Août','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'] as $m => $l)
                                <option value="{{ $m }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button class="btn btn-outline-secondary btn-sm w-100 mb-2" id="btnResetFilters">
                    <i class="fas fa-redo me-1"></i>Réinitialiser
                </button>
                <button class="btn btn-primary btn-sm w-100" id="btnApplyFilters">
                    <i class="fas fa-filter me-1"></i>Appliquer les filtres
                </button>

                <hr class="my-3">
                <h6 class="small fw-bold text-muted">Statistiques</h6>
                <div class="stat-card bg-light rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="small">Total demandes</span>
                        <strong id="statTotal">0</strong>
                    </div>
                </div>
                <div class="stat-card bg-light rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="small">Avec géolocalisation</span>
                        <strong id="statWithCoords">0</strong>
                    </div>
                </div>
                <div class="stat-card bg-light rounded p-2">
                    <div class="d-flex justify-content-between">
                        <span class="small">Régions concernées</span>
                        <strong id="statZones">0</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-danger me-2"><i class="fas fa-map-pin"></i> Zones (régions)</span>
                            <span class="badge bg-primary"><i class="fas fa-user"></i> Demandes individuelles</span>
                        </div>
                        <div>
                            <a href="{{ route('admin.demandes.index') }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-list me-1"></i>Liste des demandes
                            </a>
                            <button class="btn btn-sm btn-success" id="btnRefreshMap">
                                <i class="fas fa-sync-alt me-1"></i>Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="carte-demandes-map"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
(function() {
    const API_URL = '{{ url("/admin/carte-demandes/api") }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let map;
    let zonesLayer;
    let demandesLayer;
    let markersCluster;

    function initMap() {
        map = L.map('carte-demandes-map', {
            center: [14.7167, -17.4677],
            zoom: 6,
            zoomControl: false
        });
        L.control.zoom({ position: 'topright' }).addTo(map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        zonesLayer = L.layerGroup().addTo(map);
        demandesLayer = L.layerGroup().addTo(map);
        markersCluster = L.markerClusterGroup({
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false
        });
        demandesLayer.addLayer(markersCluster);

        loadMapData();
    }

    function getFilters() {
        return {
            region: document.getElementById('filterRegion').value || undefined,
            type: document.getElementById('filterType').value || undefined,
            status: document.getElementById('filterStatus').value || undefined,
            year: document.getElementById('filterYear').value || undefined,
            month: document.getElementById('filterMonth').value || undefined,
        };
    }

    function loadMapData() {
        const filters = getFilters();
        fetch(API_URL + '?' + new URLSearchParams(filters), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Erreur');
            renderZones(data.zones || []);
            renderDemandes(data.demandes || []);
            updateStats(data.stats || {});
        })
        .catch(err => {
            console.error('Erreur chargement carte:', err);
            document.getElementById('statTotal').textContent = '—';
        });
    }

    function renderZones(zones) {
        zonesLayer.clearLayers();
        zones.forEach(z => {
            const el = document.createElement('div');
            el.className = 'zone-marker';
            el.textContent = z.total;
            const icon = L.divIcon({
                html: el,
                className: '',
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
            const m = L.marker([z.lat, z.lng], { icon }).addTo(zonesLayer);
            const popup = `
                <div style="min-width:180px;">
                    <h6 class="mb-2 text-danger"><i class="fas fa-map-marker-alt me-1"></i>${z.region}</h6>
                    <p class="mb-1 small"><strong>Total:</strong> ${z.total} demande(s)</p>
                    <p class="mb-1 small"><strong>En attente:</strong> ${z.pending}</p>
                    <p class="mb-1 small"><strong>Approuvées:</strong> ${z.approved}</p>
                    <p class="mb-2 small"><strong>Rejetées:</strong> ${z.rejected}</p>
                    <a href="{{ route('admin.demandes.index') }}?region=${encodeURIComponent(z.region)}" class="btn btn-sm btn-primary w-100">Voir les demandes</a>
                </div>`;
            m.bindPopup(popup);
        });
    }

    function renderDemandes(demandes) {
        markersCluster.clearLayers();
        demandes.forEach(d => {
            const el = document.createElement('div');
            el.className = 'demande-marker';
            el.innerHTML = '<i class="fas fa-user"></i>';
            const icon = L.divIcon({
                html: el,
                className: '',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });
            const m = L.marker([d.lat, d.lng], { icon });
            const statusBadge = d.status === 'approved' ? 'success' : (d.status === 'rejected' ? 'danger' : 'warning');
            const popup = `
                <div style="min-width:200px;">
                    <h6 class="mb-1">${d.name}</h6>
                    <p class="mb-1 small"><strong>Région:</strong> ${d.region}</p>
                    <p class="mb-1 small"><strong>Adresse:</strong> ${d.address}</p>
                    <p class="mb-1 small"><strong>Code:</strong> ${d.tracking_code || '—'}</p>
                    <p class="mb-2"><span class="badge bg-${statusBadge}">${d.status}</span></p>
                    <a href="/admin/demandes/${d.id}" class="btn btn-sm btn-primary w-100">Voir la demande</a>
                </div>`;
            m.bindPopup(popup);
            markersCluster.addLayer(m);
        });
    }

    function updateStats(stats) {
        document.getElementById('statTotal').textContent = stats.total ?? 0;
        document.getElementById('statWithCoords').textContent = stats.with_coords ?? 0;
        document.getElementById('statZones').textContent = stats.zones_count ?? 0;
    }

    document.getElementById('btnApplyFilters').addEventListener('click', loadMapData);
    document.getElementById('btnResetFilters').addEventListener('click', function() {
        document.getElementById('filterRegion').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterYear').value = '{{ date("Y") }}';
        document.getElementById('filterMonth').value = '';
        loadMapData();
    });
    document.getElementById('btnRefreshMap').addEventListener('click', loadMapData);

    document.getElementById('btnSearchAddress').addEventListener('click', function() {
        const q = document.getElementById('searchAddress').value.trim();
        if (!q) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q + ', Senegal')}&limit=1`)
            .then(r => r.json())
            .then(data => {
                if (data && data[0]) {
                    map.setView([parseFloat(data[0].lat), parseFloat(data[0].lon)], 12);
                }
            });
    });

    if (document.getElementById('carte-demandes-map')) {
        initMap();
    }
})();
</script>
@endpush
@endsection
