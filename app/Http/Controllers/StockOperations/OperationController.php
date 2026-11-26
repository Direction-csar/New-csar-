<?php

namespace App\Http\Controllers\StockOperations;

use App\Http\Controllers\Controller;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\StockOperationService;
use App\Services\Magasin\WarehouseStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Circuit opérationnel de stock : DG → DTL → Inspecteur → Magasinier → vérification.
 * Un seul contrôleur pour les 4 portails ; le service impose les règles de rôle.
 */
class OperationController extends Controller
{
    public function __construct(
        private StockOperationService $operations,
        private WarehouseStockService $stock,
    ) {}

    private function user(): User
    {
        return Auth::user();
    }

    /** Portail courant déduit de l'URL (un admin peut visiter plusieurs portails). */
    private function portal(): array
    {
        $path = request()->path();
        $direction = match (true) {
            str_starts_with($path, 'dtl/') => 'dtl',
            str_starts_with($path, 'inspecteur/') => 'inspecteur',
            str_starts_with($path, 'magasinier/') => 'magasinier',
            default => 'dg',
        };
        return [$direction, 'Opérations de stock'];
    }

    private function scope(Request $request)
    {
        $user = $this->user();
        $q = StockOperation::with([
            'warehouse:id,name,region', 'destinationWarehouse:id,name',
            'creator:id,name', 'inspector:id,name', 'magasinier:id,name',
            'executor:id,name', 'verifier:id,name',
        ])->orderByDesc('id');

        match ($user->role) {
            'inspecteur' => $q->where('assigned_inspector_id', $user->id)
                ->where(function ($w) use ($user) {
                    $scope = array_map('intval', $this->operations->inspectorScopeIds($user) ?: []);
                    $w->whereIn('warehouse_id', $scope ?: [0])
                        ->orWhereIn('destination_warehouse_id', $scope ?: [0]);
                }),
            'magasinier' => $q->where('assigned_magasinier_id', $user->id),
            'dtl' => null, // le DTL voit tout le circuit pour superviser
            default => null,
        };

        if ($s = $request->query('status')) {
            $q->where('status', $s);
        }
        if ($t = $request->query('type')) {
            $q->where('type', $t);
        }
        if ($w = $request->query('warehouse_id')) {
            $q->where('warehouse_id', $w);
        }

        return $q->paginate(25)->withQueryString();
    }

    public function index(Request $request)
    {
        [$direction, $label] = $this->portal();
        $user = $this->user();

        $stats = [
            'soumise' => StockOperation::where('status', StockOperation::STATUS_SOUMISE)->count(),
            'a_verifier' => StockOperation::whereIn('status', [StockOperation::STATUS_EXECUTEE, StockOperation::STATUS_ANOMALIE])->count(),
            'en_cours' => StockOperation::whereIn('status', [StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_TRANSMISE])->count(),
            'verifiees' => StockOperation::where('status', StockOperation::STATUS_VERIFIEE)->count(),
        ];
        if ($user->role === 'inspecteur') {
            $stats = [
                'affectees' => StockOperation::where('assigned_inspector_id', $user->id)->where('status', StockOperation::STATUS_AFFECTEE)->count(),
                'a_verifier' => StockOperation::where('assigned_inspector_id', $user->id)->whereIn('status', [StockOperation::STATUS_EXECUTEE, StockOperation::STATUS_ANOMALIE])->count(),
                'verifiees' => StockOperation::where('assigned_inspector_id', $user->id)->where('status', StockOperation::STATUS_VERIFIEE)->count(),
            ];
        } elseif ($user->role === 'magasinier') {
            $stats = [
                'a_executer' => StockOperation::where('assigned_magasinier_id', $user->id)->where('status', StockOperation::STATUS_TRANSMISE)->count(),
                'executees' => StockOperation::where('assigned_magasinier_id', $user->id)->where('status', StockOperation::STATUS_EXECUTEE)->count(),
                'verifiees' => StockOperation::where('assigned_magasinier_id', $user->id)->where('status', StockOperation::STATUS_VERIFIEE)->count(),
            ];
        }

        return view('stock-operations.index', [
            'direction' => $direction,
            'directionLabel' => $label,
            'operations' => $this->scope($request),
            'stats' => $stats,
            'warehouses' => Warehouse::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'statuses' => StockOperation::STATUTS ?? [],
        ]);
    }

