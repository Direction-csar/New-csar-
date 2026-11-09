@extends('layouts.drh-portal')

@section('title', 'Contrats de travail')
@section('page-title', 'Contrats de travail')

@php
    use App\Models\RhDocument;
    $money = fn ($v) => $v === null ? '—' : number_format((float) $v, 0, ',', ' ');
@endphp

@section('content')
<div class="row g-3 mb-3">
    @foreach([
        ['Agents', $stats['agents'], 'dark', 'fa-users'],
        ['Avec contrat', $stats['avec_contrat'], 'primary', 'fa-file-contract'],
        ['Contrats manquants', $stats['manquants'], 'danger', 'fa-file-circle-exclamation'],
        ['Signés', $stats['signes'], 'success', 'fa-signature'],
        ['Avenants à valider', $stats['avenants'], 'warning', 'fa-hourglass-half'],
    ] as [$label, $value, $color, $icon])
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                <div class="small text-muted"><i class="fas {{ $icon }} me-1"></i>{{ $label }}</div>
                <div class="fs-4 fw-bold text-{{ $color }}">{{ $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="alert alert-info small">
    <i class="fas fa-shield-alt me-1"></i>
    Un contrat <strong>signé</strong> n'est jamais écrasé : si les données de l'agent changent, une nouvelle version
    « avenant à valider » est créée et la DRH décide de la mettre en vigueur. Toutes les versions restent archivées.
</div>

<form id="contrats-form" method="POST">
    @csrf
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            <button formaction="{{ route('admin.drh.contrats.generate') }}" name="mode" value="missing" class="btn btn-primary"
                    onclick="return confirm('Générer les contrats manquants ?')">
                <i class="fas fa-plus me-1"></i> Générer les manquants ({{ $stats['manquants'] }})
            </button>
            <button formaction="{{ route('admin.drh.contrats.generate') }}" name="mode" value="selection" class="btn btn-outline-primary">
                <i class="fas fa-check-square me-1"></i> Générer la sélection
            </button>
            <button formaction="{{ route('admin.drh.contrats.generate') }}" name="mode" value="all" class="btn btn-outline-dark"
                    onclick="return confirm('Actualiser les contrats de TOUS les agents ? Les contrats non signés modifiés auront une nouvelle version ; les contrats signés recevront un avenant à valider.')">
                <i class="fas fa-sync me-1"></i> Actualiser tous
            </button>
            <button formaction="{{ route('admin.drh.contrats.zip') }}" class="btn btn-success ms-md-auto" title="Sans sélection : tous les contrats en vigueur">
                <i class="fas fa-file-archive me-1"></i> Télécharger en ZIP
            </button>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="check-all" form="contrats-form">
            <label class="form-check-label small" for="check-all">Tout sélectionner</label>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nom ou matricule">
            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle small">
            <thead class="table-light">
                <tr><th></th><th>Agent</th><th>Type</th><th class="text-end">Brut actuel</th><th>Contrat en vigueur</th><th>Versions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                    @php
                        $docs = $documents->get($agent->id, collect());
                        $current = $docs->whereNull('superseded_at')->where('statut', '!=', RhDocument::STATUT_A_VALIDER)->first();
                        $pending = $docs->whereNull('superseded_at')->where('statut', RhDocument::STATUT_A_VALIDER)->first();
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check-input agent-check" name="agents[]" value="{{ $agent->id }}" form="contrats-form"></td>
                        <td><strong>{{ $agent->prenoms_nom }}</strong><br><span class="text-muted">{{ $agent->matricule }} · {{ $agent->poste_actuel }}</span></td>
                        <td>{{ strtoupper($agent->type_contrat ?: 'CDI') }}</td>
                        <td class="text-end">{{ $money($agent->salaire_brut) }}</td>
                        <td>
                            @if($current)
                                <span class="badge {{ $current->isSigned() ? 'bg-success' : 'bg-primary' }}">v{{ $current->version }} · {{ $current->statut }}</span>
                                <div class="text-muted">{{ $current->created_at?->format('d/m/Y') }}
                                    @if($current->isSigned()) · signé le {{ $current->signed_at->format('d/m/Y') }} @endif</div>
                            @else
                                <span class="badge bg-danger">Aucun contrat</span>
                            @endif
                            @if($pending)
                                <div class="mt-1"><span class="badge bg-warning text-dark">Avenant v{{ $pending->version }} à valider</span></div>
                            @endif
                        </td>
                        <td>
                            @foreach($docs as $doc)
                                <a href="{{ route('admin.drh.contrats.download', [$doc, 'inline' => 1]) }}" target="_blank"
                                   class="badge text-decoration-none {{ $doc->superseded_at ? 'bg-light text-muted border' : 'bg-light text-dark border' }}"
                                   title="{{ $doc->statut }} — {{ $doc->created_at?->format('d/m/Y H:i') }}">v{{ $doc->version }}</a>
                            @endforeach
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.drh.contrats.preview', $agent) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Aperçu avec les données actuelles">
                                <i class="fas fa-eye"></i></a>
                            @if($current)
                                <a href="{{ route('admin.drh.contrats.download', $current) }}" class="btn btn-sm btn-outline-primary" title="Télécharger"><i class="fas fa-download"></i></a>
                                @unless($current->isSigned())
                                    <form action="{{ route('admin.drh.contrats.sign', $current) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Confirmer que la version v{{ $current->version }} a été signée ? Elle ne pourra plus être modifiée.')">
                                        @csrf<button class="btn btn-sm btn-outline-success" title="Marquer signé"><i class="fas fa-signature"></i></button>
                                    </form>
                                @endunless
                            @endif
                            @if($pending)
                                <form action="{{ route('admin.drh.contrats.validate', $pending) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Valider l\'avenant v{{ $pending->version }} ? Il deviendra le contrat en vigueur.')">
                                    @csrf<button class="btn btn-sm btn-warning" title="Valider l'avenant"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun agent.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('check-all')?.addEventListener('change', e => {
    document.querySelectorAll('.agent-check').forEach(c => c.checked = e.target.checked);
});
</script>
@endsection
