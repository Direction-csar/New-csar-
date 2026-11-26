<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\Comptable\ComptableMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API mobile « CSAR Comptable des matières » (Sanctum).
 * Miroir du portail web /comptable : consultation et décisions
 * comptables (validation/rejet) sur les mouvements de stock.
 * Rôle « comptable » requis à chaque requête — aucun privilège
 * DG/DTL/admin n'est déduit de ce jeton.
 */
class ComptableApiController extends Controller
{
    public function __construct(
        private readonly ComptableMovementService $service,
    ) {}

    /** Authentification comptable (mêmes identifiants que le portail web) */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $user = Auth::guard('admin')->user();

        if ($user->role !== 'comptable' || !$user->is_active) {
            Auth::guard('admin')->logout();
            return response()->json(['message' => 'Accès réservé au Comptable des matières.'], 403);
        }

        $token = $user->createToken('comptable-mobile', ['comptable'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /** Révoque le jeton courant (déconnexion de l'app). */
    public function logout(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /** Tableau de bord : volumes par statut + magasins sous seuil */
    public function dashboard(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $stats = $this->service->statusCounts();
        $lowStock = $this->service->lowStockWarehouses();

        $recent = StockMovement::with(['warehouse:id,name', 'creator:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'low_stock_warehouses' => $lowStock,
            'recent_movements' => $recent,
        ]);
    }

    /** Liste des mouvements : status, warehouse_id, type, q, from/to */
    public function movements(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $query = $this->service->filteredMovementsQuery(
            $request->only(['status', 'warehouse_id', 'type', 'linked', 'from', 'to', 'q'])
        );

        return response()->json($query->paginate(30)->withQueryString());
    }

    /** Détail d'un mouvement + mission éventuellement liée */
    public function movement(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $mouvement = StockMovement::with([
            'warehouse:id,name,region',
            'creator:id,name,role',
        ])->findOrFail($id);

        $mission = $mouvement->request_mission_id
            ? \App\Models\RequestMission::with('request:id,tracking_code,full_name')
                ->find($mouvement->request_mission_id)
            : null;

        return response()->json([
            'movement' => $mouvement,
            'mission' => $mission,
        ]);
    }

    /** Valider comptablement : draft → validated (gel de l'écriture) */
    public function validateMovement(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $mouvement = StockMovement::findOrFail($id);
        $result = $this->service->validate($mouvement, $request->user());

        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'movement' => $mouvement->fresh(),
        ]);
    }

    /** Rejeter : draft → rejected (motif obligatoire ≥ 10 car.) */
    public function rejectMovement(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ]);

        $mouvement = StockMovement::findOrFail($id);
        $result = $this->service->reject($mouvement, $request->rejection_reason, $request->user());

        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'movement' => $mouvement->fresh(),
        ]);
    }

    /**
     * Soldes carburant par produit : les lignes stock_movements dont
     * l'unite est 'L' (litre) agrégées par produit et par magasin.
     * Règle métier : sorties = out/exit, entrées = in/entry.
     */
    public function fuelSummary(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        return response()->json($this->service->fuelSummary());
    }

    /** Liste des affectations de matériel (filtre status/agent). */
    public function equipmentAssignments(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $query = \App\Models\EquipmentAssignment::with([
            'warehouse:id,name',
            'assigner:id,name',
        ])->orderBy('assigned_at', 'desc');

        if (in_array($request->status, ['affectee', 'retournee', 'perdue'], true)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('agent_name', 'like', $q)
                    ->orWhere('produit', 'like', $q)
                    ->orWhere('numero_bon', 'like', $q);
            });
        }

        return response()->json($query->paginate(30)->withQueryString());
    }

    /** Enregistrer une affectation de matériel à un agent. */
    public function storeEquipmentAssignment(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $data = $request->validate([
            'produit' => 'required|string|max:255',
            'agent_name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'unite' => 'nullable|string|max:20',
            'quantity' => 'nullable|numeric|min:0.01',
            'numero_bon' => 'nullable|string|max:100',
        ]);

        $assignment = \App\Models\EquipmentAssignment::create([
            'produit' => $data['produit'],
            'agent_name' => $data['agent_name'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'unite' => $data['unite'] ?? 'pièce',
            'quantity' => $data['quantity'] ?? 1,
            'numero_bon' => $data['numero_bon'] ?? null,
            'status' => \App\Models\EquipmentAssignment::STATUS_AFFECTEE,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        return response()->json([
            'message' => 'Affectation enregistrée.',
            'assignment' => $assignment->load('warehouse:id,name', 'assigner:id,name'),
        ], 201);
    }

    /** Clôturer une affectation : retour ou perte du matériel (tracé). */
    public function closeEquipmentAssignment(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $assignment = \App\Models\EquipmentAssignment::findOrFail($id);

        if ($assignment->status !== \App\Models\EquipmentAssignment::STATUS_AFFECTEE) {
            return response()->json(['message' => 'Affectation déjà clôturée.'], 422);
        }

        $data = $request->validate([
            'outcome' => 'required|in:retournee,perdue',
            'return_note' => 'nullable|string|max:1000',
        ]);

        if ($data['outcome'] === 'perdue' && empty($data['return_note'])) {
            return response()->json(['message' => 'Un motif est obligatoire pour déclarer une perte.'], 422);
        }

        $assignment->update([
            'status' => $data['outcome'],
            'returned_at' => now(),
            'returned_by' => $request->user()->id,
            'return_note' => $data['return_note'] ?? null,
        ]);

        return response()->json([
            'message' => $data['outcome'] === 'retournee' ? 'Retour enregistré.' : 'Perte déclarée.',
            'assignment' => $assignment->fresh()->load('warehouse:id,name', 'assigner:id,name'),
        ]);
    }

    /** Magasins (filtres de l'app) */
    public function warehouses(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        return response()->json(
            Warehouse::orderBy('name')->get(['id', 'name', 'region', 'capacity', 'current_stock'])
        );
    }

    /** Vérification du rôle à chaque requête protégée */
    private function denyNonComptable(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'comptable' || !$user->is_active) {
            return response()->json(['message' => 'Accès réservé au Comptable des matières.'], 403);
        }
        return null;
    }
}
