@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')

@section('title', 'Détail réponse — Enquête Assurance Maladie')
@section('page-title', '📋 Détail réponse — Enquête Assurance Maladie')

@section('content')
@php
    use App\Models\HealthInsuranceSurvey;
    $labels = HealthInsuranceSurvey::questionLabels();
    $aspects = HealthInsuranceSurvey::aspectOptions();

    $resolve = function ($key, $value) use ($labels) {
        return $labels[$key]['options'][$value] ?? ($value ?: '—');
    };
@endphp

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0"><i class="fas fa-poll text-primary me-2"></i>Détail de la réponse</h1>
            <p class="text-muted mb-0">Soumise le {{ $survey->submitted_at?->format('d/m/Y à H:i') }}</p>
        </div>
        <a href="{{ route('admin.drh.health-survey.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    {{-- IDENTITÉ --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><strong>Informations de l'agent</strong></div>
        <div class="card-body">
            @if($survey->is_anonymous)
                <div class="alert alert-info mb-0"><i class="fas fa-user-secret me-2"></i>Réponse anonyme.</div>
            @else
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nom complet</dt>
                    <dd class="col-sm-9">{{ trim(($survey->agent_prenom ?? '') . ' ' . ($survey->agent_nom ?? '')) ?: '—' }}</dd>
                    <dt class="col-sm-3">Direction</dt>
                    <dd class="col-sm-9">{{ $survey->agent_direction ?? '—' }}</dd>
                    <dt class="col-sm-3">Région</dt>
                    <dd class="col-sm-9">{{ $survey->agent_region ?? '—' }}</dd>
                </dl>
            @endif
        </div>
    </div>

    {{-- SECTIONS QUESTIONS --}}
    @php
        $sections = [
            'I. Compréhension et accessibilité' => [
                ['q1_info_level',         'Q1. ' . $labels['q1_info_level']['label']],
                ['q2_documents_clarity',  'Q2. ' . $labels['q2_documents_clarity']['label']],
                ['q3_difficulty',         'Q3. ' . $labels['q3_difficulty']['label']],
            ],
            'II. Qualité des prestations' => [
                ['q4_soins_response',      'Q4. ' . $labels['q4_soins_response']['label']],
                ['q5_panier_soins',        'Q5. ' . $labels['q5_panier_soins']['label']],
                ['q6_delais_remboursement','Q6. ' . $labels['q6_delais_remboursement']['label']],
                ['q7_service_client',      'Q7. ' . $labels['q7_service_client']['label']],
            ],
            'III. Satisfaction et accessibilité' => [
                ['q9_coassurance',         'Q9. ' . $labels['q9_coassurance']['label']],
                ['q10_reseau_soins',       'Q10. ' . $labels['q10_reseau_soins']['label']],
            ],
        ];
    @endphp

    @foreach($sections as $title => $items)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white"><strong>{{ $title }}</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    @foreach($items as [$key, $label])
                        <dt class="col-sm-6">{{ $label }}</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-light text-dark border">{{ $resolve($key, $survey->$key) }}</span>
                        </dd>
                    @endforeach
                </dl>

                @if($title === 'II. Qualité des prestations' && $survey->q8_probleme_recent)
                    <hr>
                    <strong>Q8. Problème récent rencontré :</strong>
                    <p class="text-muted mb-0">{{ $survey->q8_probleme_recent }}</p>
                @endif

                @if($title === 'III. Satisfaction et accessibilité')
                    @if($survey->q9_autre)
                        <hr><strong>Q9 — Autre précision :</strong> <em>{{ $survey->q9_autre }}</em>
                    @endif
                    @if($survey->q10_autre)
                        <hr><strong>Q10 — Autre précision :</strong> <em>{{ $survey->q10_autre }}</em>
                    @endif
                @endif
            </div>
        </div>
    @endforeach

    {{-- IV. SUGGESTIONS --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><strong>IV. Suggestions et améliorations</strong></div>
        <div class="card-body">
            <strong>Q11. Aspects à améliorer :</strong>
            <div class="mt-2 mb-3">
                @forelse((array) $survey->q11_aspects as $a)
                    <span class="badge bg-primary me-1">{{ $aspects[$a] ?? $a }}</span>
                @empty
                    <span class="text-muted">— Aucun</span>
                @endforelse
            </div>

            @if($survey->q11_autre)
                <p><strong>Autre précision :</strong> {{ $survey->q11_autre }}</p>
            @endif

            <strong>Q12. Propositions concrètes :</strong>
            <p class="text-muted">{{ $survey->q12_propositions ?: '—' }}</p>
        </div>
    </div>

    {{-- V. NOTE --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><strong>V. Évaluation finale</strong></div>
        <div class="card-body text-center">
            @php
                $note = (int) $survey->q13_note;
                $color = $note >= 4 ? 'success' : ($note == 3 ? 'warning' : 'danger');
                $lbl   = [1 => 'Très insatisfait', 2 => 'Insatisfait', 3 => 'Peu satisfait', 4 => 'Satisfait', 5 => 'Très satisfait'][$note] ?? '—';
            @endphp
            <div class="display-3 text-{{ $color }} fw-bold">{{ $note }}/5</div>
            <p class="lead text-muted">{{ $lbl }}</p>
            <div>
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= $note ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                @endfor
            </div>
        </div>
    </div>

</div>
@endsection
