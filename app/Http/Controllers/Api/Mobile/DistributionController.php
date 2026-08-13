<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Beneficiaire;
use App\Models\BonMatiere;
use App\Models\Planning;
use App\Models\Ticket;
use App\Services\AlerteService;
use App\Services\DoublonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    public function sync(): JsonResponse
    {
        $plannings = Planning::whereHas('agents', function ($q) {
            $q->where('users.id', Auth::id());
        })
            ->where('status', '!=', 'closed')
            ->with('beneficiaires.bonMatieres.ticket', 'campaign', 'warehouse')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plannings,
        ]);
    }

    public function storeBeneficiaire(Request $request): JsonResponse
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
            'quantite_kg' => 'required|numeric|min:0.01',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['vulnerable'] = $request->boolean('vulnerable');
        $validated['religious'] = $request->boolean('religious');
        $validated['spontaneous'] = $request->boolean('spontaneous');
        $validated['status'] = 'active';

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

        return response()->json([
            'success' => true,
            'message' => 'Bénéficiaire, bon et ticket créés.',
            'data' => [
                'beneficiaire' => $beneficiaire->load('bonMatieres.ticket'),
            ],
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $ticket = Ticket::where('code', $validated['code'])->with('bonMatiere.planning')->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket non trouvé.'], 404);
        }

        if ($ticket->used) {
            return response()->json(['success' => false, 'message' => 'Ticket déjà utilisé.'], 422);
        }

        $bon = $ticket->bonMatiere;

        DB::transaction(function () use ($ticket, $bon) {
            $ticket->update([
                'used' => true,
                'used_at' => now(),
                'used_by' => Auth::id(),
            ]);

            $bon->update([
                'statut' => 'livre',
                'delivered_at' => now(),
                'delivered_by' => Auth::id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Retrait validé.',
            'data' => [
                'beneficiaire' => $bon->beneficiaire->name,
                'quantite_kg' => $bon->quantite_kg,
                'delivered_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function ticket(string $code): JsonResponse
    {
        $ticket = Ticket::where('code', $code)->with('bonMatiere.beneficiaire', 'bonMatiere.planning.campaign')->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket non trouvé.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }
}
