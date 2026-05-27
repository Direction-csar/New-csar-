<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Vérification de santé sécurité de la plateforme CSAR.
 *
 * Usage : php artisan security:healthcheck
 *
 * Vérifie :
 * - Configuration .env (APP_DEBUG, APP_ENV, HTTPS)
 * - Permissions des dossiers sensibles
 * - Présence de sauvegardes récentes
 * - Comptes admin avec 2FA non activée
 * - Comptes inactifs depuis longtemps
 */
class SecurityHealthCheck extends Command
{
    protected $signature = 'security:healthcheck {--json : Sortie au format JSON}';

    protected $description = 'Vérifie l\'état de sécurité global de la plateforme CSAR';

    public function handle(): int
    {
        $checks = [];
        $warnings = 0;
        $errors = 0;
        $jsonMode = (bool) $this->option('json');

        // 1. Configuration application
        if (!$jsonMode) $this->info('🔒 Vérification de la configuration...');

        if (config('app.debug') === true) {
            $checks['app_debug'] = ['status' => 'error', 'message' => 'APP_DEBUG=true en production est dangereux'];
            $errors++;
        } else {
            $checks['app_debug'] = ['status' => 'ok', 'message' => 'APP_DEBUG correctement désactivé'];
        }

        if (config('app.env') !== 'production') {
            $checks['app_env'] = ['status' => 'warning', 'message' => 'APP_ENV n\'est pas "production" (' . config('app.env') . ')'];
            $warnings++;
        } else {
            $checks['app_env'] = ['status' => 'ok', 'message' => 'APP_ENV=production'];
        }

        $appUrl = (string) config('app.url', '');
        if (!str_starts_with($appUrl, 'https://')) {
            $checks['app_url'] = ['status' => 'warning', 'message' => 'APP_URL ne commence pas par https://'];
            $warnings++;
        } else {
            $checks['app_url'] = ['status' => 'ok', 'message' => 'APP_URL en HTTPS'];
        }

        // 2. Caches Laravel
        if (!$jsonMode) $this->info('⚡ Vérification des caches...');

        $configCached = File::exists(base_path('bootstrap/cache/config.php'));
        $routesCached = File::exists(base_path('bootstrap/cache/routes-v7.php'));

        $checks['config_cache'] = $configCached
            ? ['status' => 'ok', 'message' => 'Config en cache']
            : ['status' => 'warning', 'message' => 'Config non cachée (php artisan config:cache)'];
        if (!$configCached) $warnings++;

        $checks['routes_cache'] = $routesCached
            ? ['status' => 'ok', 'message' => 'Routes en cache']
            : ['status' => 'warning', 'message' => 'Routes non cachées (php artisan route:cache)'];
        if (!$routesCached) $warnings++;

        // 3. Permissions
        if (!$jsonMode) $this->info('📁 Vérification des permissions...');

        $storagePath = storage_path();
        if (is_writable($storagePath)) {
            $checks['storage_writable'] = ['status' => 'ok', 'message' => 'storage/ accessible en écriture'];
        } else {
            $checks['storage_writable'] = ['status' => 'error', 'message' => 'storage/ non accessible en écriture'];
            $errors++;
        }

        // 4. Sauvegardes
        if (!$jsonMode) $this->info('💾 Vérification des sauvegardes...');

        $backupDir = '/var/backups/csar/db';
        if (is_dir($backupDir)) {
            $latestBackup = collect(File::files($backupDir))
                ->sortByDesc(fn($f) => $f->getMTime())
                ->first();

            if ($latestBackup) {
                $age = now()->diffInHours(\Carbon\Carbon::createFromTimestamp($latestBackup->getMTime()));
                if ($age > 48) {
                    $checks['backup'] = ['status' => 'error', 'message' => "Dernière sauvegarde > 48h ($age h)"];
                    $errors++;
                } elseif ($age > 26) {
                    $checks['backup'] = ['status' => 'warning', 'message' => "Dernière sauvegarde > 26h ($age h)"];
                    $warnings++;
                } else {
                    $checks['backup'] = ['status' => 'ok', 'message' => "Sauvegarde récente ($age h)"];
                }
            } else {
                $checks['backup'] = ['status' => 'error', 'message' => 'Aucune sauvegarde trouvée'];
                $errors++;
            }
        } else {
            $checks['backup'] = ['status' => 'warning', 'message' => "Dossier $backupDir n'existe pas"];
            $warnings++;
        }

        // 5. 2FA admins
        if (!$jsonMode) $this->info('🔐 Vérification 2FA des comptes sensibles...');

        try {
            $sensitiveRoles = ['admin', 'dg', 'drh'];
            $usersWithoutTwoFactor = DB::table('users')
                ->whereIn('role', $sensitiveRoles)
                ->where(function ($q) {
                    $q->whereNull('two_factor_enabled')
                      ->orWhere('two_factor_enabled', 0);
                })
                ->count();

            if ($usersWithoutTwoFactor > 0) {
                $checks['2fa_admins'] = [
                    'status' => 'warning',
                    'message' => "$usersWithoutTwoFactor compte(s) admin/dg/drh sans 2FA",
                ];
                $warnings++;
            } else {
                $checks['2fa_admins'] = [
                    'status' => 'ok',
                    'message' => 'Tous les comptes sensibles ont la 2FA activée',
                ];
            }
        } catch (\Exception $e) {
            $checks['2fa_admins'] = ['status' => 'warning', 'message' => 'Impossible de vérifier 2FA : ' . $e->getMessage()];
            $warnings++;
        }

        // 6. Comptes inactifs
        try {
            $inactive = DB::table('users')
                ->whereNotNull('last_login_at')
                ->where('last_login_at', '<', now()->subDays(90))
                ->where('is_active', 1)
                ->count();

            if ($inactive > 0) {
                $checks['inactive_accounts'] = [
                    'status' => 'warning',
                    'message' => "$inactive compte(s) actifs sans connexion depuis 90j",
                ];
                $warnings++;
            } else {
                $checks['inactive_accounts'] = ['status' => 'ok', 'message' => 'Pas de compte inactif > 90j'];
            }
        } catch (\Exception $e) {
            // ignorer si la colonne n'existe pas
        }

        // === Affichage ===
        if ($jsonMode) {
            $this->line(json_encode([
                'errors' => $errors,
                'warnings' => $warnings,
                'checks' => $checks,
            ], JSON_PRETTY_PRINT));
            return $errors > 0 ? 1 : 0;
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('  RAPPORT DE SÉCURITÉ CSAR');
        $this->info('═══════════════════════════════════════════════');

        foreach ($checks as $key => $check) {
            $icon = match ($check['status']) {
                'ok' => '✅',
                'warning' => '⚠️ ',
                'error' => '❌',
                default => '•',
            };
            $this->line("$icon  [{$key}] {$check['message']}");
        }

        $this->newLine();
        $this->info('───────────────────────────────────────────────');
        $this->info("  ✅ OK : " . collect($checks)->where('status', 'ok')->count());
        $this->warn("  ⚠️  Warnings : $warnings");
        $this->error("  ❌ Errors : $errors");
        $this->info('───────────────────────────────────────────────');

        return $errors > 0 ? 1 : 0;
    }
}
