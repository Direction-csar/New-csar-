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
use App\Models\DistributionPlanningAgent;
use App\Models\DistributionScanAttempt;
use App\Models\DistributionScanLog;
use App\Services\BeneficiaireDistributionService;
use App\Services\Distribution\PlanningAccess;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class DistributionController extends Controller
{
    /**
     * Un admin/super_admin n'est pas limite aux plannings qui lui sont affectes.
     */
    private function isAdminUser(): bool
    {
        return PlanningAccess::isAdmin(Auth::user());
    }

    /**
     * Planning sur lequel l'agent est affecte comme DISTRIBUTEUR (ou admin).
     * 404 si le planning n'existe pas ou n'est pas affecte a l'agent : on ne
     * revele pas l'existence des plannings d'autres agents. Un planning sans
     * agent affecte n'est accessible qu'a l'administrateur.
     */
    private function distributorPlanningOrFail(int $id): DistributionPlanning
    {
        $planning = DistributionPlanning::find($id);

        if (!PlanningAccess::canDistribute(Auth::user(), $planning)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Planning introuvable ou non autorisé.',
            ], 404));
        }

        return $planning;
    }

    /** Deconnexion serveur : revoque le jeton courant (les jetons ne sont plus orphelins). */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['success' => true, 'message' => 'Déconnecté.']);
    }

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

        // Cloisonnement : seuls les agents affectes au planning (pivot
        // planning_user) ou un admin peuvent consommer ce ticket.
        $planning = $bon?->planning;
        if ($planning && !$this->isAdminUser()
            && !$planning->agents()->where('users.id', Auth::id())->exists()) {
            return response()->json(['success' => false, 'message' => 'Planning non autorisé.'], 403);
        }

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

        $this->distributorPlanningOrFail((int) $validated['planning_id']);

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

    /**
     * Import en masse V2 : plusieurs beneficiaires en un seul appel.
     * Chaque ligne est validee independamment ; les doublons (telephone/CNI/nom
     * exact dans le meme planning) sont signales mais ne bloquent pas les autres.
     */
    public function batchBeneficiaries(Request $request): JsonResponse
    {
        $request->validate([
            'beneficiaries' => 'required|array|min:1|max:200',
            'beneficiaries.*.planning_id' => 'required|exists:distribution_plannings,id',
            'beneficiaries.*.full_name' => 'required|string|max:255',
            'beneficiaries.*.phone' => 'nullable|string|max:20',
            'beneficiaries.*.cni' => 'nullable|string|max:100',
            'beneficiaries.*.address' => 'nullable|string|max:500',
            'beneficiaries.*.category' => 'nullable|string|max:100',
            'beneficiaries.*.quantity_kg' => 'required|numeric|min:0.01',
            'beneficiaries.*.is_vulnerable' => 'boolean',
            'beneficiaries.*.is_pregnant' => 'boolean',
            'beneficiaries.*.is_elderly' => 'boolean',
            'beneficiaries.*.is_disabled' => 'boolean',
        ]);

        $created = [];
        $skipped = [];

        foreach ($request->input('beneficiaries') as $i => $item) {
            $planning = DistributionPlanning::find($item['planning_id']);
            if (!PlanningAccess::canDistribute(Auth::user(), $planning)) {
                $skipped[] = ['index' => $i, 'name' => $item['full_name'] ?? null, 'reason' => 'Planning non autorise.'];
                continue;
            }

            $dupQuery = DistributionBeneficiary::where('planning_id', $planning->id);
            $dup = $dupQuery->where(function ($q) use ($item) {
                $q->where('full_name', trim((string) $item['full_name']));
                if (!empty($item['phone'])) {
                    $q->orWhere('phone', $item['phone']);
                }
                if (!empty($item['cni'])) {
                    $q->orWhere('cni', $item['cni']);
                }
            })->first();

            if ($dup) {
                $skipped[] = [
                    'index' => $i,
                    'name' => $item['full_name'],
                    'reason' => "Doublon : {$dup->full_name} existe deja (statut {$dup->status}).",
                ];
                continue;
            }

            $beneficiary = DistributionBeneficiary::create([
                'planning_id' => $planning->id,
                'full_name' => trim((string) $item['full_name']),
                'phone' => $item['phone'] ?? null,
                'cni' => $item['cni'] ?? null,
                'address' => $item['address'] ?? null,
                'category' => $item['category'] ?? 'general',
                'is_vulnerable' => $item['is_vulnerable'] ?? false,
                'is_pregnant' => $item['is_pregnant'] ?? false,
                'is_elderly' => $item['is_elderly'] ?? false,
                'is_disabled' => $item['is_disabled'] ?? false,
                'quantity_kg' => $item['quantity_kg'],
                'status' => DistributionBeneficiary::STATUS_PENDING,
                'registered_by' => Auth::id(),
            ]);

            $created[] = $beneficiary;
        }

        return response()->json([
            'success' => true,
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    public function events(): JsonResponse
    {
        $uid = (int) Auth::id();
        $events = DistributionEvent::where('status', 'active')
            ->whereHas('plannings', fn ($q) => $q->assignedTo($uid, DistributionPlanningAgent::TYPE_DISTRIBUTOR))
            ->with(['plannings' => fn ($q) => $q->assignedTo($uid, DistributionPlanningAgent::TYPE_DISTRIBUTOR)])
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
            ->assignedTo((int) Auth::id(), DistributionPlanningAgent::TYPE_DISTRIBUTOR)
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
        $plannings = DistributionPlanning::assignedTo((int) Auth::id(), DistributionPlanningAgent::TYPE_DISTRIBUTOR)
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

    public function planningBeneficiaries(int $id, Request $request): JsonResponse
    {
        $planning = $this->distributorPlanningOrFail($id);

        $query = $planning->beneficiaries()
            ->with(['tickets' => fn ($q) => $q->whereIn('status', ['issued', 'scanned', 'collected'])->latest()]);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cni', 'like', "%{$search}%")
                    ->orWhereHas('tickets', fn ($t) => $t->where('ticket_code', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $beneficiaries = $query->orderBy('full_name')->get()->map(function ($b) {
            $ticket = $b->tickets->first();
            $data = $b->toArray();
            unset($data['tickets']);
            $data['ticket'] = $ticket;
            return $data;
        });

        $stats = [
            'total' => $planning->beneficiaries()->count(),
            'pending' => $planning->beneficiaries()->where('status', 'pending')->count(),
            'validated' => $planning->beneficiaries()->where('status', 'validated')->count(),
            'ticket_issued' => $planning->beneficiaries()->where('status', 'ticket_issued')->count(),
            'kit_collected' => $planning->beneficiaries()->where('status', 'kit_collected')->count(),
        ];

        return response()->json([
            'success' => true,
            'planning' => $planning,
            'stats' => $stats,
            'data' => $beneficiaries,
        ]);
    }

    public function validateBeneficiary(int $id, Request $request): JsonResponse
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);

        $this->distributorPlanningOrFail((int) $beneficiary->planning_id);

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

        return response()->json([
            'success' => true,
            'message' => 'Bénéficiaire validé avec succès.',
            'data' => $beneficiary->fresh(),
        ]);
    }

    public function generateTicket(int $id): JsonResponse
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);

        $this->distributorPlanningOrFail((int) $beneficiary->planning_id);

        if (!in_array($beneficiary->status, [DistributionBeneficiary::STATUS_VALIDATED, DistributionBeneficiary::STATUS_TICKET_ISSUED])) {
            return response()->json([
                'success' => false,
                'message' => 'Le bénéficiaire doit être validé avant de générer un ticket.',
            ], 422);
        }

        return DB::transaction(function () use ($id) {
            // Verrouille la ligne bénéficiaire pour sérialiser les appels
            // concurrents et éviter la création de deux tickets actifs.
            $beneficiary = DistributionBeneficiary::where('id', $id)->lockForUpdate()->firstOrFail();

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
                'issued_by' => Auth::id(),
            ]);

            $beneficiary->update(['status' => DistributionBeneficiary::STATUS_TICKET_ISSUED]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket généré avec succès.',
                'data' => $ticket->load('beneficiary', 'planning'),
            ]);
        });
    }

    public function ticketsHistory(Request $request): JsonResponse
    {
        $planningIds = DistributionPlanning::assignedTo((int) Auth::id(), DistributionPlanningAgent::TYPE_DISTRIBUTOR)->pluck('id');

        $query = DistributionTicket::whereIn('planning_id', $planningIds)
            ->with(['beneficiary:id,full_name,phone,cni,quantity_kg,status', 'planning:id,name,location', 'scanner:id,name'])
            ->whereIn('status', ['issued', 'scanned', 'collected']);

        if ($request->filled('planning_id')) {
            $query->where('planning_id', $request->planning_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($since = $request->query('collected_since')) {
            $query->where('status', 'collected')->where('collected_at', '>', $since);
        }
        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                    ->orWhereHas('beneficiary', fn ($b) => $b->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('cni', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->orderByRaw('COALESCE(collected_at, issued_at) DESC')->limit(500)->get();

        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
            'stats' => [
                'issued' => DistributionTicket::whereIn('planning_id', $planningIds)->whereIn('status', ['issued', 'scanned'])->count(),
                'collected' => DistributionTicket::whereIn('planning_id', $planningIds)->where('status', 'collected')->count(),
            ],
            'data' => $tickets,
        ]);
    }

    public function scansHistory(Request $request): JsonResponse
    {
        $query = DistributionTicket::where('scanned_by', Auth::id())
            ->where('status', 'collected')
            ->with(['beneficiary:id,full_name,phone,cni,quantity_kg', 'planning:id,name,location,event_id', 'planning.event:id,name']);

        if ($request->filled('planning_id')) {
            $query->where('planning_id', (int) $request->planning_id);
        }
        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                    ->orWhereHas('beneficiary', fn ($b) => $b->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('date')) {
            $query->whereDate('collected_at', $request->date);
        }

        $tickets = $query->orderBy('collected_at', 'desc')->limit(500)->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'today' => DistributionTicket::where('scanned_by', Auth::id())->where('status', 'collected')->whereDate('collected_at', today())->count(),
                'total' => DistributionTicket::where('scanned_by', Auth::id())->where('status', 'collected')->count(),
                'total_kg' => (float) DistributionBeneficiary::whereIn('id', DistributionTicket::where('scanned_by', Auth::id())->where('status', 'collected')->pluck('beneficiary_id'))->sum('quantity_kg'),
            ],
            'data' => $tickets,
        ]);
    }

    // =====================================================================
    // APK Scanner : plannings a controler, verification puis confirmation
    // =====================================================================

    /** Plannings sur lesquels l'agent est affecte comme SCANNER (jamais deduits de l'affectation distributeur). */
    public function myScanPlannings(): JsonResponse
    {
        $plannings = DistributionPlanning::assignedTo((int) Auth::id(), DistributionPlanningAgent::TYPE_SCANNER)
            ->where('status', 'active')
            ->with('event:id,name')
            ->orderBy('distribution_date', 'asc')
            ->get()
            ->map(fn (DistributionPlanning $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'location' => $p->location,
                'distribution_date' => $p->distribution_date?->toDateString(),
                'event' => $p->event ? ['id' => $p->event->id, 'name' => $p->event->name] : null,
                'tickets_count' => $p->tickets()->whereIn('status', ['issued', 'scanned', 'collected'])->count(),
                'collected_count' => $p->tickets()->where('status', 'collected')->count(),
            ]);

        return response()->json(['success' => true, 'data' => $plannings]);
    }

    /** Lecture seule : tickets d'un planning a controler (donnees minimales, utilisables en consultation hors ligne). */
    public function scanPlanningTickets(int $id, Request $request): JsonResponse
    {
        $planning = DistributionPlanning::find($id);
        if (!PlanningAccess::canScan(Auth::user(), $planning)) {
            return response()->json(['success' => false, 'message' => 'Planning introuvable ou non autorisé.'], 404);
        }

        $query = $planning->tickets()
            ->whereIn('status', ['issued', 'scanned', 'collected'])
            ->with('beneficiary:id,full_name,quantity_kg');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                    ->orWhereHas('beneficiary', fn ($b) => $b->where('full_name', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->orderBy('ticket_code')->limit(1000)->get()->map(fn (DistributionTicket $t) => [
            'ticket_code' => $t->ticket_code,
            'status' => $t->status,
            'collected_at' => $t->collected_at?->toIso8601String(),
            'beneficiary' => $t->beneficiary
                ? ['full_name' => $t->beneficiary->full_name, 'quantity_kg' => $t->beneficiary->quantity_kg]
                : null,
        ]);

        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
            'planning' => ['id' => $planning->id, 'name' => $planning->name],
            'stats' => [
                'issued' => $planning->tickets()->whereIn('status', ['issued', 'scanned'])->count(),
                'collected' => $planning->tickets()->where('status', 'collected')->count(),
            ],
            'data' => $tickets,
        ]);
    }

    /**
     * Etape 1 : verification SANS effet de bord. Controle le ticket, le beneficiaire,
     * le planning et le droit de scan de l'agent, puis renvoie l'apercu a confirmer.
     */
    public function verifyTicket(string $code, Request $request): JsonResponse
    {
        [$ticket, $error] = $this->inspectTicket($code);

        if ($error) {
            $this->logAttempt($request, DistributionScanAttempt::ACTION_VERIFY, $error['result'], $ticket, $code, $error['message']);
            return response()->json(['success' => false, 'message' => $error['message']], $error['status']);
        }

        $this->logAttempt($request, DistributionScanAttempt::ACTION_VERIFY, DistributionScanAttempt::RESULT_OK, $ticket, $code);

        return response()->json([
            'success' => true,
            'message' => 'Ticket valide : confirmez la remise du kit.',
            'data' => [
                'ticket_code' => $ticket->ticket_code,
                'status' => $ticket->status,
                'beneficiary' => [
                    'full_name' => $ticket->beneficiary->full_name,
                    'quantity_kg' => $ticket->beneficiary->quantity_kg,
                    'category' => $ticket->beneficiary->category,
                    'phone_masked' => $this->maskPhone($ticket->beneficiary->phone),
                ],
                'planning' => [
                    'id' => $ticket->planning->id,
                    'name' => $ticket->planning->name,
                    'location' => $ticket->planning->location,
                ],
            ],
        ]);
    }

    /**
     * Etape 2 : confirmation de la remise. Un ticket ne peut etre utilise qu'une seule
     * fois, meme avec plusieurs scanners simultanes : l'utilisation est « reclamee » par
     * un UPDATE conditionnel (statut encore emis) dont un seul appel peut sortir gagnant.
     */
    public function collectKit(string $qrToken, Request $request): JsonResponse
    {
        [$ticket, $error] = $this->inspectTicket($qrToken);

        if ($error) {
            $this->logAttempt($request, DistributionScanAttempt::ACTION_COLLECT, $error['result'], $ticket, $qrToken, $error['message']);
            return response()->json(['success' => false, 'message' => $error['message']] + $error['extra'], $error['status']);
        }

        $userId = (int) Auth::id();
        $location = $request->input('location') ?: $request->input('scan_location');

        try {
            $claimed = DB::transaction(function () use ($ticket, $request, $userId, $location) {
                $now = now();

                $won = DistributionTicket::where('id', $ticket->id)
                    ->whereIn('status', [DistributionTicket::STATUS_ISSUED, DistributionTicket::STATUS_SCANNED])
                    ->update([
                        'status' => DistributionTicket::STATUS_COLLECTED,
                        'scanned_at' => $now,
                        'collected_at' => $now,
                        'scanned_by' => $userId,
                        'scan_location' => $location,
                        'updated_at' => $now,
                    ]);

                if ($won !== 1) {
                    return false;
                }

                // Un beneficiaire ne recoit qu'un seul kit : si un autre ticket l'a deja servi, on annule tout.
                $served = DistributionBeneficiary::where('id', $ticket->beneficiary_id)
                    ->where('status', '!=', DistributionBeneficiary::STATUS_KIT_COLLECTED)
                    ->update(['status' => DistributionBeneficiary::STATUS_KIT_COLLECTED, 'updated_at' => $now]);

                if ($served !== 1) {
                    throw new \RuntimeException('beneficiary_already_served');
                }

                DistributionPlanning::where('id', $ticket->planning_id)
                    ->increment('executed_kg', (float) $ticket->beneficiary->quantity_kg);

                DistributionScanLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                    'action' => DistributionScanLog::ACTION_COLLECT,
                    'notes' => $request->input('notes'),
                    'device_info' => $request->input('device_info'),
                ]);

                return true;
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'beneficiary_already_served') {
                throw $e;
            }
            $claimed = false;
        }

        if (!$claimed) {
            $current = $ticket->fresh();
            $message = 'Kit déjà récupéré.';
            $this->logAttempt($request, DistributionScanAttempt::ACTION_COLLECT, DistributionScanAttempt::RESULT_ALREADY_USED, $ticket, $qrToken, $message);

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => ['collected_at' => $current?->collected_at?->toIso8601String()],
            ], 422);
        }

        $this->logAttempt($request, DistributionScanAttempt::ACTION_COLLECT, DistributionScanAttempt::RESULT_OK, $ticket, $qrToken);
        $collectedAt = $ticket->fresh()->collected_at;

        return response()->json([
            'success' => true,
            'message' => 'Récupération du kit enregistrée avec succès.',
            'data' => [
                'ticket_code' => $ticket->ticket_code,
                'beneficiaire' => [
                    'full_name' => $ticket->beneficiary->full_name,
                    'quantity_kg' => $ticket->beneficiary->quantity_kg,
                ],
                'beneficiary' => [
                    'id' => $ticket->beneficiary->id,
                    'full_name' => $ticket->beneficiary->full_name,
                    'quantity_kg' => $ticket->beneficiary->quantity_kg,
                ],
                'planning' => ['id' => $ticket->planning->id, 'name' => $ticket->planning->name],
                'collected_at' => $collectedAt?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Controles communs a la verification et a la remise, dans un ordre qui ne
     * revele rien a un agent non autorise (droit de scan verifie avant le statut).
     *
     * @return array{0: ?DistributionTicket, 1: ?array{status:int,result:string,message:string,extra:array}}
     */
    private function inspectTicket(string $code): array
    {
        $code = trim($code);

        $ticket = $code === '' ? null : DistributionTicket::where('qr_token', $code)
            ->orWhere('ticket_code', $code)
            ->with('beneficiary', 'planning.event')
            ->first();

        $fail = fn (int $status, string $result, string $message, array $extra = []) =>
            [$ticket, compact('status', 'result', 'message', 'extra')];

        if (!$ticket) {
            return $fail(404, DistributionScanAttempt::RESULT_INVALID_CODE, 'Ticket non trouvé.');
        }

        if (!$ticket->beneficiary || !$ticket->planning || (int) $ticket->beneficiary->planning_id !== (int) $ticket->planning_id) {
            return $fail(409, DistributionScanAttempt::RESULT_INCONSISTENT, 'Ticket incohérent : contactez l\'administration.');
        }

        if (!PlanningAccess::canScan(Auth::user(), $ticket->planning)) {
            return $fail(403, DistributionScanAttempt::RESULT_FORBIDDEN, 'Planning non autorisé : vous n\'êtes pas affecté comme scanner sur ce planning.');
        }

        if ($ticket->planning->status !== 'active') {
            return $fail(422, DistributionScanAttempt::RESULT_PLANNING_INACTIVE, 'Ce planning n\'est pas actif.');
        }

        if ($ticket->status === DistributionTicket::STATUS_CANCELLED) {
            return $fail(422, DistributionScanAttempt::RESULT_CANCELLED, 'Ticket annulé.');
        }

        if ($ticket->status === DistributionTicket::STATUS_COLLECTED
            || $ticket->beneficiary->status === DistributionBeneficiary::STATUS_KIT_COLLECTED) {
            return $fail(422, DistributionScanAttempt::RESULT_ALREADY_USED, 'Kit déjà récupéré.', [
                'data' => ['collected_at' => $ticket->collected_at?->toIso8601String()],
            ]);
        }

        return [$ticket, null];
    }

    /** Audit : journalise toute tentative (succes comme refus). Ne doit jamais faire echouer la requete. */
    private function logAttempt(Request $request, string $action, string $result, ?DistributionTicket $ticket, string $code, ?string $message = null): void
    {
        try {
            DistributionScanAttempt::create([
                'user_id' => Auth::id(),
                'ticket_id' => $ticket?->id,
                'planning_id' => $ticket?->planning_id,
                'beneficiary_id' => $ticket?->beneficiary_id,
                'action' => $action,
                'result' => $result,
                'code' => Str::limit($ticket?->ticket_code ?? $code, 100, ''),
                'message' => $message ? Str::limit($message, 250, '') : null,
                'device_info' => $request->input('device_info') ? Str::limit((string) $request->input('device_info'), 250, '') : null,
                'ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function maskPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);

        return strlen($digits) >= 3 ? str_repeat('•', max(0, strlen($digits) - 3)) . substr($digits, -3) : $digits;
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'planning_id' => 'required|exists:distribution_plannings,id',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'full_name' => 'nullable|string|max:255',
        ]);

        // Cloisonnement : l'agent ne peut interroger que son planning
        // (ou un planning non assigne), sauf admin.
        $planning = DistributionPlanning::findOrFail($request->planning_id);
        if (!PlanningAccess::canDistribute(Auth::user(), $planning)) {
            return response()->json([
                'success' => false,
                'message' => 'Planning non autorisé.',
            ], 403);
        }

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
            $match = (clone $query)->where('full_name', 'LIKE', $request->full_name)->first();
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
