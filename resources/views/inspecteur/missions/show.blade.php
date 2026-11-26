@extends('layouts.direction-portal')

@section('title', 'Mission #' . $mission->id . ' - Inspecteur')
@section('page-title', 'Mission d\'inspection #' . $mission->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Mission</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted" style="width:40%">Dossier</th><td><code>{{ $mission->request?->tracking_code }}</code></td></tr>
                    <tr><th class="text-muted">Type</th><td>{{ $mission->type_label }}</td></tr>
                    <tr><th class="text-muted">Statut</th><td><span class="badge bg-info">{{ $mission->status_label }}</span></td></tr>
                    <tr><th class="text-muted">Affectée par</th><td>{{ $mission->assignedBy?->name ?? 'DTL' }}</td></tr>
                    <tr><th class="text-muted">Demandeur</th><td>{{ $mission->request?->full_name ?? $mission->request?->name }}</td></tr>
                    <tr><th class="text-muted">Région</th><td>{{ $mission->request?->region }}</td></tr>
                    <tr><th class="text-muted">Adresse</th><td>{{ $mission->request?->address }}</td></tr>
                </table>
                <hr>
                <h6 class="fw-bold small">Instructions du DTL</h6>
                <p class="small mb-0">{{ $mission->instructions }}</p>
            </div>
        </div>

        @if($mission->status === 'affectee')
        <form method="POST" action="{{ route('inspecteur.missions.start', $mission->id) }}">
            @csrf
            <button class="btn btn-primary"><i class="fas fa-play me-1"></i>Prendre en charge la mission</button>
        </form>
        @endif
    </div>

    <div class="col-lg-6">
        @if(in_array($mission->status, ['affectee', 'en_cours']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fas fa-file-upload me-2 text-success"></i>Rapport de visite</div>
            <div class="card-body">
                @if($mission->review_comment)
                    <div class="alert alert-warning small">
                        <i class="fas fa-undo me-1"></i><strong>Correction demandée par le DTL :</strong> {{ $mission->review_comment }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('inspecteur.missions.report', $mission->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Rapport (observations, anomalies, recommandations) *</label>
                        <textarea name="report" class="form-control" rows="6" required minlength="20"
                            placeholder="Décrivez la visite, les observations, anomalies constatées et recommandations...">{{ old('report', $mission->report) }}</textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Photos / justificatifs</label>
                        <input type="file" name="attachments[]" class="form-control form-control-sm" multiple accept=".pdf,.png,.jpg,.jpeg">
                        <small class="text-muted">PDF ou images, 10 Mo max par fichier.</small>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Latitude</label>
                            <input type="text" name="latitude" class="form-control form-control-sm" id="lat" value="{{ old('latitude', $mission->visit_latitude) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Longitude</label>
                            <input type="text" name="longitude" class="form-control form-control-sm" id="lng" value="{{ old('longitude', $mission->visit_longitude) }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="getLocation()">
                        <i class="fas fa-map-marker-alt me-1"></i>Capturer ma position
                    </button>
                    <div>
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('Transmettre définitivement ce rapport au DTL ?')">
                            <i class="fas fa-paper-plane me-1"></i>Transmettre le rapport au DTL
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fas fa-file-alt me-2 text-info"></i>Rapport soumis</div>
            <div class="card-body">
                <p class="small">{{ $mission->report }}</p>
                @if($mission->visited_at)<p class="small text-muted mb-1">Visite : {{ $mission->visited_at->format('d/m/Y H:i') }}</p>@endif
                @if($mission->visit_latitude)<p class="small text-muted mb-1">Position : {{ $mission->visit_latitude }}, {{ $mission->visit_longitude }}</p>@endif
                @if($mission->attachments)
                    <div class="mt-2">
                        @foreach($mission->attachments as $file)
                            <a href="{{ asset('storage/' . $file) }}" target="_blank" class="btn btn-outline-info btn-sm me-1 mb-1">
                                <i class="fas fa-paperclip me-1"></i>Pièce {{ $loop->iteration }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function getLocation() {
    if (!navigator.geolocation) { alert('Géolocalisation non supportée'); return; }
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('lat').value = pos.coords.latitude.toFixed(6);
        document.getElementById('lng').value = pos.coords.longitude.toFixed(6);
    }, function() { alert('Impossible d\'obtenir la position'); });
}
</script>
@endsection
