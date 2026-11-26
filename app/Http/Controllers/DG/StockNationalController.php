<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\InspectorCoverageService;
use Illuminate\Support\Facades\Auth;

/**
 * Vue nationale des stocks pour la Direction Générale :
 * pilotage exécutif des 49 magasins, 14 régions, circuit complet.
 */
class StockNationalController extends Controller
{
    public function index(InspectorCoverageService $coverage)
    {
        $now = now();
        $startMonth = $now->copy()->startOfMonth();

        $stats = [
            'magasins_actifs'  => Warehouse::where('status', 'active')->count(),
            'regions'          => Warehouse::where('status', 'active')->whereNotNull('region')->distinct()->count('region'),
            'stock_national'   => Warehouse::where('status', 'active')->sum('current_stock'),
            'entrees_mois'     => StockMovement::whereIn('type', ['entry', 'in'])->where('created_at', '>=', $startMonth)->sum('quantity'),
            'sorties_mois'     => StockMovement::whereIn('type', ['exit', 'out'])->where('created_at', '>=', $startMonth)->sum('quantity'),
            'transferts_mois'  => StockMovement::where('type', 'transfer')->where('created_at', '>=', $startMonth)->count(),
            'ops_cours'        => StockOperation::whereIn('status', [
                StockOperation::STATUS_SOUMISE, StockOperation::STATUS_AFFECTEE,
                StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE,
            ])->count(),
            'mouvements_attente' => StockMovement::where('status', 'en_attente')->count(),
            'anomalies'        => StockOperation::where('status', StockOperation::STATUS_ANOMALIE)->count()
                + StockMovement::where('status', 'anomalie')->count(),
            'stocks_critique'  => Warehouse::where('status', 'active')->where('current_stock', '<=', 100)->count(),
        ];

        // Stock par région
        $parRegion = Warehouse::where('status', 'active')
            ->selectRaw('region, COUNT(*) as magasins, SUM(current_stock) as stock')
            ->groupBy('region')->orderByDesc('stock')->get();

        // Top produits (denrées) par quantité mouvementée sur 30 jours
        $parProduit = StockMovement::where('created_at', '>=', $now->copy()->subDays(30))
            ->whereNotNull('reason')->selectRaw('reason, SUM(quantity) as total')
            ->groupBy('reason')->orderByDesc('total')->limit(8)->get();

        // Activité 30 jours : entrées vs sorties par jour
        $activite = StockMovement::where('created_at', '>=', $now->copy()->subDays(29))
            ->selectRaw("DATE(created_at) as jour, SUM(CASE WHEN type IN ('entry','in') THEN quantity ELSE 0 END) as entrees, SUM(CASE WHEN type IN ('exit','out') THEN quantity ELSE 0 END) as sorties")
            ->groupBy('jour')->orderBy('jour')->get();

        $couverture = $coverage->report();

        $dernieresOps = StockOperation::with(['warehouse:id,name,region', 'inspector:id,name', 'magasinier:id,name'])
            ->latest('id')->limit(10)->get();

        $alertes = Warehouse::where('status', 'active')
            ->where('current_stock', '<=', 100)
            ->orderBy('current_stock')->limit(8)->get(['id', 'name', 'region', 'current_stock', 'capacity']);

        $inspecteurs = User::where('role', 'inspecteur')->where('is_active', true)->count();
        $magasiniers = User::where('role', 'magasinier')->where('is_active', true)->count();

        return view('dg.stock-national', [
            'portal' => 'dg',
            'portalLabel' => 'Direction Générale',
            'dashboardRoute' => 'dg.stock-national',
            'logoutRoute' => 'dg.logout',
        ] + compact('stats', 'parRegion', 'parProduit', 'activite', 'couverture', 'dernieresOps', 'alertes', 'inspecteurs', 'magasiniers'));
    }
}
