#!/bin/bash
# Déploiement Enquête Assurance Maladie CSAR
# À exécuter sur la VM : sudo bash deploy-health-survey.sh

set -e
APP=/var/www/csar
cd "$APP"

echo "=== 1. Migration ==="
sudo mkdir -p database/migrations
sudo tee database/migrations/2026_05_11_090000_create_health_insurance_surveys_table.php > /dev/null << 'PHPEOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_insurance_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('agent_nom')->nullable();
            $table->string('agent_prenom')->nullable();
            $table->string('agent_direction')->nullable();
            $table->string('agent_region')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->enum('q1_info_level', ['totalement', 'partiellement', 'non'])->nullable();
            $table->enum('q2_documents_clarity', ['tres_clairs', 'moyennement', 'peu_clairs'])->nullable();
            $table->enum('q3_difficulty', ['jamais', 'parfois', 'souvent'])->nullable();
            $table->enum('q4_soins_response', ['largement', 'avec_limites', 'non'])->nullable();
            $table->enum('q5_panier_soins', ['tres_suffisant', 'assez_suffisant', 'insuffisant'])->nullable();
            $table->enum('q6_delais_remboursement', ['rapides', 'acceptables', 'longs'])->nullable();
            $table->enum('q7_service_client', ['oui', 'non'])->nullable();
            $table->text('q8_probleme_recent')->nullable();
            $table->enum('q9_coassurance', ['tres_satisfait', 'satisfait', 'pas_satisfait', 'autre'])->nullable();
            $table->string('q9_autre')->nullable();
            $table->enum('q10_reseau_soins', ['tres_accessible', 'accessible', 'pas_accessible', 'autre'])->nullable();
            $table->string('q10_autre')->nullable();
            $table->json('q11_aspects')->nullable();
            $table->string('q11_autre')->nullable();
            $table->text('q12_propositions')->nullable();
            $table->tinyInteger('q13_note')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            $table->index('q13_note');
            $table->index('agent_direction');
            $table->index('submitted_at');
        });
    }
    public function down(): void { Schema::dropIfExists('health_insurance_surveys'); }
};
PHPEOF
echo "✅ Migration créée"

echo "=== 2. Modèle ==="
sudo tee app/Models/HealthInsuranceSurvey.php > /dev/null << 'PHPEOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthInsuranceSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_nom', 'agent_prenom', 'agent_direction', 'agent_region', 'is_anonymous',
        'q1_info_level', 'q2_documents_clarity', 'q3_difficulty',
        'q4_soins_response', 'q5_panier_soins', 'q6_delais_remboursement',
        'q7_service_client', 'q8_probleme_recent',
        'q9_coassurance', 'q9_autre', 'q10_reseau_soins', 'q10_autre',
        'q11_aspects', 'q11_autre', 'q12_propositions', 'q13_note',
        'ip_address', 'user_agent', 'submitted_at',
    ];

    protected $casts = [
        'q11_aspects' => 'array', 'is_anonymous' => 'boolean',
        'q13_note' => 'integer', 'submitted_at' => 'datetime',
    ];

    public static function questionLabels(): array
    {
        return [
            'q1_info_level' => ['label' => 'Le personnel est-il suffisamment informé des modalités ?', 'options' => ['totalement' => 'Oui, totalement', 'partiellement' => 'Partiellement', 'non' => 'Non']],
            'q2_documents_clarity' => ['label' => 'Les documents explicatifs sont-ils clairs ?', 'options' => ['tres_clairs' => 'Très clairs', 'moyennement' => 'Moyennement clairs', 'peu_clairs' => 'Peu clairs']],
            'q3_difficulty' => ['label' => 'Difficultés à comprendre vos droits et obligations ?', 'options' => ['jamais' => 'Jamais', 'parfois' => 'Parfois', 'souvent' => 'Souvent']],
            'q4_soins_response' => ['label' => 'Les soins répondent-ils à vos besoins ?', 'options' => ['largement' => 'Oui, largement', 'avec_limites' => 'Oui, avec des limites', 'non' => 'Non']],
            'q5_panier_soins' => ['label' => 'Le panier de soins est-il suffisant ?', 'options' => ['tres_suffisant' => 'Très suffisant', 'assez_suffisant' => 'Assez suffisant', 'insuffisant' => 'Insuffisant']],
            'q6_delais_remboursement' => ['label' => 'Les délais de remboursement sont-ils satisfaisants ?', 'options' => ['rapides' => 'Toujours rapides', 'acceptables' => 'Acceptables', 'longs' => 'Trop longs']],
            'q7_service_client' => ['label' => 'Le service client est-il de bonne qualité ?', 'options' => ['oui' => 'Oui', 'non' => 'Non']],
            'q9_coassurance' => ['label' => 'Taux de coassurance (90%)', 'options' => ['tres_satisfait' => 'Très satisfait', 'satisfait' => 'Satisfait', 'pas_satisfait' => 'Pas satisfait', 'autre' => 'Autre']],
            'q10_reseau_soins' => ['label' => 'Le réseau de soins est-il accessible ?', 'options' => ['tres_accessible' => 'Très accessible', 'accessible' => 'Accessible', 'pas_accessible' => 'Pas accessible', 'autre' => 'Autre']],
        ];
    }

    public static function aspectOptions(): array
    {
        return [
            'etendue_soins' => 'Étendue des soins couverts',
            'rapidite_rembour' => 'Rapidité des remboursements',
            'communication' => 'Communication et information',
            'autre' => 'Autre',
        ];
    }
}
PHPEOF
echo "✅ Modèle créé"

