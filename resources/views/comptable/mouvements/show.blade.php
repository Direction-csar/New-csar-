@extends('layouts.direction-portal')

@section('title', 'Mouvement ' . $mouvement->reference . ' - Comptable des matières - CSAR')
@section('page-title', 'Mouvement ' . $mouvement->reference)

@section('content')
@php
    $meta = json_decode($mouvement->metadata ?? '{}', true);
    $statusBadge = match($mouvement->status ?? 'draft') {
        'validated' => ['bg-success', 'Validé'],
        'rejected'  => ['bg-danger', 'Rejeté'],
        default     => ['bg-warning text-dark', 'En attente de validation'],
    };
@endphp

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Détail du mouvement</strong>
                <span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Référence</dt>
                    <dd class="col-sm-8"><code>{{ $mouvement->reference }}</code></dd>

                    <dt class="col-sm-4">Type</dt>
                    <dd class="col-sm-8">{{ $mouvement->type_formatted ?? ucfirst($mouvement->type) }}</dd>

                    <dt class="col-sm-4">Magasin</dt>
                    <dd class="col-sm-8">{{ $mouvement->warehouse?->name ?? '-' }}@if($mouvement->warehouse?->region) <small class="text-muted">({{ $mouvement->warehouse->region }})</small>@endif</dd>

                    <dt class="col-sm-4">Quantité</dt>
                    <dd class="col-sm-8">
                        {{ number_format((float) ($meta['entry_sacs'] ?? $meta['exit_sacs'] ?? $mouvement->quantity), 0, ',', ' ') }} contenants
                        @if(($meta['entry_kg'] ?? $meta['exit_kg'] ?? null))
                            / {{ number_format((float) ($meta['entry_kg'] ?? $meta['exit_kg']), 0, ',', ' ') }} kg
                        @endif
                    </dd>

                    <dt class="col-sm-4">Motif / Libellé</dt>
                    <dd class="col-sm-8">{{ $mouvement->reason ?? ($meta['observation'] ?? '-') }}</dd>

                    <dt class="col-sm-4">Saisi par</dt>
                    <dd class="col-sm-8">{{ $mouvement->creator?->name ?? '-' }} <small class="text-muted">({{ $mouvement->creator?->role ?? '' }})</small></dd>

                    <dt class="col-sm-4">Date</dt>
                    <dd class="col-sm-8">{{ $mouvement->created_at?->format('d/m/Y H:i') }}</dd>

                    @if($mouvement->validated_at)
                    <dt class="col-sm-4">Traité le</dt>
                    <dd class="col-sm-8">{{ $mouvement->validated_at->format('d/m/Y H:i') }}</dd>
                    @endif

                    @if($mouvement->status === 'rejected' && $mouvement->rejection_reason)
                    <dt class="col-sm-4">Motif du rejet</dt>
                    <dd class="col-sm-8 text-danger">{{ $mouvement->rejection_reason }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if($mission)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>Mission liée (DTL)</strong></div>
            <div class="card-body">
                <p class="mb-1"><span class="badge bg-info">{{ $mission->type_label }}</span> <span class="badge bg-secondary">{{ $mission->status_label }}</span></p>
                <p class="mb-1 small text-muted">Dossier : <code>{{ $mission->request?->tracking_code ?? '-' }}</code> — {{ $mission->request?->full_name ?? '' }}</p>
                @if($mission->instructions)
                    <p class="mb-0 small">{{ $mission->instructions }}</p>
                @endif
                @if($mission->report)
                    <hr><p class="mb-0 small"><strong>Rapport magasinier :</strong> {{ \Illuminate\Support\Str::limit($mission->report, 300) }}</p>
                @endif
            </div>
        </div>
        @endif

        @if($validationEnabled)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Décision comptable</strong></div>
            <div class="card-body">
                @if($mouvement->status === 'validated')
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i>Mouvement validé — écriture gelée.</p>
                @else
                    <form method="POST" action="{{ route('comptable.mouvements.valider', $mouvement->id) }}" class="mb-3"
                          onsubmit="return confirm('Valider ce mouvement ? Il sera gelé et ne pourra plus être rejeté.')">
                        @csrf
                        <button class="btn btn-success w-100"><i class="fas fa-check me-1"></i>Valider le mouvement</button>
                    </form>

                    @if($mouvement->status !== 'rejected')
                    <form method="POST" action="{{ route('comptable.mouvements.rejeter', $mouvement->id) }}"
                          onsubmit="return confirm('Rejeter ce mouvement ? Le motif sera enregistré.')">
                        @csrf
                        <label class="form-label small">Motif du rejet <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" rows="3" class="form-control form-control-sm mb-2" required minlength="10" placeholder="Expliquer pourquoi ce mouvement est rejeté…">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        <button class="btn btn-outline-danger w-100"><i class="fas fa-times me-1"></i>Rejeter</button>
                    </form>
                    @else
                        <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>Mouvement déjà rejeté. Corrigez-le côté magasin avant nouvelle soumission.</p>
                    @endif
                @endif
            </div>
        </div>
        @endif

        <a href="{{ route('comptable.mouvements.index') }}" class="btn btn-outline-secondary btn-sm mt-3">
            <i class="fas fa-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>
</div>
@endsection
