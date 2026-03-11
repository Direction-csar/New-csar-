<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Notification;
use Carbon\Carbon;

class MonitoringService
{
    private $thresholds = [
        'cpu' => 80,      // %
        'memory' => 85,   // %
        'disk' => 90,     // %
        'response_time' => 3000, // ms
    ];

    /**
     * Vérifier l'état global du système
     */
    public function checkSystemHealth()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => Carbon::now(),
            'metrics' => [],
            'alerts' => []
        ];

        try {
            // Vérifier la base de données
            $dbHealth = $this->checkDatabase();
            $health['metrics']['database'] = $dbHealth;
            if (!$dbHealth['healthy']) {
                $health['status'] = 'unhealthy';
                $health['alerts'][] = 'Base de données: ' . $dbHealth['message'];
            }

            // Vérifier le système de fichiers
            $diskHealth = $this->checkDiskSpace();
            $health['metrics']['disk'] = $diskHealth;
            if (!$diskHealth['healthy']) {
                $health['status'] = 'warning';
                $health['alerts'][] = 'Disque: ' . $diskHealth['message'];
            }

            // Vérifier la mémoire
            $memoryHealth = $this->checkMemory();
            $health['metrics']['memory'] = $memoryHealth;
            if (!$memoryHealth['healthy']) {
                $health['status'] = 'warning';
                $health['alerts'][] = 'Mémoire: ' . $memoryHealth['message'];
            }

            // Vérifier les services
            $servicesHealth = $this->checkServices();
            $health['metrics']['services'] = $servicesHealth;

            // Stocker en cache pour 5 minutes
            Cache::put('system_health', $health, now()->addMinutes(5));

            // Créer des notifications si problèmes
            if ($health['status'] !== 'healthy') {
                $this->createHealthAlert($health);
            }

            return $health;

        } catch (\Exception $e) {
            Log::error('Erreur monitoring système', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => Carbon::now()
            ];
        }
    }

    /**
     * Vérifier la connexion et performance de la base de données
     */
    private function checkDatabase()
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = (microtime(true) - $start) * 1000;

            // Test de requête simple
            $result = DB::select('SELECT 1');
            
            $healthy = $responseTime < 1000; // Moins de 1 seconde

            return [
                'healthy' => $healthy,
                'response_time' => round($responseTime, 2),
                'message' => $healthy ? 'OK' : "Lent ({$responseTime}ms)",
                'connection' => 'active'
            ];

        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Connexion échouée: ' . $e->getMessage(),
                'connection' => 'failed'
            ];
        }
    }

    /**
     * Vérifier l'espace disque
     */
    private function checkDiskSpace()
    {
        try {
            $path = storage_path();
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);
            $usedSpace = $totalSpace - $freeSpace;
            $usedPercent = ($usedSpace / $totalSpace) * 100;

            $healthy = $usedPercent < $this->thresholds['disk'];

            return [
                'healthy' => $healthy,
                'total' => $this->formatBytes($totalSpace),
                'used' => $this->formatBytes($usedSpace),
                'free' => $this->formatBytes($freeSpace),
                'percent' => round($usedPercent, 2),
                'message' => $healthy ? 'OK' : "Espace disque faible ({$usedPercent}%)"
            ];

        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Erreur vérification disque: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier l'utilisation de la mémoire
     */
    private function checkMemory()
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->getMemoryLimit();
            $memoryPercent = ($memoryUsage / $memoryLimit) * 100;

            $healthy = $memoryPercent < $this->thresholds['memory'];

            return [
                'healthy' => $healthy,
                'usage' => $this->formatBytes($memoryUsage),
                'limit' => $this->formatBytes($memoryLimit),
                'percent' => round($memoryPercent, 2),
                'message' => $healthy ? 'OK' : "Mémoire élevée ({$memoryPercent}%)"
            ];

        } catch (\Exception $e) {
            return [
                'healthy' => true,
                'message' => 'Impossible de vérifier la mémoire'
            ];
        }
    }

    /**
     * Vérifier les services essentiels
     */
    private function checkServices()
    {
        $services = [];

        // Cache
        try {
            Cache::put('health_check', true, 10);
            $services['cache'] = Cache::get('health_check') === true ? 'OK' : 'FAILED';
        } catch (\Exception $e) {
            $services['cache'] = 'FAILED';
        }

        // Queue
        try {
            $queueSize = DB::table('jobs')->count();
            $services['queue'] = [
                'status' => 'OK',
                'pending_jobs' => $queueSize
            ];
        } catch (\Exception $e) {
            $services['queue'] = 'FAILED';
        }

        // Session
        try {
            $activeSessions = DB::table('sessions')->count();
            $services['sessions'] = [
                'status' => 'OK',
                'active' => $activeSessions
            ];
        } catch (\Exception $e) {
            $services['sessions'] = 'FAILED';
        }

        return $services;
    }

    /**
     * Créer une alerte de santé système
     */
    private function createHealthAlert($health)
    {
        $message = "Problèmes système détectés:\n";
        foreach ($health['alerts'] as $alert) {
            $message .= "- {$alert}\n";
        }

        Notification::create([
            'title' => "⚠️ Alerte Système - {$health['status']}",
            'message' => $message,
            'type' => $health['status'] === 'unhealthy' ? 'error' : 'warning',
            'read' => false
        ]);
    }

    /**
     * Obtenir les métriques de performance
     */
    public function getPerformanceMetrics($period = '24h')
    {
        try {
            $metrics = [
                'response_times' => $this->getResponseTimes($period),
                'error_rate' => $this->getErrorRate($period),
                'active_users' => $this->getActiveUsers($period),
                'requests_count' => $this->getRequestsCount($period),
            ];

            return $metrics;

        } catch (\Exception $e) {
            Log::error('Erreur métriques performance', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Temps de réponse moyen
     */
    private function getResponseTimes($period)
    {
        // À implémenter avec un APM (Application Performance Monitoring)
        return [
            'average' => 250, // ms
            'p95' => 500,
            'p99' => 1000
        ];
    }

    /**
     * Taux d'erreur
     */
    private function getErrorRate($period)
    {
        $hours = $this->periodToHours($period);
        $since = Carbon::now()->subHours($hours);

        $totalLogs = DB::table('audit_logs')
            ->where('created_at', '>=', $since)
            ->count();

        $errorLogs = DB::table('audit_logs')
            ->where('created_at', '>=', $since)
            ->where('action', 'LIKE', '%error%')
            ->count();

        $errorRate = $totalLogs > 0 ? ($errorLogs / $totalLogs) * 100 : 0;

        return [
            'rate' => round($errorRate, 2),
            'total_errors' => $errorLogs,
            'total_requests' => $totalLogs
        ];
    }

    /**
     * Utilisateurs actifs
     */
    private function getActiveUsers($period)
    {
        $hours = $this->periodToHours($period);
        $since = Carbon::now()->subHours($hours);

        return DB::table('sessions')
            ->where('last_activity', '>=', $since->timestamp)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Nombre de requêtes
     */
    private function getRequestsCount($period)
    {
        $hours = $this->periodToHours($period);
        $since = Carbon::now()->subHours($hours);

        return DB::table('audit_logs')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * Convertir période en heures
     */
    private function periodToHours($period)
    {
        $map = [
            '1h' => 1,
            '6h' => 6,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720
        ];

        return $map[$period] ?? 24;
    }

    /**
     * Obtenir la limite de mémoire PHP
     */
    private function getMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');
        
        if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
            $value = $matches[1];
            $unit = strtoupper($matches[2]);
            
            switch ($unit) {
                case 'G':
                    $value *= 1024 * 1024 * 1024;
                    break;
                case 'M':
                    $value *= 1024 * 1024;
                    break;
                case 'K':
                    $value *= 1024;
                    break;
            }
            
            return $value;
        }
        
        return 128 * 1024 * 1024; // 128MB par défaut
    }

    /**
     * Formater les octets
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Nettoyer les anciennes métriques
     */
    public function cleanOldMetrics($days = 30)
    {
        try {
            $deleted = DB::table('audit_logs')
                ->where('created_at', '<', Carbon::now()->subDays($days))
                ->delete();

            Log::info("Monitoring: {$deleted} anciennes métriques supprimées");

            return $deleted;

        } catch (\Exception $e) {
            Log::error('Erreur nettoyage métriques', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}











