echo "=== 3. Controllers ==="
sudo mkdir -p app/Http/Controllers/Public app/Http/Controllers/Drh

sudo tee app/Http/Controllers/Public/HealthInsuranceSurveyController.php > /dev/null << 'PHPEOF'
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HealthInsuranceSurvey;
use Illuminate\Http\Request;

class HealthInsuranceSurveyController extends Controller
{
    public function show() { return view('public.health-insurance-survey'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'is_anonymous' => 'nullable|boolean',
            'agent_nom' => 'nullable|string|max:100',
            'agent_prenom' => 'nullable|string|max:100',
            'agent_direction' => 'nullable|string|max:150',
            'agent_region' => 'nullable|string|max:100',
            'q1_info_level' => 'required|in:totalement,partiellement,non',
            'q2_documents_clarity' => 'required|in:tres_clairs,moyennement,peu_clairs',
            'q3_difficulty' => 'required|in:jamais,parfois,souvent',
            'q4_soins_response' => 'required|in:largement,avec_limites,non',
            'q5_panier_soins' => 'required|in:tres_suffisant,assez_suffisant,insuffisant',
            'q6_delais_remboursement' => 'required|in:rapides,acceptables,longs',
            'q7_service_client' => 'required|in:oui,non',
            'q8_probleme_recent' => 'nullable|string|max:2000',
            'q9_coassurance' => 'required|in:tres_satisfait,satisfait,pas_satisfait,autre',
            'q9_autre' => 'nullable|string|max:500',
            'q10_reseau_soins' => 'required|in:tres_accessible,accessible,pas_accessible,autre',
            'q10_autre' => 'nullable|string|max:500',
            'q11_aspects' => 'nullable|array',
            'q11_aspects.*' => 'in:etendue_soins,rapidite_rembour,communication,autre',
            'q11_autre' => 'nullable|string|max:500',
            'q12_propositions' => 'nullable|string|max:2000',
            'q13_note' => 'required|integer|between:1,5',
        ]);

        $isAnonymous = (bool) $request->boolean('is_anonymous');
        if ($isAnonymous) {
            $validated['agent_nom'] = null;
            $validated['agent_prenom'] = null;
        }
        $validated['is_anonymous'] = $isAnonymous;
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = substr((string) $request->userAgent(), 0, 500);
        $validated['submitted_at'] = now();

        HealthInsuranceSurvey::create($validated);

        return redirect()->route('public.health-survey.thanks')
            ->with('success', 'Merci ! Votre questionnaire a été enregistré.');
    }

    public function thanks() { return view('public.health-insurance-survey-thanks'); }
}
PHPEOF

