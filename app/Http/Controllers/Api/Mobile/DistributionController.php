<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Beneficiaire;
use App\Models\Planning;
use App\Models\Ticket;
use App\Services\BeneficiaireDistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        if (!Planning::where('id', $validated['planning_id'])->whereHas('agents', function ($q) {
            $q->where('users.id', Auth::id());
        })->exists()) {
            return response()->json(['success' => false, 'message' => 'Planning non autorisé.'], 403);
        }

        $beneficiaire = BeneficiaireDistributionService::create($validated, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Bénéficiaire, bon et ticket créés.',
            'data' => [
                'beneficiaire' => $beneficiaire->load('bonMatieres.ticket'),
            ],
        ]);
    }

    public function batch(Request $request): JsonResponse
    {
        $request->validate(['beneficiaires' => 'required|array|max:100']);

        $created = [];
        $conflicts = [];
        $errors = [];

        foreach ($request->input('beneficiaires') as $i => $item) {
            $validator = Validator::make($item, [
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

            if ($validator->fails()) {
                $errors[] = ['index' => $i, 'errors' => $validator->errors()->toArray()];
                continue;
            }

            $data = $validator->validated();

            if (!Planning::where('id', $data['planning_id'])->whereHas('agents', function ($q) {
                $q->where('users.id', Auth::id());
            })->exists()) {
                $errors[] = ['index' => $i, 'message' => 'Planning non autorisé.'];
                continue;
            }

            $existing = Beneficiaire::where('planning_id', $data['planning_id'])
                ->where(function ($q) use ($data) {
                    if (!empty($data['phone'])) {
                        $q->orWhere('phone', $data['phone']);
                    }
                    if (!empty($data['cni'])) {
                        $q->orWhere('cni', $data['cni']);
                    }
                })
                ->first();

            if ($existing) {
                $conflicts[] = ['index' => $i, 'beneficiaire' => $existing, 'message' => 'Téléphone ou CNI déjà inscrit.'];
                continue;
            }

            try {
                $beneficiaire = BeneficiaireDistributionService::create($data, Auth::id());
                $created[] = $beneficiaire->load('bonMatieres.ticket');
            } catch (\Throwable $e) {
                $errors[] = ['index' => $i, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => true,
            'created_count' => count($created),
            'conflicts_count' => count($conflicts),
            'errors_count' => count($errors),
            'created' => $created,
            'conflicts' => $conflicts,
            'errors' => $errors,
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
