@extends('layouts.admin')

@section('title', 'Rapports de distribution')
@section('page-title', 'Rapports consolidés')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📊 Rapports de distribution</h1>
                    <div class="d-flex gap-2">
                        @if($event)
                        <a href="{{ route('admin.distribution.reports.print', ['event_id' => $event->id]) }}" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>Générer le compte rendu (PDF)</a>
                        <a href="{{ route('admin.distribution.reports.export', ['event_id' => $event->id]) }}" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a>
                        @endif
                        <a href="{{ route('admin.distribution.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Événement</label>
                        <select class="form-select form-select-sm" name="event_id" onchange="this.form.submit()">
                            <option value="">Sélectionner</option>
                            @foreach($events as $id => $name)
                            <option value="{{ $id }}" {{ ($event?->id ?? null) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($event)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-2">📝 Synthèse</h5>
                <p class="small mb-0" style="text-align:justify">{{ $report['text']['summary'] }}</p>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                @include('admin.distribution._report_widgets', ['report' => $report, 'event' => $event, 'print' => false])
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-2">🏁 Conclusion</h5>
                <p class="small mb-0" style="text-align:justify">{{ $report['text']['conclusion'] }}</p>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">🔍 Détail des doublons détectés</h5>
                @if(empty($duplicates))
                <p class="text-muted small mb-0">Aucun doublon détecté.</p>
                @else
                <ul class="list-unstyled">
                    @foreach($duplicates as $dup)
                    <li class="mb-2"><span class="badge bg-danger">DOUBLON</span> <span class="small">{{ $dup['message'] }}</span></li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3 text-center text-muted py-4">Sélectionnez un événement pour voir le rapport.</div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if($event)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endif
@endpush
