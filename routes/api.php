<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $email = strtolower($request->input('email'));
    $ip = $request->ip();

    // Rate limiting: 5 attempts per minute per IP, and 5 per minute per email
    $ipKey = 'api-login:ip:' . $ip;
    $emailKey = 'api-login:email:' . $email;

    if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 5)) {
        $seconds = max(
            RateLimiter::availableIn($ipKey),
            RateLimiter::availableIn($emailKey)
        );
        return response()->json([
            'success' => false,
            'message' => 'Trop de tentatives. Réessayez dans ' . $seconds . ' secondes.',
        ], 429, ['Retry-After' => $seconds]);
    }

    RateLimiter::hit($ipKey, 60);
    RateLimiter::hit($emailKey, 60);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        // Vérifier que le compte est actif
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été désactivé.'
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        RateLimiter::clear($ipKey);
        RateLimiter::clear($emailKey);

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
})->middleware('throttle:10,1');

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard et métriques
    Route::get('/dashboard/metrics', function () {
        $totalRequests = \App\Models\PublicRequest::count();
        $pendingRequests = \App\Models\PublicRequest::where('status', 'pending')->count();
        $completedRequests = \App\Models\PublicRequest::where('status', 'completed')->count();
        $totalStock = \App\Models\Stock::sum('quantity') ?? 0;
        $lowStockAlerts = \App\Models\Stock::where('quantity', '<', 100)->count();
        $activeUsers = \App\Models\User::where('is_active', true)->count();

        return response()->json([
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'completedRequests' => $completedRequests,
            'totalStock' => $totalStock,
            'lowStockAlerts' => $lowStockAlerts,
            'activeUsers' => $activeUsers
        ]);
    });

    // Analytics
    Route::get('/analytics/data', function () {
        $totalRequests = \App\Models\PublicRequest::count();
        $completedRequests = \App\Models\PublicRequest::where('status', 'completed')->count();
        $successRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 1) : 0;
        $activeUsers = \App\Models\User::where('is_active', true)->count();

        return response()->json([
            'overallPerformance' => $successRate,
            'activeUsers' => $activeUsers,
            'responseTime' => 0,
            'successRate' => $successRate,
            'errorRate' => 0,
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
                'value' => \App\Models\User::where('is_active', true)->count(),
                'animation' => 'smooth'
            ];
        }

        return response()->json([
            'updates' => $updates,
            'timestamp' => $currentTime
        ]);
    });

    // Notifications
    Route::get('/notifications', function (Request $request) {
        $notifications = \App\Models\Notification::where('user_id', $request->user()->id)
            ->orWhereNull('user_id')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'type', 'title', 'message', 'created_at']);

        return response()->json([
            'notifications' => $notifications
        ]);
    });

    // Suivi des collecteurs en temps réel
    Route::prefix('mobile')->group(function () {
        Route::post('/collectors/location', [\App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'updateLocation']);
        Route::get('/collectors/locations', [\App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'getActiveCollectors']);
    });

    // Rapports
    Route::get('/reports/daily', function () {
        $date = now()->format('Y-m-d');
        $requests = \App\Models\PublicRequest::whereDate('created_at', today())->count();
        $completed = \App\Models\PublicRequest::whereDate('created_at', today())->where('status', 'completed')->count();
        $pending = \App\Models\PublicRequest::whereDate('created_at', today())->where('status', 'pending')->count();

        return response()->json([
            'date' => $date,
            'metrics' => [
                'requests' => $requests,
                'completed' => $completed,
                'pending' => $pending,
                'errors' => 0
            ]
        ]);
    });

    Route::get('/reports/weekly', function () {
        $weekStart = now()->startOfWeek();
        $requests = \App\Models\PublicRequest::where('created_at', '>=', $weekStart)->count();
        $completed = \App\Models\PublicRequest::where('created_at', '>=', $weekStart)->where('status', 'completed')->count();
        $pending = \App\Models\PublicRequest::where('created_at', '>=', $weekStart)->where('status', 'pending')->count();

        return response()->json([
            'week' => now()->format('Y-W'),
            'metrics' => [
                'requests' => $requests,
                'completed' => $completed,
                'pending' => $pending,
                'errors' => 0
            ]
        ]);
    });

    Route::get('/reports/monthly', function () {
        $monthStart = now()->startOfMonth();
        $requests = \App\Models\PublicRequest::where('created_at', '>=', $monthStart)->count();
        $completed = \App\Models\PublicRequest::where('created_at', '>=', $monthStart)->where('status', 'completed')->count();
        $pending = \App\Models\PublicRequest::where('created_at', '>=', $monthStart)->where('status', 'pending')->count();

        return response()->json([
            'month' => now()->format('Y-m'),
            'metrics' => [
                'requests' => $requests,
                'completed' => $completed,
                'pending' => $pending,
                'errors' => 0
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

    // Gestion des utilisateurs (admin uniquement)
    Route::get('/users', function (Request $request) {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        return response()->json([
            'users' => \App\Models\User::select('id', 'name', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
        ]);
    });

    // Statistiques des demandes
    Route::get('/requests/stats', function () {
        $total = \App\Models\PublicRequest::count();
        $pending = \App\Models\PublicRequest::where('status', 'pending')->count();
        $completed = \App\Models\PublicRequest::where('status', 'completed')->count();
        $cancelled = \App\Models\PublicRequest::where('status', 'cancelled')->count();
        $inProgress = \App\Models\PublicRequest::where('status', 'in_progress')->count();

        $byRegion = \App\Models\PublicRequest::select('region', \DB::raw('count(*) as count'))
            ->groupBy('region')
            ->pluck('count', 'region')
            ->toArray();

        return response()->json([
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'byStatus' => [
                'pending' => $pending,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'cancelled' => $cancelled
            ],
            'byRegion' => $byRegion
        ]);
    });

    // Statistiques des stocks
    Route::get('/stock/stats', function () {
        $totalCapacity = \App\Models\Warehouse::sum('capacity') ?? 0;
        $currentStock = \App\Models\Stock::sum('quantity') ?? 0;
        $utilizationRate = $totalCapacity > 0 ? round(($currentStock / $totalCapacity) * 100, 1) : 0;

        $byWarehouse = \App\Models\Warehouse::join('stocks', 'warehouses.id', '=', 'stocks.warehouse_id')
            ->select('warehouses.name', \DB::raw('SUM(stocks.quantity) as total'))
            ->groupBy('warehouses.name')
            ->pluck('total', 'name')
            ->toArray();

        $lowStock = \App\Models\Stock::where('quantity', '<', 100)->count();

        return response()->json([
            'totalCapacity' => $totalCapacity,
            'currentStock' => $currentStock,
            'utilizationRate' => $utilizationRate,
            'byWarehouse' => $byWarehouse,
            'alerts' => [
                'lowStock' => $lowStock,
                'expired' => 0,
                'maintenance' => 0
            ]
        ]);
    });

    // Métriques de performance
    Route::get('/performance/metrics', function () {
        return response()->json([
            'responseTime' => 0,
            'throughput' => 0,
            'errorRate' => 0,
            'uptime' => 99.9,
            'cpuUsage' => 0,
            'memoryUsage' => 0,
            'diskUsage' => 0
        ]);
    });

    // Logs système (admin uniquement)
    Route::get('/system/logs', function (Request $request) {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $level = $request->input('level', 'all');
        $limit = $request->input('limit', 100);

        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logFile)) {
            $lines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -($limit * 2));
            $id = 1;
            foreach (array_reverse($lines) as $line) {
                if (preg_match('/^\[(.+?)\] (\w+)\.(\w+): (.+)/', $line, $m)) {
                    $logLevel = $m[3];
                    if ($level === 'all' || $level === $logLevel) {
                        $logs[] = [
                            'id' => $id++,
                            'level' => $logLevel,
                            'message' => $m[4],
                            'timestamp' => \Carbon\Carbon::parse($m[1])->toISOString(),
                            'context' => []
                        ];
                    }
                }
                if (count($logs) >= $limit) {
                    break;
                }
            }
        }

        return response()->json([
            'logs' => $logs,
            'total' => count($logs)
        ]);
    });

    // Configuration système (admin uniquement)
    Route::get('/system/config', function (Request $request) {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
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

    // Test de connectivité (local/testing uniquement)
    if (app()->environment('local', 'testing')) {
        Route::get('/test/connection', function () {
            return response()->json([
                'status' => 'connected',
                'timestamp' => now(),
                'server_time' => now()->toISOString(),
                'timezone' => config('app.timezone')
            ]);
        });
    }
});

// Chatbot IA (public, rate-limited)
Route::post('/chatbot/ask', [\App\Http\Controllers\Api\ChatbotController::class, 'ask']);