sudo tee app/Http/Controllers/Drh/HealthSurveyController.php > /dev/null << 'PHPEOF'
<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\HealthInsuranceSurvey;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HealthSurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = HealthInsuranceSurvey::query()->latest('submitted_at');
        if ($request->filled('direction')) $query->where('agent_direction', $request->direction);
        if ($request->filled('region')) $query->where('agent_region', $request->region);
        if ($request->filled('note')) $query->where('q13_note', $request->note);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('agent_nom', 'LIKE', "%{$s}%")
                  ->orWhere('agent_prenom', 'LIKE', "%{$s}%")
                  ->orWhere('agent_direction', 'LIKE', "%{$s}%");
            });
        }
        $surveys = $query->paginate(25)->withQueryString();
        $stats = $this->computeStats();
        $directions = HealthInsuranceSurvey::query()->whereNotNull('agent_direction')->distinct()->pluck('agent_direction')->filter()->values();
        return view('admin.drh.health-survey.index', compact('surveys', 'stats', 'directions'));
    }

    public function show($id)
    {
        $survey = HealthInsuranceSurvey::findOrFail($id);
        return view('admin.drh.health-survey.show', compact('survey'));
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'enquete_assurance_maladie_' . now()->format('Y-m-d_His') . '.csv';
        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date','Anonyme','Nom','Prénom','Direction','Région','Q1','Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','Q9 autre','Q10','Q10 autre','Q11','Q11 autre','Q12','Note /5'], ';');
            HealthInsuranceSurvey::orderBy('submitted_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->submitted_at?->format('d/m/Y H:i'),
                        $r->is_anonymous ? 'Oui' : 'Non',
                        $r->agent_nom, $r->agent_prenom, $r->agent_direction, $r->agent_region,
                        $r->q1_info_level, $r->q2_documents_clarity, $r->q3_difficulty,
                        $r->q4_soins_response, $r->q5_panier_soins, $r->q6_delais_remboursement,
                        $r->q7_service_client, $r->q8_probleme_recent,
                        $r->q9_coassurance, $r->q9_autre, $r->q10_reseau_soins, $r->q10_autre,
                        is_array($r->q11_aspects) ? implode(', ', $r->q11_aspects) : '',
                        $r->q11_autre, $r->q12_propositions, $r->q13_note,
                    ], ';');
                }
            });
            fclose($out);
        }, 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""]);
    }

    private function computeStats(): array
    {
        $total = HealthInsuranceSurvey::count();
        $avgNote = $total > 0 ? round(HealthInsuranceSurvey::avg('q13_note'), 2) : 0;
        $byNote = HealthInsuranceSurvey::selectRaw('q13_note, COUNT(*) as c')->groupBy('q13_note')->pluck('c', 'q13_note')->toArray();
        $dist = fn($col) => HealthInsuranceSurvey::selectRaw("$col, COUNT(*) as c")->whereNotNull($col)->groupBy($col)->pluck('c', $col)->toArray();
        return [
            'total' => $total, 'avg_note' => $avgNote, 'by_note' => $byNote,
            'q1' => $dist('q1_info_level'), 'q2' => $dist('q2_documents_clarity'),
            'q3' => $dist('q3_difficulty'), 'q4' => $dist('q4_soins_response'),
            'q5' => $dist('q5_panier_soins'), 'q6' => $dist('q6_delais_remboursement'),
            'q7' => $dist('q7_service_client'), 'q9' => $dist('q9_coassurance'),
            'q10' => $dist('q10_reseau_soins'),
        ];
    }
}
PHPEOF
echo "✅ Controllers créés"

echo "=== 4. Vues ==="
sudo mkdir -p resources/views/public/partials resources/views/admin/drh/health-survey

# Partial radio
sudo tee resources/views/public/partials/survey-radio.blade.php > /dev/null << 'BLADEEOF'
@php
    $name = $name ?? '';
    $label = $label ?? '';
    $options = $options ?? [];
    $required = $required ?? true;
