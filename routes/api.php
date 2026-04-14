<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Routes d'authentification API
Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Identifiants invalides'
    ], 401);
});

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard et métriques
    Route::get('/dashboard/metrics', function () {
        return response()->json([
            'totalRequests' => rand(1000, 5000),
            'pendingRequests' => rand(50, 200),
            'completedRequests' => rand(800, 1200),
            'totalStock' => rand(5000, 15000),
            'lowStockAlerts' => rand(0, 10),
            'activeUsers' => rand(50, 300)
        ]);
    });
    
    // Analytics
    Route::get('/analytics/data', function () {
        return response()->json([
            'overallPerformance' => rand(80, 95),
            'activeUsers' => rand(1000, 2000),
            'responseTime' => rand(100, 500),
            'successRate' => rand(90, 99),
            'errorRate' => rand(1, 5),
            'uptime' => 99.9
        ]);
    });
    
    Route::post('/analytics/track', function (Request $request) {
        // Logique de tracking des événements
        $eventType = $request->input('event_type');
        $data = $request->input('data');
        
        // Ici vous pourriez sauvegarder en base de données
        \Log::info('Analytics Event', [
            'type' => $eventType,
            'data' => $data,
            'timestamp' => now()
        ]);
        
        return response()->json(['success' => true]);
    });
    
    // Mises à jour en temps réel
    Route::post('/realtime/updates', function (Request $request) {
        $lastUpdate = $request->input('lastUpdate', 0);
        $currentTime = time();
        
        // Simulation de mises à jour
        $updates = [];
        
        if ($currentTime - $lastUpdate > 30) {
            $updates[] = [
                'type' => 'metric_update',
                'metricId' => 'active-users',
                'value' => rand(1000, 2000),
                'animation' => 'smooth'
            ];
        }
        
        return response()->json([
            'updates' => $updates,
            'timestamp' => $currentTime
        ]);
    });
    
    // Notifications
    Route::get('/notifications', function () {
        return response()->json([
            'notifications' => [
                [
                    'id' => 1,
                    'title' => 'Nouvelle demande reçue',
                    'message' => 'Une nouvelle demande urgente a été soumise',
                    'type' => 'info',
                    'timestamp' => now()->subMinutes(5)
                ],
                [
                    'id' => 2,
                    'title' => 'Stock faible',
                    'message' => 'Le stock de riz est en dessous du seuil',
                    'type' => 'warning',
                    'timestamp' => now()->subMinutes(15)
                ]
            ]
        ]);
    });
    
    // Suivi des collecteurs en temps réel
    Route::prefix('mobile')->group(function () {
        Route::post('/collectors/location', [\App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'updateLocation']);
        Route::get('/collectors/locations', [\App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'getActiveCollectors']);
    });
    
    // Rapports
    Route::get('/reports/daily', function () {
        return response()->json([
            'date' => now()->format('Y-m-d'),
            'metrics' => [
                'requests' => rand(50, 200),
                'completed' => rand(40, 180),
                'pending' => rand(5, 25),
                'errors' => rand(0, 5)
            ]
        ]);
    });
    
    Route::get('/reports/weekly', function () {
        return response()->json([
            'week' => now()->format('Y-W'),
            'metrics' => [
                'requests' => rand(300, 1200),
                'completed' => rand(250, 1100),
                'pending' => rand(20, 100),
                'errors' => rand(5, 30)
            ]
        ]);
    });
    
    Route::get('/reports/monthly', function () {
        return response()->json([
            'month' => now()->format('Y-m'),
            'metrics' => [
                'requests' => rand(1000, 5000),
                'completed' => rand(800, 4500),
                'pending' => rand(50, 300),
                'errors' => rand(20, 100)
            ]
        ]);
    });
    
    // Export de données
    Route::post('/export/data', function (Request $request) {
        $format = $request->input('format', 'json');
        $data = $request->input('data', []);
        
        // Logique d'export selon le format
        switch ($format) {
            case 'csv':
                return response()->json(['download_url' => '/api/download/export.csv']);
            case 'excel':
                return response()->json(['download_url' => '/api/download/export.xlsx']);
            case 'pdf':
                return response()->json(['download_url' => '/api/download/export.pdf']);
            default:
                return response()->json($data);
        }
    });
    
    // Gestion des utilisateurs
    Route::get('/users', function () {
        return response()->json([
            'users' => \App\Models\User::select('id', 'name', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
        ]);
    });
    
    // Statistiques des demandes
    Route::get('/requests/stats', function () {
        return response()->json([
            'total' => rand(1000, 5000),
            'pending' => rand(50, 200),
            'completed' => rand(800, 1200),
            'cancelled' => rand(10, 50),
            'byStatus' => [
                'pending' => rand(50, 200),
                'in_progress' => rand(100, 300),
                'completed' => rand(800, 1200),
                'cancelled' => rand(10, 50)
            ],
            'byRegion' => [
                'Dakar' => rand(200, 800),
                'Thiès' => rand(100, 400),
                'Kaolack' => rand(80, 300),
                'Saint-Louis' => rand(60, 250),
                'Ziguinchor' => rand(40, 200)
            ]
        ]);
    });
    
    // Statistiques des stocks
    Route::get('/stock/stats', function () {
        return response()->json([
            'totalCapacity' => 50000,
            'currentStock' => rand(20000, 45000),
            'utilizationRate' => rand(40, 90),
            'byWarehouse' => [
                'Dakar' => rand(5000, 15000),
                'Thiès' => rand(3000, 10000),
                'Kaolack' => rand(2000, 8000),
                'Saint-Louis' => rand(1500, 6000),
                'Ziguinchor' => rand(1000, 5000)
            ],
            'alerts' => [
                'lowStock' => rand(0, 5),
                'expired' => rand(0, 3),
                'maintenance' => rand(0, 2)
            ]
        ]);
    });
    
    // Métriques de performance
    Route::get('/performance/metrics', function () {
        return response()->json([
            'responseTime' => rand(100, 500),
            'throughput' => rand(100, 1000),
            'errorRate' => rand(0, 5),
            'uptime' => 99.9,
            'cpuUsage' => rand(20, 80),
            'memoryUsage' => rand(30, 90),
            'diskUsage' => rand(10, 70)
        ]);
    });
    
    // Logs système
    Route::get('/system/logs', function (Request $request) {
        $level = $request->input('level', 'all');
        $limit = $request->input('limit', 100);
        
        // Simulation de logs
        $logs = [];
        $levels = ['info', 'warning', 'error', 'debug'];
        
        for ($i = 0; $i < $limit; $i++) {
            $logLevel = $levels[array_rand($levels)];
            
            if ($level === 'all' || $level === $logLevel) {
                $logs[] = [
                    'id' => $i + 1,
                    'level' => $logLevel,
                    'message' => 'Log message ' . ($i + 1),
                    'timestamp' => now()->subMinutes(rand(0, 1440))->toISOString(),
                    'context' => [
                        'user_id' => rand(1, 100),
                        'action' => 'user_action',
                        'ip' => '192.168.1.' . rand(1, 254)
                    ]
                ];
            }
        }
        
        return response()->json([
            'logs' => $logs,
            'total' => count($logs)
        ]);
    });
    
    // Configuration système
    Route::get('/system/config', function () {
        return response()->json([
            'app_name' => config('app.name'),
            'app_version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'cache' => config('cache.default'),
            'queue' => config('queue.default')
        ]);
    });
    
    // Test de connectivité
    Route::get('/test/connection', function () {
        return response()->json([
            'status' => 'connected',
            'timestamp' => now(),
            'server_time' => now()->toISOString(),
            'timezone' => config('app.timezone')
        ]);
    });
});

// Routes publiques pour les tests
Route::get('/test/status', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API fonctionnelle',
        'timestamp' => now()
    ]);
});

// Route de test pour les connexions
Route::post('/test/login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');
    
    // Test simple de connexion
    if ($email && $password) {
        return response()->json([
            'success' => true,
            'message' => 'Test de connexion réussi',
            'user' => [
                'email' => $email,
                'role' => 'test'
            ]
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Email et mot de passe requis'
    ], 400);
});