@extends('layouts.dg-modern')

@section('title', 'Rapport DG')
@section('page-title', 'Aperçu Rapport - Vue DG')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h5 mb-1 text-dark fw-bold">
                            <i class="fas fa-file-pdf me-2 text-primary"></i>
                            Rapport
                        </h1>
                        <p class="text-muted mb-0 small">
                            Fichier: <span class="fw-semibold">{{ $filename }}</span> •
                            Taille: {{ $size }} •
                            Date: {{ \Carbon\Carbon::parse($created_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dg.reports.index') }}" class="btn btn-outline-primary btn-modern btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                        <a href="{{ route('dg.reports.download', ['filename' => $filename]) }}" class="btn btn-success-modern btn-modern btn-sm">
                            <i class="fas fa-download me-1"></i>Télécharger
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="alert alert-info mb-0">
                    <strong>Note:</strong> L’aperçu intégré n’est pas activé ici. Utilisez le bouton <strong>Télécharger</strong>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





