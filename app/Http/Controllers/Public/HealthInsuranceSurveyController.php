<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HealthInsuranceSurvey;
use Illuminate\Http\Request;

class HealthInsuranceSurveyController extends Controller
{
    private function validationRules(): array
    {
        return [
            'is_anonymous'           => 'nullable|boolean',
            'agent_nom'              => 'nullable|string|max:100',
            'agent_prenom'           => 'nullable|string|max:100',

            'q1_info_level'          => 'required|in:totalement,partiellement,non,pas_concerne',
            'q2_documents_clarity'   => 'required|in:tres_clairs,moyennement,peu_clairs,pas_concerne',
            'q3_difficulty'          => 'required|in:jamais,parfois,souvent,pas_concerne',

            'q4_soins_response'      => 'required|in:largement,avec_limites,non,pas_concerne',
            'q5_panier_soins'        => 'required|in:tres_suffisant,assez_suffisant,insuffisant,pas_concerne',
            'q6_delais_remboursement'=> 'required|in:rapides,acceptables,longs,pas_concerne',
            'q7_service_client'      => 'required|in:oui,non,pas_concerne',
            'q8_probleme_recent'     => 'nullable|string|max:2000',

            'q9_coassurance'         => 'required|in:tres_satisfait,satisfait,pas_satisfait,autre,pas_concerne',
            'q9_autre'               => 'nullable|string|max:500',
            'q10_reseau_soins'       => 'required|in:tres_accessible,accessible,pas_accessible,autre,pas_concerne',
            'q10_autre'              => 'nullable|string|max:500',

            'q11_aspects'            => 'nullable|array',
            'q11_aspects.*'          => 'in:etendue_soins,rapidite_rembour,communication,autre',
            'q11_autre'              => 'nullable|string|max:500',
            'q12_propositions'       => 'nullable|string|max:2000',

            'q13_note'               => 'required|integer|between:1,5',
        ];
    }

    public function show(Request $request)
    {
        // Si on revient modifier un draft
        if ($request->filled('draft_id')) {
            $draft = HealthInsuranceSurvey::find($request->draft_id);
            if ($draft && $draft->canEdit()) {
                return view('public.health-insurance-survey', ['draft' => $draft]);
            }
        }
        return view('public.health-insurance-survey');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $isAnonymous = (bool) $request->boolean('is_anonymous');
        if ($isAnonymous) {
            $validated['agent_nom']    = null;
            $validated['agent_prenom'] = null;
        }

        $validated['is_anonymous'] = $isAnonymous;
        $validated['ip_address']   = $request->ip();
        $validated['user_agent']   = substr((string) $request->userAgent(), 0, 500);
        $validated['submitted_at'] = now();
        $validated['status']       = 'draft';
        $validated['expires_at']   = now()->addMinutes(10);

        $survey = HealthInsuranceSurvey::create($validated);

        return redirect()
            ->route('public.health-survey.pending', $survey->id)
            ->with('draft_id', $survey->id);
    }

    public function pending(HealthInsuranceSurvey $survey)
    {
        if ($survey->status !== 'draft' || $survey->isExpired()) {
            abort(404);
        }

        return view('public.health-insurance-survey-pending', compact('survey'));
    }

    public function confirm(HealthInsuranceSurvey $survey)
    {
        if ($survey->status !== 'draft' || $survey->isExpired()) {
            abort(404);
        }

        $survey->update([
            'status'     => 'confirmed',
            'expires_at' => null,
        ]);

        return redirect()
            ->route('public.health-survey.thanks')
            ->with('success', 'Merci ! Votre questionnaire a été confirmé et enregistré définitivement.');
    }

    public function editDraft(HealthInsuranceSurvey $survey)
    {
        if (!$survey->canEdit()) {
            abort(404);
        }

        return redirect()->route('public.health-survey.show', ['draft_id' => $survey->id]);
    }

    public function updateDraft(Request $request, HealthInsuranceSurvey $survey)
    {
        if (!$survey->canEdit()) {
            abort(404);
        }

        $validated = $request->validate($this->validationRules());

        $isAnonymous = (bool) $request->boolean('is_anonymous');
        if ($isAnonymous) {
            $validated['agent_nom']    = null;
            $validated['agent_prenom'] = null;
        }

        $validated['is_anonymous'] = $isAnonymous;
        $validated['ip_address']   = $request->ip();
        $validated['user_agent']   = substr((string) $request->userAgent(), 0, 500);
        $validated['submitted_at'] = now();
        $validated['expires_at']   = now()->addMinutes(10);

        $survey->update($validated);

        return redirect()
            ->route('public.health-survey.pending', $survey->id)
            ->with('draft_id', $survey->id);
    }

    public function thanks()
    {
        return view('public.health-insurance-survey-thanks');
    }
}
