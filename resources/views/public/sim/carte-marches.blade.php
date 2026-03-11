@extends('layouts.public')

@section('title', 'Carte des marchés SIM | CSAR')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sim-fluid-modern.css') }}">
@endpush

@section('content')
<div class="container-fluid px-0 sim-fluid-dashboard">
    {{-- Bannière style SIMA Niger --}}
    <div class="sim-map-banner text-center py-3 mb-0">
        <h1 class="sim-map-title mb-0">CARTE DES MARCHÉS SUIVIS SIM CSAR</h1>
    </div>

    <div class="card shadow-sm border-0 rounded-0">
        <div class="card-body p-0 sim-map-wrapper">
            <div id="sim-markets-map" style="height: 75vh; width: 100%;"></div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const markets = @json($markets);
    const map = L.map('sim-markets-map').setView([14.5, -14.45], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap | Leaflet' }).addTo(map);

    const colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e', '#16a085', '#2980b9', '#8e44ad', '#27ae60', '#c0392b', '#d35400'];
    const bounds = [];

    if (markets.length === 0) {
        L.marker([14.5, -14.45]).bindPopup('<strong>Aucun marché avec coordonnées en base de données.</strong>').addTo(map);
    } else {
        markets.forEach(function (m, i) {
            const color = colors[i % colors.length];
            const circle = L.circleMarker([m.lat, m.lng], {
                radius: 10,
                fillColor: color,
                color: '#333',
                weight: 1,
                opacity: 1,
                fillOpacity: 0.85
            });
            let popupHtml = '<div class="sim-popup"><strong>' + (m.name || 'Marché') + '</strong><br>Région : ' + (m.region || '—');
            if (m.type) popupHtml += '<br>Type : ' + m.type;
            if (m.day) popupHtml += '<br>Jour : ' + m.day;
            if (m.prices && m.prices.length > 0) {
                popupHtml += '<br><hr class="my-2"><strong>Prix (dernière collecte)</strong><br>';
                m.prices.forEach(function (row) {
                    popupHtml += (row.product || '') + ' : <strong>' + (row.price != null ? row.price + ' FCFA' : '—') + '</strong><br>';
                });
                if (m.prices[0] && m.prices[0].date) {
                    popupHtml += '<small class="text-muted">Date : ' + (m.prices[0].date || '').toString().slice(0, 10) + '</small>';
                }
            } else {
                popupHtml += '<br><em class="text-muted">Aucun prix enregistré pour ce marché.</em>';
            }
            popupHtml += '</div>';
            circle.bindPopup(popupHtml);
            circle.addTo(map);
            bounds.push([m.lat, m.lng]);
        });
        if (bounds.length) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
    }
});
</script>
<style>
.sim-map-banner { background: linear-gradient(135deg, #c9a227 0%, #b8860b 50%, #daa520 100%); color: #fff; }
.sim-map-title { font-size: 1.4rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
.sim-popup { min-width: 200px; font-size: 0.9rem; }
</style>
@endsection