    /** Formulaire de création — réservé DG / admin. */
    public function create()
    {
        [, $label] = $this->portal();
        $this->authorizeDirection();

        return view('stock-operations.create', [
            'direction' => 'dg',
            'directionLabel' => $label,
            'warehouses' => Warehouse::where('status', 'active')->orderBy('name')->get(['id', 'name', 'region']),
            'products' => $this->stock->products(),
        ]);
    }

    public function store(Request $request)
    {
        [, $label] = $this->portal();
        $this->authorizeDirection();

        $data = $request->validate([
            'type' => 'required|in:' . implode(',', StockOperation::TYPES),
            'warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id|different:warehouse_id',
            'produit' => 'nullable|string|max:150',
            'unite' => 'nullable|in:kg,L',
            'format' => 'nullable|integer|min:1|max:1000',
            'quantity_requested' => 'required|numeric|min:0.01',
            'quantity_requested_kg' => 'nullable|numeric|min:0',
            'instructions' => 'nullable|string|max:3000',
        ]);

        $result = $this->operations->create($this->user(), $data);
        if (!$result['ok']) {
            return redirect()->route($this->indexRoute())->with('error', $result['message']);
        }

        return redirect()->route($this->showRoute($result['operation']))
            ->with('success', 'Instruction ' . $result['operation']->reference . ' transmise à la DTL.');
    }

    public function show($id)
    {
        [$direction, $label] = $this->portal();
        $op = StockOperation::with([
            'warehouse', 'destinationWarehouse', 'creator', 'dtlAssigner',
            'inspector', 'magasinier', 'executor', 'verifier', 'movements.creator',
        ])->findOrFail($id);

        // Isolation : un inspecteur/magasinier ne voit que ses opérations
        if ($this->user()->role === 'inspecteur'
            && ($op->assigned_inspector_id !== $this->user()->id
                || !$this->operations->inspectorCanAccessOperation($this->user(), $op))) {
            abort(403);
        }
        if ($this->user()->role === 'magasinier' && $op->assigned_magasinier_id !== $this->user()->id) {
            abort(403);
        }

        $inspectors = User::where('role', 'inspecteur')->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $magasiniers = Warehouse::whereIn('id', array_filter([$op->warehouse_id, $op->destination_warehouse_id]))
            ->with('users:id,name,role')
            ->get()
            ->flatMap->users
            ->where('role', 'magasinier')
            ->unique('id')
            ->values(['id', 'name']);

        return view('stock-operations.show', [
            'direction' => $direction,
            'directionLabel' => $label,
            'operation' => $op,
            'inspectors' => $inspectors,
            'magasiniers' => $magasiniers,
        ]);
    }

    // ------------------------------------------------------------------
    // Actions du circuit
    // ------------------------------------------------------------------
    public function assignInspector(Request $request, $id)
    {
        $data = $request->validate([
            'assigned_inspector_id' => 'required|exists:users,id',
            'dtl_comment' => 'nullable|string|max:2000',
        ]);
        $result = $this->operations->assignInspector($this->user(), (int) $id, (int) $data['assigned_inspector_id'], $data['dtl_comment'] ?? null);
        return $this->back($result, 'Inspecteur affecté.');
    }

    public function transmit(Request $request, $id)
    {
        $data = $request->validate([
            'assigned_magasinier_id' => 'required|exists:users,id',
            'inspector_comment' => 'nullable|string|max:3000',
        ]);
        $result = $this->operations->transmitToMagasinier($this->user(), (int) $id, (int) $data['assigned_magasinier_id'], $data['inspector_comment'] ?? null);
        return $this->back($result, 'Consignes transmises au magasinier.');
    }

    public function execute(Request $request, $id)
    {
        $data = $request->validate([
            'quantity_executed' => 'required|numeric|min:0.01',
            'quantity_executed_kg' => 'nullable|numeric|min:0',
            'position' => 'nullable|string|max:150',
            'observation' => 'nullable|string|max:2000',
            'movement_date' => 'nullable|date',
        ]);
        $result = $this->operations->execute($this->user(), (int) $id, $data);
        return $this->back($result, 'Opération enregistrée — en attente de vérification par l\'inspecteur.');
    }

    public function verify(Request $request, $id)
    {
        $data = $request->validate(['inspector_comment' => 'nullable|string|max:2000']);
        $result = $this->operations->verify($this->user(), (int) $id, $data['inspector_comment'] ?? null);
        return $this->back($result, 'Opération vérifiée et intégrée au stock officiel.');
    }

