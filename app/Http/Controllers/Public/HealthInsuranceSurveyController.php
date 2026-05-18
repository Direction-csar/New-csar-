<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HealthInsuranceSurvey;
use Illuminate\Http\Request;

class HealthInsuranceSurveyController extends Controller
{
    public function show()
    {
        return view('public.health-insurance-survey');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'is_anonymous'           => 'nullable|boolean',
            'agent_nom'              => 'nullable|string|max:100',
            'agent_prenom'           => 'nullable|string|max:100',
            'agent_direction'        => 'nullable|string|max:150',
            'agent_region'           => 'nullable|string|max:100',

            'q1_info_level'          => 'required|in:totalement,partiellement,non',
            'q2_documents_clarity'   => 'required|in:tres_clairs,moyennement,peu_clairs',
            'q3_difficulty'          => 'required|in:jamais,parfois,souvent',

            'q4_soins_response'      => 'required|in:largement,avec_limites,non',
            'q5_panier_soins'        => 'required|in:tres_suffisant,assez_suffisant,insuffisant',
            'q6_delais_remboursement'=> 'required|in:rapides,acceptables,longs',
            'q7_service_client'      => 'required|in:oui,non',
            'q8_probleme_recent'     => 'nullable|string|max:2000',

            'q9_coassurance'         => 'required|in:tres_satisfait,satisfait,pas_satisfait,autre',
            'q9_autre'               => 'nullable|string|max:500',
            'q10_reseau_soins'       => 'required|in:tres_accessible,accessible,pas_accessible,autre',
            'q10_autre'              => 'nullable|string|max:500',

            'q11_aspects'            => 'nullable|array',
            'q11_aspects.*'          => 'in:etendue_soins,rapidite_rembour,communication,autre',
            'q11_autre'              => 'nullable|string|max:500',
            'q12_propositions'       => 'nullable|string|max:2000',

            'q13_note'               => 'required|integer|between:1,5',
        ]);

        $isAnonymous = (bool) $request->boolean('is_anonymous');
        if ($isAnonymous) {
            $validated['agent_nom']    = null;
            $validated['agent_prenom'] = null;
        }

        $validated['is_anonymous'] = $isAnonymous;
        $validated['ip_address']   = $request->ip();
        $validated['user_agent']   = substr((string) $request->userAgent(), 0, 500);
        $validated['submitted_at'] = now();

        HealthInsuranceSurvey::create($validated);

        return redirect()
            ->route('public.health-survey.thanks')
            ->with('success', 'Merci ! Votre questionnaire a été enregistré avec succès.');
    }

    public function thanks()
    {
        return view('public.health-insurance-survey-thanks');
    }
}
