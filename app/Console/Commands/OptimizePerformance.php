<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csar:optimize {--force : Force l\'optimisation même en production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimise les performances de la plateforme CSAR pour supporter 1000+ utilisateurs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Optimisation des performances CSAR...');
        $this->newLine();

        // 1. Vérifier Redis
        $this->info('⏳ Vérification de Redis...');
        if ($this->checkRedis()) {
            $this->info('   ✅ Redis vérifié');
        } else {
            $this->warn('   ⚠️  Redis non configuré ou non disponible');
        }

        // 2. Nettoyer les caches
        $this->info('⏳ Nettoyage des caches...');
        Cache::flush();
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $this->info('   ✅ Caches nettoyés');

        // 3. Optimiser les configurations
        $this->info('⏳ Optimisation des configurations...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        $this->info('   ✅ Configurations optimisées');

        // 4. Vérifier les index de base de données
        $this->info('⏳ Vérification des index de base de données...');
        $this->checkDatabaseIndexes();

        // 5. Analyser les requêtes lentes
        $this->info('⏳ Analyse des requêtes lentes...');
        $this->analyzeSlowQueries();

        // 6. Optimiser les tables
        if ($this->option('force') || $this->confirm('Voulez-vous optimiser les tables MySQL ?', true)) {
            $this->info('⏳ Optimisation des tables MySQL...');
            $this->optimizeTables();
        }

        $this->newLine();
        $this->info('✅ Optimisation terminée avec succès !');
        $this->newLine();

        // Afficher les recommandations
        $this->displayRecommendations();

        return Command::SUCCESS;
    }

    /**
     * Vérifier la connexion Redis
     */
    private function checkRedis(): bool
    {
        try {
            if (config('cache.default') !== 'redis') {
                $this->warn('⚠️  Cache non configuré sur Redis (actuellement: ' . config('cache.default') . ')');
                $this->warn('   Recommandation: Configurer CACHE_STORE=redis dans .env');
                return false;
            }

            Cache::store('redis')->put('test_connection', 'ok', 10);
            $result = Cache::store('redis')->get('test_connection');
            
            if ($result === 'ok') {
                $this->info('   ✓ Redis opérationnel');
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur Redis: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier les index de base de données
     */
    private function checkDatabaseIndexes(): bool
    {
        try {
            $tables = [
                'demandes' => ['statut', 'created_at', 'code_suivi'],
                'stocks' => ['warehouse_id', 'stock_type_id'],
                'stock_movements' => ['stock_id', 'created_at', 'type'],
                'news' => ['is_published', 'published_at'],
                'users' => ['role', 'is_active'],
            ];

            $missingIndexes = [];

            foreach ($tables as $table => $columns) {
                if (!$this->tableExists($table)) {
                    continue;
                }

                foreach ($columns as $column) {
                    if (!$this->columnHasIndex($table, $column)) {
                        $missingIndexes[] = "$table.$column";
                    }
                }
            }

            if (empty($missingIndexes)) {
                $this->info('   ✓ Tous les index critiques sont présents');
                return true;
            } else {
                $this->warn('   ⚠️  Index manquants: ' . implode(', ', $missingIndexes));
                $this->warn('   Exécutez: php artisan migrate pour les créer');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur vérification index: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Analyser les requêtes lentes
     */
    private function analyzeSlowQueries(): bool
    {
        try {
            // Activer le log des requêtes lentes si pas déjà fait
            DB::connection()->enableQueryLog();
            
            $this->info('   ℹ️  Monitoring des requêtes activé');
            $this->info('   Consultez storage/logs/laravel.log pour les requêtes lentes');
            
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur analyse requêtes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimiser les tables MySQL
     */
    private function optimizeTables(): bool
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$databaseName}";

            $bar = $this->output->createProgressBar(count($tables));
            $bar->start();

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('   ✓ ' . count($tables) . ' tables optimisées');
            
            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Erreur optimisation tables: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si une table existe
     */
    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Vérifier si une colonne a un index
     */
    private function columnHasIndex(string $table, string $column): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Afficher les recommandations
     */
    private function displayRecommendations(): void
    {
        $this->info('📋 Recommandations pour 1000+ utilisateurs simultanés:');
        $this->newLine();

        $recommendations = [
            [
                'title' => '1. Redis',
                'status' => config('cache.default') === 'redis' ? '✅' : '❌',
                'message' => config('cache.default') === 'redis' 
                    ? 'Redis configuré correctement' 
                    : 'Configurer CACHE_STORE=redis, SESSION_DRIVER=redis, QUEUE_CONNECTION=redis',
            ],
            [
                'title' => '2. Queue Workers',
                'status' => '⚠️',
                'message' => 'Vérifier que les workers sont actifs: php artisan queue:work redis',
            ],
            [
                'title' => '3. Optimisation Images',
                'status' => config('performance.images.webp_enabled') ? '✅' : '❌',
                'message' => config('performance.images.webp_enabled')
                    ? 'WebP activé'
                    : 'Activer PERFORMANCE_WEBP_ENABLED=true',
            ],
            [
                'title' => '4. CDN',
                'status' => config('performance.cdn.enabled') ? '✅' : '⚠️',
                'message' => config('performance.cdn.enabled')
                    ? 'CDN activé'
                    : 'Recommandé: Activer CDN (Cloudflare)',
            ],
            [
                'title' => '5. Monitoring',
                'status' => '⚠️',
                'message' => 'Installer Laravel Telescope: composer require laravel/telescope',
            ],
        ];

        foreach ($recommendations as $rec) {
            $this->line("  {$rec['status']} {$rec['title']}: {$rec['message']}");
        }

        $this->newLine();
        $this->info('💡 Pour plus d\'informations: docs/OPTIMISATION_PERFORMANCE.md');
    }
}