@endphp
<div class="mb-3">
    <label class="form-label fw-semibold">{{ $label }}</label>
    <div class="d-flex flex-column gap-2">
        @foreach($options as $val => $lbl)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $name }}_{{ $val }}" value="{{ $val }}"
                       @if(old($name) === $val) checked @endif @if($required) required @endif>
                <label class="form-check-label" for="{{ $name }}_{{ $val }}">{{ $lbl }}</label>
            </div>
        @endforeach
    </div>
</div>
BLADEEOF

# Page remerciement
sudo tee resources/views/public/health-insurance-survey-thanks.blade.php > /dev/null << 'BLADEEOF'
@extends('layouts.public')
@section('title', 'Merci')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">
            <div class="card shadow-sm border-0 p-5">
                <div class="mb-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:110px;height:110px;">
                        <i class="fas fa-check-circle text-success" style="font-size:4.5rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-3">Merci !</h2>
                <p class="lead text-muted">Votre questionnaire a été enregistré. Votre contribution nous aidera à améliorer l'assurance maladie du personnel CSAR.</p>
                <a href="{{ url('/') }}" class="btn btn-primary mt-3"><i class="fas fa-home me-2"></i>Retour à l'accueil</a>
            </div>
        </div>
    </div>
</div>
@endsection
BLADEEOF
echo "✅ Vues partielles créées"

