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
    <div class="d-flex gap-2">
        <form action="{{ route('admin.drh.contrats.generate-all') }}" method="POST"
              onsubmit="return confirm('Générer les contrats de tous les agents et télécharger l\'archive ZIP ? Cela peut prendre une à deux minutes.');">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-file-archive me-1"></i> Générer tous les contrats (ZIP)
            </button>
        </form>
        <form action="{{ route('admin.drh.contrats.pdf-unique') }}" method="POST" class="d-flex gap-1"
              onsubmit="return confirm('Générer tous les contrats dans un seul PDF pour impression ? Cela peut prendre plusieurs minutes.');">
            @csrf
            <select name="pages" class="form-select form-select-sm" style="width:auto;" title="Pages à imprimer">
                <option value="all">Pages 1 et 2</option>
                <option value="first">Page 1 seulement (sans la 2e page)</option>
            </select>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-print me-1"></i> Contrats (PDF unique)
            </button>
        </form>
        <form action="{{ route('admin.drh.documents.dmt.generate-all') }}" method="POST"
              onsubmit="return confirm('Générer les déclarations de mouvement de tous les agents dans un seul PDF ? Cela peut prendre plusieurs minutes.');">
            @csrf
            <button type="submit" class="btn btn-info btn-sm">
                <i class="fas fa-file-pdf me-1"></i> Générer toutes les DMT (PDF unique)
            </button>
        </form>
        <a href="{{ route('admin.drh.documents.historique') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-history me-1"></i> Historique
        </a>
    </div>
</div>

{{-- Sélection agent --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.drh.documents') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Sélectionner un agent</label>
                <select name="personnel_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choisir un agent --</option>
                    <option value="__all__" {{ ($tousAgents ?? false) ? 'selected' : '' }}>-- Tous les agents --</option>
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

@if($tousAgents ?? false)
    <div class="alert alert-info mb-4">
        <i class="fas fa-users me-2"></i><strong>Tous les agents</strong> — génération groupée : chaque document est créé pour chaque agent (les existants sont conservés) puis un <strong>PDF unique</strong> est téléchargé pour impression.
    </div>
    <div class="doc-grid mb-4">
        @foreach([
            ['slug' => 'certificat-travail', 'label' => 'Certificat de travail', 'icon' => 'fa-file-check', 'color' => '#2f6fed'],
            ['slug' => 'attestation-travail', 'label' => 'Attestation de travail', 'icon' => 'fa-file-lines', 'color' => '#0b7f81'],
            ['slug' => 'attestation-travail-salaire', 'label' => 'Attestation travail & salaire', 'icon' => 'fa-file-invoice-dollar', 'color' => '#0b7f81'],
        ] as $t)
            <form action="{{ route('admin.drh.documents.generate-all', $t['slug']) }}" method="POST" class="doc-card" style="cursor:pointer;"
                  onsubmit="return confirm('Générer « {{ $t['label'] }} » pour TOUS les agents dans un seul PDF ? Cela peut prendre plusieurs minutes.');">
                @csrf
                <button type="submit" class="btn btn-link p-0 text-decoration-none w-100" style="color:#1e293b;">
                    <i class="fas {{ $t['icon'] }}" style="color:{{ $t['color'] }}"></i>
                    <div class="doc-label">{{ $t['label'] }}<br><small class="text-muted">(tous les agents)</small></div>
                </button>
            </form>
        @endforeach
        <form action="{{ route('admin.drh.documents.dmt.generate-all') }}" method="POST" class="doc-card" style="cursor:pointer;"
              onsubmit="return confirm('Générer les déclarations de mouvement de TOUS les agents dans un seul PDF ? Cela peut prendre plusieurs minutes.');">
            @csrf
            <button type="submit" class="btn btn-link p-0 text-decoration-none w-100" style="color:#1e293b;">
                <i class="fas fa-file-signature" style="color:#1e40af"></i>
                <div class="doc-label">Déclaration de mouvement<br><small class="text-muted">(tous les agents)</small></div>
            </button>
        </form>
    </div>
@else
{{-- Grille des documents --}}
<div class="doc-grid">
    @foreach($types as $type)
        <a href="{{ route('admin.drh.documents.form', $type['slug']) }}" class="doc-card">
            <i class="fas {{ $type['icon'] }}" style="color:{{ $type['color'] }}"></i>
            <div class="doc-label">{{ $type['label'] }}</div>
        </a>
    @endforeach
</div>
@endif

@endsection
