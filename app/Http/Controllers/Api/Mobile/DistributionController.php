<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Beneficiaire;
use App\Models\Planning;
use App\Models\Ticket;
use App\Models\DistributionEvent;
use App\Models\DistributionPlanning;
use App\Models\DistributionBeneficiary;
use App\Models\DistributionTicket;
use App\Models\DistributionScanLog;
use App\Services\BeneficiaireDistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    // =====================
    // Nouvelles méthodes
    // =====================

    public function storeBeneficiaryV2(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'planning_id' => 'required|exists:distribution_plannings,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_vulnerable' => 'boolean',
            'is_pregnant' => 'boolean',
            'is_elderly' => 'boolean',
            'is_disabled' => 'boolean',
            'quantity_kg' => 'required|numeric|min:0.01',
        ]);

        $planning = DistributionPlanning::where('id', $validated['planning_id'])
            ->where(function ($q) {
                $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to');
            })
            ->firstOrFail();

        $beneficiary = DistributionBeneficiary::create([
            'planning_id' => $validated['planning_id'],
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'cni' => $validated['cni'] ?? null,
            'address' => $validated['address'] ?? null,
            'category' => $validated['category'] ?? 'general',
            'is_vulnerable' => $validated['is_vulnerable'] ?? false,
            'is_pregnant' => $validated['is_pregnant'] ?? false,
            'is_elderly' => $validated['is_elderly'] ?? false,
            'is_disabled' => $validated['is_disabled'] ?? false,
            'quantity_kg' => $validated['quantity_kg'],
            'status' => DistributionBeneficiary::STATUS_PENDING,
            'registered_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bénéficiaire enregistré avec succès.',
            'data' => $beneficiary,
        ]);
    }

    public function events(): JsonResponse
    {
        $events = DistributionEvent::where('status', 'active')
            ->with(['plannings' => function ($q) {
                $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to');
            }])
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function eventPlannings(int $id): JsonResponse
    {
        $event = DistributionEvent::findOrFail($id);

        $plannings = $event->plannings()
            ->where(function ($q) {
                $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to');
            })
            ->where('status', '!=', 'cancelled')
            ->withCount(['beneficiaries', 'tickets'])
            ->get();

        return response()->json([
            'success' => true,
            'event' => $event,
            'data' => $plannings,
        ]);
    }

    public function myPlannings(): JsonResponse
    {
        $plannings = DistributionPlanning::where('assigned_to', Auth::id())
            ->where('status', 'active')
            ->with('event')
            ->withCount(['beneficiaries', 'tickets'])
            ->orderBy('distribution_date', 'asc')
            ->get();

        $plannings->each(function ($p) {
            $p->validated_count = $p->beneficiaries()->where('status', 'validated')->count() +
                $p->beneficiaries()->where('status', 'ticket_issued')->count() +
                $p->beneficiaries()->where('status', 'kit_collected')->count();
            $p->collected_count = $p->beneficiaries()->where('status', 'kit_collected')->count();
            $p->tickets_count = $p->tickets()->whereIn('status', ['issued', 'scanned', 'collected'])->count();
        });

        return response()->json([
            'success' => true,
            'data' => $plannings,
        ]);
    }

    public function planningBeneficiaries(int $id): JsonResponse
    {
        $planning = DistributionPlanning::where('id', $id)
            ->where(function ($q) {
                $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to');
            })
            ->firstOrFail();

        $beneficiaries = $planning->beneficiaries()
            ->with('tickets')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'planning' => $planning,
            'data' => $beneficiaries,
        ]);
    }

    public function validateBeneficiary(int $id, Request $request): JsonResponse
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);

        $planning = DistributionPlanning::where('id', $beneficiary->planning_id)
            ->where(function ($q) {
                $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to');
            })
            ->firstOrFail();

        if ($beneficiary->status !== DistributionBeneficiary::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bénéficiaire est déjà validé.',
            ], 422);
        }

        $beneficiary->update([
            'status' => DistributionBeneficiary::STATUS_VALIDATED,
            'validated_at' => now(),
            'validated_by' => Auth::id(),
        ]);

        $planning->increment('executed_kg', (float) $beneficiary->quantity_kg);

        return response()->json([
            'success' => true,
            'message' => 'Bénéficiaire validé avec succès.',
            'data' => $beneficiary->fresh(),
        ]);
    }

    public function generateTicket(int $id): JsonResponse
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);

        $planning = DistributionPlanning::where('id', $beneficiary->planning_id)
            ->where(function ($q) {
                $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to');
            })
            ->firstOrFail();

        if (!in_array($beneficiary->status, [DistributionBeneficiary::STATUS_VALIDATED, DistributionBeneficiary::STATUS_TICKET_ISSUED])) {
            return response()->json([
                'success' => false,
                'message' => 'Le bénéficiaire doit être validé avant de générer un ticket.',
            ], 422);
        }

        $existingTicket = $beneficiary->tickets()->whereIn('status', [
            DistributionTicket::STATUS_ISSUED,
            DistributionTicket::STATUS_SCANNED,
            DistributionTicket::STATUS_COLLECTED,
        ])->first();

        if ($existingTicket) {
            return response()->json([
                'success' => false,
                'message' => 'Un ticket actif existe déjà pour ce bénéficiaire.',
                'data' => $existingTicket,
            ], 422);
        }

        $ticketCode = 'CSAR-' . strtoupper(Str::random(8));
        $qrToken = Str::uuid()->toString();

        $ticket = DistributionTicket::create([
            'beneficiary_id' => $beneficiary->id,
            'planning_id' => $beneficiary->planning_id,
            'ticket_code' => $ticketCode,
            'qr_token' => $qrToken,
            'status' => DistributionTicket::STATUS_ISSUED,
            'issued_at' => now(),
        ]);

        $beneficiary->update(['status' => DistributionBeneficiary::STATUS_TICKET_ISSUED]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket généré avec succès.',
            'data' => $ticket->load('beneficiary', 'planning'),
        ]);
    }

    public function collectKit(string $qrToken, Request $request): JsonResponse
    {
        $ticket = DistributionTicket::where('qr_token', $qrToken)
            ->orWhere('ticket_code', $qrToken)
            ->with('beneficiary', 'planning')
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket non trouvé.',
            ], 404);
        }

        if ($ticket->status === DistributionTicket::STATUS_COLLECTED) {
            return response()->json([
                'success' => false,
                'message' => 'Kit déjà récupéré.',
                'data' => [
                    'collected_at' => $ticket->collected_at?->toIso8601String(),
                ],
            ], 422);
        }

        if ($ticket->status === DistributionTicket::STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket annulé.',
            ], 422);
        }

        DB::transaction(function () use ($ticket, $request) {
            $ticket->update([
                'status' => DistributionTicket::STATUS_COLLECTED,
                'scanned_at' => now(),
                'collected_at' => now(),
                'scanned_by' => Auth::id(),
                'scan_location' => $request->input('location'),
            ]);

            $ticket->beneficiary->update([
                'status' => DistributionBeneficiary::STATUS_KIT_COLLECTED,
            ]);

            DistributionScanLog::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => DistributionScanLog::ACTION_COLLECT,
                'notes' => $request->input('notes'),
                'device_info' => $request->input('device_info'),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Récupération du kit enregistrée avec succès.',
            'data' => [
                'ticket_code' => $ticket->ticket_code,
                'beneficiaire' => [
                    'full_name' => $ticket->beneficiary->full_name,
                    'quantity_kg' => $ticket->beneficiary->quantity_kg,
                    'phone' => $ticket->beneficiary->phone,
                ],
                'beneficiary' => $ticket->beneficiary,
                'planning' => $ticket->planning,
                'collected_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'planning_id' => 'required|exists:distribution_plannings,id',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'full_name' => 'nullable|string|max:255',
        ]);

        $query = DistributionBeneficiary::where('planning_id', $request->planning_id);

        $duplicates = [];

        if ($request->filled('phone')) {
            $match = (clone $query)->where('phone', $request->phone)->first();
            if ($match) {
                $duplicates[] = [
                    'field' => 'phone',
                    'value' => $request->phone,
                    'existing_beneficiary' => $match->only(['id', 'full_name', 'phone', 'status']),
                ];
            }
        }

        if ($request->filled('cni')) {
            $match = (clone $query)->where('cni', $request->cni)->first();
            if ($match) {
                $duplicates[] = [
                    'field' => 'cni',
                    'value' => $request->cni,
                    'existing_beneficiary' => $match->only(['id', 'full_name', 'cni', 'status']),
                ];
            }
        }

        if ($request->filled('full_name')) {
            $match = (clone $query)->where('full_name', 'ILIKE', $request->full_name)->first();
            if ($match) {
                $duplicates[] = [
                    'field' => 'full_name',
                    'value' => $request->full_name,
                    'existing_beneficiary' => $match->only(['id', 'full_name', 'phone', 'status']),
                ];
            }
        }

        $firstDup = count($duplicates) > 0 ? $duplicates[0]['existing_beneficiary'] : null;

        return response()->json([
            'success' => true,
            'has_duplicate' => count($duplicates) > 0,
            'data' => $firstDup,
            'duplicates' => $duplicates,
        ]);
    }
}
