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

<section class="py-5 bg-light">
    <div class="container">
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
