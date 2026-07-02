@extends('layouts.public')

@section('title', 'Confirmation — Évaluation Assurance Maladie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="alert alert-warning border-warning">
                <h4 class="alert-heading"><i class="fas fa-clock me-2"></i>Votre questionnaire n'est pas encore confirmé</h4>
                <p class="mb-0">
                    Vous avez <strong>10 minutes</strong> pour vérifier et modifier vos réponses avant l'envoi définitif.
                </p>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Récapitulatif de vos réponses</h5>
                </div>
                <div class="card-body">

                    {{-- Identité --}}
                    <h6 class="text-muted mt-3 mb-2">Identité</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="fw-semibold" style="width:40%">Nom</td><td>{{ $survey->is_anonymous ? '(Anonyme)' : ($survey->agent_nom ?: '-') }}</td></tr>
                        <tr><td class="fw-semibold">Prénom</td><td>{{ $survey->is_anonymous ? '(Anonyme)' : ($survey->agent_prenom ?: '-') }}</td></tr>
                    </table>

                    @php $labels = App\Models\HealthInsuranceSurvey::questionLabels(); @endphp

                    {{-- Q1 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q1_info_level']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q1_info_level']['options'][$survey->q1_info_level] ?? $survey->q1_info_level }}</p>

                    {{-- Q2 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q2_documents_clarity']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q2_documents_clarity']['options'][$survey->q2_documents_clarity] ?? $survey->q2_documents_clarity }}</p>

                    {{-- Q3 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q3_difficulty']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q3_difficulty']['options'][$survey->q3_difficulty] ?? $survey->q3_difficulty }}</p>

                    {{-- Q4 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q4_soins_response']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q4_soins_response']['options'][$survey->q4_soins_response] ?? $survey->q4_soins_response }}</p>

                    {{-- Q5 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q5_panier_soins']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q5_panier_soins']['options'][$survey->q5_panier_soins] ?? $survey->q5_panier_soins }}</p>

                    {{-- Q6 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q6_delais_remboursement']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q6_delais_remboursement']['options'][$survey->q6_delais_remboursement] ?? $survey->q6_delais_remboursement }}</p>

                    {{-- Q7 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q7_service_client']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q7_service_client']['options'][$survey->q7_service_client] ?? $survey->q7_service_client }}</p>

                    {{-- Q8 --}}
                    <h6 class="text-muted mt-3 mb-2">Problème récent</h6>
                    <p class="ps-3">{{ $survey->q8_probleme_recent ?: '-' }}</p>

                    {{-- Q9 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q9_coassurance']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q9_coassurance']['options'][$survey->q9_coassurance] ?? $survey->q9_coassurance }}</p>
                    @if($survey->q9_autre)
                        <p class="ps-3 text-muted small">Autre : {{ $survey->q9_autre }}</p>
                    @endif

                    {{-- Q10 --}}
                    <h6 class="text-muted mt-3 mb-2">{{ $labels['q10_reseau_soins']['label'] }}</h6>
                    <p class="ps-3">{{ $labels['q10_reseau_soins']['options'][$survey->q10_reseau_soins] ?? $survey->q10_reseau_soins }}</p>
                    @if($survey->q10_autre)
                        <p class="ps-3 text-muted small">Autre : {{ $survey->q10_autre }}</p>
                    @endif

                    {{-- Q11 --}}
                    <h6 class="text-muted mt-3 mb-2">Aspects à améliorer</h6>
                    <p class="ps-3">
                        @if(is_array($survey->q11_aspects) && count($survey->q11_aspects))
                            @foreach($survey->q11_aspects as $asp)
                                <span class="badge bg-secondary me-1">{{ App\Models\HealthInsuranceSurvey::aspectOptions()[$asp] ?? $asp }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </p>
                    @if($survey->q11_autre)
                        <p class="ps-3 text-muted small">Autre : {{ $survey->q11_autre }}</p>
                    @endif

                    {{-- Q12 --}}
                    <h6 class="text-muted mt-3 mb-2">Propositions</h6>
                    <p class="ps-3">{{ $survey->q12_propositions ?: '-' }}</p>

                    {{-- Note --}}
                    <h6 class="text-muted mt-3 mb-2">Note globale</h6>
                    <p class="ps-3">
                        @for($i=1; $i<=5; $i++)
                            @if($i <= $survey->q13_note)
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-warning"></i>
                            @endif
                        @endfor
                        <strong>{{ $survey->q13_note }}/5</strong>
                    </p>
                </div>
            </div>

            {{-- Timer + Actions --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center p-4">
                    <div id="timer-container" class="mb-3">
                        <h3 class="text-danger fw-bold mb-2" id="countdown">10:00</h3>
                        <p class="text-muted small">Temps restant pour modifier vos réponses</p>
                    </div>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('public.health-survey.edit', $survey) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-pen me-2"></i>Modifier mes réponses
                        </a>
                        <form action="{{ route('public.health-survey.confirm', $survey) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Confirmer définitivement
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="expired-message" class="d-none">
                <div class="alert alert-danger text-center">
                    <h5><i class="fas fa-hourglass-end me-2"></i>Délai expiré</h5>
                    <p>Les 10 minutes sont écoulées. Vos réponses ont été supprimées.</p>
                    <a href="{{ route('public.health-survey.show') }}" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Recommencer le questionnaire
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const expiresAt = new Date("{{ $survey->expires_at->toIso8601String() }}").getTime();

function updateTimer() {
    const now = new Date().getTime();
    const distance = expiresAt - now;

    if (distance < 0) {
        document.getElementById('timer-container').classList.add('d-none');
        document.querySelector('.card-body .d-flex').classList.add('d-none');
        document.getElementById('expired-message').classList.remove('d-none');
        clearInterval(timerInterval);
        return;
    }

    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('countdown').textContent =
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
}

const timerInterval = setInterval(updateTimer, 1000);
updateTimer();
</script>
@endsection
