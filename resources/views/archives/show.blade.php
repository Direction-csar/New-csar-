@extends($layout ?? 'layouts.admin', ['direction' => $direction])

@section('title', $archive->reference . ' - ' . $archive->title)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4><i class="fas fa-file-pdf text-danger"></i> {{ $archive->title }}</h4>
            <div class="text-muted small">
                <code>{{ $archive->reference }}</code> &bull; {{ $archive->direction }} &bull; {{ $archive->annee }}
                @if($archive->page_count) &bull; {{ $archive->page_count }} pages @endif
                &bull; {{ number_format($archive->file_size / 1024, 2) }} KB
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('archives.' . strtolower($direction) . '.download', $archive) }}" class="btn btn-secondary">
                <i class="fas fa-download"></i> Télécharger
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Panneau sélection de pages (PDF uniquement) --}}
        <div class="col-lg-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-print"></i> Impression sélective
                </div>
                <div class="card-body">
                    @if($archive->mime_type === 'application/pdf' && $archive->page_count)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pages à imprimer :</label>
                            <input type="text" id="page-range" class="form-control form-control-sm" placeholder="ex: 1,5,10-15,20">
                            <div class="form-text">
                                Formats: <code>1,3,5</code> ou <code>10-20</code> ou <code>1,5-10,20</code>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button onclick="printSelected()" class="btn btn-primary btn-sm">
                                <i class="fas fa-print"></i> Imprimer la sélection
                            </button>
                            <button onclick="printAll()" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-print"></i> Tout imprimer
                            </button>
                        </div>
                        <hr>
                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle"></i> Document de <strong>{{ $archive->page_count }}</strong> pages.
                        </div>
                    @else
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle"></i> L'impression sélective n'est disponible que pour les fichiers PDF.
                        </div>
                        <a href="{{ route('archives.' . strtolower($direction) . '.download', $archive) }}" class="btn btn-primary w-100">
                            <i class="fas fa-download"></i> Télécharger pour imprimer
                        </a>
                    @endif
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <i class="fas fa-info-circle"></i> Informations
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Créé par</span>
                        <span class="fw-bold">{{ $archive->creator?->name ?? 'Inconnu' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Date</span>
                        <span>{{ $archive->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Type</span>
                        <span>{{ $archive->mime_type ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Taille</span>
                        <span>{{ number_format($archive->file_size / 1024, 2) }} KB</span>
                    </li>
                    @if($archive->folder)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Dossier</span>
                            <span class="fw-bold">{{ $archive->folder->name }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Visionneuse --}}
        <div class="col-lg-9">
            <div class="card shadow-sm" style="height: 85vh;">
                <div class="card-body p-0" style="height: 100%;">
                    @if($archive->mime_type === 'application/pdf')
                        <iframe
                            id="pdf-viewer"
                            src="{{ asset('storage/' . $archive->file_path) }}#toolbar=1&navpanes=1"
                            width="100%"
                            height="100%"
                            style="border: none;"
                        ></iframe>
                    @elseif(str_starts_with($archive->mime_type ?? '', 'image/'))
                        <div class="d-flex justify-content-center align-items-center h-100 bg-dark">
                            <img src="{{ asset('storage/' . $archive->file_path) }}" class="img-fluid" style="max-height: 100%;" alt="{{ $archive->title }}">
                        </div>
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted">
                            <i class="fas fa-file-alt fa-4x mb-3"></i>
                            <p>Ce type de fichier ne peut pas être prévisualisé directement.</p>
                            <a href="{{ route('archives.' . strtolower($direction) . '.download', $archive) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Télécharger le fichier
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printSelected() {
    const range = document.getElementById('page-range').value.trim();
    if (!range) {
        alert('Veuillez saisir les pages à imprimer.');
        return;
    }
    window.open(
        '{{ route("archives." . strtolower($direction) . ".print", $archive) }}?pages=' + encodeURIComponent(range),
        '_blank'
    );
}

function printAll() {
    window.open(
        '{{ route("archives." . strtolower($direction) . ".print", $archive) }}',
        '_blank'
    );
}
</script>
@endsection
