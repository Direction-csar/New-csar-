@extends('layouts.drh-portal')

@section('title', 'Documents RH')
@section('page-title', 'Documents RH')

@section('styles')
<style>
    .doc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    @media (max-width: 992px) { .doc-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .doc-grid { grid-template-columns: 1fr; } }
    .doc-card { background: white; border-radius: 12px; padding: 24px 16px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; transition: transform 0.15s, box-shadow 0.15s; cursor: pointer; text-decoration: none; color: #1e293b; display: block; }
    .doc-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); color: #1e293b; }
    .doc-card i { font-size: 2rem; margin-bottom: 12px; display: block; }
    .doc-card .doc-label { font-weight: 600; font-size: 0.9rem; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="fas fa-file-alt me-2 text-primary"></i>Générer un document RH</h4>
    <a href="{{ route('admin.drh.documents.historique') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-history me-1"></i> Historique
    </a>
</div>

{{-- Sélection agent --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.drh.documents') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Sélectionner un agent</label>
                <select name="personnel_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choisir un agent --</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ session('documents_personnel_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->prenoms_nom }} — {{ $a->poste_actuel ?? 'Sans poste' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check me-1"></i> Confirmer</button>
            </div>
        </form>
    </div>
</div>

@if(session('documents_personnel_id'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-check-circle me-2"></i> Agent sélectionné. Choisissez un document ci-dessous.
    </div>
@endif

{{-- Grille des documents --}}
<div class="doc-grid">
    @foreach($types as $type)
        <a href="{{ route('admin.drh.documents.form', $type['slug']) }}" class="doc-card">
            <i class="fas {{ $type['icon'] }}" style="color:{{ $type['color'] }}"></i>
            <div class="doc-label">{{ $type['label'] }}</div>
        </a>
    @endforeach
</div>

@endsection
