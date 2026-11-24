<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\StockOperationService;
use App\Services\Magasin\WarehouseStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API mobile du circuit de stock :
 * - rôle inspecteur : instructions, transmission magasinier, vérification, anomalies (photos + GPS)
 * - rôle magasinier : opérations transmises + exécution
 * Partagée par l'APK Inspecteur et l'APK Magasinier (mêmes données que le Web).
 */
class StockOperationApiController extends Controller
{
    public function __construct(
        private StockOperationService $operations,
        private WarehouseStockService $stock,
    ) {}

    // ------------------------------------------------------------------
    // Authentification APK Inspecteur (compte personnel, rôle inspecteur)
    // ------------------------------------------------------------------
    public function loginInspector(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!\Illuminate\Support\Facades\Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $user = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($user->role !== 'inspecteur') {
            \Illuminate\Support\Facades\Auth::guard('admin')->logout();
            return response()->json(['message' => 'Accès réservé aux inspecteurs.'], 403);
        }

        return response()->json([
            'token' => $user->createToken('inspector', ['inspector'])->plainTextToken,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
        ]);
    }

    private function user(Request $request): ?User
    {
        return $request->user();
    }

    private function denyRole(User $user, array $roles)
    {
        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Rôle non autorisé.'], 403);
        }
        return null;
    }

    // ------------------------------------------------------------------
    // Liste des opérations visibles par l'utilisateur connecté
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'admin', 'super_admin'])) return $r;

        $q = StockOperation::with(['warehouse:id,name,region', 'destinationWarehouse:id,name',
            'inspector:id,name', 'magasinier:id,name', 'executor:id,name', 'verifier:id,name'])
            ->orderByDesc('id');

        if ($user->role === 'inspecteur') {
            $scope = array_map('intval', $this->stock->inspectorScopeWarehouseIds($user) ?: []);
            $q->where('assigned_inspector_id', $user->id)
                ->where(function ($w) use ($scope) {
                    $w->whereIn('warehouse_id', $scope ?: [0])
                        ->orWhereIn('destination_warehouse_id', $scope ?: [0]);
                });
        } elseif ($user->role === 'magasinier') {
            $q->where('assigned_magasinier_id', $user->id);
        }

        if ($s = $request->query('status')) {
            $q->where('status', $s);
        }

        return response()->json([
            'operations' => $q->paginate(50)->items(),
        ]);
    }

    /**
     * Magasiniers rattachés à un magasin — pour que l'inspecteur choisisse
     * le destinataire des consignes opérationnelles.
     */
    public function magasinierCandidates(Request $request)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'dtl', 'dg', 'admin', 'super_admin'])) return $r;

        $warehouseId = (int) $request->query('warehouse_id');
        if (!$warehouseId) {
            return response()->json(['message' => 'warehouse_id requis.'], 422);
        }
        if ($user->role === 'inspecteur' && !$this->stock->inspectorCanAccessWarehouse($user, $warehouseId)) {
            return response()->json(['message' => 'Magasin hors de votre périmètre d\'affectation.'], 403);
        }

        $magasiniers = User::where('role', 'magasinier')
            ->where('warehouse_id', $warehouseId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json(['magasiniers' => $magasiniers]);
    }

    public function show(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'admin', 'super_admin'])) return $r;

        $op = StockOperation::with(['warehouse', 'destinationWarehouse', 'movements'])->findOrFail($id);

        if ($user->role === 'inspecteur'
            && ($op->assigned_inspector_id !== $user->id
                || !$this->operations->inspectorCanAccessOperation($user, $op))) {
            return response()->json(['message' => 'Opération non affectée à cet inspecteur.'], 403);
        }
        if ($user->role === 'magasinier' && $op->assigned_magasinier_id !== $user->id) {
            return response()->json(['message' => 'Opération non transmise à ce magasinier.'], 403);
        }

        return response()->json(['operation' => $op]);
    }

    // ------------------------------------------------------------------
    // Inspecteur : consignes au magasinier
    // ------------------------------------------------------------------
    public function transmit(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur'])) return $r;

        $data = $request->validate([
            'assigned_magasinier_id' => 'required|exists:users,id',
            'inspector_comment' => 'nullable|string|max:3000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $result = $this->operations->transmitToMagasinier(
            $user, (int) $id, (int) $data['assigned_magasinier_id'], $data['inspector_comment'] ?? null
        );

        return $this->respond($result, 'Consignes transmises au magasinier.');
    }

    // ------------------------------------------------------------------
    // Magasinier : exécution de l'opération réelle
    // ------------------------------------------------------------------
    public function execute(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['magasinier'])) return $r;

        $data = $request->validate([
            'quantity_executed' => 'required|numeric|min:0.01',
            'quantity_executed_kg' => 'nullable|numeric|min:0',
            'position' => 'nullable|string|max:150',
            'observation' => 'nullable|string|max:2000',
            'movement_date' => 'nullable|date',
            'client_ref' => 'nullable|string|max:100',
            'photo_base64' => 'nullable|string',
            'signature_base64' => 'nullable|string',
        ]);

        $result = $this->operations->execute($user, (int) $id, $data);
        return $this->respond($result, 'Opération enregistrée — en attente de vérification.');
    }

    // ------------------------------------------------------------------
    // Inspecteur : vérification / anomalie / rejet
    // ------------------------------------------------------------------
    public function verify(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur'])) return $r;

        $data = $request->validate([
            'inspector_comment' => 'nullable|string|max:2000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $result = $this->operations->verify($user, (int) $id, $data['inspector_comment'] ?? null);
        return $this->respond($result, 'Opération vérifiée et intégrée au stock officiel.');
    }

    public function anomaly(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur'])) return $r;

        $data = $request->validate([
            'anomaly_report' => 'required|string|max:3000',
            'photo_base64' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $attachments = [];
        if (!empty($data['photo_base64'])) {
            $attachments[] = [
                'type' => 'photo',
                'data' => $data['photo_base64'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'captured_at' => now()->toIso8601String(),
            ];
        }

        $result = $this->operations->reportAnomaly($user, (int) $id, $data['anomaly_report'], $attachments ?: null);
        return $this->respond($result, 'Anomalie signalée.', 201);
    }

    public function reject(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'dtl', 'dg', 'admin', 'super_admin'])) return $r;

        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $result = $this->operations->reject($user, (int) $id, $data['rejection_reason']);
        return $this->respond($result, 'Opération rejetée.');
    }

    // ------------------------------------------------------------------
    // Mouvements directs en attente (entrées/sorties/transferts/inventaires
    // saisis hors circuit stock_operations — Web, APK ou sync hors ligne)
    // ------------------------------------------------------------------
    public function pendingMovements(Request $request)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'dtl', 'dg', 'admin', 'super_admin'])) return $r;

        $movements = $this->stock->pendingMovements($user, $request->only(['warehouse_id', 'type']));

        return response()->json([
            'movements' => $movements->map(function ($m) {
                $meta = json_decode($m->metadata ?? '{}', true) ?: [];
                return [
                    'id' => $m->id,
                    'reference' => $m->reference,
                    'type' => $m->type,
                    'type_label' => $this->stock->movementTypeLabel($m->type),
                    'quantity' => (float) $m->quantity,
                    'reason' => $m->reason,
                    'warehouse' => $m->warehouse ? ['id' => $m->warehouse->id, 'name' => $m->warehouse->name] : null,
                    'creator' => $m->creator ? ['id' => $m->creator->id, 'name' => $m->creator->name] : null,
                    'created_at' => $m->created_at?->toIso8601String(),
                    'is_adjustment' => $this->stock->isAdjustmentMovement($m),
                    'is_transfer_in' => $this->stock->isTransferIn($m),
                    'receipt_confirmed' => !empty($meta['receipt_confirmed_at']),
                ];
            })->values(),
        ]);
    }

    public function verifyMovement(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'dtl', 'dg', 'admin', 'super_admin'])) return $r;

        $result = $this->stock->verifyPendingMovement($user, (int) $id);
        return $this->respondMovement($result, 'Mouvement vérifié et intégré au stock officiel.');
    }

    public function rejectMovement(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'dtl', 'dg', 'admin', 'super_admin'])) return $r;

        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $result = $this->stock->rejectPendingMovement($user, (int) $id, $data['rejection_reason']);
        return $this->respondMovement($result, 'Mouvement rejeté.');
    }

    public function confirmReceipt(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['magasinier', 'inspecteur', 'dtl', 'dg', 'admin', 'super_admin'])) return $r;

        $result = $this->stock->confirmTransferReceipt($user, (int) $id);
        return $this->respondMovement($result, $result['message'] ?? 'Réception confirmée.');
    }

    private function respondMovement(array $result, string $okMessage)
    {
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], $result['status'] ?? 422);
        }
        return response()->json([
            'message' => $okMessage,
            'movement' => $result['movement'] ?? null,
            'delta' => $result['delta'] ?? null,
        ]);
    }

    // ------------------------------------------------------------------
    // Consultation stock (inspecteur : lecture seule des magasins affectés)
    // ------------------------------------------------------------------
    public function stockStatus(Request $request)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'admin', 'super_admin'])) return $r;

        $warehouseId = (int) $request->query('warehouse_id');
        if (!$warehouseId) {
            return response()->json(['message' => 'warehouse_id requis.'], 422);
        }
        if ($user->role === 'magasinier' && !$this->stock->canAccess($user, $warehouseId)) {
            return response()->json(['message' => 'Magasin non autorisé.'], 403);
        }
        if ($user->role === 'inspecteur' && !$this->stock->inspectorCanAccessWarehouse($user, $warehouseId)) {
            return response()->json(['message' => 'Magasin hors de votre périmètre d\'affectation.'], 403);
        }

        return response()->json($this->stock->stockStatus($warehouseId));
    }

    public function warehouses(Request $request)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'admin', 'super_admin'])) return $r;

        if ($user->role === 'magasinier') {
            $warehouses = $this->stock->accessibleWarehouses($user);
        } elseif ($user->role === 'inspecteur') {
            $scope = $this->stock->inspectorScopeWarehouseIds($user) ?: [];
            $warehouses = Warehouse::where('status', 'active')->whereIn('id', $scope)->orderBy('name')->get();
        } else {
            $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        }

        if ($user->role === 'inspecteur') {
            $mags = \App\Models\User::where('role', 'magasinier')->where('is_active', true)
                ->whereIn('warehouse_id', $warehouses->pluck('id'))->get(['id', 'name', 'phone', 'warehouse_id'])
                ->groupBy('warehouse_id');
            $warehouses = $warehouses->map(function ($w) use ($mags) {
                $arr = $w->toArray();
                $arr['magasiniers'] = $mags->get($w->id, collect())->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'phone' => $u->phone])->values()->all();

                return $arr;
            })->values();
        }

        return response()->json(['warehouses' => $warehouses]);
    }

    // ------------------------------------------------------------------
    private function respond(array $result, string $okMessage, int $okStatus = 200)
    {
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], $result['status'] ?? 422);
        }
        return response()->json([
            'message' => $okMessage,
            'operation' => $result['operation'],
            'movements' => $result['movements'] ?? null,
        ], $okStatus);
    }
}