# Vue formulaire principal (téléchargée séparément vu sa taille)
sudo tee resources/views/public/health-insurance-survey.blade.php > /dev/null << 'BLADEEOF'
@extends('layouts.public')
@section('title', 'Questionnaire — Évaluation de l\'Assurance Maladie')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5">
                <span class="badge bg-primary px-3 py-2 mb-3">CSAR — DRH</span>
                <h1 class="fw-bold">Questionnaire d'évaluation de l'assurance maladie</h1>
                <p class="text-muted">Destiné aux agents du CSAR.</p>
            </div>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

            <form action="{{ route('public.health-survey.submit') }}" method="POST" class="card shadow-sm border-0">
                @csrf
                <div class="card-body p-4 p-md-5">

                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-user-circle me-2"></i>Vos informations</h4>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_anonymous">Répondre de manière <strong>anonyme</strong></label>
                        </div>
                        <div class="row g-3" id="identity-fields">
                            <div class="col-md-6"><label class="form-label">Nom</label><input type="text" name="agent_nom" value="{{ old('agent_nom') }}" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Prénom</label><input type="text" name="agent_prenom" value="{{ old('agent_prenom') }}" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Direction</label><input type="text" name="agent_direction" value="{{ old('agent_direction') }}" class="form-control" placeholder="Ex: DRH, DAF..."></div>
                            <div class="col-md-6"><label class="form-label">Région</label><input type="text" name="agent_region" value="{{ old('agent_region') }}" class="form-control" placeholder="Ex: Dakar..."></div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-book-open me-2"></i>I. Compréhension et accessibilité</h4>
                        @include('public.partials.survey-radio', ['name' => 'q1_info_level', 'label' => '1. Le personnel est-il suffisamment informé ?', 'options' => ['totalement' => 'Oui, totalement', 'partiellement' => 'Partiellement', 'non' => 'Non']])
                        @include('public.partials.survey-radio', ['name' => 'q2_documents_clarity', 'label' => '2. Les documents sont-ils clairs ?', 'options' => ['tres_clairs' => 'Très clairs', 'moyennement' => 'Moyennement clairs', 'peu_clairs' => 'Peu clairs']])
                        @include('public.partials.survey-radio', ['name' => 'q3_difficulty', 'label' => '3. Difficultés à comprendre vos droits ?', 'options' => ['jamais' => 'Jamais', 'parfois' => 'Parfois', 'souvent' => 'Souvent']])
                    </div>

                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-heartbeat me-2"></i>II. Qualité des prestations</h4>
                        @include('public.partials.survey-radio', ['name' => 'q4_soins_response', 'label' => '4. Les soins répondent-ils à vos besoins ?', 'options' => ['largement' => 'Oui, largement', 'avec_limites' => 'Oui, avec des limites', 'non' => 'Non']])
                        @include('public.partials.survey-radio', ['name' => 'q5_panier_soins', 'label' => '5. Le panier de soins est-il suffisant ?', 'options' => ['tres_suffisant' => 'Très suffisant', 'assez_suffisant' => 'Assez suffisant', 'insuffisant' => 'Insuffisant']])
                        @include('public.partials.survey-radio', ['name' => 'q6_delais_remboursement', 'label' => '6. Les délais de remboursement sont-ils satisfaisants ?', 'options' => ['rapides' => 'Toujours rapides', 'acceptables' => 'Acceptables', 'longs' => 'Trop longs']])
                        @include('public.partials.survey-radio', ['name' => 'q7_service_client', 'label' => '7. Le service client est-il de bonne qualité ?', 'options' => ['oui' => 'Oui', 'non' => 'Non']])
                        <div class="mb-3"><label class="form-label fw-semibold">8. Quel problème récent avez-vous rencontré ?</label><textarea name="q8_probleme_recent" rows="3" class="form-control">{{ old('q8_probleme_recent') }}</textarea></div>
                    </div>

                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-smile me-2"></i>III. Satisfaction et accessibilité</h4>
                        @include('public.partials.survey-radio', ['name' => 'q9_coassurance', 'label' => '9. Comment qualifierez-vous le taux de coassurance (90%) ?', 'options' => ['tres_satisfait' => 'Très satisfait(e)', 'satisfait' => 'Satisfait(e)', 'pas_satisfait' => 'Pas satisfait(e)', 'autre' => 'Autre']])
                        <div class="mb-3 ms-4"><input type="text" name="q9_autre" value="{{ old('q9_autre') }}" class="form-control" placeholder="Si autre, précisez..."></div>
                        @include('public.partials.survey-radio', ['name' => 'q10_reseau_soins', 'label' => '10. Le réseau de soins des partenaires est-il accessible ?', 'options' => ['tres_accessible' => 'Très accessible', 'accessible' => 'Accessible', 'pas_accessible' => 'Pas accessible', 'autre' => 'Autre']])
                        <div class="mb-3 ms-4"><input type="text" name="q10_autre" value="{{ old('q10_autre') }}" class="form-control" placeholder="Si autre, précisez..."></div>
                    </div>

                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>IV. Suggestions</h4>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">11. Quels aspects améliorer ? <em class="text-muted">(plusieurs choix)</em></label>
                            @php $aspects = old('q11_aspects', []); @endphp
                            @foreach(\App\Models\HealthInsuranceSurvey::aspectOptions() as $val => $lbl)
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="q11_aspects[]" value="{{ $val }}" id="asp_{{ $val }}" @if(in_array($val, $aspects)) checked @endif><label class="form-check-label" for="asp_{{ $val }}">{{ $lbl }}</label></div>
                            @endforeach
                            <input type="text" name="q11_autre" value="{{ old('q11_autre') }}" class="form-control mt-2" placeholder="Si autre, précisez...">
                        </div>
                        <div class="mb-3"><label class="form-label fw-semibold">12. Propositions concrètes</label><textarea name="q12_propositions" rows="4" class="form-control">{{ old('q12_propositions') }}</textarea></div>
                    </div>

                    <div class="mb-5">
                        <h4 class="text-primary mb-3"><i class="fas fa-star me-2"></i>V. Évaluation finale</h4>
                        <label class="form-label fw-semibold mb-3">13. Note sur 5</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach([1 => 'Très insatisfait', 2 => 'Insatisfait', 3 => 'Peu satisfait', 4 => 'Satisfait', 5 => 'Très satisfait'] as $n => $lbl)
                                <div class="form-check"><input class="form-check-input" type="radio" name="q13_note" id="note_{{ $n }}" value="{{ $n }}" @if((string)old('q13_note') === (string)$n) checked @endif required><label class="form-check-label" for="note_{{ $n }}"><strong>{{ $n }}</strong> — {{ $lbl }}</label></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-center mt-4"><button type="submit" class="btn btn-primary btn-lg px-5"><i class="fas fa-paper-plane me-2"></i>Envoyer mes réponses</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('is_anonymous').addEventListener('change', function() {
    const fields = document.getElementById('identity-fields');
    const inputs = fields.querySelectorAll('input');
    if (this.checked) { inputs.forEach(i => { i.value = ''; i.disabled = true; }); fields.style.opacity = '0.4'; }
    else { inputs.forEach(i => i.disabled = false); fields.style.opacity = '1'; }
});
</script>
@endsection
BLADEEOF

