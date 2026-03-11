<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealtimeDataController extends Controller
{
    /**
     * Obtenir les données temps réel partagées entre Admin et DG
     */
    public function getSharedData()
    {
        try {
            // Statistiques générales (mêmes valeurs que Admin : pending, approved, rejected)
            $stats = [
                'total_requests' => PublicRequest::count(),
                'pending_requests' => PublicRequest::where('status', 'pending')->count(),
                'approved_requests' => PublicRequest::where('status', 'approved')->count(),
                'rejected_requests' => PublicRequest::where('status', 'rejected')->count(),
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_warehouses' => Warehouse::count(),
                'total_personnel' => Personnel::count(),
                'total_stocks' => Stock::count(),
                'low_stock_items' => Stock::whereColumn('quantity', '<=', 'min_quantity')->count(),
            ];

            // Évolution des demandes (7 derniers jours)
            $requestsEvolution = DB::table('public_requests')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            // Demandes récentes
            $recentRequests = PublicRequest::with(['user'])
                ->latest()
                ->take(5)
                ->get();

            // Entrepôts avec stock
            $warehouses = Warehouse::withCount(['stocks'])
                ->get();

            // Données de la carte
            $mapData = Warehouse::select('name', 'latitude', 'longitude', 'region', 'current_stock')
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'requests_evolution' => $requestsEvolution,
                    'recent_requests' => $recentRequests,
                    'warehouses' => $warehouses,
                    'map_data' => $mapData,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des données',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques de performance
     */
    public function getPerformanceStats()
    {
        try {
            // Données réelles : taux de traitement des demandes (aligné Admin/DG)
            $totalRequests = PublicRequest::count();
            $processedRequests = PublicRequest::whereIn('status', ['approved', 'rejected'])->count();
            $efficiencyRate = $totalRequests > 0 ? round(($processedRequests / $totalRequests) * 100, 1) : 0;

            // Délai moyen / satisfaction : pas de champs en BDD → afficher "—" côté vue
            return response()->json([
                'success' => true,
                'data' => [
                    'efficiency_rate' => $efficiencyRate . '%',
                    'response_time' => '—',
                    'satisfaction_rate' => '—',
                    'total_processed' => $processedRequests,
                    'processing_rate' => $efficiencyRate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du calcul des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les alertes temps réel
     */
    public function getAlerts()
    {
        try {
            $alerts = [];

            // Alertes de stock faible
            $lowStockItems = Stock::whereColumn('quantity', '<=', 'min_quantity')
                ->with('warehouse')
                ->get();

            foreach ($lowStockItems as $item) {
                $alerts[] = [
                    'type' => 'stock_low',
                    'title' => 'Stock faible détecté',
                    'message' => "Stock faible dans l'entrepôt {$item->warehouse->name} ({$item->quantity} unités)",
                    'priority' => 'high',
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ];
            }

            // Alertes de demandes en attente
            $pendingRequests = PublicRequest::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->count();

            if ($pendingRequests > 0) {
                $alerts[] = [
                    'type' => 'pending_requests',
                    'title' => 'Demandes en attente',
                    'message' => "{$pendingRequests} demande(s) en attente depuis plus de 24h",
                    'priority' => 'medium',
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'alerts' => $alerts,
                    'total_alerts' => count($alerts)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des alertes',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