    public function anomaly(Request $request, $id)
    {
        $data = $request->validate([
            'anomaly_report' => 'required|string|max:3000',
            'attachments' => 'nullable|array',
        ]);
        $result = $this->operations->reportAnomaly($this->user(), (int) $id, $data['anomaly_report'], $data['attachments'] ?? null);
        return $this->back($result, 'Anomalie signalée.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $result = $this->operations->reject($this->user(), (int) $id, $data['rejection_reason']);
        return $this->back($result, 'Opération rejetée.', 'error');
    }

    public function cancel(Request $request, $id)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:1000']);
        $result = $this->operations->cancel($this->user(), (int) $id, $data['reason'] ?? null);
        return $this->back($result, 'Opération annulée.', 'error');
    }

    // ------------------------------------------------------------------
    // Mouvements directs en attente (entrées/sorties/transferts/inventaires
    // saisis via /movements, /sync, /transferts, /inventaires — Web ou APK)
    // ------------------------------------------------------------------
    public function pending(Request $request)
    {
        [$direction, $label] = $this->portal();
        $user = $this->user();

        $role = $user->role;
        $canVerifyOps = in_array($role, ['inspecteur', 'dtl', 'dg', 'admin', 'super_admin'], true);
        $canVerifyAdjust = in_array($role, ['dtl', 'dg', 'admin', 'super_admin'], true);
        $canConfirm = in_array($role, ['magasinier', 'inspecteur', 'dtl', 'dg', 'admin', 'super_admin'], true);
        $isDirection = $canVerifyAdjust;

        $movements = $this->stock->pendingMovements($user, $request->only(['warehouse_id', 'type']));

        $rows = $movements->map(function ($m) use ($canVerifyOps, $canVerifyAdjust, $canConfirm, $isDirection, $user) {
            $meta = json_decode($m->metadata ?? '{}', true) ?: [];
            $isAdjust = $this->stock->isAdjustmentMovement($m);
            $isTransferIn = $this->stock->isTransferIn($m);
            $receiptConfirmed = !empty($meta['receipt_confirmed_at']);
            $canVerify = $isAdjust ? $canVerifyAdjust : $canVerifyOps;
            // Un inspecteur ne peut pas vérifier une réception non confirmée
            if ($isTransferIn && !$receiptConfirmed && !$isDirection) {
                $canVerify = false;
            }
            $canConfirmThis = $canConfirm && $isTransferIn && !$receiptConfirmed
                && ($user->role !== 'magasinier' || $this->stock->canAccess($user, (int) $m->warehouse_id));

            return [
                'movement' => $m,
                'is_adjustment' => $isAdjust,
                'is_transfer_in' => $isTransferIn,
                'receipt_confirmed' => $receiptConfirmed,
                'can_verify' => $canVerify,
                'can_confirm' => $canConfirmThis,
            ];
        });

        return view('stock-operations.pending', [
            'direction' => $direction,
            'directionLabel' => $label,
            'rows' => $rows,
            'warehouses' => Warehouse::where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function verifyMovement(Request $request, $id)
    {
        $data = $request->validate(['comment' => 'nullable|string|max:1000']);
        $result = $this->stock->verifyPendingMovement($this->user(), (int) $id);
        return $this->backMovement($result, 'Mouvement vérifié et intégré au stock officiel.');
    }

    public function rejectMovement(Request $request, $id)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $result = $this->stock->rejectPendingMovement($this->user(), (int) $id, $data['rejection_reason']);
        return $this->backMovement($result, 'Mouvement rejeté.', true);
    }

    public function confirmReceipt(Request $request, $id)
    {
        $result = $this->stock->confirmTransferReceipt($this->user(), (int) $id);
        return $this->backMovement($result, 'Réception confirmée.', isset($result['duplicate']));
    }

    // ------------------------------------------------------------------
    private function authorizeDirection(): void
    {
        if (!in_array($this->user()->role, ['dg', 'admin', 'super_admin'], true)) {
            abort(403);
        }
    }

    private function indexRoute(): string
    {
        return $this->portal()[0] . '.stock-operations.index';
    }

    private function showRoute(StockOperation $op): string
    {
        return $this->portal()[0] . '.stock-operations.show';
    }

    private function backMovement(array $result, string $okMessage, bool $warn = false)
    {
        if (!$result['ok']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->route($this->portal()[0] . '.stock-operations.pending')
            ->with($warn ? 'warning' : 'success', $okMessage);
    }

    private function back(array $result, string $okMessage, string $koType = 'success')
    {
        if (!$result['ok']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->route($this->showRoute($result['operation']))->with($koType, $okMessage);
    }
}
