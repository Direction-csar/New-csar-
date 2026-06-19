<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }} — CSAR</title>
    <link rel="icon" type="image/png" href="{{ asset('images/csar-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:'Segoe UI',Tahoma,sans-serif; }
        .album-hero {
            background: linear-gradient(135deg,#1e293b 0%,#0f172a 100%);
            padding: 2.5rem 1rem; text-align:center; border-bottom:1px solid rgba(255,255,255,.08);
        }
        .album-hero img.logo { width:54px; height:54px; border-radius:12px; margin-bottom:1rem; }
        .album-cover { width:100%; max-height:280px; object-fit:cover; }
        .media-card { background:#1e293b; border:1px solid rgba(255,255,255,.06); border-radius:14px; overflow:hidden; transition:transform .2s; }
        .media-card:hover { transform:translateY(-4px); }
        .media-thumb { width:100%; aspect-ratio:1/1; object-fit:cover; cursor:pointer; }
        .section-title { border-left:4px solid #38bdf8; padding-left:.75rem; }
        .btn-zip { border-radius:30px; }
        .lightbox {
            position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:2000;
            display:none; align-items:center; justify-content:center; flex-direction:column;
        }
        .lightbox.show { display:flex; }
        .lightbox img { max-width:92vw; max-height:80vh; border-radius:8px; }
        .lightbox .close-btn { position:absolute; top:18px; right:24px; font-size:2rem; color:#fff; cursor:pointer; }
        .lightbox .dl-btn { margin-top:1rem; }
        footer { padding:2rem 1rem; text-align:center; color:#64748b; font-size:.85rem; }
    </style>
</head>
<body>
    <div class="album-hero">
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" class="logo" onerror="this.style.display='none'">
        <h1 class="fw-bold mb-1">{{ $event->title }}</h1>
        @if($event->event_date)
            <p class="text-info mb-2"><i class="far fa-calendar me-1"></i>{{ $event->event_date->translatedFormat('d F Y') }}</p>
        @endif
        @if($event->description)
            <p class="text-white-50 mb-0 mx-auto" style="max-width:640px;">{{ $event->description }}</p>
        @endif
    </div>

    @if($event->cover_image)
        <img src="{{ asset('storage/' . $event->cover_image) }}" alt="" class="album-cover">
    @endif

    <div class="container py-5">
        @if($event->images->isEmpty() && $event->videos->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-secondary mb-3"></i>
                <p class="text-white-50">Aucun média disponible pour le moment.</p>
            </div>
        @endif

        {{-- Photos --}}
        @if($event->images->isNotEmpty())
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="section-title mb-0"><i class="fas fa-images me-2"></i>Photos ({{ $event->images->count() }})</h4>
                <a href="{{ route('public.media.zip', [$event->slug, 'images']) }}" class="btn btn-info btn-zip">
                    <i class="fas fa-file-archive me-2"></i>Tout télécharger (ZIP)
                </a>
            </div>
            <div class="row g-3 mb-5">
                @foreach($event->images as $img)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="media-card">
                            <img src="{{ asset('storage/' . $img->file_path) }}" class="media-thumb"
                                 onclick="openLightbox('{{ asset('storage/' . $img->file_path) }}', '{{ route('public.media.download', [$event->slug, $img->id]) }}')" alt="">
                            <div class="p-2 text-center">
                                <a href="{{ route('public.media.download', [$event->slug, $img->id]) }}" class="btn btn-sm btn-outline-info w-100">
                                    <i class="fas fa-download me-1"></i>Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Vidéos --}}
        @if($event->videos->isNotEmpty())
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="section-title mb-0"><i class="fas fa-video me-2"></i>Vidéos ({{ $event->videos->count() }})</h4>
                <a href="{{ route('public.media.zip', [$event->slug, 'videos']) }}" class="btn btn-info btn-zip">
                    <i class="fas fa-file-archive me-2"></i>Tout télécharger (ZIP)
                </a>
            </div>
            <div class="row g-3">
                @foreach($event->videos as $vid)
                    <div class="col-md-6 col-lg-4">
                        <div class="media-card">
                            <video src="{{ asset('storage/' . $vid->file_path) }}" controls style="width:100%; aspect-ratio:16/9; background:#000;"></video>
                            <div class="p-2 text-center">
                                <a href="{{ route('public.media.download', [$event->slug, $vid->id]) }}" class="btn btn-sm btn-outline-info w-100">
                                    <i class="fas fa-download me-1"></i>Télécharger ({{ $vid->human_size }})
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <footer>
        <img src="{{ asset('images/csar-logo.png') }}" alt="" style="height:28px; opacity:.6;" onerror="this.style.display='none'"><br>
        Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)
    </footer>

    {{-- Lightbox --}}
    <div class="lightbox" id="lightbox">
        <span class="close-btn" onclick="closeLightbox()">&times;</span>
        <img id="lightboxImg" src="" alt="">
        <a id="lightboxDl" href="#" class="btn btn-info dl-btn"><i class="fas fa-download me-2"></i>Télécharger</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openLightbox(src, dl) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightboxDl').href = dl;
            document.getElementById('lightbox').classList.add('show');
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('show');
        }
        document.getElementById('lightbox').addEventListener('click', function(e){
            if (e.target === this) closeLightbox();
        });
    </script>
</body>
</html>