# Vue DRH index
sudo tee resources/views/admin/drh/health-survey/index.blade.php > /dev/null << 'BLADEEOF'
@extends('layouts.admin')
@section('title', 'Enquête Assurance Maladie')
@section('content')
@php use App\Models\HealthInsuranceSurvey; $labels = HealthInsuranceSurvey::questionLabels(); @endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-0"><i class="fas fa-poll-h text-primary me-2"></i>Enquête Assurance Maladie</h1><p class="text-muted mb-0">Résultats du questionnaire</p></div>
        <div>
            <a href="{{ route('admin.drh.health-survey.export') }}" class="btn btn-success"><i class="fas fa-file-csv me-1"></i> Exporter CSV</a>
            <a href="{{ route('public.health-survey.show') }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i> Formulaire</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Total réponses</div><div class="h2 mb-0 text-primary fw-bold">{{ $stats['total'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Note moyenne /5</div><div class="h2 mb-0 text-warning fw-bold">{{ $stats['avg_note'] }}<small class="text-muted fs-6">/5</small></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Satisfaits (≥4)</div>@php $sat = ($stats['by_note'][4] ?? 0) + ($stats['by_note'][5] ?? 0); @endphp<div class="h2 mb-0 text-success fw-bold">{{ $sat }}<small class="text-muted fs-6">/ {{ $stats['total'] }}</small></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Insatisfaits (≤2)</div>@php $insat = ($stats['by_note'][1] ?? 0) + ($stats['by_note'][2] ?? 0); @endphp<div class="h2 mb-0 text-danger fw-bold">{{ $insat }}<small class="text-muted fs-6">/ {{ $stats['total'] }}</small></div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Distribution des notes</strong></div>
        <div class="card-body">
            @php $labels_note = [1 => 'Très insatisfait', 2 => 'Insatisfait', 3 => 'Peu satisfait', 4 => 'Satisfait', 5 => 'Très satisfait']; @endphp
            @foreach($labels_note as $n => $lbl)
                @php $count = $stats['by_note'][$n] ?? 0; $pct = $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0; @endphp
                <div class="mb-2"><div class="d-flex justify-content-between small"><span>{{ $n }} — {{ $lbl }}</span><span class="text-muted">{{ $count }} ({{ $pct }}%)</span></div><div class="progress" style="height:10px;"><div class="progress-bar bg-{{ $n >= 4 ? 'success' : ($n == 3 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%"></div></div></div>
            @endforeach
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher..."></div>
                <div class="col-md-3"><select name="direction" class="form-select"><option value="">Toutes directions</option>@foreach($directions as $d)<option value="{{ $d }}" @selected(request('direction') === $d)>{{ $d }}</option>@endforeach</select></div>
                <div class="col-md-2"><select name="note" class="form-select"><option value="">Toutes notes</option>@for($i = 1; $i <= 5; $i++)<option value="{{ $i }}" @selected(request('note') == $i)>Note {{ $i }}/5</option>@endfor</select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filtrer</button></div>
                <div class="col-md-2"><a href="{{ route('admin.drh.health-survey.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Réponses ({{ $surveys->total() }})</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Agent</th><th>Direction</th><th>Région</th><th class="text-center">Note</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($surveys as $s)
                        <tr>
                            <td><small>{{ $s->submitted_at?->format('d/m/Y H:i') }}</small></td>
                            <td>@if($s->is_anonymous)<em class="text-muted">Anonyme</em>@else{{ trim(($s->agent_prenom ?? '') . ' ' . ($s->agent_nom ?? '')) ?: '—' }}@endif</td>
                            <td>{{ $s->agent_direction ?? '—' }}</td>
                            <td>{{ $s->agent_region ?? '—' }}</td>
                            <td class="text-center">@php $note = (int) $s->q13_note; @endphp<span class="badge bg-{{ $note >= 4 ? 'success' : ($note == 3 ? 'warning' : 'danger') }} fs-6">{{ $note }}/5</span></td>
                            <td class="text-end"><a href="{{ route('admin.drh.health-survey.show', $s->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune réponse.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())<div class="card-footer bg-white">{{ $surveys->links() }}</div>@endif
    </div>
