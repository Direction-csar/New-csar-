<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Models\Demande;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\Personnel;
use App\Models\Notification;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord Admin
     */
    public function index()
    {
        try {
            // Statistiques générales
            $stats = $this->getDashboardStats();
            
            // Activités récentes
            $recentActivities = $this->getRecentActivities();
            
            // Graphiques des données
            $chartsData = $this->getChartsData();
            
            // Alertes et notifications
            $alerts = $this->getSystemAlerts();
            
            // Log de l'accès au dashboard
            Log::info('Accès au dashboard Admin', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);

            return view('admin.dashboard.index', compact('stats', 'recentActivities', 'chartsData', 'alerts'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du dashboard Admin', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement du tableau de bord.');
        }
    }

    /**
     * Obtenir les statistiques du dashboard
     */
    private function getDashboardStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_requests' => PublicRequest::count(),
                'pending_requests' => PublicRequest::where('status', 'pending')->count(),
                'approved_requests' => PublicRequest::where('status', 'approved')->count(),
                'rejected_requests' => PublicRequest::where('status', 'rejected')->count(),
                'total_warehouses' => Warehouse::count(),
                'active_warehouses' => Warehouse::where('is_active', true)->count(),
                'total_stocks' => Stock::sum('quantity') ?? 0,
                'low_stock_items' => $this->getLowStockCount(),
                'total_personnel' => Personnel::count(),
                'active_personnel' => Personnel::where('statut', '!=', 'inactif')->count(),
                'today_requests' => PublicRequest::whereDate('created_at', today())->count(),
                'month_requests' => PublicRequest::whereMonth('created_at', now()->month)->count(),
                'fuel_available' => $this->getFuelAvailable(),
                'total_stock' => Stock::count(),
                'unread_notifications' => Notification::where('read', false)->count(),
                'unread_messages' => Message::where('lu', false)->count(),
            ];

            // Newsletter & Communication (optional tables - skip if not exist)
            try {
                $stats['total_newsletters'] = \Schema::hasTable('newsletters') ? DB::table('newsletters')->count() : 0;
                $stats['sent_newsletters'] = \Schema::hasTable('newsletters') ? DB::table('newsletters')->where('status', 'sent')->count() : 0;
                $stats['total_subscribers'] = \Schema::hasTable('newsletter_subscribers') ? DB::table('newsletter_subscribers')->count() : 0;
                $stats['active_subscribers'] = \Schema::hasTable('newsletter_subscribers') ? DB::table('newsletter_subscribers')->where('status', 'subscribed')->count() : 0;
            } catch (\Exception $e) {
                $stats['total_newsletters'] = 0;
                $stats['sent_newsletters'] = 0;
                $stats['total_subscribers'] = 0;
                $stats['active_subscribers'] = 0;
            }

            // Audit & Sécurité (optional tables - skip if not exist)
            try {
                $stats['total_audit_logs'] = \Schema::hasTable('audit_logs') ? DB::table('audit_logs')->count() : 0;
                $stats['today_audit_logs'] = \Schema::hasTable('audit_logs') ? DB::table('audit_logs')->whereDate('created_at', today())->count() : 0;
            } catch (\Exception $e) {
                $stats['total_audit_logs'] = 0;
                $stats['today_audit_logs'] = 0;
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Erreur dans getDashboardStats', ['error' => $e->getMessage()]);
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
                'active_personnel' => 0,
                'today_requests' => 0,
                'month_requests' => 0,
                'fuel_available' => 0,
                'total_stock' => 0,
                'unread_notifications' => 0,
                'unread_messages' => 0
            ];
        }
    }

    /**
     * Obtenir les activités récentes
     */
    private function getRecentActivities()
    {
        try {
            return [
                'recent_requests' => PublicRequest::orderBy('created_at', 'desc')->limit(5)->get(),
                'recent_users' => User::where('role', '!=', 'admin')->orderBy('created_at', 'desc')->limit(5)->get(),
                'recent_stocks' => Stock::with('warehouse')->orderBy('created_at', 'desc')->limit(5)->get()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans getRecentActivities', ['error' => $e->getMessage()]);
            return [
                'recent_requests' => collect(),
                'recent_users' => collect(),
                'recent_stocks' => collect()
            ];
        }
    }

    /**
     * Obtenir les données pour les graphiques
     */
    private function getChartsData()
    {
        try {
            // Évolution des demandes sur 7 jours
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $count = PublicRequest::whereDate('created_at', $date)->count();
                $last7Days[] = [
                    'label' => $date->format('D'),
                    'requests' => $count
                ];
            }

            // Répartition des entrepôts par région
            $entrepotsByRegion = Warehouse::select('region', DB::raw('count(*) as count'))
                ->groupBy('region')
                ->pluck('count', 'region')
                ->toArray();

            // Niveau d'occupation par entrepôt
            $fuelByEntrepot = Warehouse::select('name', 'current_stock')
                ->limit(5)
                ->pluck('current_stock', 'name')
                ->toArray();

            // Stock par entrepôt
            $stockByCategory = [];
            try {
                $stockByCategory = Stock::select('warehouse_id', DB::raw('sum(quantity) as total'))
                    ->with('warehouse')
                    ->groupBy('warehouse_id')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get()
                    ->pluck('total', 'warehouse.name')
                    ->toArray();
            } catch (\Exception $e) {
                $stockByCategory = [];
            }

            // Données pour la carte - Entrepôts
            $warehousesMapData = Warehouse::select('name', 'latitude', 'longitude', 'status', 'address', 'region')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'type' => 'warehouse',
                        'lat' => (float)$warehouse->latitude,
                        'lng' => (float)$warehouse->longitude,
                        'name' => $warehouse->name,
                        'status' => $warehouse->status,
                        'address' => $warehouse->address,
                        'region' => $warehouse->region ?? 'Non spécifié'
                    ];
                })
                ->toArray();

            // Données pour la carte - Demandes d'aide alimentaire (PublicRequest uniquement)
            $publicRequestsMapData = PublicRequest::select('id', 'full_name', 'latitude', 'longitude', 
                                                          'region', 'status', 'type', 'created_at', 'address')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('type', 'aide')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($request) {
                    return [
                        'type' => 'demande',
                        'id' => 'P' . $request->id,
                        'lat' => (float)$request->latitude,
                        'lng' => (float)$request->longitude,
                        'name' => $request->full_name,
                        'status' => $request->status,
                        'region' => $request->region ?? 'Non spécifié',
                        'demande_type' => $request->type,
                        'created_at' => $request->created_at->format('d/m/Y H:i'),
                        'address' => $request->address ?? 'Non spécifié'
                    ];
                })
                ->toArray();

            // Combiner toutes les données
            $allMapData = array_merge($warehousesMapData, $publicRequestsMapData);

            // Récupérer les années disponibles dans les demandes
            $availableYears = PublicRequest::selectRaw('DISTINCT YEAR(created_at) as year')
                ->whereNotNull('created_at')
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // Récupérer les régions disponibles dans les demandes et entrepôts
            $publicRegions = PublicRequest::select('region')
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->pluck('region')
                ->toArray();
            
            $warehouseRegions = Warehouse::select('region')
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->pluck('region')
                ->toArray();
            
            $allRegions = array_unique(array_merge($publicRegions, $warehouseRegions));
            sort($allRegions);

            return [
                'last7Days' => $last7Days,
                'entrepotsByRegion' => $entrepotsByRegion,
                'fuelByEntrepot' => $fuelByEntrepot,
                'stockByCategory' => $stockByCategory,
                'mapData' => $allMapData,
                'warehousesOnly' => $warehousesMapData,
                'demandesOnly' => $publicRequestsMapData,
                'availableYears' => $availableYears,
                'availableRegions' => $allRegions
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans getChartsData', ['error' => $e->getMessage()]);
            return [
                'last7Days' => [],
                'entrepotsByRegion' => [],
                'fuelByEntrepot' => [],
                'stockByCategory' => [],
                'mapData' => [],
                'warehousesOnly' => [],
                'demandesOnly' => [],
                'availableYears' => [],
                'availableRegions' => []
            ];
        }
    }

    /**
     * Obtenir les alertes système
     */
    private function getSystemAlerts()
    {
        try {
            $alerts = [];
            
            // Vérifier les stocks faibles
            $lowStockWarehouses = Warehouse::where('current_stock', '<', 100)->get();
            foreach ($lowStockWarehouses as $warehouse) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => "Stock faible détecté dans l'entrepôt {$warehouse->name} ({$warehouse->current_stock} unités)",
                    'priority' => 'medium'
                ];
            }
            
            // Vérifier les demandes en attente
            $pendingRequests = PublicRequest::where('status', 'pending')->count();
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
            Log::error('Erreur dans getSystemAlerts', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * API pour les statistiques en temps réel
     */
    public function realtimeStats(Request $request)
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
                'low_stock_items' => $stats['low_stock_items'],
                'fuel_available' => $stats['fuel_available'],
                'total_stock' => $stats['total_stock'],
                'unread_notifications' => $stats['unread_notifications'],
                'unread_messages' => $stats['unread_messages'],
                'map_data' => $chartsData['mapData'],
                'last7Days' => $chartsData['last7Days'],
                'entrepotsByRegion' => $chartsData['entrepotsByRegion'],
                'fuelByEntrepot' => $chartsData['fuelByEntrepot'],
                'stockByCategory' => $chartsData['stockByCategory']
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans realtimeStats', ['error' => $e->getMessage()]);
            return response()->json([
                'total_requests' => 0,
                'pending_requests' => 0,
                'approved_requests' => 0,
                'rejected_requests' => 0,
                'total_users' => 0,
                'active_users' => 0,
                'total_warehouses' => 0,
                'stock_value' => 0,
                'low_stock_items' => 0,
                'fuel_available' => 0,
                'total_stock' => 0,
                'unread_notifications' => 0,
                'unread_messages' => 0,
                'map_data' => [],
                'last7Days' => [],
                'entrepotsByRegion' => [],
                'fuelByEntrepot' => [],
                'stockByCategory' => []
            ]);
        }
    }

    /**
     * API pour filtrer les données de la carte
     */
    public function filterMapData(Request $request)
    {
        try {
            $filters = $request->validate([
                'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
                'month' => 'nullable|integer|min:1|max:12',
                'region' => 'nullable|string',
                'status' => 'nullable|string',
                'type' => 'nullable|in:all,warehouses,demandes'
            ]);

            // Demandes publiques uniquement
            $publicQuery = PublicRequest::select('id', 'full_name', 'latitude', 'longitude', 
                                                 'region', 'status', 'type', 'created_at', 'address')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('type', 'aide');

            if (!empty($filters['year'])) {
                $publicQuery->whereYear('created_at', $filters['year']);
            }
            
            if (!empty($filters['month'])) {
                $publicQuery->whereMonth('created_at', $filters['month']);
            }
            
            if (!empty($filters['region'])) {
                $publicQuery->where('region', $filters['region']);
            }
            
            if (!empty($filters['status'])) {
                $publicQuery->where('status', $filters['status']);
            }

            $publicRequestsMapData = $publicQuery->orderBy('created_at', 'desc')
                ->get()
                ->map(function($request) {
                    return [
                        'type' => 'demande',
                        'id' => 'P' . $request->id,
                        'lat' => (float)$request->latitude,
                        'lng' => (float)$request->longitude,
                        'name' => $request->full_name,
                        'status' => $request->status,
                        'region' => $request->region ?? 'Non spécifié',
                        'demande_type' => $request->type,
                        'created_at' => $request->created_at->format('d/m/Y H:i'),
                        'address' => $request->address ?? 'Non spécifié'
                    ];
                })
                ->toArray();

            $allDemandes = $publicRequestsMapData;

            // Si type = 'all' ou 'warehouses', ajouter les entrepôts
            $mapData = $allDemandes;
            if (empty($filters['type']) || $filters['type'] === 'all' || $filters['type'] === 'warehouses') {
                $warehousesQuery = Warehouse::select('name', 'latitude', 'longitude', 'status', 'address', 'region')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude');
                
                if (!empty($filters['region'])) {
                    $warehousesQuery->where('region', $filters['region']);
                }

                $warehousesMapData = $warehousesQuery->get()
                    ->map(function($warehouse) {
                        return [
                            'type' => 'warehouse',
                            'lat' => (float)$warehouse->latitude,
                            'lng' => (float)$warehouse->longitude,
                            'name' => $warehouse->name,
                            'status' => $warehouse->status,
                            'address' => $warehouse->address,
                            'region' => $warehouse->region ?? 'Non spécifié'
                        ];
                    })
                    ->toArray();

                if ($filters['type'] === 'warehouses') {
                    $mapData = $warehousesMapData;
                } elseif (empty($filters['type']) || $filters['type'] === 'all') {
                    $mapData = array_merge($warehousesMapData, $allDemandes);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $mapData,
                'count' => count($mapData),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans filterMapData', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du filtrage des données',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un rapport
     */
    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:dashboard,financial,operational,inventory,personnel',
                'format' => 'required|in:pdf,excel,csv',
                'period' => 'nullable|in:week,month,quarter,year',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            // Définir la période par défaut si non spécifiée
            $period = $request->get('period', 'month');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            if (!$dateFrom || !$dateTo) {
                $dates = $this->getPeriodDates($period);
                $dateFrom = $dates['from'];
                $dateTo = $dates['to'];
            }

            // Collecter les vraies données de la base MySQL
            $reportData = $this->collectDashboardData($dateFrom, $dateTo, $request->type);
            
            // Générer le rapport selon le format demandé
            $reportFile = $this->generateReportFile($reportData, $request->format, $request->type, $dateFrom, $dateTo);
            
            // Créer une notification
            $this->createNotification(
                'Rapport généré',
                "Le rapport {$request->type} a été généré avec succès",
                'success'
            );

            return response()->json([
                'success' => true,
                'message' => 'Rapport généré avec succès',
                'download_url' => $reportFile['url'],
                'filename' => $reportFile['filename'],
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DashboardController@generateReport', [
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
     * Collecter les données du tableau de bord
     */
    private function collectDashboardData($dateFrom, $dateTo, $type)
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
            // Statistiques générales
            $data['summary'] = [
                'total_users' => \App\Models\User::count(),
                'total_entrepots' => \App\Models\Warehouse::count(),
                'total_stock_movements' => \App\Models\StockMovement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_demandes' => \App\Models\PublicRequest::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_personnel' => \App\Models\Personnel::count(),
                'total_notifications' => \App\Models\Notification::whereBetween('created_at', [$dateFrom, $dateTo])->count()
            ];

            // Données détaillées selon le type
            switch ($type) {
                case 'financial':
                    $data['details'] = $this->getFinancialData($dateFrom, $dateTo);
                    break;
                case 'operational':
                    $data['details'] = $this->getOperationalData($dateFrom, $dateTo);
                    break;
                case 'inventory':
                    $data['details'] = $this->getInventoryData($dateFrom, $dateTo);
                    break;
                case 'personnel':
                    $data['details'] = $this->getPersonnelData($dateFrom, $dateTo);
                    break;
                default:
                    $data['details'] = $this->getDashboardData($dateFrom, $dateTo);
            }

            return $data;

        } catch (\Exception $e) {
            Log::warning('Erreur lors de la collecte des données', ['error' => $e->getMessage()]);
            
            // Retourner des données vides si erreur
            return [
                'period' => ['from' => $dateFrom, 'to' => $dateTo, 'type' => $type],
                'summary' => ['message' => 'Aucune donnée disponible'],
                'details' => ['message' => 'Aucune donnée disponible']
            ];
        }
    }

    /**
     * Données financières
     */
    private function getFinancialData($dateFrom, $dateTo)
    {
        return [
            'total_value' => 0, // À implémenter selon votre logique métier
            'movements_value' => 0,
            'costs' => 0,
            'revenue' => 0,
            'message' => 'Données financières à implémenter selon vos besoins'
        ];
    }

    /**
     * Données opérationnelles
     */
    private function getOperationalData($dateFrom, $dateTo)
    {
        try {
            $demandes = \App\Models\PublicRequest::whereBetween('created_at', [$dateFrom, $dateTo])->get();
            $stockMovements = \App\Models\StockMovement::whereBetween('created_at', [$dateFrom, $dateTo])->get();

            return [
                'demandes' => [
                    'total' => $demandes->count(),
                    'pending' => $demandes->where('status', 'pending')->count(),
                    'approved' => $demandes->where('status', 'approved')->count(),
                    'rejected' => $demandes->where('status', 'rejected')->count(),
                    'by_type' => $demandes->groupBy('type')->map->count()->toArray()
                ],
                'stock_movements' => [
                    'total' => $stockMovements->count(),
                    'in' => $stockMovements->where('type', 'in')->count(),
                    'out' => $stockMovements->where('type', 'out')->count(),
                    'by_entrepot' => $stockMovements->groupBy('entrepot_id')->map->count()->toArray()
                ]
            ];
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la collecte des données opérationnelles'];
        }
    }

    /**
     * Données d'inventaire
     */
    private function getInventoryData($dateFrom, $dateTo)
    {
        try {
            $entrepots = \App\Models\Warehouse::all();
            $stockMovements = \App\Models\StockMovement::whereBetween('created_at', [$dateFrom, $dateTo])->get();

            return [
                'entrepots' => $entrepots->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'location' => $warehouse->address,
                        'total_stock' => 0, // À calculer selon votre logique métier
                        'stock_value' => 0 // À calculer selon votre logique métier
                    ];
                }),
                'movements' => [
                    'total' => $stockMovements->count(),
                    'by_type' => $stockMovements->groupBy('type')->map->count()->toArray(),
                    'by_product' => $stockMovements->groupBy('product_name')->map->count()->toArray()
                ]
            ];
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la collecte des données d\'inventaire'];
        }
    }

    /**
     * Données du personnel
     */
    private function getPersonnelData($dateFrom, $dateTo)
    {
        try {
            $personnel = \App\Models\Personnel::all();
            $users = \App\Models\User::whereBetween('created_at', [$dateFrom, $dateTo])->get();

            return [
                'total_personnel' => $personnel->count(),
                'by_department' => $personnel->groupBy('department')->map->count()->toArray(),
                'by_position' => $personnel->groupBy('position')->map->count()->toArray(),
                'new_users' => $users->count(),
                'active_users' => \App\Models\User::where('last_login_at', '>=', $dateFrom)->count()
            ];
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la collecte des données du personnel'];
        }
    }

    /**
     * Données générales du tableau de bord
     */
    private function getDashboardData($dateFrom, $dateTo)
    {
        return [
            'overview' => $this->getOverviewStats($dateFrom, $dateTo),
            'recent_activities' => $this->getRecentActivities($dateFrom, $dateTo),
            'alerts' => $this->getAlerts($dateFrom, $dateTo)
        ];
    }

    /**
     * Générer le fichier de rapport
     */
    private function generateReportFile($data, $format, $type, $dateFrom, $dateTo)
    {
        $filename = "rapport_{$type}_" . date('Y-m-d_H-i-s') . ".{$format}";
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
            'url' => route('admin.reports.download', ['filename' => $filename])
        ];
    }

    /**
     * Générer un rapport PDF
     */
    private function generatePdfReport($data, $filePath, $type, $dateFrom, $dateTo)
    {
        $html = view('admin.reports.pdf-template', compact('data', 'type', 'dateFrom', 'dateTo'))->render();
        
        // Utiliser DomPDF ou une autre bibliothèque PDF
        // Pour l'instant, créer un fichier HTML simple
        file_put_contents($filePath, $html);
    }

    /**
     * Générer un rapport Excel
     */
    private function generateExcelReport($data, $filePath, $type, $dateFrom, $dateTo)
    {
        // Utiliser PhpSpreadsheet ou Laravel Excel
        // Pour l'instant, créer un fichier CSV
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
        $csv = "Rapport CSAR - " . date('Y-m-d H:i:s') . "\n";
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
            $filePath = storage_path("app/reports/{$filename}");
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé'
                ], 404);
            }

            return response()->download($filePath, $filename);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du rapport', [
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
     * Afficher le profil de l'administrateur
     */
    public function profile()
    {
        try {
            $user = auth()->user();
            
            // Données du profil
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'Administrateur',
                'created_at' => $user->created_at,
                'last_login' => $user->last_login_at ?? $user->updated_at,
                'avatar' => $user->avatar ?? null,
                'phone' => $user->phone ?? null,
                'department' => 'Direction Générale',
                'permissions' => [
                    'Gestion des utilisateurs',
                    'Gestion des entrepôts',
                    'Gestion des stocks',
                    'Gestion des demandes',
                    'Gestion du personnel',
                    'Rapports et statistiques'
                ]
            ];

            // Statistiques personnelles réelles
            $personalStats = [
                'total_actions' => StockMovement::where('created_by', $user->id)->count(),
                'demandes_traitees' => 0, // Pas de colonne user_id dans demandes
                'entrepots_geres' => Warehouse::count(),
                'rapports_generes' => 0, // À implémenter si nécessaire
                'connexions_mois' => 0 // À implémenter si nécessaire
            ];

            // Activités récentes réelles
            $recentActivities = [];
            
            // Récupérer les dernières activités de l'utilisateur
            $recentMovements = StockMovement::where('created_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
                
            foreach ($recentMovements as $movement) {
                $recentActivities[] = [
                    'action' => "Mouvement de stock {$movement->type} - {$movement->reference}",
                    'time' => $movement->created_at->format('H:i'),
                    'icon' => $movement->type === 'in' ? 'fas fa-arrow-down' : 'fas fa-arrow-up',
                    'color' => $movement->type === 'in' ? 'success' : 'danger'
                ];
            }

            return view('admin.profile.index', compact('profileData', 'personalStats', 'recentActivities'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du profil Admin', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement du profil.');
        }
    }

    /**
     * Mettre à jour le profil de l'administrateur
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Vérifier si c'est un changement de mot de passe
            if ($request->has('change_password')) {
                // Validation pour changement de mot de passe
                $request->validate([
                    'current_password' => 'required|string',
                    'new_password' => 'required|string|min:8',
                    'new_password_confirmation' => 'required|string|same:new_password'
                ]);
                
                // Vérifier le mot de passe actuel
                if (!\Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le mot de passe actuel est incorrect.'
                    ], 400);
                }
                
                // Mettre à jour le mot de passe
                $user->password = \Hash::make($request->new_password);
                $user->save();
                
                Log::info('Mot de passe Admin changé', [
                    'user_id' => $user->id,
                    'timestamp' => Carbon::now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Mot de passe changé avec succès.'
                ]);
                
            } else {
                // Validation pour mise à jour du profil
                $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'nullable|string|max:20'
                ]);
                
                // Mettre à jour les informations de base
                $user->name = $request->name;
                $user->email = $request->email;
                
                if ($request->filled('phone')) {
                    $user->phone = $request->phone;
                }
                
                $user->save();
                
                Log::info('Profil Admin mis à jour', [
                    'user_id' => $user->id,
                    'timestamp' => Carbon::now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès.',
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ]
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil Admin', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil.'
            ], 500);
        }
    }

    /**
     * Obtenir le nombre d'articles en stock faible
     */
    private function getLowStockCount()
    {
        try {
            return Stock::whereRaw('quantity <= min_quantity')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtenir la quantité de carburant disponible
     */
    private function getFuelAvailable()
    {
        try {
            return Stock::where(function($query) {
                $query->where('item_name', 'like', '%carburant%')
                      ->orWhere('item_name', 'like', '%fuel%')
                      ->orWhere('item_name', 'like', '%essence%');
            })->sum('quantity') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}