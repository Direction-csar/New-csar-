@extends('layouts.admin')

@section('title', 'SIM — Suivi GPS Collecteurs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Suivi GPS des Collecteurs</h1>
        <span class="badge bg-success">En direct</span>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users me-2"></i>Collecteurs actifs (30 min)
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($locations as $loc)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $loc->collector->name ?? 'Inconnu' }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-store me-1"></i>{{ $loc->current_market ?: '—' }}
                                    <span class="mx-1">|</span>
                                    <i class="fas fa-clock me-1"></i>{{ $loc->last_activity_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $loc->isOnline() ? 'success' : 'secondary' }}">
                                {{ $loc->isOnline() ? 'En ligne' : 'Hors ligne' }}
                            </span>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Aucun collecteur actif récemment.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>Légende
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success me-2">●</span> En ligne (5 min)
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2">●</span> Actif récemment (30 min)
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-2">●</span> Hors ligne
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const locations = @json($locations->map(fn($l) => [
    'name' => $l->collector->name ?? 'Inconnu',
    'lat' => (float) $l->latitude,
    'lng' => (float) $l->longitude,
    'market' => $l->current_market,
    'status' => $l->status,
    'online' => $l->isOnline(),
    'last' => $l->last_activity_at->diffForHumans(),
    'collections' => $l->collections_today
]));

const map = L.map('map').setView([14.4974, -14.4524], 7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

const markers = [];
locations.forEach(loc => {
    const color = loc.online ? 'green' : (loc.status === 'paused' ? 'orange' : 'gray');
    const marker = L.circleMarker([loc.lat, loc.lng], {
        radius: 10,
        fillColor: color,
        color: '#fff',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.9
    }).addTo(map);

    marker.bindPopup(`
        <div class="fw-bold">${loc.name}</div>
        <div><i class="fas fa-store"></i> ${loc.market || '—'}</div>
        <div><i class="fas fa-clock"></i> ${loc.last}</div>
        <div><i class="fas fa-clipboard-list"></i> ${loc.collections} collectes aujourd'hui</div>
    `);
    markers.push(marker);
});

if (markers.length > 0) {
    const group = L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.2));
}

// Auto-refresh toutes les 60 secondes
setInterval(() => location.reload(), 60000);
</script>
@endpush
