<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord DG (lecture seule)
     */
    public function index()
    {
        try {
            // Statistiques générales (lecture seule)
            $stats = $this->getDashboardStats();
            
            // Activités récentes (lecture seule)
            $recentActivities = $this->getRecentActivities();
            
            // Demandes récentes pour l'affichage
            $recentRequests = \App\Models\PublicRequest::latest()->take(5)->get();
            
            // Graphiques des données (lecture seule)
            $chartsData = $this->getChartsData();
            
            // Alertes système (lecture seule)
            $alerts = $this->getSystemAlerts();
            
            // Données de la carte interactive
            $mapData = $this->getMapData();
            
            // Log de l'accès au dashboard DG
            Log::info('Accès au dashboard DG', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);

            return view('dg.dashboard-executive', compact('stats', 'recentActivities', 'chartsData', 'alerts', 'mapData', 'recentRequests'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du dashboard DG', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement du tableau de bord.');
        }
    }

    /**
     * Obtenir les statistiques du dashboard (lecture seule)
     */
    private function getDashboardStats()
    {
        try {
            // Utiliser les données partagées pour synchroniser avec Admin
            $sharedController = new \App\Http\Controllers\Shared\RealtimeDataController();
            $response = $sharedController->getSharedData();
            
            if ($response->getStatusCode() === 200) {
                $sharedData = json_decode($response->getContent(), true)['data'];
                $stats = $sharedData['stats'];
                
                // Ajouter les KPIs de performance
                $perfResponse = $sharedController->getPerformanceStats();
                if ($perfResponse->getStatusCode() === 200) {
                    $perfData = json_decode($perfResponse->getContent(), true)['data'];
                    $stats = array_merge($stats, $perfData);
                }
                
                return $stats;
            } else {
                throw new \Exception('Erreur lors de la récupération des données partagées');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans getDashboardStats DG', ['error' => $e->getMessage()]);
            return [
                'total_users' => 0,
                'active_users' => 0,
                'total_requests' => 0,
                'pending_requests' => 0,
                'approved_requests' => 0,
                'rejected_requests' => 0,
                'total_warehouses' => 0,
                'active_warehouses' => 0,
                'total_stocks' => 0,
                'low_stock_items' => 0,
                'total_personnel' => 0,
                'approval_rate' => 0,
                'average_processing_time' => 0,
            ];
        }
    }

    /**
     * Obtenir les activités récentes (lecture seule)
     */
    private function getRecentActivities()
    {
        try {
            return [
                'recent_requests' => \App\Models\PublicRequest::with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'recent_users' => \App\Models\User::where('role', '!=', 'admin')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'recent_movements' => \App\Models\StockMovement::with('warehouse')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans getRecentActivities DG', ['error' => $e->getMessage()]);
            return [
                'recent_requests' => collect(),
                'recent_users' => collect(),
                'recent_movements' => collect()
            ];
        }
    }

    /**
     * Obtenir les données pour les graphiques (lecture seule)
     */
    private function getChartsData()
    {
        try {
            // Évolution des demandes sur 7 jours
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = \App\Models\PublicRequest::whereDate('created_at', $date)->count();
                $last7Days[] = [
                    'label' => $date->format('D'),
                    'requests' => $count
                ];
            }

            // Répartition des entrepôts par région
            $entrepotsByRegion = \App\Models\Warehouse::select('region', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('region')
                ->pluck('count', 'region')
                ->toArray();

            // Données pour la carte
            $mapData = \App\Models\Warehouse::select('name', 'latitude', 'longitude', 'is_active')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'lat' => (float)$warehouse->latitude,
                        'lng' => (float)$warehouse->longitude,
                        'name' => $warehouse->name,
                        'status' => $warehouse->is_active ? 'active' : 'inactive'
                    ];
                })
                ->toArray();

            return [
                'last7Days' => $last7Days,
                'entrepotsByRegion' => $entrepotsByRegion,
                'mapData' => $mapData
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans getChartsData DG', ['error' => $e->getMessage()]);
            return [
                'last7Days' => [],
                'entrepotsByRegion' => [],
                'mapData' => []
            ];
        }
    }

    /**
     * Obtenir les alertes système (lecture seule)
     */
    private function getSystemAlerts()
    {
        try {
            $alerts = [];
            
            // Vérifier les stocks faibles
            $lowStockWarehouses = \App\Models\Warehouse::where('current_stock', '<', 100)->get();
            foreach ($lowStockWarehouses as $warehouse) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => "Stock faible détecté dans l'entrepôt {$warehouse->name} ({$warehouse->current_stock} unités)",
                    'priority' => 'medium'
                ];
            }
            
            // Vérifier les demandes en attente
            $pendingRequests = \App\Models\PublicRequest::where('status', 'pending')->count();
            if ($pendingRequests > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'info-circle',
                    'message' => "{$pendingRequests} nouvelles demandes en attente de traitement",
                    'priority' => 'low'
                ];
            }
            
            return $alerts;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans getSystemAlerts DG', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Obtenir les données de la carte
     */
    private function getMapData()
    {
        try {
            return \App\Models\Warehouse::select('name', 'latitude', 'longitude', 'is_active')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'lat' => (float)$warehouse->latitude,
                        'lng' => (float)$warehouse->longitude,
                        'name' => $warehouse->name,
                        'status' => $warehouse->is_active ? 'active' : 'inactive'
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans getMapData DG', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * API pour les statistiques en temps réel
     */
    public function getRealtimeStats(Request $request)
    {
        try {
            $stats = $this->getDashboardStats();
            $chartsData = $this->getChartsData();
            
            return response()->json([
                'total_requests' => $stats['total_requests'],
                'pending_requests' => $stats['pending_requests'],
                'approved_requests' => $stats['approved_requests'],
                'rejected_requests' => $stats['rejected_requests'],
                'total_users' => $stats['total_users'],
                'active_users' => $stats['active_users'],
                'total_warehouses' => $stats['total_warehouses'],
                'stock_value' => $stats['total_stocks'],
                'map_data' => $chartsData['mapData']
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans getRealtimeStats DG', ['error' => $e->getMessage()]);
            return response()->json([
                'total_requests' => 0,
                'pending_requests' => 0,
                'approved_requests' => 0,
                'rejected_requests' => 0,
                'total_users' => 0,
                'active_users' => 0,
                'total_warehouses' => 0,
                'stock_value' => 0,
                'map_data' => []
            ]);
        }
    }

    /**
     * Générer un rapport
     */
    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:monthly,financial,personnel,operational',
                'format' => 'required|in:pdf,excel,csv',
                'period' => 'nullable|in:week,month,quarter,year'
            ]);

            // Définir la période par défaut si non spécifiée
            $period = $request->get('period', 'month');
            $dates = $this->getPeriodDates($period);

            // Collecter les données selon le type de rapport
            $reportData = $this->collectDGData($dates['from'], $dates['to'], $request->type);
            
            // Générer le fichier de rapport
            $reportFile = $this->generateReportFile($reportData, $request->format, $request->type, $dates['from'], $dates['to']);
            
            return response()->json([
                'success' => true,
                'message' => 'Rapport DG généré avec succès',
                'download_url' => $reportFile['url'],
                'filename' => $reportFile['filename'],
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DGDashboardController@generateReport', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Collecter les données DG
     */
    private function collectDGData($dateFrom, $dateTo, $type)
    {
        $data = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'type' => $type
            ],
            'summary' => [],
            'details' => []
        ];

        try {
            // Statistiques générales DG
            $data['summary'] = [
                'total_entrepots' => \App\Models\Entrepot::count(),
                'total_personnel' => \App\Models\Personnel::count(),
                'total_demandes' => \App\Models\PublicRequest::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_stock_movements' => \App\Models\StockMovement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_budget_utilise' => 0, // À implémenter selon votre logique
                'total_revenus' => 0 // À implémenter selon votre logique
            ];

            // Données détaillées selon le type
            switch ($type) {
                case 'financial':
                    $data['details'] = $this->getDGFinancialData($dateFrom, $dateTo);
                    break;
                case 'personnel':
                    $data['details'] = $this->getDGPersonnelData($dateFrom, $dateTo);
                    break;
                case 'operational':
                    $data['details'] = $this->getDGOperationalData($dateFrom, $dateTo);
                    break;
                default:
                    $data['details'] = $this->getDGMonthlyData($dateFrom, $dateTo);
            }

            return $data;

        } catch (\Exception $e) {
            Log::warning('Erreur lors de la collecte des données DG', ['error' => $e->getMessage()]);
            
            return [
                'period' => ['from' => $dateFrom, 'to' => $dateTo, 'type' => $type],
                'summary' => ['message' => 'Aucune donnée disponible'],
                'details' => ['message' => 'Aucune donnée disponible']
            ];
        }
    }

    /**
     * Données financières DG
     */
    private function getDGFinancialData($dateFrom, $dateTo)
    {
        return [
            'budget_annuel' => 0, // À implémenter
            'budget_utilise' => 0,
            'budget_restant' => 0,
            'cout_operations' => 0,
            'revenus_ventes' => 0,
            'message' => 'Données financières à implémenter selon vos besoins'
        ];
    }

    /**
     * Données du personnel DG
     */
    private function getDGPersonnelData($dateFrom, $dateTo)
    {
        try {
            $personnel = \App\Models\Personnel::all();
            $users = \App\Models\User::whereBetween('created_at', [$dateFrom, $dateTo])->get();

            return [
                'total_personnel' => $personnel->count(),
                'by_department' => $personnel->groupBy('department')->map->count()->toArray(),
                'by_position' => $personnel->groupBy('position')->map->count()->toArray(),
                'new_employees' => $users->count(),
                'active_employees' => \App\Models\User::where('last_login_at', '>=', $dateFrom)->count(),
                'departments' => $personnel->groupBy('department')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'positions' => $group->groupBy('position')->map->count()->toArray()
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la collecte des données du personnel'];
        }
    }

    /**
     * Données opérationnelles DG
     */
    private function getDGOperationalData($dateFrom, $dateTo)
    {
        try {
            $demandes = \App\Models\PublicRequest::whereBetween('created_at', [$dateFrom, $dateTo])->get();
            $stockMovements = \App\Models\StockMovement::whereBetween('created_at', [$dateFrom, $dateTo])->get();
            $entrepots = \App\Models\Entrepot::with('stocks')->get();

            return [
                'demandes' => [
                    'total' => $demandes->count(),
                    'pending' => $demandes->where('status', 'pending')->count(),
                    'approved' => $demandes->where('status', 'approved')->count(),
                    'rejected' => $demandes->where('status', 'rejected')->count(),
                    'by_type' => $demandes->groupBy('type')->map->count()->toArray(),
                    'by_region' => $demandes->groupBy('region')->map->count()->toArray()
                ],
                'stock_movements' => [
                    'total' => $stockMovements->count(),
                    'in' => $stockMovements->where('type', 'in')->count(),
                    'out' => $stockMovements->where('type', 'out')->count(),
                    'by_entrepot' => $stockMovements->groupBy('entrepot_id')->map->count()->toArray()
                ],
                'entrepots' => $entrepots->map(function($entrepot) {
                    return [
                        'id' => $entrepot->id,
                        'name' => $entrepot->name,
                        'location' => $entrepot->location,
                        'total_stock' => $entrepot->stocks->sum('quantity'),
                        'stock_value' => $entrepot->stocks->sum('value')
                    ];
                })
            ];
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la collecte des données opérationnelles'];
        }
    }

    /**
     * Données mensuelles DG
     */
    private function getDGMonthlyData($dateFrom, $dateTo)
    {
        return [
            'overview' => $this->getOverviewStats($dateFrom, $dateTo),
            'performance_indicators' => $this->getPerformanceIndicators($dateFrom, $dateTo),
            'key_achievements' => $this->getKeyAchievements($dateFrom, $dateTo)
        ];
    }

    /**
     * Générer le fichier de rapport
     */
    private function generateReportFile($data, $format, $type, $dateFrom, $dateTo)
    {
        $filename = "rapport_dg_{$type}_" . date('Y-m-d_H-i-s') . ".{$format}";
        $filePath = storage_path("app/reports/{$filename}");

        // Créer le dossier s'il n'existe pas
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        switch ($format) {
            case 'pdf':
                $this->generatePdfReport($data, $filePath, $type, $dateFrom, $dateTo);
                break;
            case 'excel':
                $this->generateExcelReport($data, $filePath, $type, $dateFrom, $dateTo);
                break;
            case 'csv':
                $this->generateCsvReport($data, $filePath, $type, $dateFrom, $dateTo);
                break;
        }

        return [
            'filename' => $filename,
            'path' => $filePath,
            'url' => route('dg.reports.download', ['filename' => $filename])
        ];
    }

    /**
     * Générer un rapport PDF
     */
    private function generatePdfReport($data, $filePath, $type, $dateFrom, $dateTo)
    {
        // Génération PDF réelle (DomPDF) au lieu d'écrire du HTML avec extension .pdf
        $pdf = Pdf::loadView('dg.reports.pdf-template', compact('data', 'type', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'portrait');

        file_put_contents($filePath, $pdf->output());
    }

    /**
     * Générer un rapport Excel
     */
    private function generateExcelReport($data, $filePath, $type, $dateFrom, $dateTo)
    {
        $csv = $this->arrayToCsv($data);
        file_put_contents($filePath, $csv);
    }

    /**
     * Générer un rapport CSV
     */
    private function generateCsvReport($data, $filePath, $type, $dateFrom, $dateTo)
    {
        $csv = $this->arrayToCsv($data);
        file_put_contents($filePath, $csv);
    }

    /**
     * Convertir un tableau en CSV
     */
    private function arrayToCsv($data)
    {
        $csv = "Rapport DG CSAR - " . date('Y-m-d H:i:s') . "\n";
        $csv .= "Période: {$data['period']['from']} à {$data['period']['to']}\n";
        $csv .= "Type: {$data['period']['type']}\n\n";
        
        $csv .= "RÉSUMÉ\n";
        foreach ($data['summary'] as $key => $value) {
            $csv .= ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }
        
        $csv .= "\nDÉTAILS\n";
        $csv .= json_encode($data['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return $csv;
    }

    /**
     * Obtenir les dates de période
     */
    private function getPeriodDates($period)
    {
        $now = now();
        
        switch ($period) {
            case 'week':
                return [
                    'from' => $now->startOfWeek()->format('Y-m-d'),
                    'to' => $now->endOfWeek()->format('Y-m-d')
                ];
            case 'month':
                return [
                    'from' => $now->startOfMonth()->format('Y-m-d'),
                    'to' => $now->endOfMonth()->format('Y-m-d')
                ];
            case 'quarter':
                return [
                    'from' => $now->startOfQuarter()->format('Y-m-d'),
                    'to' => $now->endOfQuarter()->format('Y-m-d')
                ];
            case 'year':
                return [
                    'from' => $now->startOfYear()->format('Y-m-d'),
                    'to' => $now->endOfYear()->format('Y-m-d')
                ];
            default:
                return [
                    'from' => $now->subMonth()->format('Y-m-d'),
                    'to' => $now->format('Y-m-d')
                ];
        }
    }

    /**
     * Télécharger un rapport
     */
    public function downloadReport($filename)
    {
        try {
            $safe = basename($filename);
            if ($safe !== $filename) {
                return response()->json(['success' => false, 'message' => 'Fichier invalide'], 400);
            }

            $filePath = storage_path("app/reports/{$safe}");
            
            if (!file_exists($filePath)) {
                // Fallback: rapports livrés dans public/rapport
                $publicPath = public_path("rapport/{$safe}");
                if (file_exists($publicPath)) {
                    return response()->download($publicPath, $safe);
                }

                return response()->json(['success' => false, 'message' => 'Fichier non trouvé'], 404);
            }

            return response()->download($filePath, $safe);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du rapport DG', [
                'error' => $e->getMessage(),
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement du rapport'
            ], 500);
        }
    }

    /**
     * Obtenir le nombre d'articles en stock faible
     */
    private function getLowStockCount()
    {
        try {
            return \App\Models\StockMovement::whereHas('stock', function($query) {
                $query->whereRaw('quantity_after < min_quantity');
            })->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtenir le taux d'approbation
     */
    private function getApprovalRate()
    {
        try {
            $total = \App\Models\PublicRequest::count();
            $approved = \App\Models\PublicRequest::where('status', 'approved')->count();
            return $total > 0 ? round(($approved / $total) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtenir le temps de traitement moyen
     */
    private function getAverageProcessingTime()
    {
        try {
            $requests = \App\Models\PublicRequest::whereNotNull('processed_at')
                ->whereNotNull('created_at')
                ->get();
            
            if ($requests->isEmpty()) {
                return 0;
            }
            
            $totalHours = $requests->sum(function($request) {
                return $request->created_at->diffInHours($request->processed_at);
            });
            
            return round($totalHours / $requests->count(), 1);
        } catch (\Exception $e) {
            return 0;
        }
    }
}