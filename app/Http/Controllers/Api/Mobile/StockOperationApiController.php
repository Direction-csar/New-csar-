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
            $q->where('assigned_inspector_id', $user->id);
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

    public function show(Request $request, $id)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'admin', 'super_admin'])) return $r;

        $op = StockOperation::with(['warehouse', 'destinationWarehouse', 'movements'])->findOrFail($id);

        if ($user->role === 'inspecteur' && $op->assigned_inspector_id !== $user->id) {
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

        return response()->json($this->stock->stockStatus($warehouseId));
    }

    public function warehouses(Request $request)
    {
        $user = $this->user($request);
        if ($r = $this->denyRole($user, ['inspecteur', 'magasinier', 'admin', 'super_admin'])) return $r;

        $warehouses = $user->role === 'magasinier'
            ? $this->stock->accessibleWarehouses($user)
            : Warehouse::where('status', 'active')->orderBy('name')->get();

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
