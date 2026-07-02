@extends('layouts.admin')

@section('title', 'SIM — Géolocalisation des marchés')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #geo-map { height: 70vh; width: 100%; border-radius: .5rem; }
    .market-list { max-height: 70vh; overflow-y: auto; }
    .market-item { cursor: pointer; transition: background .2s; }
    .market-item:hover, .market-item.active { background: #e7f1ff; }
    .market-item.placed { border-left: 4px solid #28a745; }
    .market-item.missing { border-left: 4px solid #dc3545; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-map-marker-alt me-2"></i>Géolocalisation des marchés SIM
        </h1>
        <div>
            <span class="badge bg-success me-2">{{ $okCount }} géolocalisés</span>
            <span class="badge bg-danger">{{ $missingCount }} sans coordonnées</span>
        </div>
    </div>

    <div class="row g-3">
        {{-- Liste des marchés --}}
        <div class="col-lg-3">
            <div class="card shadow">
                <div class="card-header py-2">
                    <input type="text" id="search-market" class="form-control form-control-sm" placeholder="Rechercher un marché...">
                </div>
                <div class="card-body p-0 market-list" id="market-list">
                    @foreach($markets as $m)
                        <div class="market-item p-2 border-bottom {{ $m->latitude && $m->longitude ? 'placed' : 'missing' }}"
                             data-id="{{ $m->id }}"
                             data-name="{{ $m->name }}"
                             data-lat="{{ $m->latitude }}"
                             data-lng="{{ $m->longitude }}"
                             data-commune="{{ $m->commune }}"
                             data-dept="{{ $m->department->name ?? '' }}">
                            <div class="fw-bold small">{{ $m->name }}</div>
                            <div class="text-muted smaller">{{ $m->commune }} — {{ $m->department->name ?? '' }}</div>
                            @if($m->latitude && $m->longitude)
                                <div class="text-success smaller"><i class="fas fa-check-circle me-1"></i>{{ $m->latitude }}, {{ $m->longitude }}</div>
                            @else
                                <div class="text-danger smaller"><i class="fas fa-times-circle me-1"></i>Non géolocalisé</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Carte --}}
        <div class="col-lg-9">
            <div class="card shadow">
                <div class="card-body p-2 position-relative">
                    <div id="geo-map"></div>
                    <div class="position-absolute top-0 end-0 m-3 bg-white rounded shadow p-2" style="z-index:1000; max-width:300px;">
                        <div class="small mb-2"><strong>Instructions</strong></div>
                        <ol class="small ps-3 mb-0">
                            <li>Sélectionnez un marché dans la liste rouge</li>
                            <li>Cliquez sur la carte à l'emplacement du marché</li>
                            <li>Confirmez la position</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal confirmation --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6">Confirmer la position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><strong id="modal-market-name">Marché</strong></p>
                <p class="small text-muted mb-2">Cliquez sur la carte pour ajuster.</p>
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text">Lat</span>
                    <input type="text" id="modal-lat" class="form-control" readonly>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Lng</span>
                    <input type="text" id="modal-lng" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-sm btn-primary" id="btn-save-geo">Enregistrer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('geo-map').setView([14.5, -14.45], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let selectedMarket = null;
    let tempMarker = null;
    const markers = {};
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

    // Placer les marchés déjà géolocalisés
    document.querySelectorAll('.market-item').forEach(function (item) {
        const lat = parseFloat(item.dataset.lat);
        const lng = parseFloat(item.dataset.lng);
        const id = item.dataset.id;

        if (!isNaN(lat) && !isNaN(lng)) {
            const marker = L.circleMarker([lat, lng], {
                radius: 8, fillColor: '#28a745', color: '#fff', weight: 2, opacity: 1, fillOpacity: 0.9
            }).addTo(map);
            marker.bindPopup('<strong>' + item.dataset.name + '</strong><br>' + item.dataset.commune);
            markers[id] = marker;
        }
    });

    // Sélection d'un marché dans la liste
    document.querySelectorAll('.market-item').forEach(function (item) {
        item.addEventListener('click', function () {
            document.querySelectorAll('.market-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            selectedMarket = {
                id: item.dataset.id,
                name: item.dataset.name
            };

            // Si déjà placé, centrer la carte
            const lat = parseFloat(item.dataset.lat);
            const lng = parseFloat(item.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 14);
                if (markers[item.dataset.id]) markers[item.dataset.id].openPopup();
            }
        });
    });

    // Recherche
    document.getElementById('search-market').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.market-item').forEach(function (item) {
            const text = (item.dataset.name + ' ' + item.dataset.commune + ' ' + item.dataset.dept).toLowerCase();
            item.style.display = text.includes(term) ? '' : 'none';
        });
    });

    // Clic sur la carte
    map.on('click', function (e) {
        if (!selectedMarket) {
            alert('Veuillez d\'abord sélectionner un marché dans la liste de gauche.');
            return;
        }

        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);

        document.getElementById('modal-market-name').textContent = selectedMarket.name;
        document.getElementById('modal-lat').value = lat;
        document.getElementById('modal-lng').value = lng;

        if (tempMarker) map.removeLayer(tempMarker);
        tempMarker = L.marker([lat, lng]).addTo(map);

        confirmModal.show();
    });

    // Sauvegarde AJAX
    document.getElementById('btn-save-geo').addEventListener('click', function () {
        if (!selectedMarket) return;

        const lat = document.getElementById('modal-lat').value;
        const lng = document.getElementById('modal-lng').value;
        const token = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/admin/sim/markets-geolocation/' + selectedMarket.id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'item dans la liste
                const item = document.querySelector('.market-item[data-id="' + selectedMarket.id + '"]');
                if (item) {
                    item.classList.remove('missing');
                    item.classList.add('placed');
                    item.dataset.lat = lat;
                    item.dataset.lng = lng;
                    item.querySelector('.text-danger, .text-success').outerHTML =
                        '<div class="text-success smaller"><i class="fas fa-check-circle me-1"></i>' + lat + ', ' + lng + '</div>';
                }

                // Ajouter/mettre à jour le marqueur sur la carte
                if (markers[selectedMarket.id]) map.removeLayer(markers[selectedMarket.id]);
                markers[selectedMarket.id] = L.circleMarker([lat, lng], {
                    radius: 8, fillColor: '#28a745', color: '#fff', weight: 2, opacity: 1, fillOpacity: 0.9
                }).addTo(map).bindPopup('<strong>' + selectedMarket.name + '</strong>');

                confirmModal.hide();
                selectedMarket = null;
                document.querySelectorAll('.market-item').forEach(i => i.classList.remove('active'));
            } else {
                alert('Erreur lors de la sauvegarde.');
            }
        })
        .catch(() => alert('Erreur réseau.'));
    });
});
</script>
@endpush
