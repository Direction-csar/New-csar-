@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')

@section('title', 'Enquête Assurance Maladie')
@section('page-title', '📋 Enquête Assurance Maladie')

@section('content')
@php
    use App\Models\HealthInsuranceSurvey;
    $labels = HealthInsuranceSurvey::questionLabels();
@endphp

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-poll-h text-primary me-2"></i>Enquête Assurance Maladie</h1>
            <p class="text-muted mb-0">Résultats du questionnaire destiné aux agents du CSAR</p>
        </div>
        <div>
            <a href="{{ route('admin.drh.health-survey.export') }}" class="btn btn-success">
                <i class="fas fa-file-csv me-1"></i> Exporter CSV
            </a>
            <a href="{{ route('public.health-survey.show') }}" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt me-1"></i> Voir le formulaire
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total réponses</div>
                    <div class="h2 mb-0 text-primary fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Note moyenne /5</div>
                    <div class="h2 mb-0 text-warning fw-bold">
                        {{ $stats['avg_note'] }}
                        <small class="text-muted fs-6">/5</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Satisfaits (note ≥ 4)</div>
                    @php $sat = ($stats['by_note'][4] ?? 0) + ($stats['by_note'][5] ?? 0); @endphp
                    <div class="h2 mb-0 text-success fw-bold">{{ $sat }}
                        <small class="text-muted fs-6">/ {{ $stats['total'] }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Insatisfaits (note ≤ 2)</div>
                    @php $insat = ($stats['by_note'][1] ?? 0) + ($stats['by_note'][2] ?? 0); @endphp
                    <div class="h2 mb-0 text-danger fw-bold">{{ $insat }}
                        <small class="text-muted fs-6">/ {{ $stats['total'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DISTRIBUTION NOTES + GRAPHIQUE --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>Distribution des notes</strong></div>
                <div class="card-body">
                    @php $labels_note = [1 => 'Très insatisfait', 2 => 'Insatisfait', 3 => 'Peu satisfait', 4 => 'Satisfait', 5 => 'Très satisfait']; @endphp
                    @foreach($labels_note as $n => $lbl)
                        @php $count = $stats['by_note'][$n] ?? 0; $pct = $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0; @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>{{ $n }} — {{ $lbl }}</span>
                                <span class="text-muted">{{ $count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-{{ $n >= 4 ? 'success' : ($n == 3 ? 'warning' : 'danger') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>Distributions par question (résumé)</strong></div>
                <div class="card-body">
                    @foreach(['q1' => $labels['q1_info_level'], 'q4' => $labels['q4_soins_response'], 'q6' => $labels['q6_delais_remboursement'], 'q7' => $labels['q7_service_client']] as $key => $info)
                        <div class="mb-3">
                            <div class="small fw-semibold mb-1">{{ $info['label'] }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($info['options'] as $val => $lbl)
                                    @php $c = $stats[$key][$val] ?? 0; @endphp
                                    <span class="badge bg-light text-dark border">{{ $lbl }} : <strong>{{ $c }}</strong></span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher (nom, direction)...">
                </div>
                <div class="col-md-3">
                    <select name="direction" class="form-select">
                        <option value="">Toutes directions</option>
                        @foreach($directions as $d)
                            <option value="{{ $d }}" @selected(request('direction') === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="note" class="form-select">
                        <option value="">Toutes notes</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected(request('note') == $i)>Note {{ $i }}/5</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filtrer</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.drh.health-survey.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLEAU RÉPONSES --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Liste des réponses ({{ $surveys->total() }})</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Agent</th>
                        <th>Direction</th>
                        <th>Région</th>
                        <th class="text-center">Note</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surveys as $s)
                        <tr>
                            <td><small>{{ $s->submitted_at?->format('d/m/Y H:i') }}</small></td>
                            <td>
                                @if($s->is_anonymous)
                                    <em class="text-muted">Anonyme</em>
                                @else
                                    {{ trim(($s->agent_prenom ?? '') . ' ' . ($s->agent_nom ?? '')) ?: '—' }}
                                @endif
                            </td>
                            <td>{{ $s->agent_direction ?? '—' }}</td>
                            <td>{{ $s->agent_region ?? '—' }}</td>
                            <td class="text-center">
                                @php $note = (int) $s->q13_note; @endphp
                                <span class="badge bg-{{ $note >= 4 ? 'success' : ($note == 3 ? 'warning' : 'danger') }} fs-6">
                                    {{ $note }}/5
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.drh.health-survey.show', $s->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune réponse pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())
            <div class="card-footer bg-white">{{ $surveys->links() }}</div>
        @endif
    </div>
</div>
@endsection
