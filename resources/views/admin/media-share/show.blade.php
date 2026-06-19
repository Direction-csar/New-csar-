@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@php $rp = request()->routeIs('ctc.*') ? 'ctc' : 'admin'; @endphp

@section('title', $event->title . ' - QR Media Share')
@section('page-title', 'Gestion de l\'événement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0 {{ $rp === 'ctc' ? 'text-white' : '' }}">
                <i class="fas fa-photo-film me-2"></i>{{ $event->title }}
            </h1>
            @if($event->event_date)
                <p class="{{ $rp === 'ctc' ? 'text-white-50' : 'text-muted' }} mb-0">
                    <i class="far fa-calendar me-1"></i>{{ $event->event_date->format('d/m/Y') }}
                </p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route($rp . '.media-share.edit', $event->id) }}" class="btn btn-outline-light {{ $rp === 'ctc' ? '' : 'btn-outline-secondary' }}">
                <i class="fas fa-edit me-1"></i>Modifier
            </a>
            <a href="{{ route($rp . '.media-share.index') }}" class="btn btn-outline-light {{ $rp === 'ctc' ? '' : 'btn-outline-secondary' }}">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Colonne QR Code + lien --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="mb-3"><i class="fas fa-qrcode me-2"></i>QR Code d'accès</h5>
                    <div class="d-inline-block p-3 bg-white border rounded mb-3" id="qrBox">
                        {!! $qrSvg !!}
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="{{ $publicUrl }}" id="publicUrl" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyUrl()"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route($rp . '.media-share.qrcode', $event->id) }}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Télécharger le QR Code
                        </a>
                        <a href="{{ $publicUrl }}" target="_blank" class="btn btn-outline-success">
                            <i class="fas fa-external-link-alt me-2"></i>Voir l'album public
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Statistiques</h6>
                    <div class="d-flex justify-content-between mb-2"><span><i class="fas fa-image me-2 text-primary"></i>Photos</span><strong>{{ $event->images->count() }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span><i class="fas fa-video me-2 text-danger"></i>Vidéos</span><strong>{{ $event->videos->count() }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span><i class="fas fa-eye me-2 text-warning"></i>Scans / Vues</span><strong>{{ $event->views }}</strong></div>
                    <div class="d-flex justify-content-between"><span><i class="fas fa-download me-2 text-success"></i>Téléchargements</span><strong>{{ $event->files->sum('downloads') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Colonne upload + médias --}}
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-cloud-upload-alt me-2"></i>Ajouter des photos / vidéos</h5>
                    <form action="{{ route($rp . '.media-share.upload', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="files[]" class="form-control" multiple
                                   accept="image/jpeg,image/png,image/jpg,image/webp,image/gif,video/mp4,video/quicktime,video/x-msvideo,video/webm" required>
                            <small class="text-muted">Images (JPG, PNG, WEBP, GIF) et vidéos (MP4, MOV, AVI, WEBM). Max 150 Mo / fichier.</small>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload me-2"></i>Téléverser</button>
                    </form>
                </div>
            </div>

            {{-- Photos --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-image me-2 text-primary"></i>Photos ({{ $event->images->count() }})</h5>
                    @if($event->images->isEmpty())
                        <p class="text-muted mb-0">Aucune photo pour le moment.</p>
                    @else
                        <div class="row g-3">
                            @foreach($event->images as $img)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="position-relative border rounded overflow-hidden" style="aspect-ratio:1/1;">
                                        <img src="{{ asset('storage/' . $img->file_path) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                                        <form action="{{ route($rp . '.media-share.files.destroy', [$event->id, $img->id]) }}" method="POST"
                                              class="position-absolute top-0 end-0 m-1" onsubmit="return confirm('Supprimer ce fichier ?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger py-0 px-1"><i class="fas fa-times"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Vidéos --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-video me-2 text-danger"></i>Vidéos ({{ $event->videos->count() }})</h5>
                    @if($event->videos->isEmpty())
                        <p class="text-muted mb-0">Aucune vidéo pour le moment.</p>
                    @else
                        <div class="row g-3">
                            @foreach($event->videos as $vid)
                                <div class="col-md-6">
                                    <div class="position-relative border rounded overflow-hidden">
                                        <video src="{{ asset('storage/' . $vid->file_path) }}" controls style="width:100%; max-height:220px; background:#000;"></video>
                                        <div class="d-flex justify-content-between align-items-center p-2">
                                            <small class="text-muted text-truncate">{{ $vid->file_name }} · {{ $vid->human_size }}</small>
                                            <form action="{{ route($rp . '.media-share.files.destroy', [$event->id, $vid->id]) }}" method="POST" onsubmit="return confirm('Supprimer cette vidéo ?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyUrl() {
    const input = document.getElementById('publicUrl');
    input.select();
    navigator.clipboard.writeText(input.value);
}
</script>
@endpush
@endsection
