@extends('layouts.public')

@section('title', $simReport->title . ' - Rapports SIM')

@section('content')
@php
    $cover = $simReport->cover_image
        ? asset('storage/' . $simReport->cover_image)
        : asset('images/sim-default-cover.jpg');
@endphp

<!-- Hero avec couverture -->
<section class="sim-hero" style="background-image:url('{{ $cover }}')">
    <div class="sim-hero-overlay"></div>
    <div class="container">
        <div class="sim-hero-content">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge sim-type">{{ $simReport->report_type_label }}</span>
                <span class="badge bg-success-subtle text-success">{{ $simReport->sector_label }}</span>
            </div>
            <h1 class="display-6 mb-2">{{ $simReport->title }}</h1>
            <p class="text-white-50 mb-0">{{ $simReport->description }}</p>
        </div>
    </div>
</section>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Période :</strong> {{ $simReport->formatted_period }}</p>
                            <p class="mb-2"><strong>Région :</strong> {{ $simReport->region ?: 'Toutes' }}</p>
                            <p class="mb-0"><strong>Publié le :</strong> {{ $simReport->published_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Généré par :</strong> {{ $simReport->generator ? $simReport->generator->name : 'Système' }}</p>
                            <p class="mb-2"><strong>Vues :</strong> {{ $simReport->view_count }}</p>
                            <p class="mb-0"><strong>Téléchargements :</strong> {{ $simReport->download_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2 mb-4">
                <a href="{{ route('sim.download', $simReport->id) }}" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>
                    Télécharger le rapport
                </a>
                <a href="{{ route('sim.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour à la liste
                </a>
            </div>

            <!-- Contenu du rapport -->
            <div class="row">
                <div class="col-12">
                    @if($simReport->document_file)
                        <div class="card mb-4">
                            <div class="card-body d-flex align-items-center gap-3">
                                <img src="{{ $cover }}" alt="Couverture" class="rounded shadow-sm" style="width:120px;height:160px;object-fit:cover;">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Bulletin PDF</h5>
                                    <p class="text-muted mb-3">Prévisualisez ou téléchargez le bulletin officiel.</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('sim.view', $simReport->id) }}" target="_blank" class="btn btn-success">
                                            <i class="fas fa-eye me-1"></i> Ouvrir le PDF
                                        </a>
                                        <a href="{{ route('sim.download', $simReport->id) }}" class="btn btn-outline-success">
                                            <i class="fas fa-download me-1"></i> Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- Sections Résumé Exécutif et Recommandations masquées sur la vue publique --}}

                    {{-- Section Données détaillées retirée pour une vue publique plus simple --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sim-hero{position:relative;min-height:280px;background-size:cover;background-position:center}
.sim-hero-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.35),rgba(0,0,0,.55))}
.sim-hero .container{position:relative;z-index:2}
.sim-hero-content{padding:3rem 0;color:#fff}
.sim-type{background:#ede9fe;color:#6d28d9;font-weight:700;border-radius:999px;padding:.35rem .75rem}
.whitespace-pre-line {
    white-space: pre-line;
}

/* Responsive SIM Show */
@media (max-width: 768px) {
    .sim-hero { min-height: 200px; }
    .sim-hero-content { padding: 2rem 0; }
    .sim-hero-content h1 { font-size: 1.5rem !important; }
    .sim-hero-content p { font-size: 0.9rem; }
    .d-flex.gap-2 { flex-direction: column; }
    .d-flex.gap-2 .btn { width: 100%; justify-content: center; }
    .card-body .row .col-md-6 { margin-bottom: 1rem; }
}

@media (max-width: 480px) {
    .sim-hero { min-height: 180px; }
    .sim-hero-content { padding: 1.5rem 0; }
    .card-body { padding: 1rem !important; }
    .card-body img { width: 80px !important; height: 106px !important; }
}
</style>
@endsection