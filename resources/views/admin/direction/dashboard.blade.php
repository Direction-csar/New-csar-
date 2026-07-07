@extends('layouts.direction-portal', ['direction' => $direction, 'directionLabel' => $directionLabel])

@section('title', 'Tableau de bord ' . strtoupper($direction))
@section('page-title', 'Tableau de bord')

@section('content')
<div class="container-fluid py-2">
    <h2 class="mb-1">{{ $directionLabel }}</h2>
    <p class="text-muted mb-4">Bienvenue dans votre espace dédié.</p>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(30,58,138,0.1);">
                        <i class="fas fa-archive fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $stats['archives_count'] }}</div>
                        <div class="text-muted small">Documents archivés</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(30,58,138,0.1);">
                        <i class="fas fa-folder fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $stats['folders_count'] }}</div>
                        <div class="text-muted small">Dossiers</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;background:rgba(30,58,138,0.1);">
                        <i class="fas fa-calendar fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $stats['archives_annee'] }}</div>
                        <div class="text-muted small">Ajoutés en {{ now()->year }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('archives.' . $direction . '.index') }}" class="btn btn-primary">
            <i class="fas fa-archive me-2"></i> Accéder aux Archives {{ strtoupper($direction) }}
        </a>
    </div>
</div>
@endsection
