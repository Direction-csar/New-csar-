@extends('layouts.public')

@section('title', 'Questionnaire — Évaluation de l\'Assurance Maladie')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="text-center mb-5">
                <span class="badge bg-primary px-3 py-2 mb-3">CSAR — Direction des Ressources Humaines</span>
                <h1 class="fw-bold">Questionnaire d'évaluation de l'assurance maladie</h1>
                <p class="text-muted">
                    Votre avis nous aide à améliorer la prise en charge de votre santé.
                    Ce questionnaire est destiné aux agents du CSAR.
                </p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('public.health-survey.submit') }}" method="POST" class="card shadow-sm border-0">
                @csrf
                <div class="card-body p-4 p-md-5">

                    {{-- IDENTITÉ --}}
                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-user-circle me-2"></i>Vos informations</h4>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_anonymous">
                                Répondre de manière <strong>anonyme</strong>
                            </label>
                        </div>

                        <div class="row g-3" id="identity-fields">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="agent_nom" value="{{ old('agent_nom') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="agent_prenom" value="{{ old('agent_prenom') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Direction</label>
                                <input type="text" name="agent_direction" value="{{ old('agent_direction') }}" class="form-control" placeholder="Ex: DRH, DAF, DSI...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Région</label>
                                <input type="text" name="agent_region" value="{{ old('agent_region') }}" class="form-control" placeholder="Ex: Dakar, Thiès...">
                            </div>
                        </div>
                    </div>

                    {{-- I. COMPRÉHENSION --}}
                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-book-open me-2"></i>I. Compréhension et accessibilité du régime</h4>

                        @include('public.partials.survey-radio', [
                            'name'    => 'q1_info_level',
                            'label'   => '1. Le personnel est-il suffisamment informé des modalités de l\'assurance maladie ?',
                            'options' => ['totalement' => 'Oui, totalement', 'partiellement' => 'Partiellement', 'non' => 'Non'],
                        ])

                        @include('public.partials.survey-radio', [
                            'name'    => 'q2_documents_clarity',
                            'label'   => '2. Les documents explicatifs (brochures, guides, intranet) sont-ils clairs et accessibles ?',
                            'options' => ['tres_clairs' => 'Très clairs', 'moyennement' => 'Moyennement clairs', 'peu_clairs' => 'Peu clairs'],
                        ])

                        @include('public.partials.survey-radio', [
                            'name'    => 'q3_difficulty',
                            'label'   => '3. Avez-vous eu des difficultés à comprendre vos droits et obligations ?',
                            'options' => ['jamais' => 'Jamais', 'parfois' => 'Parfois', 'souvent' => 'Souvent'],
                        ])
                    </div>

                    {{-- II. QUALITÉ --}}
                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-heartbeat me-2"></i>II. Qualité des prestations couvertes</h4>

                        @include('public.partials.survey-radio', [
                            'name'    => 'q4_soins_response',
                            'label'   => '4. Les soins médicaux pris en charge répondent-ils à vos besoins ?',
                            'options' => ['largement' => 'Oui, largement', 'avec_limites' => 'Oui, mais avec des limites', 'non' => 'Non'],
                        ])

                        @include('public.partials.survey-radio', [
                            'name'    => 'q5_panier_soins',
                            'label'   => '5. Le panier de soins (consultations, hospitalisations, médicaments, analyses) est-il jugé suffisant ?',
                            'options' => ['tres_suffisant' => 'Très suffisant', 'assez_suffisant' => 'Assez suffisant', 'insuffisant' => 'Insuffisant'],
                        ])

                        @include('public.partials.survey-radio', [
                            'name'    => 'q6_delais_remboursement',
                            'label'   => '6. Les délais de remboursement sont-ils satisfaisants ?',
                            'options' => ['rapides' => 'Toujours rapides', 'acceptables' => 'Acceptables', 'longs' => 'Trop longs'],
                        ])

                        @include('public.partials.survey-radio', [
                            'name'    => 'q7_service_client',
                            'label'   => '7. Le service client (écoute, conseils) est-il de bonne qualité ?',
                            'options' => ['oui' => 'Oui', 'non' => 'Non'],
                        ])

                        <div class="mb-3">
                            <label class="form-label fw-semibold">8. Quel problème récent avez-vous rencontré ?</label>
                            <textarea name="q8_probleme_recent" rows="3" class="form-control">{{ old('q8_probleme_recent') }}</textarea>
                        </div>
                    </div>

                    {{-- III. SATISFACTION --}}
                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-smile me-2"></i>III. Satisfaction et accessibilité</h4>

                        @include('public.partials.survey-radio', [
                            'name'    => 'q9_coassurance',
                            'label'   => '9. Comment qualifierez-vous le taux de coassurance (90%) de l\'assurance maladie ?',
                            'options' => ['tres_satisfait' => 'Très satisfait(e)', 'satisfait' => 'Satisfait(e)', 'pas_satisfait' => 'Pas satisfait(e)', 'autre' => 'Autre'],
                        ])
                        <div class="mb-3 ms-4">
                            <input type="text" name="q9_autre" value="{{ old('q9_autre') }}" class="form-control" placeholder="Si autre, précisez...">
                        </div>

                        @include('public.partials.survey-radio', [
                            'name'    => 'q10_reseau_soins',
                            'label'   => '10. Le réseau de soins des partenaires (plateau médical) est-il accessible ?',
                            'options' => ['tres_accessible' => 'Très accessible', 'accessible' => 'Accessible', 'pas_accessible' => 'Pas accessible', 'autre' => 'Autre'],
                        ])
                        <div class="mb-3 ms-4">
                            <input type="text" name="q10_autre" value="{{ old('q10_autre') }}" class="form-control" placeholder="Si autre, précisez...">
                        </div>
                    </div>

                    {{-- IV. SUGGESTIONS --}}
                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>IV. Suggestions et améliorations</h4>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">11. Quels aspects de l'assurance maladie devraient être améliorés ? <em class="text-muted">(plusieurs choix possibles)</em></label>
                            @php $aspects = old('q11_aspects', []); @endphp
                            @foreach(\App\Models\HealthInsuranceSurvey::aspectOptions() as $val => $lbl)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="q11_aspects[]" value="{{ $val }}" id="asp_{{ $val }}" @if(in_array($val, $aspects)) checked @endif>
                                    <label class="form-check-label" for="asp_{{ $val }}">{{ $lbl }}</label>
                                </div>
                            @endforeach
                            <input type="text" name="q11_autre" value="{{ old('q11_autre') }}" class="form-control mt-2" placeholder="Si autre, précisez...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">12. Avez-vous des propositions concrètes pour améliorer le dispositif ?</label>
                            <textarea name="q12_propositions" rows="4" class="form-control">{{ old('q12_propositions') }}</textarea>
                        </div>
                    </div>

                    {{-- V. NOTE --}}
                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-star me-2"></i>V. Évaluation finale</h4>

                        <label class="form-label fw-semibold mb-3">13. Sur une échelle de 1 à 5, quelle note donnez-vous actuellement à l'assurance maladie ?</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach([1 => 'Très insatisfait', 2 => 'Insatisfait', 3 => 'Peu satisfait', 4 => 'Satisfait', 5 => 'Très satisfait'] as $n => $lbl)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q13_note" id="note_{{ $n }}" value="{{ $n }}" @if((string)old('q13_note') === (string)$n) checked @endif required>
                                    <label class="form-check-label" for="note_{{ $n }}">
                                        <strong>{{ $n }}</strong> — {{ $lbl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer mes réponses
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('is_anonymous').addEventListener('change', function() {
    const fields = document.getElementById('identity-fields');
    const inputs = fields.querySelectorAll('input');
    if (this.checked) {
        inputs.forEach(i => { i.value = ''; i.disabled = true; });
        fields.style.opacity = '0.4';
    } else {
        inputs.forEach(i => i.disabled = false);
        fields.style.opacity = '1';
    }
});
</script>
@endsection
