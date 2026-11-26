<?php

namespace App\Http\Controllers\Dtl;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\InspectorCoverageService;
use Illuminate\Support\Facades\Auth;

/**
 * Tableau de bord DTL « centre de pilotage » : vision opérationnelle
 * du circuit DG → DTL → Inspecteur → Magasinier sur données réelles.
 */
class StockDashboardController extends Controller
{
    public function index(InspectorCoverageService $coverage)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['dtl', 'admin', 'super_admin', 'dg'], true), 403);

        $now = now();

        $ops = StockOperation::query();
        $stats = [
            'instructions_reçues'   => (clone $ops)->where('status', StockOperation::STATUS_SOUMISE)->count(),
            'a_affecter'            => (clone $ops)->where('status', StockOperation::STATUS_SOUMISE)->count(),
            'en_cours'              => (clone $ops)->whereIn('status', [StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE])->count(),
            'verifiees_mois'        => (clone $ops)->where('status', StockOperation::STATUS_VERIFIEE)->where('verified_at', '>=', $now->copy()->startOfMonth())->count(),
            'anomalies'             => (clone $ops)->where('status', StockOperation::STATUS_ANOMALIE)->count(),
            'rejets_mois'           => (clone $ops)->where('status', StockOperation::STATUS_REJETEE)->where('updated_at', '>=', $now->copy()->startOfMonth())->count(),
        ];

        $inspectors = User::where('role', 'inspecteur')->where('is_active', true)->count();
        $magasins = Warehouse::where('status', 'active')->count();
        $magasinsAvecMagasinier = Warehouse::where('status', 'active')
            ->whereHas('users', fn ($q) => $q->where('role', 'magasinier')->where('is_active', true))
            ->count();

        $mouvements = [
            'en_attente' => StockMovement::where('status', 'en_attente')->count(),
            'receptions' => StockMovement::where('status', 'en_attente')
                ->where('type', 'entry')
                ->where('metadata', 'like', '%"transfer_type":"in"%')
                ->where('metadata', 'not like', '%receipt_confirmed_at%')
                ->count(),
        ];

        $aAffecter = StockOperation::with(['warehouse:id,name,region', 'destinationWarehouse:id,name'])
            ->where('status', StockOperation::STATUS_SOUMISE)
            ->latest('id')->limit(8)->get();

        $enCours = StockOperation::with(['warehouse:id,name', 'inspector:id,name', 'magasinier:id,name'])
            ->whereIn('status', [StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE, StockOperation::STATUS_ANOMALIE])
            ->latest('id')->limit(10)->get();

        $anomalies = StockOperation::with(['warehouse:id,name', 'inspector:id,name'])
            ->where('status', StockOperation::STATUS_ANOMALIE)
            ->latest('id')->limit(6)->get();

        $couverture = $coverage->report();
        $regionsCouvertes = collect($couverture)->where('status', 'COUVERT')->count();
        $regionsIncomplete = collect($couverture)->filter(fn ($r) => str_starts_with($r['status'], 'INCOMPLET'))->count();
        $regionsSansMagasin = collect($couverture)->where('status', 'AUCUN MAGASIN')->count();

        // Activité par statut sur 30 jours (graphique)
        $parStatut = StockOperation::selectRaw('status, count(*) as total')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->groupBy('status')->pluck('total', 'status');

        return view('dtl.stock-dashboard', compact(
            'stats', 'inspectors', 'magasins', 'magasinsAvecMagasinier',
            'mouvements', 'aAffecter', 'enCours', 'anomalies',
            'regionsCouvertes', 'regionsIncomplete', 'regionsSansMagasin', 'parStatut'
        ) + ['direction' => 'dtl', 'directionLabel' => 'DTL — Pilotage stocks', 'portal' => 'dtl', 'portalLabel' => 'DTL — Pilotage']);
    }
}
