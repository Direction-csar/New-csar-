@extends('layouts.public')

@section('title', __('pages.ressources'))
@section('meta_description', 'Rapports annuels, données SIM, cartographies et FAQ. Espace documentaire du CSAR pour chercheurs, journalistes et partenaires.')
@section('meta_keywords', 'CSAR, ressources, rapports, sécurité alimentaire, résilience, données marché, SIM, Sénégal, FAQ')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff;">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-3">{{ __('messages.ressources.title') }}</h1>
        <p class="lead mb-0">{{ __('messages.ressources.subtitle') }}</p>
    </div>
</section>

<!-- Documents publiés -->
@if($documents->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="h3 mb-4">Documents publiés</h2>
        <div class="row g-4">
            @foreach($documents as $document)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3">
                                <i class="fas fa-file-alt fa-3x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title">{{ $document->title }}</h5>
                                <span class="badge bg-secondary">{{ $document->type }}</span>
                            </div>
                        </div>
                        @if($document->description)
                        <p class="card-text text-muted small">{{ Str::limit($document->description, 100) }}</p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $document->published_at ? $document->published_at->format('d/m/Y') : $document->created_at->format('d/m/Y') }}
                            </small>
                            <a href="{{ $document->file_url }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-download me-1"></i> Télécharger
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Rapports SIM publiés -->
@if(isset($simReports) && $simReports->count() > 0)
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="h3 mb-4">Rapports SIM</h2>
        <div class="row g-4">
            @foreach($simReports as $report)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    @if($report->cover_image)
                    <img src="{{ $report->cover_image_url }}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="{{ $report->title }}">
                    @endif
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3">
                                <i class="fas fa-chart-line fa-3x text-success"></i>
                            </div>
                            <div>
                                <h5 class="card-title">{{ $report->title }}</h5>
                                <span class="badge bg-success">{{ $report->report_type_label }}</span>
                            </div>
                        </div>
                        @if($report->description)
                        <p class="card-text text-muted small">{{ Str::limit($report->description, 100) }}</p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $report->published_at ? $report->published_at->format('d/m/Y') : $report->created_at->format('d/m/Y') }}
                            </small>
                            @if($report->public_download_url)
                            <a href="{{ $report->public_download_url }}" target="_blank" class="btn btn-sm btn-success">
                                <i class="fas fa-download me-1"></i> Télécharger
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Liens vers autres ressources -->
<section class="py-5">
    <div class="container">
        <h2 class="h3 mb-4">Autres ressources</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('reports', ['locale' => app()->getLocale()]) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                            <h3 class="h5">{{ __('messages.ressources.official_reports') }}</h3>
                            <p class="text-muted small mb-0">{{ __('messages.ressources.official_reports_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('sim-reports.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                            <h3 class="h5">{{ __('messages.ressources.sim_reports') }}</h3>
                            <p class="text-muted small mb-0">{{ __('messages.ressources.sim_reports_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('map', ['locale' => app()->getLocale()]) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                            <h3 class="h5">{{ __('messages.ressources.mapping') }}</h3>
                            <p class="text-muted small mb-0">{{ __('messages.ressources.mapping_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('faq.index', ['locale' => app()->getLocale()]) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-question-circle fa-3x text-warning mb-3"></i>
                            <h3 class="h5">{{ __('messages.nav.faq') }}</h3>
                            <p class="text-muted small mb-0">{{ __('messages.ressources.faq_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h3 mb-4">{{ __('messages.ressources.seo_title') }}</h2>
        <p class="text-muted">Ces pages sont optimisées pour les recherches : <strong>sécurité alimentaire Sénégal</strong>, <strong>résilience marché SIM</strong>, <strong>données marché Sénégal</strong>, <strong>partenaires sécurité alimentaire</strong>.</p>
    </div>
</section>
@endsection
