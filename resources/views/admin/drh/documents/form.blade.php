@extends('layouts.drh-portal')

@section('title', $docInfo['label'])
@section('page-title', $docInfo['label'])

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4><i class="fas {{ $docInfo['icon'] ?? 'fa-file-alt' }} me-2 text-primary"></i>{{ $docInfo['label'] }}</h4>
        @if($agent)
            <p class="text-muted mb-0">Agent : <strong>{{ $agent->prenoms_nom }}</strong></p>
        @else
            <p class="text-muted mb-0">Aucun agent sélectionné. <a href="{{ route('admin.drh.documents') }}">Choisir un agent</a></p>
        @endif
    </div>
    <a href="{{ route('admin.drh.documents') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        @if(is_array($fields) && count($fields) > 0)
            <form action="{{ route('admin.drh.documents.save', $type) }}" method="POST">
                @csrf
                <div class="row g-3">
                    @foreach($fields as $field)
                        @php
                            $label = str_replace('_', ' ', $field);
                            $label = ucfirst($label);
                            $value = $savedData[$field] ?? '';
                            $isDate = str_contains($field, 'date');
                            $isNumber = str_contains($field, 'salaire') || str_contains($field, 'montant') || str_contains($field, 'nombre') || str_contains($field, 'duree') || str_contains($field, 'taux');
                            $isTextarea = str_contains($field, 'motif') || str_contains($field, 'observations') || str_contains($field, 'biens') || str_contains($field, 'sanction');
                        @endphp
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ $label }}</label>
                            @if($isTextarea)
                                <textarea name="{{ $field }}" rows="3" class="form-control">{{ old($field, $value) }}</textarea>
                            @elseif($isDate)
                                <input type="date" name="{{ $field }}" value="{{ old($field, $value) }}" class="form-control">
                            @elseif($isNumber)
                                <input type="number" name="{{ $field }}" value="{{ old($field, $value) }}" class="form-control" step="any">
                            @else
                                <input type="text" name="{{ $field }}" value="{{ old($field, $value) }}" class="form-control">
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 d-flex gap-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                    @if($agent)
                        <a href="{{ route('admin.drh.documents.' . $type) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i>Générer PDF
                        </a>
                    @endif
                </div>
            </form>
        @else
            <div class="text-center py-5">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <p class="text-muted">Ce document ne nécessite pas de saisie de champs supplémentaires.</p>
                @if($agent)
                    <a href="{{ route('admin.drh.documents.certificat-travail') }}" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-2"></i>Générer le PDF
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@endsection
