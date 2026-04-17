<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AgentTabaski;
use App\Models\AvanceTabaski;
use App\Models\TabaskiConfig;
use Illuminate\Http\Request;

class TabaskiController extends Controller
{
    public function form()
    {
        $expire  = TabaskiConfig::dateExpiration();
        $ferme   = TabaskiConfig::estFerme();
        $regions = AgentTabaski::select('region')->whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region');
        return view('public.tabaski.form', compact('ferme', 'expire', 'regions'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'prenom' => 'required|string|min:2',
            'nom'    => 'required|string|min:2',
            'region' => 'nullable|string',
        ]);

        $agents = AgentTabaski::rechercher($request->prenom, $request->nom, $request->region);

        if ($agents->isEmpty()) {
            return response()->json(['found' => false, 'message' => 'Aucun agent trouvé avec ces informations.']);
        }

        return response()->json([
            'found'  => true,
            'agents' => $agents->map(function ($a) {
                return [
                    'id'        => $a->id,
                    'prenom'    => $a->prenom,
                    'nom'       => $a->nom,
                    'poste'     => $a->poste,
                    'direction' => $a->direction,
                    'region'    => $a->region,
                    'deja_inscrit' => $a->inscription()->exists(),
                ];
            }),
        ]);
    }

    public function submit(Request $request)
    {
        if (TabaskiConfig::estFerme()) {
            return response()->json(['success' => false, 'message' => 'Les inscriptions pour l\'avance Tabaski sont clôturées.'], 403);
        }

        $request->validate([
            'agent_id' => 'required|exists:agents_tabaski,id',
            'montant'  => 'required|in:100000,150000,200000',
        ]);

        $agent = AgentTabaski::findOrFail($request->agent_id);

        if ($agent->inscription()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà effectué votre demande d\'avance Tabaski.',
            ], 422);
        }

        AvanceTabaski::create([
            'agent_id'         => $agent->id,
            'montant'          => $request->montant,
            'ip_address'       => $request->ip(),
            'date_inscription' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre demande a bien été enregistrée !',
            'agent'   => [
                'prenom'    => $agent->prenom,
                'nom'       => $agent->nom,
                'direction' => $agent->direction,
                'region'    => $agent->region,
                'poste'     => $agent->poste,
                'montant'   => number_format((int) $request->montant, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }
}