</div>
@endsection
BLADEEOF

# Vue DRH show
sudo tee resources/views/admin/drh/health-survey/show.blade.php > /dev/null << 'BLADEEOF'
@extends('layouts.admin')
@section('title', 'Détail réponse')
@section('content')
@php
    use App\Models\HealthInsuranceSurvey;
    $labels = HealthInsuranceSurvey::questionLabels();
    $aspects = HealthInsuranceSurvey::aspectOptions();
    $resolve = fn($key, $value) => $labels[$key]['options'][$value] ?? ($value ?: '—');
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h4 mb-0"><i class="fas fa-poll text-primary me-2"></i>Détail de la réponse</h1><p class="text-muted mb-0">{{ $survey->submitted_at?->format('d/m/Y à H:i') }}</p></div>
        <a href="{{ route('admin.drh.health-survey.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><strong>Agent</strong></div>
        <div class="card-body">
            @if($survey->is_anonymous)<div class="alert alert-info mb-0"><i class="fas fa-user-secret me-2"></i>Réponse anonyme.</div>
            @else<dl class="row mb-0"><dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ trim(($survey->agent_prenom ?? '') . ' ' . ($survey->agent_nom ?? '')) ?: '—' }}</dd><dt class="col-sm-3">Direction</dt><dd class="col-sm-9">{{ $survey->agent_direction ?? '—' }}</dd><dt class="col-sm-3">Région</dt><dd class="col-sm-9">{{ $survey->agent_region ?? '—' }}</dd></dl>@endif
        </div>
    </div>

    @php
        $sections = [
            'I. Compréhension' => [['q1_info_level','Q1. '.$labels['q1_info_level']['label']],['q2_documents_clarity','Q2. '.$labels['q2_documents_clarity']['label']],['q3_difficulty','Q3. '.$labels['q3_difficulty']['label']]],
            'II. Qualité' => [['q4_soins_response','Q4. '.$labels['q4_soins_response']['label']],['q5_panier_soins','Q5. '.$labels['q5_panier_soins']['label']],['q6_delais_remboursement','Q6. '.$labels['q6_delais_remboursement']['label']],['q7_service_client','Q7. '.$labels['q7_service_client']['label']]],
            'III. Satisfaction' => [['q9_coassurance','Q9. '.$labels['q9_coassurance']['label']],['q10_reseau_soins','Q10. '.$labels['q10_reseau_soins']['label']]],
        ];
    @endphp

    @foreach($sections as $title => $items)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white"><strong>{{ $title }}</strong></div>
            <div class="card-body">
                <dl class="row mb-0">@foreach($items as [$key, $label])<dt class="col-sm-6">{{ $label }}</dt><dd class="col-sm-6"><span class="badge bg-light text-dark border">{{ $resolve($key, $survey->$key) }}</span></dd>@endforeach</dl>
                @if($title === 'II. Qualité' && $survey->q8_probleme_recent)<hr><strong>Q8 :</strong> <p class="text-muted mb-0">{{ $survey->q8_probleme_recent }}</p>@endif
                @if($title === 'III. Satisfaction')@if($survey->q9_autre)<hr><strong>Q9 autre :</strong> <em>{{ $survey->q9_autre }}</em>@endif @if($survey->q10_autre)<hr><strong>Q10 autre :</strong> <em>{{ $survey->q10_autre }}</em>@endif @endif
            </div>
        </div>
    @endforeach

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><strong>IV. Suggestions</strong></div>
        <div class="card-body">
            <strong>Q11. Aspects à améliorer :</strong><div class="mt-2 mb-3">@forelse((array) $survey->q11_aspects as $a)<span class="badge bg-primary me-1">{{ $aspects[$a] ?? $a }}</span>@empty<span class="text-muted">— Aucun</span>@endforelse</div>
            @if($survey->q11_autre)<p><strong>Autre :</strong> {{ $survey->q11_autre }}</p>@endif
            <strong>Q12. Propositions :</strong><p class="text-muted">{{ $survey->q12_propositions ?: '—' }}</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><strong>V. Évaluation finale</strong></div>
        <div class="card-body text-center">
            @php $note = (int) $survey->q13_note; $color = $note >= 4 ? 'success' : ($note == 3 ? 'warning' : 'danger'); $lbl = [1 => 'Très insatisfait', 2 => 'Insatisfait', 3 => 'Peu satisfait', 4 => 'Satisfait', 5 => 'Très satisfait'][$note] ?? '—'; @endphp
            <div class="display-3 text-{{ $color }} fw-bold">{{ $note }}/5</div>
            <p class="lead text-muted">{{ $lbl }}</p>
            <div>@for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $note ? 'text-warning' : 'text-muted opacity-25' }}"></i>@endfor</div>
        </div>
    </div>
