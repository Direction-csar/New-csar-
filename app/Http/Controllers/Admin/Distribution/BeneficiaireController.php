<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Beneficiaire;
use App\Models\BonMatiere;
use App\Models\Planning;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AlerteService;
use App\Services\DoublonService;

class BeneficiaireController extends Controller
{
    public function index()
    {
        $beneficiaires = Beneficiaire::with('planning', 'bonMatieres.ticket')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.distribution.beneficiaires.index', compact('beneficiaires'));
    }

    public function create()
    {
        $plannings = Planning::where('status', '!=', 'closed')
            ->with('campaign')
            ->orderBy('name')
            ->get();
        return view('admin.distribution.beneficiaires.form', compact('plannings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'planning_id' => 'required|exists:plannings,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'category' => 'required|string|max:100',
            'vulnerable' => 'boolean',
            'religious' => 'boolean',
            'spontaneous' => 'boolean',
            'status' => 'required|in:active,blocked',
            'quantite_kg' => 'required|numeric|min:0.01',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['vulnerable'] = $request->boolean('vulnerable');
        $validated['religious'] = $request->boolean('religious');
        $validated['spontaneous'] = $request->boolean('spontaneous');

        $beneficiaire = null;
        $planning = null;

        DB::transaction(function () use ($validated, $request, &$beneficiaire, &$planning) {
            $planning = Planning::findOrFail($validated['planning_id']);

            $beneficiaire = Beneficiaire::create($validated);

            do {
                $numeroBon = 'BM-' . $planning->id . '-' . strtoupper(bin2hex(random_bytes(4)));
            } while (BonMatiere::where('numero_bon', $numeroBon)->exists());

            $bon = BonMatiere::create([
                'planning_id' => $planning->id,
                'beneficiaire_id' => $beneficiaire->id,
                'numero_bon' => $numeroBon,
                'quantite_kg' => $validated['quantite_kg'],
                'categorie' => $planning->category,
                'statut' => 'attribue',
                'attributed_at' => now(),
                'attributed_by' => Auth::id(),
            ]);

            $planning->increment('executed_quota_kg', $validated['quantite_kg']);
            $planning->campaign->increment('executed_stock_kg', $validated['quantite_kg']);

            do {
                $code = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
            } while (Ticket::where('code', $code)->exists());

            Ticket::create([
                'bon_matiere_id' => $bon->id,
                'code' => $code,
                'qr_data' => json_encode([
                    'bon_id' => $bon->id,
                    'numero_bon' => $numeroBon,
                    'code' => $code,
                    'beneficiaire' => $beneficiaire->name,
                ]),
            ]);
        });

        assert($beneficiaire !== null && $planning !== null);

        DoublonService::detecter($beneficiaire);
        AlerteService::verifierPlanning($planning);
        AlerteService::verifierCampaign($planning->campaign);

        return redirect()->route('admin.distribution.beneficiaires.index')
            ->with('success', 'Bénéficiaire, bon et ticket créés avec succès.');
    }

    public function edit(Beneficiaire $beneficiaire)
    {
        $plannings = Planning::where('status', '!=', 'closed')
            ->with('campaign')
            ->orderBy('name')
            ->get();
        return view('admin.distribution.beneficiaires.form', compact('beneficiaire', 'plannings'));
    }

    public function update(Request $request, Beneficiaire $beneficiaire)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'category' => 'required|string|max:100',
            'vulnerable' => 'boolean',
            'religious' => 'boolean',
            'spontaneous' => 'boolean',
            'status' => 'required|in:active,blocked',
        ]);

        $validated['vulnerable'] = $request->boolean('vulnerable');
        $validated['religious'] = $request->boolean('religious');
        $validated['spontaneous'] = $request->boolean('spontaneous');

        $beneficiaire->update($validated);

        return redirect()->route('admin.distribution.beneficiaires.index')
            ->with('success', 'Bénéficiaire mis à jour.');
    }

    public function destroy(Beneficiaire $beneficiaire)
    {
        DB::transaction(function () use ($beneficiaire) {
            foreach ($beneficiaire->bonMatieres as $bon) {
                $bon->planning->decrement('executed_quota_kg', $bon->quantite_kg);
                $bon->planning->campaign->decrement('executed_stock_kg', $bon->quantite_kg);
                $bon->ticket()->delete();
                $bon->delete();
            }
            $beneficiaire->delete();
        });

        return redirect()->route('admin.distribution.beneficiaires.index')
            ->with('success', 'Bénéficiaire et bons associés supprimés.');
    }
}
