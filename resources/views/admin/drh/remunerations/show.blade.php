@extends('layouts.drh-portal')

@section('title', 'Prévisualisation des rémunérations')
@section('page-title', 'Prévisualisation des rémunérations')

@php
    $s = $import->summary ?? [];
    $by = $s['by_status'] ?? [];
    $money = fn ($v) => $v === null ? '—' : number_format((float) $v, 0, ',', ' ');
    $statusLabels = [
        'modifie' => ['Modifié', 'primary'], 'inchange' => ['Inchangé', 'secondary'],
        'non_trouve' => ['Non retrouvé', 'danger'], 'sans_matricule' => ['Sans matricule', 'warning'],
        'doublon_fichier' => ['Doublon fichier', 'danger'],
    ];
    $warningLabels = [
        'nom_different' => 'Nom différent de la fiche (bloquant)', 'conflit_feuilles' => 'Feuilles contradictoires (bloquant)',
        'baisse_brut' => 'Baisse du salaire brut', 'brut_incoherent' => 'Brut ≠ base + sursalaire + indemnité',
    ];
    $colLabels = [
        'poste_actuel' => 'Poste', 'categorie' => 'Catégorie', 'salaire_base' => 'Base', 'sursalaire' => 'Sursalaire',
        'indemnite_fonction' => 'Ind. fonction', 'indemnite_transport' => 'Ind. transport', 'salaire_brut' => 'Brut', 'net_a_payer' => 'Net',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-0">{{ $import->filename }}</h5>
        <small class="text-muted">Feuille utilisée : « {{ $s['sheet'] ?? '—' }} » · analysé le {{ $import->created_at->format('d/m/Y H:i') }}
            @if($import->isApplied()) · enregistré le {{ $import->applied_at?->format('d/m/Y H:i') }} par {{ $import->applier?->name }} @endif
        </small>
    </div>
    <a href="{{ route('admin.drh.remunerations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['Agents dans le fichier', $s['total'] ?? 0, 'dark'],
        ['À mettre à jour', $s['applicable'] ?? 0, 'primary'],
        ['Inchangés', $by['inchange'] ?? 0, 'secondary'],
        ['Non retrouvés', $by['non_trouve'] ?? 0, 'danger'],
        ['Sans matricule', $by['sans_matricule'] ?? 0, 'warning'],
        ['Bloqués (à vérifier)', $s['blocked'] ?? 0, 'danger'],
    ] as [$label, $value, $color])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                <div class="small text-muted">{{ $label }}</div>
                <div class="fs-4 fw-bold text-{{ $color }}">{{ $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-4 align-items-center">
        <div><span class="text-muted small">Masse brute mensuelle (agents retrouvés)</span><br>
            <strong>{{ $money($s['masse_brute_avant'] ?? 0) }} F</strong> → <strong>{{ $money($s['masse_brute_apres'] ?? 0) }} F</strong></div>
        <div><span class="text-muted small">Fiches en base absentes du fichier</span><br><strong>{{ $s['absent_du_fichier'] ?? 0 }}</strong> (non modifiées)</div>
        @foreach(($s['warnings'] ?? []) as $w => $n)
            <span class="badge bg-light text-dark border">{{ $warningLabels[$w] ?? $w }} : {{ $n }}</span>
        @endforeach
        <div class="ms-auto">
            @if($import->isApplied())
                <span class="badge bg-success fs-6"><i class="fas fa-check me-1"></i>{{ $s['updated'] ?? 0 }} fiche(s) mise(s) à jour</span>
            @elseif(($s['applicable'] ?? 0) > 0)
                <form action="{{ route('admin.drh.remunerations.apply', $import) }}" method="POST"
                      onsubmit="return confirm('Enregistrer les nouvelles rémunérations pour {{ $s['applicable'] }} agent(s) ? Les anciennes valeurs restent conservées dans cet import.');">
                    @csrf
                    <button class="btn btn-success"><i class="fas fa-save me-1"></i> Valider l'actualisation ({{ $s['applicable'] }})</button>
                </form>
            @else
                <span class="text-muted">Aucune modification à enregistrer.</span>
            @endif
        </div>
    </div>
</div>

<div class="mb-2 d-flex gap-1 flex-wrap">
    @foreach(['' => 'Tous', 'modifie' => 'Modifiés', 'alertes' => 'Avec alerte', 'non_trouve' => 'Non retrouvés', 'sans_matricule' => 'Sans matricule', 'inchange' => 'Inchangés'] as $key => $label)
        <a href="{{ route('admin.drh.remunerations.show', [$import, 'filtre' => $key ?: null]) }}"
           class="btn btn-sm {{ ($filter ?? '') === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle small">
            <thead class="table-light">
                <tr>
                    <th>Matricule</th><th>Agent</th><th>Statut</th>
                    <th class="text-end">Brut actuel</th><th class="text-end">Nouveau brut</th>
                    <th>Changements</th><th>Alertes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    @php [$lbl, $color] = $statusLabels[$r['status']] ?? [$r['status'], 'secondary']; @endphp
                    <tr class="{{ in_array('nom_different', $r['warnings']) ? 'table-danger' : '' }}">
                        <td class="fw-semibold">{{ $r['matricule'] ?: '—' }}</td>
                        <td>{{ $r['nom_fichier'] }}
                            @if($r['nom_base'] && $r['nom_base'] !== $r['nom_fichier'])<br><span class="text-muted">Fiche : {{ $r['nom_base'] }}</span>@endif
                            <br><span class="text-muted">{{ $r['section'] }}</span>
                        </td>
                        <td><span class="badge bg-{{ $color }}">{{ $lbl }}</span>
                            @if(!empty($r['applied']))<span class="badge bg-success">Enregistré</span>@endif</td>
                        <td class="text-end">{{ $money($r['old']['salaire_brut'] ?? null) }}</td>
                        <td class="text-end fw-semibold">{{ $money($r['new']['salaire_brut'] ?? null) }}</td>
                        <td>
                            @foreach($r['changes'] as $col)
                                <div><span class="text-muted">{{ $colLabels[$col] ?? $col }} :</span>
                                    @if(in_array($col, ['poste_actuel', 'categorie']))
                                        {{ $r['old'][$col] ?? '—' }} → <strong>{{ $r['new'][$col] ?? '—' }}</strong>
                                    @else
                                        {{ $money($r['old'][$col] ?? null) }} → <strong>{{ $money($r['new'][$col] ?? null) }}</strong>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td>@foreach($r['warnings'] as $w)<span class="badge bg-warning text-dark d-block mb-1">{{ $warningLabels[$w] ?? $w }}</span>@endforeach</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune ligne.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