</div>
@endsection
BLADEEOF
echo "✅ Toutes les vues créées"

echo "=== 5. Routes ==="
# Ajouter les routes si pas déjà là
if ! grep -q "health-survey" routes/web.php; then
    # Ajouter dans le groupe admin/drh
    sudo python3 << 'PY'
path = 'routes/web.php'
content = open(path).read()

# Ajouter dans le groupe DRH existant
old = """    Route::get('/avances-tabaski/print', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'exportPdf'])->name('tabaski.print');
});"""
new = """    Route::get('/avances-tabaski/print', [\\App\\Http\\Controllers\\Drh\\AvanceTabaskiController::class, 'exportPdf'])->name('tabaski.print');

    // Enquête Assurance Maladie
    Route::get('/enquete-assurance',        [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'index'])->name('health-survey.index');
    Route::get('/enquete-assurance/export', [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'exportCsv'])->name('health-survey.export');
    Route::get('/enquete-assurance/{id}',   [\\App\\Http\\Controllers\\Drh\\HealthSurveyController::class, 'show'])->name('health-survey.show');
});

// Formulaire public enquête assurance maladie
Route::prefix('enquete-assurance-maladie')->name('public.health-survey.')->group(function () {
    Route::get('/',      [\\App\\Http\\Controllers\\Public\\HealthInsuranceSurveyController::class, 'show'])->name('show');
    Route::post('/',     [\\App\\Http\\Controllers\\Public\\HealthInsuranceSurveyController::class, 'store'])->name('submit');
    Route::get('/merci', [\\App\\Http\\Controllers\\Public\\HealthInsuranceSurveyController::class, 'thanks'])->name('thanks');
});"""

if old in content:
    open(path, 'w').write(content.replace(old, new))
    print("OK routes ajoutées")
else:
    print("⚠️  Pattern non trouvé — ajouter routes manuellement")
PY
else
    echo "↩ Routes déjà présentes"
fi

echo "=== 6. Permissions ==="
sudo chown -R www-data:www-data app/Models/HealthInsuranceSurvey.php app/Http/Controllers/Drh/HealthSurveyController.php app/Http/Controllers/Public/HealthInsuranceSurveyController.php resources/views/public/health-insurance-survey* resources/views/public/partials resources/views/admin/drh/health-survey database/migrations/2026_05_11_*

echo "=== 7. Migration + cache ==="
sudo -u www-data php artisan migrate --force
sudo php artisan route:clear
sudo php artisan view:clear
sudo php artisan config:clear

echo ""
echo "==========================================="
echo "✅ DÉPLOIEMENT TERMINÉ !"
echo ""
echo "👉 Tester :"
echo "   Agents : https://csar.sn/enquete-assurance-maladie"
echo "   DRH    : https://csar.sn/admin/drh/enquete-assurance"
echo "==========================================="
