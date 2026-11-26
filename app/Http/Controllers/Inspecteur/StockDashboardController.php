<?php

namespace App\Http\Controllers\Inspecteur;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\WarehouseStockService;
use Illuminate\Support\Facades\Auth;

/**
 * Tableau de bord « CSAR — Gestion du contrôle » de l'inspecteur.
 * Strictement limité au périmètre d'affectation (magasins couverts).
 */
class StockDashboardController extends Controller
{
    public function __construct(private WarehouseStockService $stock) {}

    public function index()
    {
        $user = Auth::guard('inspecteur')->user();
        abort_unless($user && in_array($user->role, ['inspecteur', 'admin'], true), 403);

        $scopeIds = $this->stock->inspectorScopeWarehouseIds($user) ?? [];

        $warehouses = Warehouse::whereIn('id', $scopeIds)->orderBy('name')->get(['id', 'name', 'region', 'current_stock']);

        $scope = function ($q) use ($scopeIds) {
            $q->whereIn('warehouse_id', $scopeIds)->orWhereIn('destination_warehouse_id', $scopeIds);
        };

        $stats = [
            'magasins'        => $warehouses->count(),
            'ops_reçues'      => StockOperation::where($scope)->count(),
            'a_traiter'       => StockOperation::where($scope)->whereIn('status', [StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE])->count(),
            'executees'       => StockOperation::where($scope)->where('status', StockOperation::STATUS_EXECUTEE)->count(),
            'verifiees'       => StockOperation::where($scope)->where('status', StockOperation::STATUS_VERIFIEE)->count(),
            'anomalies'       => StockOperation::where($scope)->where('status', StockOperation::STATUS_ANOMALIE)->count(),
        ];

        $aTraiter = StockOperation::with(['warehouse:id,name,region', 'destinationWarehouse:id,name', 'magasinier:id,name'])
            ->where($scope)
            ->whereIn('status', [StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE])
            ->latest('id')->limit(10)->get();

        $mouvements = $this->stock->pendingMovements($user)->take(10);

        $receptions = StockMovement::where('status', 'en_attente')
            ->whereIn('warehouse_id', $scopeIds)
            ->where('type', 'entry')
            ->where('metadata', 'like', '%"transfer_type":"in"%')
            ->latest('id')->limit(6)->get();

        $historiques = StockOperation::with(['warehouse:id,name'])
            ->where(function ($q) use ($scopeIds) {
                $q->whereIn('warehouse_id', $scopeIds)->orWhereIn('destination_warehouse_id', $scopeIds);
            })
            ->whereIn('status', [StockOperation::STATUS_VERIFIEE, StockOperation::STATUS_REJETEE])
            ->latest('verified_at')->latest('id')->limit(8)->get();

        $magasiniers = User::where('role', 'magasinier')->where('is_active', true)
            ->whereIn('warehouse_id', $scopeIds)->get(['id', 'name', 'warehouse_id']);

        return view('inspecteur.stock-dashboard', compact(
            'warehouses', 'stats', 'aTraiter', 'mouvements', 'receptions', 'historiques', 'magasiniers'
        ) + ['direction' => 'inspecteur', 'directionLabel' => 'Gestion du contrôle', 'portal' => 'inspecteur', 'portalLabel' => 'Gestion du contrôle']);
    }
}
