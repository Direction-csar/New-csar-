@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@php $rp = request()->routeIs('ctc.*') ? 'ctc' : 'admin'; @endphp

@section('title', 'QR Media Share')
@section('page-title', 'Partage Média par QR Code')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0 {{ $rp === 'ctc' ? 'text-white' : '' }}">
                <i class="fas fa-qrcode me-2"></i>QR Media Share
            </h1>
            <p class="{{ $rp === 'ctc' ? 'text-white-50' : 'text-muted' }} mb-0">
                Créez des albums photos/vidéos accessibles via QR Code
            </p>
        </div>
        <a href="{{ route($rp . '.media-share.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouvel événement
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left: 4px solid #0d6efd;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold">Événements</div>
                        <div class="h4 mb-0">{{ $stats['total_events'] }}</div>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x text-primary opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left: 4px solid #198754;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold">Médias</div>
                        <div class="h4 mb-0">{{ $stats['total_medias'] }}</div>
                    </div>
                    <i class="fas fa-photo-film fa-2x text-success opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left: 4px solid #fd7e14;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold">Scans / Vues</div>
                        <div class="h4 mb-0">{{ $stats['total_views'] }}</div>
                    </div>
                    <i class="fas fa-eye fa-2x text-warning opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-left: 4px solid #6f42c1;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted text-uppercase small fw-bold">Téléchargements</div>
                        <div class="h4 mb-0">{{ $stats['total_downloads'] }}</div>
                    </div>
                    <i class="fas fa-download fa-2x" style="color:#6f42c1; opacity:.25;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Recherche --}}
    <form method="GET" class="mb-4">
        <div class="input-group" style="max-width: 420px;">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher un événement...">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>

    {{-- Liste --}}
    <div class="row">
        @forelse($events as $event)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div style="height: 170px; background:#f1f5f9; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                        @if($event->cover_image)
                            <img src="{{ asset('storage/' . $event->cover_image) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <i class="fas fa-images fa-3x text-secondary opacity-25"></i>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1">{{ $event->title }}</h5>
                            <span class="badge {{ $event->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $event->status === 'active' ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        @if($event->event_date)
                            <p class="text-muted small mb-2"><i class="far fa-calendar me-1"></i>{{ $event->event_date->format('d/m/Y') }}</p>
                        @endif
                        <div class="d-flex gap-3 text-muted small mb-3">
                            <span><i class="fas fa-image me-1"></i>{{ $event->images_count }}</span>
                            <span><i class="fas fa-video me-1"></i>{{ $event->videos_count }}</span>
                            <span><i class="fas fa-eye me-1"></i>{{ $event->views }}</span>
                            <span><i class="fas fa-download me-1"></i>{{ $event->downloads_count }}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route($rp . '.media-share.show', $event->id) }}" class="btn btn-sm btn-primary flex-fill">
                                <i class="fas fa-qrcode me-1"></i>Gérer
                            </a>
                            <a href="{{ route('public.media.album', $event->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form action="{{ route($rp . '.media-share.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Supprimer cet événement et tous ses médias ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-qrcode fa-3x text-muted opacity-25 mb-3"></i>
                        <h5>Aucun événement pour le moment</h5>
                        <p class="text-muted">Créez votre premier album photos/vidéos partageable par QR Code.</p>
                        <a href="{{ route($rp . '.media-share.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Nouvel événement
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
